<?php
/**
 * Header común para el panel de administración
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $page_title ?? 'Admin Panel' ?> - Dr. Omar Valdez</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="/admin/assets/admin.css">
    <link rel="stylesheet" href="/admin/assets/admin-tables.css">
    <link rel="stylesheet" href="/admin/assets/admin-modals.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
