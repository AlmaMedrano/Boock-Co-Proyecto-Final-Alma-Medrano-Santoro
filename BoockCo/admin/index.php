<?php
require __DIR__ . '/_auth.php';
$me = current_user();
$pdo = db();
$section = $_GET['seccion'] ?? 'resumen';
$allowed = ['resumen','usuarios','archivados','productos','productos_archivados','nuevo_producto'];
if (!in_array($section, $allowed, true)) $section = 'resumen';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if (str_starts_with($action, 'user_')) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0 || $id === (int)$me['id']) throw new RuntimeException('No podés modificar tu propia cuenta desde este panel.');
            $q = $pdo->prepare("SELECT id_usuario,email,rol FROM usuarios WHERE id_usuario=?");
            $q->execute([$id]); $target = $q->fetch();
            if (!$target || $target['rol'] === 'admin') throw new RuntimeException('No se encontró el cliente o no se puede modificar una cuenta administradora.');
            if ($action === 'user_archive') {
                $pdo->prepare("UPDATE usuarios SET estado='suspendido' WHERE id_usuario=?")->execute([$id]);
                admin_log('usuarios','cambiar_estado',$id,'Usuario archivado: '.$target['email']);
                flash('success','Usuario archivado correctamente.');
            } elseif ($action === 'user_restore') {
                $pdo->prepare("UPDATE usuarios SET estado='activo' WHERE id_usuario=?")->execute([$id]);
                admin_log('usuarios','cambiar_estado',$id,'Usuario desarchivado: '.$target['email']);
                flash('success','Usuario desarchivado correctamente.');
            } elseif ($action === 'user_delete') {
                $pdo->prepare("DELETE FROM usuarios WHERE id_usuario=?")->execute([$id]);
                admin_log('usuarios','eliminar',$id,'Usuario eliminado: '.$target['email']);
                flash('success','Usuario eliminado de la base de datos.');
            }
            redirect('admin/index.php?seccion='.($action==='user_restore'?'archivados':'usuarios'));
        }
        if ($action === 'product_save' || $action === 'product_create') {
            $id = (int)($_POST['id_producto'] ?? 0);
            $name = trim($_POST['nombre'] ?? '');
            $sku = strtoupper(trim($_POST['sku'] ?? ''));
            $description = trim($_POST['descripcion'] ?? '');
            $price = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
            $category = (int)($_POST['id_categoria'] ?? 0);
            if ($name==='' || $sku==='' || $price===false || $price<0 || $stock===false || $stock<0 || $category<1) {
                throw new RuntimeException('Completá todos los campos con valores válidos.');
            }
            $cat=$pdo->prepare("SELECT id_categoria FROM categorias WHERE id_categoria=? AND activa=1");
            $cat->execute([$category]);
            if (!$cat->fetch()) throw new RuntimeException('La categoría seleccionada no existe.');
            if ($action === 'product_create') {
                $q=$pdo->prepare("INSERT INTO productos (id_categoria,sku,nombre,descripcion,precio,stock,activo) VALUES (?,?,?,?,?,?,1)");
                $q->execute([$category,$sku,$name,$description,$price,$stock]);
                $newId=(int)$pdo->lastInsertId();
                admin_log('productos','crear',$newId,'Producto creado: '.$name);
                flash('success','Producto agregado al catálogo.');
            } else {
                $q=$pdo->prepare("UPDATE productos SET id_categoria=?,sku=?,nombre=?,descripcion=?,precio=?,stock=? WHERE id_producto=?");
                $q->execute([$category,$sku,$name,$description,$price,$stock,$id]);
                admin_log('productos','editar',$id,'Producto actualizado: '.$name);
                flash('success','Cambios guardados en el producto.');
            }
            redirect('admin/index.php?seccion=productos');
        }
        if ($action === 'product_stockout' || $action === 'product_restore' || $action === 'product_delete') {
            $id=(int)($_POST['id_producto'] ?? 0);
            $q=$pdo->prepare("SELECT nombre FROM productos WHERE id_producto=?");
            $q->execute([$id]); $product=$q->fetch();
            if (!$product) throw new RuntimeException('Producto no encontrado.');
            if ($action==='product_stockout') {
                $pdo->prepare("UPDATE productos SET stock=0 WHERE id_producto=?")->execute([$id]);
                admin_log('productos','editar',$id,'Producto marcado sin stock: '.$product['nombre']);
                flash('success','El producto ahora figura sin stock.');
            } elseif ($action==='product_restore') {
                $pdo->prepare("UPDATE productos SET activo=1 WHERE id_producto=?")->execute([$id]);
                admin_log('productos','cambiar_estado',$id,'Producto desarchivado: '.$product['nombre']);
                flash('success','Producto desarchivado.');
            } else {
                // Baja lógica para no romper referencias históricas de carritos.
                $pdo->prepare("UPDATE productos SET activo=0 WHERE id_producto=?")->execute([$id]);
                admin_log('productos','eliminar',$id,'Producto dado de baja: '.$product['nombre']);
                flash('success','Producto eliminado del catálogo (baja lógica).');
            }
            redirect('admin/index.php?seccion=productos');
        }
    } catch (Throwable $e) {
        $error = $e instanceof PDOException ? 'No se pudo guardar. Revisá que el SKU no esté repetido y que los datos sean válidos.' : $e->getMessage();
    }
}

