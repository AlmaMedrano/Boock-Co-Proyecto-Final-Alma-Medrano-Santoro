<?php
declare(strict_types=1);


require_once __DIR__ . '/includes/funciones.php';

$tipoCategoria = $_GET['tipo'] ?? 'libros';
$nombreLegible = ucfirst($tipoCategoria);

$pageTitle = $nombreLegible;
require_once __DIR__ . '/includes/header.php';


$productos = [];
try {

    $stmt = db()->prepare("SELECT * FROM productos WHERE categoria = ? OR tipo = ? ORDER BY id_producto DESC");
    $stmt->execute([$tipoCategoria, $tipoCategoria]);
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    try {
        $stmt = db()->prepare("SELECT * FROM productos WHERE categoria = ? OR tipo = ? ORDER BY id DESC");
        $stmt->execute([$tipoCategoria, $tipoCategoria]);
        $productos = $stmt->fetchAll();
    } catch (Exception $ex) {
        $productos = [];
    }
}
?>


<main class="main-content" style="padding: 2rem 1rem; max-width: 1200px; margin: 0 auto;">
    <section class="page-heading" style="margin-bottom: 2rem;">
        <p class="eyebrow">BOOCK&CO</p>
        <h1><?= e($nombreLegible) ?></h1>
        <p>Explorá nuestra selección exclusiva de <?= e(strtolower($nombreLegible)) ?>.</p>
    </section>

    <div class="catalog-grid">
        <?php if (empty($productos)): ?>
            <p class="empty-message">No hay productos disponibles en esta categoría por el momento.</p>
        <?php else: ?>
            <?php foreach ($productos as $p): ?>
                <?php 
                    $prodId = (int)($p['id_producto'] ?? $p['id'] ?? 0);
                    $nombre = $p['nombre'] ?? $p['titulo'] ?? 'Producto sin nombre';
                    $precio = (float)($p['precio'] ?? 0);
                    $imagen = $p['imagen'] ?? $p['foto'] ?? '';
                ?>
                <article class="product-card">
                    <div class="product-thumb">
                        <?php if (!empty($imagen)): ?>
                            <img src="<?= BASE_URL ?>/img/productos/<?= e($imagen) ?>" alt="<?= e($nombre) ?>">
                        <?php else: ?>
                            <span>♡</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <h3><?= e($nombre) ?></h3>
                        <p class="product-price">$<?= number_format($precio, 2, ',', '.') ?></p>
                    </div>

                    <div class="product-actions">
                        <form method="post" action="<?= BASE_URL ?>/carrito_accion.php">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="agregar">
                            <input type="hidden" name="producto_id" value="<?= $prodId ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" class="btn">Agregar al carrito</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php

require_once __DIR__ . '/includes/footer.php';
?>