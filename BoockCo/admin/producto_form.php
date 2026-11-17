<?php
declare(strict_types=1);

// Asegúrate de ajustar la ruta de include según la subcarpeta (ej. '../includes/funciones.php')
require_once __DIR__ . '/../includes/funciones.php';

// Validación estricta: Solo permite acceso al administrador con tu correo y rol
$usuarioActual = $_SESSION['usuario'] ?? $_SESSION['user'] ?? null;

if (!$usuarioActual || ($usuarioActual['email'] ?? '') !== 'alma@gmail.com' || ($usuarioActual['rol'] ?? '') !== 'admin') {
    flash('error', 'Acceso no autorizado. Área exclusiva para el administrador.');
    redirect('../login.php');
    exit;
}
?>
<?php
// Formulario parcial para productos
$producto = $producto ?? ['titulo' => '', 'precio' => '', 'descripcion' => ''];
?>
<div style="margin-bottom: 1.25rem;">
    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Título del Libro</label>
    <input type="text" name="titulo" value="<?= e($producto['titulo'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
</div>

<div style="margin-bottom: 1.25rem;">
    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Precio</label>
    <input type="number" step="0.01" name="precio" value="<?= e((string)($producto['precio'] ?? '')) ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
</div>

<div style="margin-bottom: 1.5rem;">
    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Descripción</label>
    <textarea name="descripcion" rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;"><?= e($producto['descripcion'] ?? '') ?></textarea>
</div>