<?php
require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/Producto.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: " . $_ENV['BASE_URL'] . "dashboard/pedidos");
    exit;
}

// Obtener datos del pedido
$todosLosPedidos = Pedido::getAll();
$pedido = array_filter($todosLosPedidos, fn($p) => $p['id'] == $id);
$pedido = reset($pedido); 

if (!$pedido) {
    echo "<div class='p-8 text-center text-red-500'>Pedido no encontrado.</div>";
    exit;
}

$detalles = Pedido::getDetalles($id);

$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);

$badges = [
    'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'procesando' => 'bg-blue-100 text-blue-800 border-blue-200',
    'completado' => 'bg-green-100 text-green-800 border-green-200',
    'cancelado' => 'bg-red-100 text-red-800 border-red-200',
];

// LÓGICA PARA BUSCAR EL COMPROBANTE DE YAPE/PLIN
$comprobanteUrl = null;
$comprobanteDir = __DIR__ . '/../../public/uploads/comprobantes/';
// Busca cualquier archivo que empiece por "comprobante_pedido_ID" sin importar si es pdf, jpg o png
$comprobanteFiles = glob($comprobanteDir . 'comprobante_pedido_' . $pedido['id'] . '.*');

if (!empty($comprobanteFiles)) {
    $fileName = basename($comprobanteFiles[0]);
    // Generamos la URL pública para el enlace
    $comprobanteUrl = $_ENV['BASE_URL'] . 'public/uploads/comprobantes/' . $fileName;
}
?>

<div class="mx-auto max-w-5xl px-4 py-8 md:px-6">
    <a href="<?= $_ENV['BASE_URL'] ?>dashboard/pedidos" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground mb-6 transition-colors">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Volver a pedidos
    </a>

    <?php if ($success): ?>
        <div class="mb-6 rounded-xl bg-green-100 p-4 text-sm text-green-800 border border-green-200">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 md:grid-cols-[1fr_300px]">
        
        <div class="space-y-6">
            <!-- Cabecera del Pedido -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="font-display text-2xl font-bold uppercase text-foreground">Pedido DG-<?= str_pad($pedido['id'], 5, "0", STR_PAD_LEFT) ?></h1>
                    <p class="text-sm text-muted-foreground mt-1"><i data-lucide="calendar" class="inline h-4 w-4 mr-1"></i> <?= date('d de F Y, H:i', strtotime($pedido['fecha'])) ?></p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold border <?= $badges[$pedido['estado']] ?? 'bg-gray-100' ?> uppercase tracking-wider">
                    <?= $pedido['estado'] ?>
                </span>
            </div>

            <!-- Lista de Productos -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
                <h2 class="font-display text-lg font-bold uppercase border-b border-border pb-3 mb-4">Artículos comprados</h2>
                <ul class="space-y-4">
                    <?php foreach ($detalles as $d): ?>
                        <?php 
                        // Decodificar array de imágenes del producto
                        $imgs = json_decode($d['images'], true);
                        $firstImg = is_array($imgs) ? $imgs[0] : $d['images'];
                        $imgUrl = (strpos($firstImg, 'http') === 0) ? $firstImg : $_ENV['BASE_URL'] . 'assets/img/' . $firstImg;
                        ?>
                        <li class="flex items-center gap-4 py-2">
                            <img src="<?= $imgUrl ?>" class="h-16 w-16 rounded-xl object-cover border border-border bg-secondary">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-foreground truncate"><?= htmlspecialchars($d['producto_nombre']) ?></p>
                                <p class="text-sm text-muted-foreground">ID Producto: <?= $d['producto_id'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-foreground"><?= Producto::formatPrice($d['precio_unitario']) ?></p>
                                <p class="text-sm text-muted-foreground">Cant: <?= $d['cantidad'] ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Datos del Cliente y Método de Pago -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
                <h2 class="font-display text-lg font-bold uppercase border-b border-border pb-3 mb-4">Información del Cliente</h2>
                <div class="space-y-3 text-sm">
                    <p><strong>Nombre:</strong><br><?= htmlspecialchars($pedido['cliente_nombre']) ?></p>
                    <p><strong>Email:</strong><br><a href="mailto:<?= htmlspecialchars($pedido['cliente_email']) ?>" class="text-primary hover:underline"><?= htmlspecialchars($pedido['cliente_email']) ?></a></p>
                    <p><strong>Dirección de Envío:</strong><br><span class="text-muted-foreground"><?= htmlspecialchars($pedido['direccion_envio']) ?></span></p>
                    
                    <p class="pt-2 border-t border-border">
                        <strong>Método de pago:</strong><br>
                        <span class="uppercase font-semibold text-primary"><?= str_replace('_', ' ', $pedido['metodo_pago']) ?></span>
                    </p>

                    <!-- CAJA DEL COMPROBANTE -->
                    <?php if ($comprobanteUrl): ?>
                        <div class="mt-4 rounded-xl border border-border bg-secondary/50 p-4">
                            <p class="mb-3 text-sm font-semibold flex items-center gap-2">
                                <i data-lucide="file-check" class="h-4 w-4 text-green-600"></i> Comprobante adjunto
                            </p>
                            <a href="<?= $comprobanteUrl ?>" target="_blank" class="btn-base btn-outline bg-background hover:bg-primary !py-2 w-full text-xs flex justify-center gap-2">
                                <i data-lucide="external-link" class="h-4 w-4"></i> Ver documento
                            </a>
                        </div>
                    <?php elseif ($pedido['metodo_pago'] === 'yape_plin'): ?>
                        <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-3 text-xs font-medium text-yellow-800">
                            <i data-lucide="alert-circle" class="inline h-4 w-4 mr-1"></i> No se encontró comprobante para este pedido.
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Resumen y Cambio de Estado -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
                <h2 class="font-display text-lg font-bold uppercase border-b border-border pb-3 mb-4">Actualizar Pedido</h2>
                <div class="flex justify-between items-center mb-6">
                    <span class="font-semibold">Total cobrado:</span>
                    <span class="font-display text-2xl font-bold text-primary"><?= Producto::formatPrice($pedido['total']) ?></span>
                </div>

                <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" class="space-y-3">
                    <input type="hidden" name="admin_action" value="update_order_status">
                    <input type="hidden" name="id" value="<?= $pedido['id'] ?>">
                    <select name="estado" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                        <option value="pendiente" <?= $pedido['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente de revisión</option>
                        <option value="procesando" <?= $pedido['estado'] === 'procesando' ? 'selected' : '' ?>>Procesando envío</option>
                        <option value="completado" <?= $pedido['estado'] === 'completado' ? 'selected' : '' ?>>Completado / Entregado</option>
                        <option value="cancelado" <?= $pedido['estado'] === 'cancelado' ? 'selected' : '' ?>>Cancelado / Reembolsado</option>
                    </select>
                    <button type="submit" class="btn-base btn-cta !py-2.5 w-full">Guardar estado</button>
                </form>
            </div>
        </div>

    </div>
</div>