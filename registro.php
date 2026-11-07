<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/models/Estudiante.php';

$datos   = [];
$errores = [];
$errorGeneral = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $errorGeneral = 'La sesión del formulario expiró. Recargue la página e intente de nuevo.';
    } else {
        [$datos, $errores] = Estudiante::validar($_POST);

        if (!$errores) {
            try {
                $modelo  = new Estudiante();
                $errores = $modelo->existeDuplicado($datos['cedula'], $datos['correo']);

                if (!$errores) {
                    $id = $modelo->insertar($datos, $_SERVER['REMOTE_ADDR'] ?? null);
                    flash('exito', "Estudiante registrado correctamente (ID #$id).");
                    header('Location: listado.php?nuevo=' . $id, true, 303); // Post/Redirect/Get
                    exit;
                }
            } catch (Throwable $ex) {
                Conexion::registrarError($ex);
                $errorGeneral = $ex instanceof RuntimeException
                    ? $ex->getMessage()
                    : 'Ocurrió un error al guardar el registro. Intente más tarde.';
            }
        }
    }
}

/** Valor previo del campo, ya escapado para HTML. */
$v = static fn(string $campo): string => e($datos[$campo] ?? '');
/** Atributos de error accesibles para un campo. */
$inv = static fn(string $campo): string => isset($errores[$campo])
    ? 'aria-invalid="true" aria-describedby="err-' . $campo . '"' : '';

$titulo = 'Registrar estudiante';
$activo = 'registro';
require __DIR__ . '/includes/header.php';
?>
<section class="tarjeta">
    <h1>Registro de estudiante</h1>
    <p class="subtitulo">Complete los datos. Los campos marcados con <span class="req">*</span> son obligatorios.</p>

    <?php if ($errorGeneral): ?>
        <div class="alerta alerta--error" role="alert"><?= e($errorGeneral) ?></div>
    <?php elseif ($errores): ?>
        <div class="alerta alerta--error" role="alert">Revise los campos marcados en rojo.</div>
    <?php endif; ?>

    <form id="form-registro" method="post" action="registro.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="rejilla">
            <div class="campo">
                <label for="cedula">Cédula <span class="req">*</span></label>
                <input type="text" id="cedula" name="cedula" value="<?= $v('cedula') ?>"
                       required inputmode="numeric" pattern="\d{10}" maxlength="10"
                       placeholder="0912345678" autocomplete="off" <?= $inv('cedula') ?>>
                <small class="error" id="err-cedula"><?= e($errores['cedula'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="correo">Correo electrónico <span class="req">*</span></label>
                <input type="email" id="correo" name="correo" value="<?= $v('correo') ?>"
                       required maxlength="120" placeholder="nombre@ecotec.edu.ec" autocomplete="email" <?= $inv('correo') ?>>
                <small class="error" id="err-correo"><?= e($errores['correo'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="nombres">Nombres <span class="req">*</span></label>
                <input type="text" id="nombres" name="nombres" value="<?= $v('nombres') ?>"
                       required minlength="2" maxlength="80" autocomplete="given-name" <?= $inv('nombres') ?>>
                <small class="error" id="err-nombres"><?= e($errores['nombres'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="apellidos">Apellidos <span class="req">*</span></label>
                <input type="text" id="apellidos" name="apellidos" value="<?= $v('apellidos') ?>"
                       required minlength="2" maxlength="80" autocomplete="family-name" <?= $inv('apellidos') ?>>
                <small class="error" id="err-apellidos"><?= e($errores['apellidos'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="telefono">Celular <span class="req">*</span></label>
                <input type="tel" id="telefono" name="telefono" value="<?= $v('telefono') ?>"
                       required pattern="09\d{8}" maxlength="10" placeholder="0991234567"
                       inputmode="numeric" autocomplete="tel" <?= $inv('telefono') ?>>
                <small class="error" id="err-telefono"><?= e($errores['telefono'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="fecha_nacimiento">Fecha de nacimiento <span class="req">*</span></label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $v('fecha_nacimiento') ?>"
                       required max="<?= e(date('Y-m-d', strtotime('-16 years'))) ?>" <?= $inv('fecha_nacimiento') ?>>
                <small class="error" id="err-fecha_nacimiento"><?= e($errores['fecha_nacimiento'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="carrera">Carrera <span class="req">*</span></label>
                <select id="carrera" name="carrera" required <?= $inv('carrera') ?>>
                    <option value="">— Seleccione —</option>
                    <?php foreach (Estudiante::CARRERAS as $c): ?>
                        <option value="<?= e($c) ?>" <?= ($datos['carrera'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="error" id="err-carrera"><?= e($errores['carrera'] ?? '') ?></small>
            </div>

            <div class="campo">
                <label for="semestre">Semestre <span class="req">*</span></label>
                <input type="number" id="semestre" name="semestre" value="<?= $v('semestre') ?>"
                       required min="1" max="10" step="1" <?= $inv('semestre') ?>>
                <small class="error" id="err-semestre"><?= e($errores['semestre'] ?? '') ?></small>
            </div>
        </div>

        <div class="acciones">
            <button type="reset" class="boton boton--secundario">Limpiar</button>
            <button type="submit" class="boton">Guardar registro</button>
        </div>
    </form>
</section>
<script src="assets/js/validacion.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
