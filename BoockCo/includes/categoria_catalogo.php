<?php
declare(strict_types=1);


require_once __DIR__ . '/funciones.php';

try {
    $stmt = db()->prepare("SELECT * FROM productos ORDER BY id_producto DESC");
    $stmt->execute();
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    try {
        $stmt = db()->prepare("SELECT * FROM productos ORDER BY id DESC");
        $stmt->execute();
        $productos = $stmt->fetchAll();
    } catch (Exception $ex) {
        $productos = [];
    }
}
?>

<div class="catalog-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 2rem; padding: 2rem 0;">
    <?php if (empty($productos)): ?>
        <p style="grid-column: 1 / -1; text-align: center; color: #666;">No hay productos disponibles por el momento.</p>
    <?php else: ?>
        <?php foreach ($productos as $p): ?>
            <?php 
                $prodId = (int)($p['id_producto'] ?? $p['id'] ?? 0);
                $nombre = $p['nombre'] ?? $p['titulo'] ?? 'Producto sin nombre';
                $precio = (float)($p['precio'] ?? 0);
                $imagen = $p['imagen'] ?? $p['foto'] ?? '';
            ?>
            <div class="product-card" style="background: #fff; border: 1px solid #eaeaea; border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div class="product-thumb" style="height: 200px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; border-radius: 6px; margin-bottom: 1rem; overflow: hidden;">
                    <?php if (!empty($imagen)): ?>
                        <img src="<?= BASE_URL ?>/img/productos/<?= e($imagen) ?>" alt="<?= e($nombre) ?>" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="color: #bbb; font-size: 2rem;">♡</span>
                    <?php endif; ?>
                </div>

                <div class="product-info" style="margin-bottom: 1rem;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #333;"><?= e($nombre) ?></h3>
                    <p style="font-weight: 700; color: #2c3e50; font-size: 1rem;">$<?= number_format($precio, 2, ',', '.') ?></p>
                </div>

                <div class="product-actions">
                    <form method="post" action="<?= BASE_URL ?>/carrito_accion.php">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="producto_id" value="<?= $prodId ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn btn-full" style="width: 100%; padding: 0.6rem; background: #2c3e50; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Agregar al carrito</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>