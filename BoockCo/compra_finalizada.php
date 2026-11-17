<?php

require_once __DIR__ . '/includes/funciones.php';
require_login();

$purchase = $_SESSION['compra_finalizada'] ?? null;

if (!$purchase) {
    flash('warning', 'No hay una compra reciente para mostrar.');
    redirect('tienda.php');
}


unset($_SESSION['compra_finalizada']);

$pageTitle = 'Compra finalizada';
require __DIR__ . '/includes/header.php';
?>


<section class="purchase-success">
    <div class="purchase-success__icon" aria-hidden="true">✓</div>

    <p class="eyebrow">BOOCK&CO · GRACIAS POR ELEGIRNOS</p>

    <h1>¡Tu compra fue finalizada!</h1>

    <p class="purchase-success__message">
        Gracias por comprar en Boock&Co. Esperamos que disfrutes tus productos
        y que cada uno acompañe muchos momentos especiales de lectura.
    </p>

    <div class="purchase-success__details">
        <span>Compra n.º <?= (int) $purchase['carrito_id'] ?></span>
        <span><?= (int) $purchase['cantidad'] ?> producto(s)</span>
        <strong>Total: $<?= number_format((float) $purchase['total'], 2, ',', '.') ?></strong>
        <span><?= e($purchase['fecha']) ?></span>
    </div>

    <div class="purchase-success__actions">
        <a class="btn" href="<?= BASE_URL ?>/tienda.php">Volver a la tienda</a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/perfil.php">Ir a mi cuenta</a>
    </div>

    <p class="checkout-note">
        Confirmación local de la aplicación. Esta versión no procesa pagos en línea
        ni genera comprobantes fiscales.
    </p>
</section>

<?php

require __DIR__ . '/includes/footer.php';
?>
