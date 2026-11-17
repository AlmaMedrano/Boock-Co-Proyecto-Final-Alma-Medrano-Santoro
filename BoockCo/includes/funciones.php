<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']), 'samesite' => 'Lax']);
    session_start();
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirect(string $path): never {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('La sesión del formulario venció. Volvé atrás y actualizá la página.');
    }
}
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function require_login(): void {
    if (!current_user()) {
        flash('error', 'Iniciá sesión para continuar.');
        redirect('login.php');
    }
}
function require_admin(): void {
    require_login();
    if (($_SESSION['user']['rol'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('No tenés permisos para acceder a esta sección.');
    }
}
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id_usuario'],
        'nombre' => $user['nombre'],
        'apellido' => $user['apellido'],
        'email' => $user['email'],
        'rol' => $user['rol']
    ];
}
function get_or_create_cart(int $userId): int {
    $pdo = db();
    $q = $pdo->prepare("SELECT * FROM carritos WHERE id_usuario = ? AND estado = 'abierto' LIMIT 1");
    $q->execute([$userId]);
    $carrito = $q->fetch();
    
    if ($carrito) {
        return (int)($carrito['id'] ?? $carrito['id_carrito'] ?? 0);
    }
    
    $q = $pdo->prepare("INSERT INTO carritos (id_usuario, estado) VALUES (?, 'abierto')");
    $q->execute([$userId]);
    return (int)$pdo->lastInsertId();
}
function category_map(): array {
    return [
        'libros' => ['nombre' => 'Libros', 'archivo' => 'categoria_libros.php', 'limite' => 20],
        'postit' => ['nombre' => 'Post-it y papelería', 'archivo' => 'categoria_postit.php', 'limite' => 9],
        'kindles' => ['nombre' => 'Kindles', 'archivo' => 'categoria_kindles.php', 'limite' => 9],
        'fundas' => ['nombre' => 'Fundas lectoras', 'archivo' => 'categoria_fundas.php', 'limite' => 9],
        'lamparas' => ['nombre' => 'Lámparas lectoras', 'archivo' => 'categoria_lamparas.php', 'limite' => 9],
        'marcapaginas' => ['nombre' => 'Marcapáginas', 'archivo' => 'categoria_marcapaginas.php', 'limite' => 9],
    ];
}
function admin_log(string $modulo, string $accion, ?int $registro, string $detalle): void {
    $u = current_user();
    if (!$u || ($u['rol'] ?? '') !== 'admin') return;
    $q = db()->prepare("INSERT INTO panel_administracion_logs
        (id_admin, modulo_afectado, accion_ejecutada, id_registro_afectado, descripcion_cambio)
        VALUES (?, ?, ?, ?, ?)");
    $q->execute([(int)$u['id'], $modulo, $accion, $registro, $detalle]);
}
