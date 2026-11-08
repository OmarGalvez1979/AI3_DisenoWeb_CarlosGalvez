<?php
/**
 * conexion.php
 * Módulo de conexión y configuración a la base de datos mediante PDO.
 *
 * - Credenciales separadas del resto del código.
 * - Si existe config/config.local.php (ignorado por Git) se usan sus valores;
 *   en caso contrario, los valores por defecto de XAMPP.
 * - Errores capturados con try-catch: se registran en logs/ y al usuario
 *   solo se le muestra un mensaje genérico (nunca credenciales).
 *
 * @author Carlos Omar Galvez
 */

declare(strict_types=1);

final class Conexion
{
    private static ?PDO $instancia = null;

    /** Configuración por defecto (entorno local XAMPP). */
    private const CONFIG_DEFECTO = [
        'host'    => '127.0.0.1',
        'puerto'  => 3306,
        'bd'      => 'db_sistema_web',
        'usuario' => 'root',
        'clave'   => '',
        'charset' => 'utf8mb4',
    ];

    private function __construct() {}   // Patrón Singleton: no se instancia
    private function __clone() {}

    /** Devuelve la configuración combinando valores locales y por defecto. */
    private static function configuracion(): array
    {
        $local = __DIR__ . '/config.local.php';
        $extra = is_file($local) ? (array) require $local : [];
        return array_merge(self::CONFIG_DEFECTO, $extra);
    }

    /**
     * Obtiene la única conexión PDO de la aplicación.
     * @throws RuntimeException con mensaje genérico si no se puede conectar.
     */
    public static function obtener(): PDO
    {
        if (self::$instancia instanceof PDO) {
            return self::$instancia;
        }

        $cfg = self::configuracion();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['puerto'], $cfg['bd'], $cfg['charset']
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // errores como excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,                  // sentencias preparadas reales en el servidor
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ];

        try {
            self::$instancia = new PDO($dsn, $cfg['usuario'], $cfg['clave'], $opciones);
        } catch (PDOException $e) {
            self::registrarError($e);
            // Mensaje genérico: no expone host, usuario, clave ni detalle técnico.
            throw new RuntimeException('No fue posible conectar con la base de datos. Intente más tarde.');
        }

        return self::$instancia;
    }

    /** Registra el detalle técnico del error en logs/errores.log (fuera de la vista del usuario). */
    public static function registrarError(Throwable $e): void
    {
        $dir = dirname(__DIR__) . '/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $linea = sprintf("[%s] %s: %s en %s:%d\n",
            date('Y-m-d H:i:s'), get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        error_log($linea, 3, $dir . '/errores.log');
    }
}
