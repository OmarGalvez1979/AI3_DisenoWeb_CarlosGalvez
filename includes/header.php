<?php
/** @var string $titulo  @var string $activo */
$titulo = $titulo ?? 'Sistema Web';
$activo = $activo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Carlos Omar Galvez">
    <title><?= e($titulo) ?> | Sistema de Registro Estudiantil</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
<header class="barra">
    <div class="contenedor barra__interior">
        <a class="marca" href="index.php">
            <span class="marca__logo" aria-hidden="true">SE</span>
            <span>Sistema de Registro Estudiantil</span>
        </a>
        <nav class="menu" aria-label="Navegación principal">
            <a href="registro.php" class="<?= $activo === 'registro' ? 'activo' : '' ?>">Registrar</a>
            <a href="listado.php" class="<?= $activo === 'listado' ? 'activo' : '' ?>">Consultar</a>
        </nav>
    </div>
</header>
<main class="contenedor">
