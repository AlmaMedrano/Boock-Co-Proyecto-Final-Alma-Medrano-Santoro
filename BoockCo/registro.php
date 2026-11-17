<?php
require_once __DIR__ . '/includes/funciones.php';
if (current_user()) redirect('tienda.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    if ($nombre === '' || $apellido === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Completá todos los campos. La contraseña debe tener al menos 8 caracteres.';
    } else {
        $check = db()->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Ese correo electrónico ya está registrado.';
        } else {
           
            $stmt = db()->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, rol, estado) VALUES (?, ?, ?, ?, 'cliente', 'activo')");
            $stmt->execute([$nombre, $apellido, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Tu cuenta fue creada. Ya podés iniciar sesión.');
            redirect('login.php');
        }
    }
}
$pageTitle = 'Crear cuenta';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout auth-layout-register">
    <div class="auth-decoration">
        <span class="decor-star">✿</span>
        <p class="eyebrow">UN NUEVO COMIENZO</p>
        <h1>Tu próxima<br>lectura te<br>está esperando.</h1>
        <p>Creá tu cuenta y descubrí todo lo que preparamos para vos.</p>
    </div>
    <div class="auth-card">
        <span class="eyebrow">BOOCK&CO</span>
        <h2>Crear cuenta</h2>
        <p class="muted">Completá tus datos para registrarte.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            
            <div class="form-two">
                <label>Nombre<input name="nombre" required maxlength="100" value="<?= e($_POST['nombre'] ?? '') ?>"></label>
                <label>Apellido<input name="apellido" required maxlength="100" value="<?= e($_POST['apellido'] ?? '') ?>"></label>
            </div>
            
            <label>Correo electrónico<input type="email" name="email" required maxlength="150" value="<?= e($_POST['email'] ?? '') ?>"></label>
            <label>Contraseña<input type="password" name="password" minlength="8" required><small>Mínimo 8 caracteres.</small></label>
            
            <button class="btn btn-full" type="submit">Crear mi cuenta</button>
        </form>
        
        <p class="auth-switch">¿Ya tenés cuenta? <a href="<?= BASE_URL ?>/login.php">Iniciar sesión</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>