<?php
require_once __DIR__ . '/includes/funciones.php';
if (current_user()) redirect('tienda.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Ingresá un correo válido y tu contraseña.';
    } else {
        $stmt = db()->prepare("SELECT * FROM usuarios WHERE email = ? AND estado = 'activo' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user);
            redirect('tienda.php');
        }
        $error = 'El correo o la contraseña no son correctos.';
    }
}
$pageTitle = 'Iniciar sesión';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <div class="auth-decoration"><span class="decor-star">✦</span><p class="eyebrow">BIENVENIDA A TU PRÓXIMA HISTORIA</p><h1>Los libros son<br>lugares a los<br>que volver.</h1><p>Un rincón cálido para descubrir, regalar y disfrutar la lectura.</p><div class="decor-line"></div></div>
    <div class="auth-card">
        <span class="eyebrow">BOOCK&CO</span><h2>¡Qué lindo verte!</h2><p class="muted">Ingresá a tu cuenta para continuar.</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label>Correo electrónico<input type="email" name="email" required autocomplete="email" placeholder="tu correo"></label>
            <label>Contraseña<input type="password" name="password" required autocomplete="current-password" placeholder="Tu contraseña"></label>
            <button class="btn btn-full" type="submit">Iniciar sesión</button>
        </form>
        <p class="auth-switch">¿Todavía no tenés cuenta? <a href="<?= BASE_URL ?>/registro.php">Crear una cuenta</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
