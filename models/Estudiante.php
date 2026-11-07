<?php


declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

final class Estudiante
{
    public const CARRERAS = [
        'Ingeniería en Sistemas Inteligentes',
        'Ingeniería en Ciberseguridad',
        'Ingeniería en Software',
        'Ciencia de Datos e Inteligencia Artificial',
        'Administración de Empresas',
        'Marketing',
        'Derecho',
        'Psicología',
    ];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Conexion::obtener();
    }

    /* =========================================================
     *  VALIDACIÓN DEL LADO DEL SERVIDOR
     * ========================================================= */

    /**
     * Normaliza y valida los datos recibidos del formulario.
     * @return array{0: array, 1: array} [datosLimpios, errores]
     */
    public static function validar(array $entrada): array
    {
        $d = [
            'cedula'           => trim((string) ($entrada['cedula'] ?? '')),
            'nombres'          => trim(preg_replace('/\s+/u', ' ', (string) ($entrada['nombres'] ?? ''))),
            'apellidos'        => trim(preg_replace('/\s+/u', ' ', (string) ($entrada['apellidos'] ?? ''))),
            'correo'           => mb_strtolower(trim((string) ($entrada['correo'] ?? ''))),
            'telefono'         => trim((string) ($entrada['telefono'] ?? '')),
            'fecha_nacimiento' => trim((string) ($entrada['fecha_nacimiento'] ?? '')),
            'carrera'          => trim((string) ($entrada['carrera'] ?? '')),
            'semestre'         => trim((string) ($entrada['semestre'] ?? '')),
        ];
        $err = [];

        if (!self::cedulaValida($d['cedula'])) {
            $err['cedula'] = 'Ingrese una cédula ecuatoriana válida de 10 dígitos.';
        }
        $patronNombre = '/^[\p{L}][\p{L} \'\-]{1,79}$/u';
        if (!preg_match($patronNombre, $d['nombres'])) {
            $err['nombres'] = 'Los nombres deben tener entre 2 y 80 letras.';
        }
        if (!preg_match($patronNombre, $d['apellidos'])) {
            $err['apellidos'] = 'Los apellidos deben tener entre 2 y 80 letras.';
        }
        if (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['correo']) > 120) {
            $err['correo'] = 'Ingrese un correo electrónico válido.';
        }
        if (!preg_match('/^09\d{8}$/', $d['telefono'])) {
            $err['telefono'] = 'El celular debe tener el formato 09XXXXXXXX.';
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $d['fecha_nacimiento']);
        if (!$fecha || $fecha->format('Y-m-d') !== $d['fecha_nacimiento']) {
            $err['fecha_nacimiento'] = 'Ingrese una fecha válida.';
        } else {
            $edad = $fecha->diff(new DateTimeImmutable('today'))->y;
            if ($fecha > new DateTimeImmutable('today') || $edad < 16 || $edad > 90) {
                $err['fecha_nacimiento'] = 'La edad debe estar entre 16 y 90 años.';
            }
        }
        if (!in_array($d['carrera'], self::CARRERAS, true)) {
            $err['carrera'] = 'Seleccione una carrera de la lista.';
        }
        if (filter_var($d['semestre'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]) === false) {
            $err['semestre'] = 'El semestre debe estar entre 1 y 10.';
        } else {
            $d['semestre'] = (int) $d['semestre'];
        }

        return [$d, $err];
    }

    /** Algoritmo módulo 10 del Registro Civil del Ecuador. */
    public static function cedulaValida(string $cedula): bool
    {
        if (!preg_match('/^\d{10}$/', $cedula)) {
            return false;
        }
        $provincia = (int) substr($cedula, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            return false;
        }
        if ((int) $cedula[2] > 5) {
            return false;
        }
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $v = (int) $cedula[$i] * ($i % 2 === 0 ? 2 : 1);
            $suma += $v > 9 ? $v - 9 : $v;
        }
        $verificador = (10 - $suma % 10) % 10;
        return $verificador === (int) $cedula[9];
    }

    /* =========================================================
     *  CREATE
     * ========================================================= */

    /** Verifica duplicados de cédula o correo (sentencia preparada). */
    public function existeDuplicado(string $cedula, string $correo): array
    {
        $sql = 'SELECT cedula, correo FROM estudiantes WHERE cedula = :cedula OR correo = :correo';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cedula' => $cedula, ':correo' => $correo]);

        $dup = [];
        foreach ($stmt->fetchAll() as $fila) {
            if ($fila['cedula'] === $cedula) { $dup['cedula'] = 'Esta cédula ya está registrada.'; }
            if ($fila['correo'] === $correo) { $dup['correo'] = 'Este correo ya está registrado.'; }
        }
        return $dup;
    }

    /**
     * Inserta un estudiante con SENTENCIA PREPARADA.
     * Los datos viajan separados del SQL (bindValue), por lo que un valor
     * como  ' OR 1=1 --  se guarda como texto y nunca se ejecuta.
     */
    public function insertar(array $d, ?string $ip = null): int
    {
        $sql = 'INSERT INTO estudiantes
                    (cedula, nombres, apellidos, correo, telefono,
                     fecha_nacimiento, carrera, semestre, ip_registro)
                VALUES
                    (:cedula, :nombres, :apellidos, :correo, :telefono,
                     :fecha_nacimiento, :carrera, :semestre, :ip)';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cedula',           $d['cedula'],           PDO::PARAM_STR);
        $stmt->bindValue(':nombres',          $d['nombres'],          PDO::PARAM_STR);
        $stmt->bindValue(':apellidos',        $d['apellidos'],        PDO::PARAM_STR);
        $stmt->bindValue(':correo',           $d['correo'],           PDO::PARAM_STR);
        $stmt->bindValue(':telefono',         $d['telefono'],         PDO::PARAM_STR);
        $stmt->bindValue(':fecha_nacimiento', $d['fecha_nacimiento'], PDO::PARAM_STR);
        $stmt->bindValue(':carrera',          $d['carrera'],          PDO::PARAM_STR);
        $stmt->bindValue(':semestre',         $d['semestre'],         PDO::PARAM_INT);
        $stmt->bindValue(':ip',               $ip,                    $ip === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /* =========================================================
     *  READ
     * ========================================================= */

    /** Lista estudiantes; el filtro de búsqueda también usa parámetros enlazados. */
    public function listar(string $busqueda = ''): array
    {
        $sql = 'SELECT id_estudiante, cedula, nombres, apellidos, correo, telefono,
                       fecha_nacimiento, carrera, semestre, estado, fecha_creacion
                  FROM estudiantes';
        $params = [];

        if ($busqueda !== '') {
            $sql .= ' WHERE cedula LIKE :b1 OR nombres LIKE :b2 OR apellidos LIKE :b3
                         OR correo LIKE :b4 OR carrera LIKE :b5';
            $like = '%' . addcslashes($busqueda, '%_\\') . '%';
            $params = [':b1' => $like, ':b2' => $like, ':b3' => $like, ':b4' => $like, ':b5' => $like];
        }
        $sql .= ' ORDER BY fecha_creacion DESC, id_estudiante DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contar(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM estudiantes')->fetchColumn();
    }
}
