<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/funciones.php';

$pageTitle = 'Fundas';
require_once __DIR__ . '/includes/header.php';

$productos = [];
try {
    $stmt = db()->prepare("SELECT * FROM vista_categoria_fundas ORDER BY id_producto DESC");
    $stmt->execute();
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    try {
        $stmt = db()->prepare("SELECT * FROM vista_categoria_fundas ORDER BY id DESC");
        $stmt->execute();
        $productos = $stmt->fetchAll();
    } catch (Exception $ex) {
        $productos = [];
    }
}
?>

<main class="main-content" style="padding: 3rem 1rem; max-width: 1200px; margin: 0 auto;">
    <section class="page-heading" style="text-align: center; margin-bottom: 2.5rem;">
        <p class="eyebrow" style="text-transform: uppercase; letter-spacing: 2px; font-size: 0.85rem; color: #666;">BOOCK&CO</p>
        <h1 style="font-size: 2.5rem; margin: 0.5rem 0; color: #222;">Fundas</h1>
        <p style="color: #666;">Protegé tus lecturas con estilo.</p>
    </section>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; justify-content: center; max-width: 1100px; margin: 0 auto;">
        <?php if (empty($productos)): ?>
            <p style="grid-column: 1 / -1; text-align: center; color: #666;">No hay fundas disponibles en este momento.</p>
        <?php else: ?>
            <?php foreach ($productos as $p): ?>
                <?php 
                    $prodId = (int)($p['id_producto'] ?? $p['id'] ?? 0);
                    $nombre = $p['nombre'] ?? $p['titulo'] ?? 'Producto sin nombre';
                    $precio = (float)($p['precio'] ?? 0);
                    $imagen = trim($p['imagen_url'] ?? '');
                ?>
                <article class="product-card" style="background: #fff; border: 1px solid #eaeaea; border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <div class="product-thumb" style="height: 220px; background: #fcfcfc; display: flex; align-items: center; justify-content: center; border-radius: 8px; margin-bottom: 1.25rem; overflow: hidden;">
                        <?php if (!empty($imagen)): ?>
                            <img src="<?= BASE_URL ?>/img/productos/<?= e($imagen) ?>" alt="<?= e($nombre) ?>" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="color: #ccc; font-size: 2rem;">♡</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info" style="margin-bottom: 1.25rem; text-align: left;">
                        <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: #222; font-weight: 600;"><?= e($nombre) ?></h3>
                        <p class="product-price" style="font-weight: 700; color: #2c3e50; font-size: 1.1rem;">$<?= number_format($precio, 2, ',', '.') ?></p>
                    </div>
                    <div class="product-actions">
                        <form method="post" action="<?= BASE_URL ?>/carrito_accion.php">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="agregar">
                            <input type="hidden" name="producto_id" value="<?= $prodId ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" class="btn btn-full" style="width: 100%; padding: 0.75rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Agregar al carrito</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>