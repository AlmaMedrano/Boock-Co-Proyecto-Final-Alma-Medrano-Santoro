<?php
require_once __DIR__ . '/funciones.php';
$pageTitle = $pageTitle ?? 'Boock&Co';
$user = current_user();
$isAdmin = $user && (($user['rol'] ?? '') === 'admin');
$isAdminArea = strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false && $isAdmin;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Boock&Co</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="topbar">
    <?php if (!$isAdminArea): ?><button class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false">☰</button><?php else: ?><span class="admin-top-label">ADMINISTRACIÓN</span><?php endif; ?>
    <a class="brand" href="<?= BASE_URL ?>/<?= $isAdminArea ? 'admin/index.php' : 'tienda.php' ?>"><span class="brand-mark">B</span><span>Boock<span class="brand-amp">&</span>Co<small>un rincón para leer</small></span></a>
    <nav class="top-actions">
        <?php if ($user): ?>
            <?php if ($isAdminArea): ?>
                <a href="<?= BASE_URL ?>/admin/index.php" class="nav-action admin-link">Organización</a>
                <a href="<?= BASE_URL ?>/admin/index.php?seccion=usuarios" class="nav-action">Usuarios</a>
                <a href="<?= BASE_URL ?>/admin/index.php?seccion=productos" class="nav-action">Productos</a>
                <a href="<?= BASE_URL ?>/tienda.php" class="nav-action">Ver tienda</a>
                <a href="<?= BASE_URL ?>/logout.php" class="nav-action">Cerrar sesión</a>
            <?php else: ?>
                <?php if ($isAdmin): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="nav-action admin-link">Administración</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/carrito.php" class="nav-action">♡ Carrito</a>
                <a href="<?= BASE_URL ?>/perfil.php" class="nav-action">Mi cuenta</a>
                <a href="<?= BASE_URL ?>/logout.php" class="nav-action">Salir</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</header>
<?php if ($user && !$isAdminArea): ?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-heading">Explorá la tienda</div>
    <?php foreach (category_map() as $slug => $cat): ?>
        <a href="<?= BASE_URL ?>/<?= e($cat['archivo']) ?>"><?= e($cat['nombre']) ?></a>
    <?php endforeach; ?>
    <div class="sidebar-divider"></div>
    <a href="<?= BASE_URL ?>/tienda.php">Inicio de la tienda</a>
    <?php if ($isAdmin): ?>
        
        <a href="<?= BASE_URL ?>/admin/index.php">Panel de organización</a>
    <?php endif; ?>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php endif; ?>
<main class="main-content">
<?php show_flash(); ?>
