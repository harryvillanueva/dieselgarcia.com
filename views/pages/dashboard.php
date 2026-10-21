<?php
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php'; // NUEVO: Importar modelo de categorías

$busqueda = $_GET['q'] ?? '';
$categoria = $_GET['cat'] ?? '';
$productos = Producto::buscarFiltrados($busqueda, $categoria);

$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);
?>

<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    <!-- MENU LATERAL (SIDEBAR) -->
   <?php 
    $activeTab = 'inventario';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold uppercase text-foreground">Inventario de Productos</h1>
            </div>
            <a href="<?= $_ENV['BASE_URL'] ?>dashboard/crear" class="btn-base btn-cta !py-2.5 px-5 shadow-[0_0_15px_rgba(224,193,18,0.3)] flex items-center gap-2">
                <i data-lucide="plus" class="h-4 w-4"></i> Añadir Producto
            </a>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 rounded-xl bg-primary/10 p-4 text-sm text-primary font-medium border border-primary/20">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Buscador -->
        <div class="mb-6 rounded-2xl border border-border bg-card p-4 shadow-[var(--shadow-soft)]">
            <form method="GET" action="<?= $_ENV['BASE_URL'] ?>dashboard" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre..." class="w-full h-11 pl-10 rounded-xl border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="w-full md:w-48">
                    <!-- ACTUALIZADO: Select dinámico de categorías -->
                    <select name="cat" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="">Todas las categorías</option>
                        <?php 
                        $categoriasList = Categoria::getAll();
                        foreach ($categoriasList as $cat): 
                        ?>
                            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $categoria === $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-base bg-secondary text-foreground hover:bg-secondary/80 !py-2.5 px-6 rounded-xl font-semibold">Filtrar</button>
            </form>
        </div>

        <!-- Tabla -->
        <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-[var(--shadow-soft)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-secondary/50 border-b border-border uppercase text-xs font-semibold text-muted-foreground">
                        <tr>
                            <th class="px-6 py-4">Producto</th>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4">Precio</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($productos as $p): ?>
                            <tr class="hover:bg-secondary/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 rounded-lg border border-border bg-background overflow-hidden">
                                            <img src="<?= htmlspecialchars($p['images'][0] ?? '') ?>" class="h-full w-full object-cover">
                                        </div>
                                        <div class="font-medium text-foreground"><?= htmlspecialchars($p['name']) ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 capitalize"><?= htmlspecialchars($p['category']) ?></td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($p['sale_price'])): ?>
                                        <span class="text-destructive font-bold"><?= Producto::formatPrice($p['sale_price']) ?></span>
                                        <span class="text-xs text-muted-foreground line-through ml-1"><?= Producto::formatPrice($p['price']) ?></span>
                                    <?php else: ?>
                                        <span class="font-semibold"><?= Producto::formatPrice($p['price']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= $_ENV['BASE_URL'] ?>dashboard/editar?id=<?= $p['id'] ?>" class="grid h-8 w-8 place-items-center rounded-lg bg-secondary text-foreground hover:bg-primary transition-colors"><i data-lucide="edit-2" class="h-4 w-4"></i></a>
                                        <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" onsubmit="return confirm('¿Seguro que deseas eliminarlo?');" class="inline-block">
                                            <input type="hidden" name="admin_action" value="delete_product">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground transition-colors"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>