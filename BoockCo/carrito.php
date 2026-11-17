<?php

require_once __DIR__ . '/includes/funciones.php';
require_login();

$userId = (int) (current_user()['id'] ?? current_user()['id_usuario'] ?? 0);
$cartId = get_or_create_cart($userId);


$stmt = db()->prepare("SELECT * FROM carrito_items WHERE id_carrito = ? ORDER BY id_item DESC");
$stmt->execute([$cartId]);
$rawItems = $stmt->fetchAll();

$items = [];
$total = 0;

foreach ($rawItems as $ci) {
    $prodId = (int)($ci['id_producto'] ?? 0);
    $producto = false;

    try {
        $qp = db()->prepare("SELECT * FROM productos WHERE id_producto = ? LIMIT 1");
        $qp->execute([$prodId]);
        $producto = $qp->fetch();
    } catch (Exception $e) {}

    if (!$producto) {
        try {
            $qp = db()->prepare("SELECT * FROM productos WHERE id = ? LIMIT 1");
            $qp->execute([$prodId]);
            $producto = $qp->fetch();
        } catch (Exception $e) {}
    }

    $nombre = $producto['nombre'] ?? $producto['titulo'] ?? 'Producto #' . $prodId;
    $stock = (int)($producto['stock'] ?? 99);
    $imagen = $producto['imagen_url'] ?? '';

    $cantidad = (int)$ci['cantidad'];
    $precio = (float)$ci['precio_unitario'];
    $subtotal = $cantidad * $precio;
    $total += $subtotal;

    $items[] = [
        'item_id' => (int)($ci['id_item'] ?? 0),
        'cantidad' => $cantidad,
        'precio_unitario' => $precio,
        'nombre' => $nombre,
        'stock' => $stock,
        'imagen_url' => $imagen,
        'subtotal' => $subtotal
    ];
}

$pageTitle = 'Mi carrito';
require __DIR__ . '/includes/header.php';
?>


<section class="page-heading">
    <p class="eyebrow">TUS FAVORITOS</p>
    <h1>Mi carrito</h1>
    <p>Revisá los productos que elegiste antes de finalizar.</p>
</section>


<?php if (!$items): ?>
    <div class="empty-state">
        <span>♡</span>
        <h2>Tu carrito está esperando historias</h2>
        <p>Cuando agregues productos, los vas a encontrar acá.</p>
        <a class="btn" href="<?= BASE_URL ?>/categoria_libros.php">Explorar catálogo</a>
    </div>
<?php else: ?>
    <div class="cart-layout">
        <div class="cart-items">
            <?php foreach ($items as $item): ?>
                <article class="cart-item">
                    <div class="cart-thumb">
                        <?php if (!empty($item['imagen_url'])): ?>
                            <img
                                src="<?= BASE_URL ?>/img/productos/<?= e($item['imagen_url']) ?>"
                                alt="<?= e($item['nombre']) ?>"
                            >
                        <?php else: ?>
                            ♡
                        <?php endif; ?>
                    </div>

                    <div class="cart-description">
                        <h3><?= e($item['nombre']) ?></h3>
                        <p>$<?= number_format((float) $item['precio_unitario'], 2, ',', '.') ?> c/u</p>

                       
                        <form method="post" action="<?= BASE_URL ?>/carrito_accion.php" class="quantity-form">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="actualizar">
                            <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">

                            <label>
                                Cantidad
                                <input
                                    type="number"
                                    name="cantidad"
                                    min="1"
                                    max="<?= (int) $item['stock'] ?>"
                                    value="<?= (int) $item['cantidad'] ?>"
                                    required
                                >
                            </label>
                            <button class="btn btn-outline btn-small" type="submit">Actualizar</button>
                        </form>
                    </div>

                    <strong class="cart-subtotal">
                        $<?= number_format($item['subtotal'], 2, ',', '.') ?>
                    </strong>

                    
                    <form method="post" action="<?= BASE_URL ?>/carrito_accion.php">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                        <button class="remove-btn" type="submit" aria-label="Eliminar producto">×</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>

       
        <aside class="cart-summary">
            <h2>Resumen de compra</h2>

            <div class="summary-line">
                <span>Subtotal</span>
                <strong>$<?= number_format($total, 2, ',', '.') ?></strong>
            </div>

            <div class="summary-line">
                <span>Total</span>
                <strong class="total-price">$<?= number_format($total, 2, ',', '.') ?></strong>
            </div>

            <form method="post" action="<?= BASE_URL ?>/carrito_accion.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="accion" value="finalizar">
                <button class="btn btn-full" type="submit">Finalizar compra</button>
            </form>

            <p class="muted">Al finalizar, verás la confirmación de tu compra.</p>
        </aside>
    </div>
<?php endif; ?>

<?php

require __DIR__ . '/includes/footer.php';
?>