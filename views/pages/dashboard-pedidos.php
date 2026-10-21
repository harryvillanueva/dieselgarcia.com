<?php
require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/Producto.php'; 

$filtro_estado = $_GET['estado'] ?? '';
$filtro_fecha = $_GET['fecha'] ?? '';

$todosLosPedidos = Pedido::getAll();

$pedidos = array_filter($todosLosPedidos, function($p) use ($filtro_estado, $filtro_fecha) {
    $passEstado = empty($filtro_estado) || $p['estado'] === $filtro_estado;
    $passFecha = empty($filtro_fecha) || strpos($p['fecha'], $filtro_fecha) === 0;
    return $passEstado && $passFecha;
});

$badges = [
    'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'procesando' => 'bg-blue-100 text-blue-800 border-blue-200',
    'completado' => 'bg-green-100 text-green-800 border-green-200',
    'cancelado' => 'bg-red-100 text-red-800 border-red-200',
];
?>

<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    
    <?php 
    $activeTab = 'pedidos';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <main class="flex-1 space-y-6">
        <div>
            <h1 class="font-display text-2xl font-bold uppercase text-foreground">Gestion de Pedidos</h1>
            <p class="text-sm text-muted-foreground mt-1">Mostrando <?= count($pedidos) ?> pedidos.</p>
        </div>

        <div class="rounded-2xl border border-border bg-card p-4 shadow-[var(--shadow-soft)]">
            <form method="GET" action="<?= $_ENV['BASE_URL'] ?>dashboard/pedidos" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full space-y-1">
                    <label class="text-xs font-bold uppercase text-muted-foreground">Filtrar por Estado</label>
                    <select name="estado" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="procesando" <?= $filtro_estado === 'procesando' ? 'selected' : '' ?>>Procesando</option>
                        <option value="completado" <?= $filtro_estado === 'completado' ? 'selected' : '' ?>>Completado</option>
                        <option value="cancelado" <?= $filtro_estado === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="flex-1 w-full space-y-1">
                    <label class="text-xs font-bold uppercase text-muted-foreground">Filtrar por Fecha</label>
                    <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <a href="<?= $_ENV['BASE_URL'] ?>dashboard/pedidos" class="btn-base bg-secondary text-foreground !py-2.5 px-4 rounded-xl">Limpiar</a>
                    <button type="submit" class="btn-base btn-cta !py-2.5 px-6 rounded-xl flex-1 md:flex-none">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-[var(--shadow-soft)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-secondary/50 border-b border-border uppercase text-xs font-semibold text-muted-foreground">
                        <tr>
                            <th class="px-6 py-4">Ref. / Fecha</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4 text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php if (empty($pedidos)): ?>
                            <tr><td colspan="5" class="px-6 py-8 text-center text-muted-foreground">No hay pedidos que coincidan con los filtros.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $p): ?>
                                <tr class="hover:bg-secondary/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="block font-mono font-medium text-foreground">DG-<?= str_pad($p['id'], 5, "0", STR_PAD_LEFT) ?></span>
                                        <span class="text-xs text-muted-foreground"><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="block font-medium"><?= htmlspecialchars($p['cliente_nombre']) ?></span>
                                        <span class="text-xs text-muted-foreground"><?= htmlspecialchars($p['cliente_email']) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $badges[$p['estado']] ?? 'bg-gray-100' ?>">
                                            <?= ucfirst($p['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-primary">
                                        <?= Producto::formatPrice($p['total']) ?>
                                    </td>
                                    <td class="p-4 align-middle text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?= $_ENV['BASE_URL'] ?>dashboard/pedidos/detalle?id=<?= $p['id'] ?>" class="btn-base btn-outline bg-background hover:bg-secondary !py-1.5 !px-3 text-xs">
                                                Ver detalle <i data-lucide="arrow-right" class="h-3 w-3 ml-1"></i>
                                            </a>
                                            
                                            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" onsubmit="return confirm('¿Estás seguro de eliminar este pedido? Esta acción no se puede deshacer.');" class="inline-block">
                                                <input type="hidden" name="admin_action" value="delete_order">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <button type="submit" title="Eliminar pedido" class="grid h-8 w-8 place-items-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>