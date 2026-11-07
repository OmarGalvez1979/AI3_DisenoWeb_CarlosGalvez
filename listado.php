<?php


declare(strict_types=1);

require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/models/Estudiante.php';

$busqueda = trim((string) ($_GET['q'] ?? ''));
$busqueda = mb_substr($busqueda, 0, 60);
$nuevoId  = filter_input(INPUT_GET, 'nuevo', FILTER_VALIDATE_INT) ?: 0;
$flash    = flash_obtener();

$registros = [];
$total = 0;
$errorGeneral = null;

try {
    $modelo    = new Estudiante();
    $registros = $modelo->listar($busqueda);
    $total     = $modelo->contar();
} catch (Throwable $ex) {
    Conexion::registrarError($ex);
    $errorGeneral = $ex instanceof RuntimeException
        ? $ex->getMessage()
        : 'No fue posible consultar los registros. Intente más tarde.';
}

$titulo = 'Consultar estudiantes';
$activo = 'listado';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta">
    <div class="cabecera-listado">
        <div>
            <h1>Estudiantes registrados</h1>
            <p class="subtitulo">
                Mostrando <strong><?= e(count($registros)) ?></strong> de <strong><?= e($total) ?></strong> registros
                <?php if ($busqueda !== ''): ?> para «<?= e($busqueda) ?>»<?php endif; ?>
            </p>
        </div>
        <form class="buscador" method="get" action="listado.php" role="search">
            <label for="q" class="oculto">Buscar</label>
            <input type="search" id="q" name="q" value="<?= e($busqueda) ?>" maxlength="60"
                   placeholder="Buscar por cédula, nombre, correo o carrera">
            <button type="submit" class="boton">Buscar</button>
        </form>
    </div>

    <?php if ($flash): ?>
        <div class="alerta alerta--<?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensaje']) ?></div>
    <?php endif; ?>
    <?php if ($errorGeneral): ?>
        <div class="alerta alerta--error" role="alert"><?= e($errorGeneral) ?></div>
    <?php endif; ?>

    <?php if (!$registros && !$errorGeneral): ?>
        <p class="vacio">No hay registros para mostrar. <a href="registro.php">Registre el primero</a>.</p>
    <?php elseif ($registros): ?>
        <div class="tabla-responsiva">
            <table class="tabla">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Cédula</th>
                    <th scope="col">Apellidos y nombres</th>
                    <th scope="col">Correo</th>
                    <th scope="col">Celular</th>
                    <th scope="col">Nacimiento</th>
                    <th scope="col">Carrera</th>
                    <th scope="col">Sem.</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Fecha de creación</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr class="<?= (int) $r['id_estudiante'] === $nuevoId ? 'resaltado' : '' ?>">
                        <td data-label="ID"><?= e($r['id_estudiante']) ?></td>
                        <td data-label="Cédula"><?= e($r['cedula']) ?></td>
                        <td data-label="Estudiante"><?= e($r['apellidos'] . ', ' . $r['nombres']) ?></td>
                        <td data-label="Correo"><?= e($r['correo']) ?></td>
                        <td data-label="Celular"><?= e($r['telefono']) ?></td>
                        <td data-label="Nacimiento"><?= e(date('d/m/Y', strtotime($r['fecha_nacimiento']))) ?></td>
                        <td data-label="Carrera"><?= e($r['carrera']) ?></td>
                        <td data-label="Semestre"><?= e($r['semestre']) ?></td>
                        <td data-label="Estado"><span class="estado estado--<?= e(strtolower($r['estado'])) ?>"><?= e($r['estado']) ?></span></td>
                        <td data-label="Creado"><?= e(date('d/m/Y H:i', strtotime($r['fecha_creacion']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
