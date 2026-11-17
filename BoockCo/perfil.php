<?php
/* =========================================================
   1. LÓGICA PHP: PERFIL Y FORMULARIO DE EDICIÓN
   ========================================================= */
require_once __DIR__ . '/includes/funciones.php';
require_login();

$userId = (int) (current_user()['id'] ?? current_user()['id_usuario'] ?? 0);

$stmt = db()->prepare("SELECT * FROM usuarios WHERE id_usuario = ? LIMIT 1");
$stmt->execute([$userId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    try {
        $stmt = db()->prepare("SELECT * FROM usuarios WHERE id_usuario = ? LIMIT 1");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch();
    } catch (Exception $e) {}
}

$pageTitle = 'Mi perfil';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading">
    <p class="eyebrow">MI CUENTA</p>
    <h1>Perfil de usuario</h1>
    <p>Actualiza tus datos personales en Boock&Co.</p>
</section>

<div class="profile-container" style="max-width: 600px; margin: 0 auto 3rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <?php show_flash(); ?>
    
    <?php if ($usuario): ?>
        <form method="post" action="<?= BASE_URL ?>/perfil_actualizar.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Nombre</label>
                <input type="text" name="nombre" value="<?= e($usuario['nombre'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Apellido</label>
                <input type="text" name="apellido" value="<?= e($usuario['apellido'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Correo electrónico</label>
                <input type="email" name="email" value="<?= e($usuario['email'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <button type="submit" class="btn btn-full">Guardar cambios</button>
        </form>
    <?php else: ?>
        <p>No se pudieron cargar los datos del perfil.</p>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>