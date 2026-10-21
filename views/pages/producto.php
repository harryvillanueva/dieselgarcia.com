<?php
require_once __DIR__ . '/../../models/Producto.php';

$slug = $params['slug'] ?? null;
$product = $slug ? Producto::getBySlug($slug) : null;

if (!$product) {
    echo '<div class="flex min-h-[50vh] items-center justify-center"><div class="text-center"><h1 class="font-display text-7xl font-bold text-primary">404</h1><h2 class="mt-4 text-xl font-semibold text-foreground">Producto no encontrado</h2><a href="' . $_ENV['BASE_URL'] . 'tienda" class="btn-base btn-cta mt-6">Volver a la tienda</a></div></div>';
    return;
}

$gallery = $product['images']; 
$allProducts = Producto::getAll();
$related = array_slice(array_filter($allProducts, fn($p) => $p['id'] !== $product['id']), 0, 3);

// Evaluar el stock actual
$stockActual = (int)($product['stock'] ?? 0);
$hayStock = $stockActual > 0;

function getImgUrl($img) {
    global $_ENV;
    return (strpos($img, 'http') === 0) ? $img : $_ENV['BASE_URL'] . 'assets/img/' . $img;
}
?>

<div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
    <nav aria-label="Migas de pan" class="mb-8 text-sm text-muted-foreground">
        <a href="<?= $_ENV['BASE_URL'] ?>" class="hover:text-primary">Inicio</a>
        <span class="px-2">/</span>
        <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="hover:text-primary">Tienda</a>
        <span class="px-2">/</span>
        <span class="text-foreground"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-2">
        <section>
            <div class="overflow-hidden rounded-3xl border border-border bg-muted shadow-[var(--shadow-soft)] relative">
                <!-- Etiqueta visual de Agotado -->
                <?php if (!$hayStock): ?>
                    <span class="absolute top-4 left-4 z-10 rounded-full bg-red-600 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-white shadow-lg">Agotado</span>
                <?php endif; ?>

                <img 
                    id="main-gallery-image"
                    src="<?= getImgUrl($gallery[0]) ?>" 
                    alt="<?= htmlspecialchars($product['name']) ?>" 
                    class="aspect-square w-full object-cover transition-transform duration-500 hover:scale-105"
                >
            </div>
            <?php if(count($gallery) > 1): ?>
            <ul class="mt-4 grid grid-cols-3 gap-4 md:grid-cols-4">
                <?php foreach ($gallery as $index => $img): ?>
                    <li>
                        <button 
                            type="button" 
                            class="js-gallery-thumb w-full overflow-hidden rounded-2xl border-2 bg-muted transition-all <?= $index === 0 ? 'border-primary ring-2 ring-primary/20' : 'border-transparent hover:border-border' ?>"
                            data-src="<?= getImgUrl($img) ?>"
                            onclick="document.getElementById('main-gallery-image').src = this.getAttribute('data-src'); document.querySelectorAll('.js-gallery-thumb').forEach(b => {b.classList.remove('border-primary', 'ring-2', 'ring-primary/20'); b.classList.add('border-transparent');}); this.classList.remove('border-transparent'); this.classList.add('border-primary', 'ring-2', 'ring-primary/20');"
                        >
                            <img src="<?= getImgUrl($img) ?>" alt="Miniatura" loading="lazy" class="aspect-square w-full object-cover">
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <section>
            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                <?= htmlspecialchars($product['brand'] ?? 'GENÉRICO') ?>
            </p>
            <h1 class="mt-2 font-display text-3xl font-bold uppercase md:text-4xl lg:text-5xl leading-tight">
                <?= htmlspecialchars($product['name']) ?>
            </h1>
            <p class="mt-3 flex items-center gap-1 text-sm text-muted-foreground">
                <i data-lucide="star" class="h-4 w-4 fill-accent text-accent"></i> 
                <?= number_format($product['rating'] ?? 5.0, 1) ?> · 128 valoraciones
            </p>

            <p class="mt-6 flex items-end gap-3">
                <span class="font-display text-5xl font-bold text-primary">
                    <?= Producto::formatPrice($product['sale_price'] ?? $product['price']) ?>
                </span>
                <?php if (!empty($product['sale_price'])): ?>
                    <span class="text-lg text-muted-foreground line-through pb-1">
                        <?= Producto::formatPrice($product['price']) ?>
                    </span>
                <?php endif; ?>
            </p>

            <p class="mt-5 text-muted-foreground leading-relaxed text-sm md:text-base">
                <?= htmlspecialchars($product['description']) ?>
            </p>

            <dl class="mt-6 grid gap-2 rounded-2xl bg-secondary/50 p-5 text-sm border border-border">
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">Disponibilidad</dt>
                    <?php if ($hayStock): ?>
                        <dd class="font-semibold text-green-600">Disponible</dd>
                    <?php else: ?>
                        <dd class="font-semibold text-red-600">Agotado</dd>
                    <?php endif; ?>
                </div>
            </dl>

            <!-- Bloquear formulario si no hay stock -->
            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>carrito" class="mt-7 flex flex-wrap items-center gap-4 <?= $hayStock ? 'js-add-to-cart-form' : '' ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="flex items-center rounded-xl border border-border bg-card <?= !$hayStock ? 'opacity-50 pointer-events-none' : '' ?>">
                    <button type="button" onclick="let q = document.getElementById('qty'); if(q.value>1)q.value--;" class="grid h-12 w-12 place-items-center rounded-l-xl hover:bg-secondary transition-colors"><i data-lucide="minus" class="h-4 w-4"></i></button>
                    <!-- Bloquear input máximo según stock -->
                    <input type="number" id="qty" name="qty" value="1" min="1" max="<?= $stockActual ?>" class="w-14 text-center font-semibold bg-transparent border-0 focus:ring-0 p-0">
                    <button type="button" onclick="let q = document.getElementById('qty'); if(q.value < <?= $stockActual ?>)q.value++;" class="grid h-12 w-12 place-items-center rounded-r-xl hover:bg-secondary transition-colors"><i data-lucide="plus" class="h-4 w-4"></i></button>
                </div>
                
                <button type="submit" <?= !$hayStock ? 'disabled' : '' ?> class="btn-base btn-cta !px-8 !py-3.5 flex-1 min-w-[220px] <?= !$hayStock ? '!bg-gray-400 !text-gray-200 !shadow-none cursor-not-allowed border-none' : '' ?>">
                    <i data-lucide="shopping-cart" class="h-5 w-5"></i> 
                    <?= $hayStock ? 'Añadir al Carrito' : 'Producto Agotado' ?>
                </button>
            </form>

            <hr class="my-7 border-border">

            <?php if (!empty($product['origen']) || !empty($product['certificacion']) || !empty($product['modelo'])): ?>
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-muted-foreground mb-3 uppercase tracking-wider">Detalles del Producto:</h3>
                    <div class="overflow-hidden rounded-xl border border-border bg-card">
                        <table class="w-full text-sm text-left">
                            <tbody class="divide-y divide-border">
                                <?php if (!empty($product['origen'])): ?>
                                <tr class="divide-x divide-border">
                                    <th class="px-4 py-3 bg-secondary/30 font-medium text-foreground w-1/3">Lugar de Origen:</th>
                                    <td class="px-4 py-3 text-muted-foreground"><?= htmlspecialchars($product['origen']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="divide-x divide-border">
                                    <th class="px-4 py-3 bg-secondary/30 font-medium text-foreground w-1/3">Marca:</th>
                                    <td class="px-4 py-3 text-muted-foreground uppercase"><?= htmlspecialchars($product['brand']) ?></td>
                                </tr>
                                <?php if (!empty($product['certificacion'])): ?>
                                <tr class="divide-x divide-border">
                                    <th class="px-4 py-3 bg-secondary/30 font-medium text-foreground w-1/3">Certificación:</th>
                                    <td class="px-4 py-3 text-muted-foreground"><?= htmlspecialchars($product['certificacion']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($product['modelo'])): ?>
                                <tr class="divide-x divide-border">
                                    <th class="px-4 py-3 bg-secondary/30 font-medium text-foreground w-1/3">Modelo:</th>
                                    <td class="px-4 py-3 text-muted-foreground"><?= htmlspecialchars($product['modelo']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <section class="mt-24">
        <h2 class="font-display text-2xl font-bold uppercase mb-8 border-b border-border pb-3">También te puede interesar</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($related as $p): ?>
                <?php $product = $p; ?>
                <div class="reveal-hidden">
                    <?php include __DIR__ . '/../components/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>