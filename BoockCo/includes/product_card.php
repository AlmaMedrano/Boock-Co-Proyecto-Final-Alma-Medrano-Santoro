<?php
// Recibe $product (array) y $showAdd (bool).
?>
<article class="product-card">
    <div class="product-image">
        <?php if (!empty($product['imagen'])): ?>
            <img src="<?= BASE_URL ?>/img/productos/<?= e($product['imagen']) ?>" alt="<?= e($product['nombre']) ?>">
        <?php else: ?>
            <div class="image-placeholder"><span>♡</span><small>Imagen disponible próximamente</small></div>
        <?php endif; ?>
    </div>
    <div class="product-info">
        <h3><?= e($product['nombre']) ?></h3>
        <p><?= e($product['descripcion']) ?></p>
        <div class="product-bottom">
            <strong>$<?= number_format((float)$product['precio'], 2, ',', '.') ?></strong>
            <?php if ($showAdd && (int)$product['stock'] > 0): ?>
                <form method="post" action="<?= BASE_URL ?>/carrito_accion.php">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="producto_id" value="<?= (int)$product['id'] ?>">
                    <button class="btn btn-small" type="submit">Agregar</button>
                </form>
            <?php elseif ($showAdd): ?>
                <span class="stock-out">Sin stock</span>
            <?php endif; ?>
        </div>
    </div>
</article>
