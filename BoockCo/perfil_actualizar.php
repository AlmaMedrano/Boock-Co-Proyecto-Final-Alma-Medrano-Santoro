<?php
/* =========================================================
   PROCESAMIENTO Y MENSAJE DE ACTUALIZACIÓN DE PERFIL
   ========================================================= */
declare(strict_types=1);
require_once __DIR__ . '/includes/funciones.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('perfil.php');
}

verify_csrf();

$userId = (int) (current_user()['id'] ?? current_user()['id_usuario'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($nombre === '' || $apellido === '' || $email === '') {
    flash('error', 'Todos los campos son obligatorios.');
    redirect('perfil.php');
}

// Actualizamos los datos en la base de datos de forma segura
try {
    $stmt = db()->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE id_usuario = ?");
    $stmt->execute([$nombre, $apellido, $email, $userId]);
} catch (Exception $e) {
    $stmt = db()->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE id = ?");
    $stmt->execute([$nombre, $apellido, $email, $userId]);
}

// Actualizamos la sesión actual
if (isset($_SESSION['user'])) {
    $_SESSION['user']['nombre'] = $nombre;
    $_SESSION['user']['apellido'] = $apellido;
    $_SESSION['user']['email'] = $email;
}

$pageTitle = 'Datos actualizados';
require __DIR__ . '/includes/header.php';
?>

<section class="page-heading" style="text-align: center; padding: 4rem 1rem;">
    <div style="font-size: 3rem; color: #28a745; margin-bottom: 1rem;">✓</div>
    <p class="eyebrow">¡LISTO!</p>
    <h1>Tus datos fueron actualizados con éxito</h1>
    <p style="margin-bottom: 2rem;">Los cambios se guardaron correctamente en tu cuenta de Boock&Co.</p>
    
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="<?= BASE_URL ?>/perfil.php" class="btn btn-outline">Ver mi perfil</a>
        <a href="<?= BASE_URL ?>/index.php" class="btn">Volver al inicio</a>
    </div>
</section>

<?php
require __DIR__ . '/includes/footer.php';
?>