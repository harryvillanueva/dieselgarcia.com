<?php
// Ya no procesamos POST aquí. Solo leemos la sesión que el Front Controller preparó.
$lines = $cart_lines ?? [];
$subtotal = $cart_subtotal ?? 0;
$shipping = $cart_shipping ?? 0;
$total = $cart_total ?? 0;
?>

<div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
    <h1 class="font-display text-4xl font-bold uppercase md:text-5xl">Carrito</h1>

    <?php if (empty($lines)): ?>
        <div class="mt-10 rounded-3xl border border-dashed border-border p-14 text-center">
            <p class="text-lg font-semibold">Tu carrito está vacío</p>
            <p class="mt-2 text-muted-foreground">
                Añade componentes desde la tienda para verlos aquí.
            </p>
            <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="btn-base btn-cta !px-8 !py-3.5 !text-lg mt-6 inline-flex shadow-[0_0_20px_rgba(224,193,18,0.4)] transition-shadow hover:shadow-[0_0_30px_rgba(224,193,18,0.7)]">
                Ir a la tienda
            </a>
        </div>
    <?php else: ?>
        <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
            <!-- LÍNEAS DE PRODUCTOS -->
            <ul class="space-y-4">
                <?php foreach ($lines as $line): 
                    $p = $line['product'];
                    $qty = $line['qty'];
                    $precioReal = !empty($p['sale_price']) ? floatval($p['sale_price']) : floatval($p['price']);
                    
                    // Asegurar carga de imagen dinámica
                    $imgPath = $p['images'][0] ?? 'default.jpg';
                    $imgUrl = (strpos($imgPath, 'http') === 0) ? $imgPath : $_ENV['BASE_URL'] . 'assets/img/' . $imgPath;
                ?>
                    <li class="grid grid-cols-[80px_minmax(0,1fr)] items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-[var(--shadow-soft)] sm:grid-cols-[96px_minmax(0,1fr)_auto]">
                        
                        <img 
                            src="<?= $imgUrl ?>" 
                            alt="<?= htmlspecialchars($p['name']) ?>" 
                            loading="lazy" 
                            width="1024" height="1024" 
                            class="aspect-square w-full rounded-xl object-cover"
                        >
                        
                        <div class="min-w-0">
                            <a href="<?= $_ENV['BASE_URL'] ?>producto/<?= $p['id'] ?>" class="block truncate font-semibold hover:text-primary transition-colors">
                                <?= htmlspecialchars($p['name']) ?>
                            </a>
                            <p class="text-sm text-muted-foreground">
                                <?php if (!empty($p['sale_price'])): ?>
                                    <span class="text-destructive font-bold"><?= Producto::formatPrice($precioReal) ?></span>
                                    <span class="text-xs line-through ml-1"><?= Producto::formatPrice($p['price']) ?></span> / unidad
                                <?php else: ?>
                                    <?= Producto::formatPrice($precioReal) ?> / unidad
                                <?php endif; ?>
                            </p>
                            
                            <div class="mt-3 flex items-center gap-3">
                                <!-- Controles de Cantidad -->
                                <div class="flex items-center rounded-lg border border-border">
                                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>carrito" class="m-0 flex">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="qty" value="<?= $qty - 1 ?>">
                                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-l-md hover:bg-secondary text-foreground transition-colors" aria-label="Restar unidad">
                                            <i data-lucide="minus" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                    
                                    <span class="w-8 text-center text-sm font-semibold"><?= $qty ?></span>
                                    
                                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>carrito" class="m-0 flex">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="qty" value="<?= $qty + 1 ?>">
                                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-r-md hover:bg-secondary text-foreground transition-colors" aria-label="Sumar unidad">
                                            <i data-lucide="plus" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Botón Eliminar -->
                                <form method="POST" action="<?= $_ENV['BASE_URL'] ?>carrito" class="m-0 flex">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i> Quitar
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <p class="col-span-2 text-right font-display text-2xl font-bold text-primary sm:col-span-1">
                            <?= Producto::formatPrice($precioReal * $qty) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- RESUMEN DEL PEDIDO -->
            <aside class="h-max rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] lg:sticky lg:top-24">
                <h2 class="font-display text-xl font-bold uppercase">Resumen del pedido</h2>
                <hr class="my-4 border-border">
                
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Subtotal</dt>
                        <dd class="font-semibold"><?= Producto::formatPrice($subtotal) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Envío estimado</dt>
                        <dd class="font-semibold">
                            <?= $shipping === 0 ? "Gratis" : Producto::formatPrice($shipping) ?>
                        </dd>
                    </div>
                </dl>
                
                <hr class="my-4 border-border">
                
                <div class="flex items-baseline justify-between">
                    <span class="font-semibold uppercase">Total</span>
                    <span class="font-display text-3xl font-bold text-primary">
                        <?= Producto::formatPrice($total) ?>
                    </span>
                </div>
                
                <!-- Botón de Checkout normal -->
                <a href="<?= $_ENV['BASE_URL'] ?>checkout" class="btn-base btn-cta !py-3.5 w-full !text-lg mt-6 shadow-[0_0_20px_rgba(224,193,18,0.4)] transition-shadow hover:shadow-[0_0_30px_rgba(224,193,18,0.7)]">
                    Finalizar compra <i data-lucide="arrow-right" class="h-5 w-5"></i>
                </a>

                <!-- NUEVO: Botón de WhatsApp con el resumen del carrito -->
                <?php 
                require_once __DIR__ . '/../../models/Configuracion.php';
                $numeroWs = Configuracion::get('whatsapp') ?? '34600000000';
                
                // Construimos el mensaje de WhatsApp línea por línea
                $mensajePedido = "Hola, me gustaría confirmar el siguiente pedido:%0A%0A";
                foreach($lines as $item) {
                    $nombreLimpio = urlencode($item['product']['name']);
                    $mensajePedido .= "▪ " . $item['qty'] . "x " . $nombreLimpio . "%0A";
                }
                $mensajePedido .= "%0A*Total estimado: " . urlencode(Producto::formatPrice($total)) . "*";
                ?>
                <a href="https://wa.me/<?= $numeroWs ?>?text=<?= $mensajePedido ?>" target="_blank" class="w-full rounded-xl bg-[#25D366] px-4 py-3.5 text-center text-lg font-bold text-white transition-colors hover:bg-[#1DA851] flex items-center justify-center gap-2 mt-3 shadow-[0_0_15px_rgba(37,211,102,0.3)]">
                    <i data-lucide="message-circle" class="h-5 w-5"></i> Hacer pedido por WhatsApp
                </a>
                
                <p class="mt-4 text-center text-xs text-muted-foreground">
                    Pedido seguro y protegido.
                </p>
            </aside>
        </div>
    <?php endif; ?>
</div>