$search=trim($_GET['q']??'');
$users=[]; $products=[]; $categories=[];
if (in_array($section,['usuarios','archivados'],true)) {
    $status = $section==='archivados' ? 'suspendido' : 'activo';
    $sql="SELECT id_usuario,nombre,apellido,email,rol,estado,fecha_creacion FROM usuarios WHERE rol='cliente' AND ".($section==='archivados' ? "estado='suspendido'" : "estado IN ('activo','pendiente')");
    $params=[];
    if ($search!=='') { $sql.=" AND (nombre LIKE ? OR apellido LIKE ? OR email LIKE ?)"; $like='%'.$search.'%'; array_push($params,$like,$like,$like); }
    $sql.=" ORDER BY id_usuario DESC";
    $q=$pdo->prepare($sql);$q->execute($params);$users=$q->fetchAll();
}
if (in_array($section,['productos','productos_archivados'],true)) {
    $status=$section==='productos' ? 1 : 0;
    $sql="SELECT p.*,c.nombre_visible categoria FROM productos p JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.activo=?";
    $params=[$status];
    if($search!==''){ $sql.=" AND (p.nombre LIKE ? OR p.sku LIKE ?)";$like='%'.$search.'%';array_push($params,$like,$like); }
    $sql.=" ORDER BY p.id_producto DESC";
    $q=$pdo->prepare($sql);$q->execute($params);$products=$q->fetchAll();
}
$categories=$pdo->query("SELECT id_categoria,nombre_visible FROM categorias WHERE activa=1 ORDER BY id_categoria")->fetchAll();
$counts=$pdo->query("SELECT COUNT(*) total,SUM(estado='activo') activos,SUM(estado='suspendido') suspendidos FROM usuarios WHERE rol='cliente'")->fetch();
$productCounts=$pdo->query("SELECT COUNT(*) total,SUM(activo=1) activos,SUM(activo=0) archivados FROM productos")->fetch();
$edit=null;
if($section==='productos' && isset($_GET['editar'])) {
    $q=$pdo->prepare("SELECT * FROM productos WHERE id_producto=?");$q->execute([(int)$_GET['editar']]);$edit=$q->fetch();
}
$pageTitle='Administración';
require dirname(__DIR__).'/includes/header.php';
?>
<style>
.admin-wrap{max-width:1180px;margin:auto}.admin-nav{display:flex;gap:9px;flex-wrap:wrap;margin:24px 0 30px}.admin-nav a{padding:10px 17px;border-radius:30px;border:1px solid var(--beige);background:#fff;color:var(--brown);font-weight:600;font-size:13px}.admin-nav a.active,.admin-nav a:hover{background:var(--brown);color:#fff}.admin-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:24px 0}.admin-stat,.admin-panel{background:#fff;border:1px solid #f0e5e0;border-radius:17px;padding:20px;box-shadow:0 7px 22px rgba(84,55,47,.045)}.admin-stat span{display:block;color:var(--muted);font-size:13px}.admin-stat strong{font:600 30px 'Playfair Display',serif;color:var(--brown)}.admin-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin:20px 0}.admin-tools label{flex:1;min-width:220px}.admin-tools button,.admin-wrap .btn,.admin-actions button{border:0;border-radius:10px;padding:10px 15px;background:var(--brown);color:#fff;font-weight:600}.admin-actions{display:flex;gap:7px;flex-wrap:wrap}.admin-actions form{display:inline}.admin-actions button{font-size:12px;padding:8px 10px}.admin-actions .danger{background:#a85d68}.admin-actions .soft{background:#b87d89}.admin-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}.admin-form label{font-size:13px;color:var(--brown);font-weight:600}.admin-form .wide{grid-column:1/-1}.admin-form textarea{min-height:90px}.admin-note{color:var(--muted);font-size:13px}.admin-error{padding:12px 15px;border-radius:10px;background:#fbe9eb;color:#873f4a;margin:14px 0}.admin-table td{vertical-align:middle}.admin-table td:last-child{min-width:180px}.admin-top-label{font-size:12px;letter-spacing:1px;color:var(--rose-dark);font-weight:700}@media(max-width:760px){.admin-stats{grid-template-columns:1fr 1fr}.admin-form{grid-template-columns:1fr}.admin-form .wide{grid-column:auto}.admin-table{min-width:850px}}
</style>
<div class="admin-wrap">
<section class="page-heading"><p class="eyebrow">BOOCK&CO · ESPACIO PRIVADO</p><h1>Administración</h1><p>Gestioná usuarios y productos. Los cambios se guardan directamente en la base de datos.</p></section>
<?php if($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<nav class="admin-nav">
<a class="<?= $section==='resumen'?'active':'' ?>" href="?seccion=resumen">Resumen</a>
<a class="<?= $section==='usuarios'?'active':'' ?>" href="?seccion=usuarios">Usuarios</a>
<a class="<?= $section==='archivados'?'active':'' ?>" href="?seccion=archivados">Usuarios archivados</a>
<a class="<?= $section==='productos'?'active':'' ?>" href="?seccion=productos">Productos</a>
<a class="<?= $section==='productos_archivados'?'active':'' ?>" href="?seccion=productos_archivados">Productos archivados</a>
<a class="<?= $section==='nuevo_producto'?'active':'' ?>" href="?seccion=nuevo_producto">+ Agregar producto</a>
<a href="<?= BASE_URL ?>/tienda.php">Ver tienda ↗</a>
</nav>
<?php if($section==='resumen'): ?>
<div class="admin-stats">
<div class="admin-stat"><span>Clientes registrados</span><strong><?= (int)$counts['total'] ?></strong></div>
<div class="admin-stat"><span>Clientes activos</span><strong><?= (int)$counts['activos'] ?></strong></div>
<div class="admin-stat"><span>Clientes archivados</span><strong><?= (int)$counts['suspendidos'] ?></strong></div>
<div class="admin-stat"><span>Productos activos</span><strong><?= (int)$productCounts['activos'] ?></strong></div>
</div>
<div class="admin-panel"><h2>Panel de control</h2><p>Desde acá podés administrar las cuentas de clientes y mantener actualizado el catálogo de Boock&Co.</p><p class="admin-note">Las contraseñas están protegidas mediante hash y no se pueden recuperar en texto legible. Podés administrar el estado de cada cuenta sin acceder a su contraseña.</p></div>
<?php elseif(in_array($section,['usuarios','archivados'],true)): ?>
<section class="page-heading"><h2><?= $section==='usuarios'?'Usuarios activos':'Usuarios archivados' ?></h2><p>Buscá clientes por nombre, apellido o correo electrónico.</p></section>
<form class="admin-tools" method="get"><input type="hidden" name="seccion" value="<?= e($section) ?>"><label><input name="q" placeholder="Buscar usuario..." value="<?= e($search) ?>"></label><button>Buscar</button></form>
<div class="table-wrap"><table class="admin-table"><thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($users as $u): ?><tr><td>#<?= (int)$u['id_usuario'] ?></td><td><?= e($u['nombre'].' '.$u['apellido']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['estado']==='suspendido'?'Archivado':($u['estado']==='pendiente'?'Pendiente':'Activo')) ?></td><td><?= e($u['fecha_creacion']) ?></td><td><div class="admin-actions">
<?php if($section==='usuarios'): ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$u['id_usuario'] ?>"><button name="action" value="user_archive" class="soft" onclick="return confirm('¿Archivar este usuario?')">Archivar</button></form><?php else: ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$u['id_usuario'] ?>"><button name="action" value="user_restore">Desarchivar</button></form><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$u['id_usuario'] ?>"><button name="action" value="user_delete" class="danger" onclick="return confirm('¿Eliminar definitivamente este usuario?')">Eliminar</button></form>
</div></td></tr><?php endforeach; ?><?php if(!$users): ?><tr><td colspan="6">No hay usuarios en esta sección.</td></tr><?php endif; ?>
</tbody></table></div>
<?php elseif($section==='productos' || $section==='productos_archivados'): ?>
<section class="page-heading"><h2><?= $section==='productos'?'Productos del catálogo':'Productos archivados' ?></h2><p>Actualizá precios, stock y datos de cada producto.</p></section>
<form class="admin-tools" method="get"><input type="hidden" name="seccion" value="<?= e($section) ?>"><label><input name="q" placeholder="Buscar por nombre o SKU..." value="<?= e($search) ?>"></label><button>Buscar</button></form>
<div class="table-wrap"><table class="admin-table"><thead><tr><th>SKU</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr><td><?= e($p['sku']) ?></td><td><?= e($p['nombre']) ?></td><td><?= e($p['categoria']) ?></td><td>$<?= number_format((float)$p['precio'],2,',','.') ?></td><td><?= (int)$p['stock']===0?'Sin stock':(int)$p['stock'] ?></td><td><div class="admin-actions">
<?php if($section==='productos'): ?> <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>"><button name="action" value="product_stockout" class="soft" onclick="return confirm('¿Marcar sin stock?')">Sin stock</button></form><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>"><button name="action" value="product_delete" class="danger" onclick="return confirm('¿Quitar del catálogo?')">Archivar</button></form><?php else: ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>"><button name="action" value="product_restore">Desarchivar</button></form><?php endif; ?>
</div></td></tr><?php endforeach; ?><?php if(!$products): ?><tr><td colspan="6">No hay productos en esta sección.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if($section==='productos' && (isset($_GET['editar']))): ?>
<?php if($edit): ?><section class="admin-panel" style="margin-top:28px"><h2>Editar producto</h2><?php $formAction='product_save'; require __DIR__.'/_product_form.php'; ?></section><?php else: ?><p>Producto no encontrado.</p><?php endif; ?>
<?php endif; ?>
<?php elseif($section==='nuevo_producto'): ?>
<section class="admin-panel"><h2>Agregar producto</h2><p class="admin-note">El producto se insertará en la tabla productos y aparecerá en el catálogo según su categoría.</p><?php $formAction='product_create'; $edit=null; require __DIR__.'/_product_form.php'; ?></section>
<?php endif; ?>
</div>
<?php require dirname(__DIR__).'/includes/footer.php'; ?>
