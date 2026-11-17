<form method="post" class="admin-form">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="action" value="<?= e($formAction) ?>">
<?php if($edit): ?><input type="hidden" name="id_producto" value="<?= (int)$edit['id_producto'] ?>"><?php endif; ?>
<label>Nombre del producto<input name="nombre" required value="<?= e($edit['nombre']??'') ?>"></label>
<label>SKU<input name="sku" required value="<?= e($edit['sku']??'') ?>"></label>
<label>Categoría<select name="id_categoria" required><?php foreach($categories as $c): ?><option value="<?= (int)$c['id_categoria'] ?>" <?= (int)($edit['id_categoria']??0)===(int)$c['id_categoria']?'selected':'' ?>><?= e($c['nombre_visible']) ?></option><?php endforeach; ?></select></label>
<label>Precio ($)<input type="number" name="precio" min="0" step="0.01" required value="<?= e((string)($edit['precio']??'')) ?>"></label>
<label>Stock (unidades)<input type="number" name="stock" min="0" step="1" required value="<?= e((string)($edit['stock']??0)) ?>"></label>
<label class="wide">Descripción<textarea name="descripcion"><?= e($edit['descripcion']??'') ?></textarea></label>
<div class="wide"><button class="btn" type="submit"><?= $edit?'Guardar cambios':'Agregar producto' ?></button></div>
</form>