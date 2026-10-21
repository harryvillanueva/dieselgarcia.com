<?php
require_once __DIR__ . '/../../models/Marca.php';

$marcas = Marca::getAll();
$editId = $_GET['edit'] ?? null;
$editMarca = $editId ? Marca::getById($editId) : null;
$isEdit = $editMarca !== null;

$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>

<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    
    <!-- MENU LATERAL -->
    <?php 
    $activeTab = 'marcas';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <main class="flex-1 space-y-8">
        <!-- FORMULARIO CREAR/EDITAR -->
        <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
            <h2 class="font-display text-xl font-bold uppercase mb-4"><?= $isEdit ? 'Editar Marca' : 'Añadir Nueva Marca' ?></h2>
            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="admin_action" value="<?= $isEdit ? 'edit_brand' : 'create_brand' ?>">
                <?php if ($isEdit): ?> <input type="hidden" name="id" value="<?= $editMarca['id'] ?>"> <?php endif; ?>
                
                <div class="flex-1 space-y-2 w-full">
                    <label class="text-sm font-medium">Nombre de la Marca</label>
                    <input type="text" name="name" required value="<?= $isEdit ? htmlspecialchars($editMarca['name']) : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                
                <div class="flex gap-2 w-full md:w-auto">
                    <?php if ($isEdit): ?>
                        <a href="<?= $_ENV['BASE_URL'] ?>dashboard/marcas" class="btn-base bg-secondary text-foreground !py-2.5 px-4 rounded-xl">Cancelar</a>
                    <?php endif; ?>
                    <button type="submit" class="btn-base btn-cta !py-2.5 px-6 rounded-xl flex-1 md:flex-none">
                        <?= $isEdit ? 'Guardar' : 'Añadir' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- LISTADO -->
        <div class="rounded-2xl border border-border bg-card overflow-hidden shadow-[var(--shadow-soft)]">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-secondary/50 border-b border-border uppercase text-xs font-semibold text-muted-foreground">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Marca</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($marcas as $m): ?>
                        <tr class="hover:bg-secondary/20 transition-colors">
                            <td class="px-6 py-4 font-mono text-muted-foreground">#<?= $m['id'] ?></td>
                            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($m['name']) ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= $_ENV['BASE_URL'] ?>dashboard/marcas?edit=<?= $m['id'] ?>" class="grid h-8 w-8 place-items-center rounded-lg bg-secondary text-foreground hover:bg-primary transition-colors" title="Editar">
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </a>
                                    
                                    <!-- Formulario de eliminación con SweetAlert2 -->
                                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" onsubmit="confirmarEliminacion(event, this);" class="inline-block">
                                        <input type="hidden" name="admin_action" value="delete_brand">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground transition-colors" title="Eliminar">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- SCRIPT SWEETALERT2 PARA MARCAS -->
<script>
    // Mostrar notificaciones de éxito o error al recargar la página
    <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Operación exitosa!',
            text: '<?= addslashes($success) ?>',
            confirmButtonColor: '#e0c112', // Primary
            background: 'var(--card)',
            color: 'var(--foreground)'
        });
    <?php endif; ?>

    <?php if ($error): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#e0c112',
            background: 'var(--card)',
            color: 'var(--foreground)'
        });
    <?php endif; ?>

    // Función para interceptar el botón de eliminar
    function confirmarEliminacion(event, form) {
        event.preventDefault(); // Detenemos el envío automático
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará la marca permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: 'var(--card)',
            color: 'var(--foreground)'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>