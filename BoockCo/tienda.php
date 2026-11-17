<?php
require_once __DIR__ . '/includes/funciones.php';
require_login();
$pageTitle = 'Inicio';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-hero">
    <div class="welcome-copy"><p class="eyebrow">UN MUNDO ENTRE PÁGINAS</p><h1>Encontrá tu<br><em>próxima historia.</em></h1><p>Libros, accesorios y pequeños detalles para hacer de cada lectura un momento especial.</p><a class="btn" href="<?= BASE_URL ?>/categoria_libros.php">Explorar libros</a></div>
    <div class="welcome-art"><div class="art-circle"></div><div class="book-stack"><div class="book book-one">un mundo<br>de historias</div><div class="book book-two"></div><div class="book book-three"></div></div><span class="art-note">lee · sueña · descubre</span></div>
</section>
<section class="home-note"><span>♡</span><div><h2>Un espacio hecho para vos</h2><p>Usá el menú de la esquina para recorrer nuestras categorías. Cada objeto está pensado para acompañar tus momentos favoritos.</p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php
require __DIR__ . '/admin/_auth.php';

$seccion = $_GET['seccion'] ?? 'panel';
$mensaje = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $tipo = $_POST['tipo'] ?? '';
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0 && $tipo === 'usuario') {
        $q = db()->prepare("SELECT email FROM usuarios WHERE id_usuario = ?");
        $q->execute([$id]);
        $u = $q->fetch();
        if ($u) {
            if ($accion === 'archivar') {
                db()->prepare("UPDATE usuarios SET estado = 'suspendido' WHERE id_usuario = ?")->execute([$id]);
                admin_log('usuarios', 'cambiar_estado', $id, "Usuario archivado: {$u['email']}");
                flash('success', 'Usuario archivado correctamente.');
            } elseif ($accion === 'desarchivar') {
                db()->prepare("UPDATE usuarios SET estado = 'activo' WHERE id_usuario = ?")->execute([$id]);
                admin_log('usuarios', 'cambiar_estado', $id, "Usuario desarchivado: {$u['email']}");
                flash('success', 'Usuario desarchivado y restaurado.');
            } elseif ($accion === 'eliminar') {
                db()->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id]);
                admin_log('usuarios', 'eliminar', $id, "Usuario eliminado: {$u['email']}");
                flash('success', 'Usuario eliminado definitivamente.');
            }
        }
        redirect('admin/index.php?seccion=usuarios');
    } elseif ($id > 0 && $tipo === 'producto') {
        $q = db()->prepare("SELECT nombre FROM productos WHERE id_producto = ?");
        $q->execute([$id]);
        $p = $q->fetch();
        if ($p) {
            if ($accion === 'archivar') {
                db()->prepare("UPDATE productos SET activo = 0 WHERE id_producto = ?")->execute([$id]);
                admin_log('productos', 'cambiar_estado', $id, "Producto archivado: {$p['nombre']}");
                flash('success', 'Producto archivado.');
            } elseif ($accion === 'desarchivar') {
                db()->prepare("UPDATE productos SET activo = 1 WHERE id_producto = ?")->execute([$id]);
                admin_log('productos', 'cambiar_estado', $id, "Producto desarchivado: {$p['nombre']}");
                flash('success', 'Producto desarchivado y reactivado.');
            } elseif ($accion === 'eliminar') {
                db()->prepare("DELETE FROM productos WHERE id_producto = ?")->execute([$id]);
                admin_log('productos', 'eliminar', $id, "Producto eliminado: {$p['nombre']}");
                flash('success', 'Producto eliminado definitivamente.');
            }
        }
        redirect('admin/index.php?seccion=productos');
    }
}
