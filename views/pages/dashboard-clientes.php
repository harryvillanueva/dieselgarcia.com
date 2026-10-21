<?php
require_once __DIR__ . '/../../models/Usuario.php';

$todosLosClientes = Usuario::getClientes();

// Buscador
$buscar = $_GET['buscar'] ?? '';
$clientes = array_filter($todosLosClientes, function($c) use ($buscar) {
    if (empty($buscar)) return true;
    return stripos($c['nombre'], $buscar) !== false || stripos($c['email'], $buscar) !== false;
});

// Comprobar si estamos editando
$editId = $_GET['edit'] ?? null;
$editCliente = $editId ? Usuario::obtenerPorId($editId) : null;
$isEdit = $editCliente !== null;

$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);
?>

<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    
    <!-- MENU LATERAL -->
    <?php 
    $activeTab = 'clientes';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <main class="flex-1 space-y-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold uppercase text-foreground">Directorio de Clientes</h1>
                <p class="text-sm text-muted-foreground mt-1">Total registrados: <?= count($todosLosClientes) ?></p>
            </div>
            
            <!-- Buscador -->
            <form method="GET" action="<?= $_ENV['BASE_URL'] ?>dashboard/clientes" class="flex gap-2">
                <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar nombre o email..." class="h-11 rounded-xl border border-input bg-background px-4 text-sm focus:ring-2 focus:ring-primary w-full sm:w-64">
                <button type="submit" class="btn-base btn-primary !py-2.5 !px-4 rounded-xl"><i data-lucide="search" class="h-4 w-4"></i></button>
                <?php if(!empty($buscar)): ?>
                    <a href="<?= $_ENV['BASE_URL'] ?>dashboard/clientes" class="btn-base bg-secondary !py-2.5 !px-4 rounded-xl"><i data-lucide="x" class="h-4 w-4"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <!-- FORMULARIO DE EDICIÓN (Aparece si pulsamos el lápiz) -->
        <?php if ($isEdit): ?>
            <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] relative">
                <h2 class="font-display text-xl font-bold uppercase mb-4 text-primary">Editar Cliente: <?= htmlspecialchars($editCliente['nombre']) ?></h2>
                <a href="<?= $_ENV['BASE_URL'] ?>dashboard/clientes" class="absolute top-6 right-6 text-muted-foreground hover:text-foreground"><i data-lucide="x" class="h-5 w-5"></i></a>
                
                <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" class="grid gap-4 sm:grid-cols-2 items-end">
                    <input type="hidden" name="admin_action" value="edit_client">
                    <input type="hidden" name="id" value="<?= $editCliente['id'] ?>">
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Nombre / Taller</label>
                        <input type="text" name="nombre" required value="<?= htmlspecialchars($editCliente['nombre']) ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Email (No editable)</label>
                        <input type="text" disabled value="<?= htmlspecialchars($editCliente['email']) ?>" class="w-full h-11 rounded-xl border border-input bg-secondary text-muted-foreground px-3 text-sm cursor-not-allowed">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Teléfono</label>
                        <input type="text" name="telefono" value="<?= htmlspecialchars($editCliente['telefono'] ?? '') ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Dirección</label>
                        <input type="text" name="direccion" value="<?= htmlspecialchars($editCliente['direccion'] ?? '') ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="sm:col-span-2 space-y-2">
                        <label class="text-sm font-medium">Nueva Contraseña <span class="text-xs font-normal text-muted-foreground">(Dejar en blanco para no cambiar)</span></label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div class="sm:col-span-2 pt-2">
                        <button type="submit" class="btn-base btn-cta !py-2.5 px-6 rounded-xl w-full sm:w-auto">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- TABLA DE CLIENTES -->
        <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-[var(--shadow-soft)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-secondary/50 border-b border-border uppercase text-xs font-semibold text-muted-foreground">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Contacto</th>
                            <th class="px-6 py-4">Dirección</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">No se encontraron clientes.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $c): ?>
                                <tr class="hover:bg-secondary/20 transition-colors">
                                    <td class="px-6 py-4 font-mono text-muted-foreground">#<?= $c['id'] ?></td>
                                    <td class="px-6 py-4 font-medium text-foreground">
                                        <?= htmlspecialchars($c['nombre']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col text-muted-foreground">
                                            <span class="text-foreground"><?= htmlspecialchars($c['email']) ?></span>
                                            <span class="text-xs"><?= htmlspecialchars($c['telefono'] ?? 'Sin teléfono') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-muted-foreground truncate max-w-[200px]" title="<?= htmlspecialchars($c['direccion'] ?? '') ?>">
                                        <?= htmlspecialchars($c['direccion'] ?? 'No especificada') ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?= $_ENV['BASE_URL'] ?>dashboard/clientes?edit=<?= $c['id'] ?>" class="grid h-8 w-8 place-items-center rounded-lg bg-secondary text-foreground hover:bg-primary transition-colors" title="Editar / Clave">
                                                <i data-lucide="edit-2" class="h-4 w-4"></i>
                                            </a>
                                            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" onsubmit="confirmarEliminacion(event, this);" class="inline-block">
                                                <input type="hidden" name="admin_action" value="delete_client">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground transition-colors" title="Eliminar Cliente">
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

<!-- SCRIPT SWEETALERT2 -->
<script>
    <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Operación exitosa!',
            text: '<?= addslashes($success) ?>',
            confirmButtonColor: '#e0c112',
            background: 'var(--card)',
            color: 'var(--foreground)'
        });
    <?php endif; ?>

    function confirmarEliminacion(event, form) {
        event.preventDefault(); 
        Swal.fire({
            title: '¿Eliminar cliente?',
            text: "Se borrará su cuenta y no podrá iniciar sesión. Los pedidos asociados se mantendrán por historial.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: 'var(--card)',
            color: 'var(--foreground)'
        }).then((result) => {
            if (result.isConfirmed) { form.submit(); }
        });
    }
</script>