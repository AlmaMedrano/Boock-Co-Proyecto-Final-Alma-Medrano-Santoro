<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/funciones.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('carrito.php');
}

verify_csrf();

$accion = $_POST['accion'] ?? '';
$userId = (int) (current_user()['id'] ?? current_user()['id_usuario'] ?? 0);
$cartId = get_or_create_cart($userId);

switch ($accion) {
    case 'agregar':
        $productoId = (int) ($_POST['producto_id'] ?? $_POST['id_producto'] ?? $_POST['id'] ?? 0);
        $cantidadAgregada = max(1, (int) ($_POST['cantidad'] ?? 1));

        if ($productoId <= 0) {
            flash('error', 'Producto inválido.');
            redirect('carrito.php');
        }

        $producto = null;
        try {
            $qProd = db()->prepare("SELECT * FROM productos WHERE id_producto = ? LIMIT 1");
            $qProd->execute([$productoId]);
            $producto = $qProd->fetch();
        } catch (Exception $e) {
            try {
                $qProd = db()->prepare("SELECT * FROM productos WHERE id = ? LIMIT 1");
                $qProd->execute([$productoId]);
                $producto = $qProd->fetch();
            } catch (Exception $ex) {
                $producto = null;
            }
        }

        if (!$producto) {
            flash('error', 'El producto no existe.');
            redirect('carrito.php');
        }

        $prodIdReal = (int)($producto['id_producto'] ?? $producto['id'] ?? $productoId);
        $precio = (float)($producto['precio'] ?? 0);
        $stock = (int)($producto['stock'] ?? 99);

        $qItem = db()->prepare("SELECT * FROM carrito_items WHERE id_carrito = ? AND id_producto = ? LIMIT 1");
        $qItem->execute([$cartId, $prodIdReal]);
        $itemExistente = $qItem->fetch();

        if ($itemExistente) {
            $itemIdExistente = (int)($itemExistente['id_item'] ?? $itemExistente['id'] ?? 0);
            $nuevaCantidad = (int)$itemExistente['cantidad'] + $cantidadAgregada;
            if ($nuevaCantidad > $stock) $nuevaCantidad = $stock;

            $qUpd = db()->prepare("UPDATE carrito_items SET cantidad = ?, precio_unitario = ? WHERE id_item = ? OR id = ?");
            $qUpd->execute([$nuevaCantidad, $precio, $itemIdExistente, $itemIdExistente]);
        } else {
            if ($cantidadAgregada > $stock) $cantidadAgregada = $stock;

            $qIns = db()->prepare("INSERT INTO carrito_items (id_carrito, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $qIns->execute([$cartId, $prodIdReal, $cantidadAgregada, $precio]);
        }

        flash('success', 'Producto agregado al carrito.');
        redirect('carrito.php');
        break;

 case 'actualizar':
    $itemId = (int) ($_POST['item_id'] ?? $_POST['id'] ?? 0);
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));

    
    $qUpd = db()->prepare("UPDATE carrito_items SET cantidad = ? WHERE id_item = ? AND id_carrito = ?");
    $qUpd->execute([$cantidad, $itemId, $cartId]);

    flash('success', 'Cantidad actualizada.');
    redirect('carrito.php');
    break;

case 'eliminar':
    $itemId = (int) ($_POST['item_id'] ?? $_POST['id'] ?? 0);

    $qDel = db()->prepare("DELETE FROM carrito_items WHERE id_item = ? AND id_carrito = ?");
    $qDel->execute([$itemId, $cartId]);

    flash('success', 'Producto eliminado del carrito.');
    redirect('carrito.php');
    break;

    case 'finalizar':
        
        try {
            $qClose = db()->prepare("UPDATE carritos SET estado = 'cerrado' WHERE id_carrito = ?");
            $qClose->execute([$cartId]);
        } catch (Exception $e) {
            try {
                $qClose = db()->prepare("UPDATE carritos SET estado = 'cerrado' WHERE id = ?");
                $qClose->execute([$cartId]);
            } catch (Exception $ex) {
                
            }
        }

        flash('success', '¡Compra finalizada con éxito!');
        redirect('carrito.php');
        break;

    default:
        redirect('carrito.php');
}