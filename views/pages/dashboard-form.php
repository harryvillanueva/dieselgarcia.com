<?php
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Marca.php'; 
require_once __DIR__ . '/../../models/Categoria.php';

$id = $_GET['id'] ?? null;
$producto = $id ? Producto::getById($id) : null;

$isEdit = $producto !== null;
$titulo = $isEdit ? 'Editar Producto' : 'Crear Nuevo Producto';
$action = $isEdit ? 'edit_product' : 'create_product';

$hasSale = $isEdit && !empty($producto['sale_price']);
?>

<div class="mx-auto max-w-4xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    <?php 
    $activeTab = 'inventario';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <main class="flex-1">
        <a href="<?= $_ENV['BASE_URL'] ?>dashboard" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground mb-6 transition-colors">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Volver al inventario
        </a>

        <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)]">
            <h1 class="font-display text-2xl font-bold uppercase border-b border-border pb-4 mb-6"><?= $titulo ?></h1>

            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="admin_action" value="<?= $action ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                <?php endif; ?>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Nombre del producto</label>
                    <input type="text" name="name" required value="<?= $isEdit ? htmlspecialchars($producto['name']) : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Categoría</label>
                        <select name="category" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            <?php 
                            $categoriasList = Categoria::getAll();
                            foreach ($categoriasList as $cat): 
                            ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($isEdit && $producto['category'] === $cat['slug']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Marca</label>
                        <select name="brand" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            <?php 
                            $marcasList = Marca::getAll();
                            foreach ($marcasList as $m): 
                            ?>
                                <option value="<?= htmlspecialchars($m['name']) ?>" <?= ($isEdit && ($producto['brand'] ?? '') === $m['name']) ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Subir Imágenes</label>
                        <input type="file" name="images[]" accept="image/*" multiple class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 pt-1.5">
                        <?php if ($isEdit && !empty($producto['images'])): ?>
                            <p class="text-xs text-muted-foreground mt-1">Imágenes actuales: <?= count($producto['images']) ?>.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl border border-border p-4 space-y-4">
                    <h3 class="font-display text-lg font-bold uppercase border-b border-border pb-2">Especificaciones Técnicas (Opcional)</h3>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Lugar de Origen</label>
                            <input type="text" name="origen" placeholder="Ej: FRANCE" value="<?= $isEdit ? htmlspecialchars($producto['origen'] ?? '') : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Certificación</label>
                            <input type="text" name="certificacion" placeholder="Ej: GENUINE AND BRAND NEW" value="<?= $isEdit ? htmlspecialchars($producto['certificacion'] ?? '') : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Modelo / Referencia</label>
                            <input type="text" name="modelo" placeholder="Ej: 28264952" value="<?= $isEdit ? htmlspecialchars($producto['modelo'] ?? '') : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm">
                        </div>
                    </div>
                </div>

                <!-- ACTUALIZADO: Añadida columna de Stock -->
                <div class="grid gap-6 md:grid-cols-3 p-4 bg-secondary/30 rounded-2xl border border-border">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Precio Normal (S/)</label>
                        <input type="number" step="0.01" name="price" required value="<?= $isEdit ? $producto['price'] : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" name="is_sale" id="is_sale" value="1" <?= $hasSale ? 'checked' : '' ?> onchange="document.getElementById('sale_price_container').classList.toggle('hidden', !this.checked)" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="is_sale" class="text-sm font-medium text-destructive font-bold">Oferta</label>
                        </div>
                        <div id="sale_price_container" class="<?= $hasSale ? '' : 'hidden' ?> space-y-2">
                            <label class="text-xs font-medium">Precio Rebajado (S/)</label>
                            <input type="number" step="0.01" name="sale_price" value="<?= $hasSale ? $producto['sale_price'] : '' ?>" class="w-full h-11 rounded-xl border border-destructive/50 bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-destructive">
                        </div>
                    </div>

                    <div class="space-y-2 border-l border-border pl-6">
                        <label class="text-sm font-medium">Stock Total (Unidades)</label>
                        <input type="number" step="1" min="0" name="stock" required value="<?= $isEdit ? ($producto['stock'] ?? 0) : 0 ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Descripción detallada</label>
                    <textarea name="description" rows="4" required class="w-full rounded-xl border border-input bg-background p-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"><?= $isEdit ? htmlspecialchars($producto['description']) : '' ?></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="featured" id="featured" <?= ($isEdit && $producto['featured']) ? 'checked' : '' ?> class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="featured" class="text-sm font-medium">Destacar producto en la página de inicio</label>
                </div>

                <div class="pt-4 border-t border-border">
                    <button type="submit" class="btn-base btn-cta !py-3 w-full shadow-[0_0_15px_rgba(224,193,18,0.3)]">
                        <?= $isEdit ? 'Guardar Cambios' : 'Añadir al Inventario' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>