<?php
// Validar si la imagen es una ruta completa (subida) o un archivo local de assets
$imgPath = $product['images'][0] ?? 'default.jpg';
$imgUrl = (strpos($imgPath, 'http') === 0) ? $imgPath : $_ENV['BASE_URL'] . 'assets/img/' . $imgPath;
?>
<article class="surface-card group flex h-full flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] transition-transform hover:-translate-y-1">
    
    <!-- Enlace e Imagen (Altura fija h-56 para mantener la cuadrícula uniforme) -->
    <a href="<?= $_ENV['BASE_URL'] ?>producto/<?= $product['id'] ?>" class="zoom-media relative block h-56 w-full shrink-0 bg-secondary overflow-hidden">
        <img 
            src="<?= $imgUrl ?>" 
            alt="Fotografía de repuesto diésel <?= htmlspecialchars(strtolower($product['name'])) ?>" 
            loading="lazy" 
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
        >
        <!-- Etiqueta de Oferta -->
        <?php if (!empty($product['sale_price'])): ?>
            <span class="absolute left-3 top-3 rounded-full bg-destructive px-3 py-1 text-xs font-bold text-destructive-foreground uppercase tracking-wider shadow-sm">
                Oferta
            </span>
        <?php endif; ?>
    </a>

    <!-- Contenido de la Tarjeta -->
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            <?= htmlspecialchars($product['category']) ?>
        </p>
        <h3 class="mt-1 line-clamp-2 text-lg font-semibold leading-tight text-foreground">
            <a href="<?= $_ENV['BASE_URL'] ?>producto/<?= $product['id'] ?>" class="hover:text-primary transition-colors">
                <?= htmlspecialchars($product['name']) ?>
            </a>
        </h3>
        <p class="mt-1 flex items-center gap-1 text-sm text-muted-foreground">
            <i data-lucide="star" class="h-3.5 w-3.5 fill-accent text-accent"></i>
            <?= number_format($product['rating'] ?? 5.0, 1) ?> · <?= htmlspecialchars($product['brand'] ?? 'Genérico') ?>
        </p>

        <!-- Contenedor inferior (mt-auto empuja esto al fondo para igualar alturas) -->
        <div class="mt-auto flex flex-col gap-3 pt-4">
            
            <!-- Fila: Precios y Botón de Carrito -->
            <div class="flex items-end justify-between gap-3">
                <div class="min-w-0 flex flex-col">
                    <?php if (!empty($product['sale_price'])): ?>
                        <span class="font-display text-2xl font-bold text-destructive">
                            <?= Producto::formatPrice($product['sale_price']) ?>
                        </span>
                        <span class="text-sm text-muted-foreground line-through">
                            <?= Producto::formatPrice($product['price']) ?>
                        </span>
                    <?php else: ?>
                        <span class="font-display text-2xl font-bold text-primary">
                            <?= Producto::formatPrice($product['price']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- NUEVO: Agregada la clase js-add-to-cart-form -->
                <form method="POST" action="<?= $_ENV['BASE_URL'] ?>carrito" class="m-0 js-add-to-cart-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn-base btn-cta shrink-0 px-4 py-2 text-sm shadow-[0_0_10px_rgba(224,193,18,0.3)]">
                        Añadir
                    </button>
                </form>
            </div>
            
            <!-- Botón de WhatsApp -->
            <?php 
            require_once __DIR__ . '/../../models/Configuracion.php';
            $numeroWs = Configuracion::get('whatsapp');
            $precioReal = !empty($product['sale_price']) ? $product['sale_price'] : $product['price'];
            $mensajeWs = urlencode("Hola, me interesa comprar el producto: " . $product['name'] . " por " . Producto::formatPrice($precioReal));
            ?>
            <a href="https://wa.me/<?= $numeroWs ?>?text=<?= $mensajeWs ?>" target="_blank" class="w-full rounded-lg bg-[#25D366] px-4 py-2 text-center text-sm font-bold text-white transition-colors hover:bg-[#1DA851] flex items-center justify-center gap-2">
                <i data-lucide="message-circle" class="h-4 w-4"></i> Comprar por WhatsApp
            </a>

        </div>
    </div>
</article>