<?php
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Categoria.php';

// Obtener solo los productos marcados como destacados en el dashboard
$featuredProducts = Producto::buscarFiltrados(); 
$featuredProducts = array_filter($featuredProducts, fn($p) => $p['featured'] == 1);
// Limitamos a 4 para que quede bien en la cuadrícula
$featuredProducts = array_slice($featuredProducts, 0, 4);

// CARGA DINÁMICA DE CATEGORÍAS
$categoriasBD = Categoria::getAll();
$categories = [];
foreach ($categoriasBD as $cat) {
    $categories[] = [
        "id" => $cat['slug'], 
        "name" => $cat['name'], 
        "icon" => $cat['icon']
    ];
}

$features = [
    ["icon" => "truck", "title" => "Envío 24/48 h", "text" => "Península y Baleares"],
    ["icon" => "shield-check", "title" => "Garantía 24 meses", "text" => "En componentes clave"],
    ["icon" => "wrench", "title" => "Probado en banco", "text" => "Informe incluido"],
    ["icon" => "gauge", "title" => "Soporte técnico", "text" => "Atención de mecánicos"]
];
?>

<!-- Hero -->
<section class="relative isolate overflow-hidden">
    <img src="<?= $_ENV['BASE_URL'] ?>assets/img/hero-diesel.jpg" alt="Motor diésel en taller" width="1920" height="1088" class="absolute inset-0 -z-10 h-full w-full object-cover">
    <div class="absolute inset-0 -z-10 bg-primary-deep/80"></div>
    <div class="mx-auto max-w-7xl px-4 py-24 md:px-6 md:py-36">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 rounded-full border border-accent/50 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-accent">
                Especialistas en inyección diésel
            </p>
            <h1 class="mt-6 font-display text-5xl font-bold uppercase leading-[0.95] text-primary-foreground sm:text-6xl md:text-7xl">
                Potencia que <span class="text-accent">no se detiene</span>
            </h1>
            <p class="mt-6 max-w-lg text-lg text-primary-foreground/80">
                Inyectores, turbos y equipos de diagnóstico probados en banco, listos para montar y con garantía real. Tu taller merece componentes fiables.
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="btn-base btn-cta !px-8 !py-4 !text-lg shadow-[0_0_20px_rgba(224,193,18,0.4)] transition-shadow hover:shadow-[0_0_30px_rgba(224,193,18,0.7)]">
                    Explorar Tienda <i data-lucide="arrow-right" class="h-5 w-5"></i>
                </a>
                <a href="<?= $_ENV['BASE_URL'] ?>contacto" class="btn-base btn-ghost-light !px-8 !py-4 !text-lg">
                    Hablar con un técnico
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Ventajas -->
<section class="border-b border-border bg-secondary/60">
    <ul class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:grid-cols-2 md:grid-cols-4 md:px-6">
        <?php foreach ($features as $f): ?>
            <li class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="<?= $f['icon'] ?>" class="h-5 w-5"></i>
                </span>
                <span class="min-w-0">
                    <span class="block truncate font-semibold"><?= $f['title'] ?></span>
                    <span class="block text-sm text-muted-foreground"><?= $f['text'] ?></span>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- Productos destacados -->
<section class="mx-auto max-w-7xl px-4 py-20 md:px-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-4xl font-bold uppercase">Productos destacados</h2>
            <p class="mt-2 text-muted-foreground">Lo más pedido por talleres esta temporada.</p>
        </div>
        <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="btn-base btn-outline">
            Ver toda la tienda <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </a>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($featuredProducts as $p): ?>
            <div class="h-full">
                <?php $product = $p; // Pasamos la variable $p como $product al componente ?>
                <?php include __DIR__ . '/../components/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Categorías -->
<section class="bg-secondary/50 py-20">
    <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div>
            <h2 class="font-display text-4xl font-bold uppercase">Categorías</h2>
            <p class="mt-2 text-muted-foreground">Encuentra la pieza exacta por sistema del motor.</p>
        </div>
        
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
            <?php foreach ($categories as $cat): ?>
                <div>
                    <a href="<?= $_ENV['BASE_URL'] ?>tienda?categoria=<?= $cat['id'] ?>" class="group flex h-full flex-col justify-between gap-8 rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-lg">
                        <span class="grid h-12 w-12 place-items-center rounded-xl bg-primary text-primary-foreground transition-transform duration-300 group-hover:scale-110">
                            <i data-lucide="<?= $cat['icon'] ?>" class="h-5 w-5"></i>
                        </span>
                        <span>
                            <span class="block font-display text-xl font-bold uppercase"><?= $cat['name'] ?></span>
                            <span class="mt-1 inline-flex items-center gap-1 text-sm text-primary">
                                Ver productos <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                            </span>
                        </span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Banda CTA -->
<section class="mx-auto max-w-7xl px-4 py-20 md:px-6">
    <div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-gradient-to-r from-primary-deep to-primary p-10 md:flex-row md:items-center md:p-14 shadow-xl shadow-primary-deep/20">
        <div>
            <h2 class="font-display text-3xl font-bold uppercase text-primary-foreground md:text-4xl">
                ¿No encuentras tu referencia?
            </h2>
            <p class="mt-2 max-w-xl text-primary-foreground/80">
                Envíanos la matrícula o el número de bastidor y localizamos la pieza compatible.
            </p>
        </div>
        <a href="<?= $_ENV['BASE_URL'] ?>contacto" class="btn-base btn-cta !px-8 !py-4 !text-lg shrink-0 shadow-[0_0_20px_rgba(224,193,18,0.4)] transition-shadow hover:shadow-[0_0_30px_rgba(224,193,18,0.7)]">
            Solicitar presupuesto
        </a>
    </div>
</section>