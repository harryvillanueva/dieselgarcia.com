<?php
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';
require_once __DIR__ . '/../../models/Marca.php';

$allProducts = Producto::getAll();

// CARGA DINÁMICA DE CATEGORÍAS
$categoriasBD = Categoria::getAll();
$categories = [];
$catMapping = []; 
foreach ($categoriasBD as $cat) {
    $categories[] = ["id" => $cat['slug'], "name" => $cat['name']];
    $catMapping[$cat['name']] = $cat['slug'];
}

// CARGA DINÁMICA DE MARCAS
$marcasBD = Marca::getAll();
$brands = array_column($marcasBD, 'name');

// 1. CAPTURAR LOS FILTROS DE LA URL
$selectedCats = isset($_GET['cat']) ? (is_array($_GET['cat']) ? $_GET['cat'] : [$_GET['cat']]) : [];

if (isset($_GET['categoria']) && !in_array($_GET['categoria'], $selectedCats)) {
    $selectedCats[] = $_GET['categoria'];
}

$selectedBrands = isset($_GET['brand']) ? (is_array($_GET['brand']) ? $_GET['brand'] : [$_GET['brand']]) : [];

// 2. LÓGICA DE FILTRADO
$visibleProducts = array_filter($allProducts, function($p) use ($selectedCats, $selectedBrands, $catMapping) {
    $pCatId = isset($catMapping[$p['category']]) ? $catMapping[$p['category']] : strtolower($p['category']);

    $passCat = empty($selectedCats) || in_array($pCatId, $selectedCats);
    $passBrand = empty($selectedBrands) || in_array($p['brand'], $selectedBrands);

    return $passCat && $passBrand;
});
?>

<div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
    <header class="mb-8">
        <h1 class="font-display text-4xl font-bold uppercase md:text-5xl">Tienda</h1>
        <p class="mt-2 text-muted-foreground">
            <?= count($visibleProducts) ?> productos disponibles para envío inmediato.
        </p>
    </header>

    <!-- Botón Móvil -->
    <button type="button" id="mobile-filter-btn" class="btn-base btn-outline mb-4 w-full lg:hidden">
        <i data-lucide="sliders-horizontal" class="h-4 w-4"></i> Mostrar filtros
    </button>

    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
        
        <!-- BARRA LATERAL DE FILTROS -->
        <aside id="filter-sidebar" class="hidden h-max rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] lg:sticky lg:top-24 lg:block">
            <h2 class="font-display text-lg font-bold uppercase">Filtros</h2>
            <hr class="my-4 border-border">
            
            <form id="filter-form" action="<?= $_ENV['BASE_URL'] ?>tienda" method="GET">
                <!-- Categorías -->
                <fieldset>
                    <legend class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                        Categoría
                    </legend>
                    <ul class="mt-3 space-y-3">
                        <?php foreach ($categories as $cat): ?>
                            <li class="flex items-center gap-3">
                                <input 
                                    type="checkbox" 
                                    id="cat-<?= $cat['id'] ?>" 
                                    name="cat[]" 
                                    value="<?= $cat['id'] ?>" 
                                    <?= in_array($cat['id'], $selectedCats) ? 'checked' : '' ?>
                                    class="js-filter-checkbox h-4 w-4 shrink-0 rounded-sm border-2 border-primary accent-primary cursor-pointer"
                                >
                                <label for="cat-<?= $cat['id'] ?>" class="cursor-pointer text-sm font-normal text-foreground">
                                    <?= $cat['name'] ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>

                <hr class="my-5 border-border">
                
                <!-- Marcas -->
                <fieldset>
                    <legend class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                        Marca
                    </legend>
                    <ul class="mt-3 space-y-3">
                        <?php foreach ($brands as $brand): ?>
                            <li class="flex items-center gap-3">
                                <input 
                                    type="checkbox" 
                                    id="brand-<?= $brand ?>" 
                                    name="brand[]" 
                                    value="<?= $brand ?>" 
                                    <?= in_array($brand, $selectedBrands) ? 'checked' : '' ?>
                                    class="js-filter-checkbox h-4 w-4 shrink-0 rounded-sm border-2 border-primary accent-primary cursor-pointer"
                                >
                                <label for="brand-<?= $brand ?>" class="cursor-pointer text-sm font-normal text-foreground">
                                    <?= $brand ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>

                <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="btn-base btn-ghost-light mt-6 w-full !text-foreground hover:!bg-secondary text-center">
                    Limpiar filtros
                </a>
            </form>
        </aside>

        <!-- CUADRÍCULA DE PRODUCTOS -->
        <section>
            <?php if (empty($visibleProducts)): ?>
                <p class="rounded-2xl border border-dashed border-border p-12 text-center text-muted-foreground">
                    No hay productos que coincidan con estos filtros.
                </p>
            <?php else: ?>
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <?php 
                    $index = 0;
                    foreach ($visibleProducts as $product): ?>
                        <div class="reveal-hidden h-full" style="transition-delay: <?= ($index % 6) * 60 ?>ms">
                            <?php include __DIR__ . '/../components/product-card.php'; ?>
                        </div>
                    <?php 
                    $index++;
                    endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>