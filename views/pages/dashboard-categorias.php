<?php
require_once __DIR__ . '/../../models/Categoria.php';

$categorias = Categoria::getAll();

// Comprobar si estamos editando
$editId = $_GET['edit'] ?? null;
$editCat = $editId ? Categoria::getById($editId) : null;
$isEdit = $editCat !== null;

$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);
$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);
?>

<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    
    <!-- MENU LATERAL -->
    <?php 
    $activeTab = 'categorias';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <main class="flex-1 space-y-8">
        <!-- FORMULARIO CREAR/EDITAR -->
        <div class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
            <h2 class="font-display text-xl font-bold uppercase mb-4"><?= $isEdit ? 'Editar Categoría' : 'Añadir Nueva Categoría' ?></h2>
            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="admin_action" value="<?= $isEdit ? 'edit_category' : 'create_category' ?>">
                <?php if ($isEdit): ?> <input type="hidden" name="id" value="<?= $editCat['id'] ?>"> <?php endif; ?>
                
                <div class="flex-1 space-y-2 w-full">
                    <label class="text-sm font-medium">Nombre de categoría</label>
                    <input type="text" name="name" required value="<?= $isEdit ? htmlspecialchars($editCat['name']) : '' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                
                <div class="flex-1 space-y-2 w-full">
                    <label class="text-sm font-medium">Icono (Lucide)</label>
                    <input type="text" name="icon" placeholder="ej: wrench, gauge, box..." required value="<?= $isEdit ? htmlspecialchars($editCat['icon']) : 'box' ?>" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                
                <div class="flex gap-2 w-full md:w-auto">
                    <?php if ($isEdit): ?>
                        <a href="<?= $_ENV['BASE_URL'] ?>dashboard/categorias" class="btn-base bg-secondary text-foreground !py-2.5 px-4 rounded-xl">Cancelar</a>
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
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4">Slug (URL)</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($categorias as $c): ?>
                        <tr class="hover:bg-secondary/20 transition-colors">
                            <td class="px-6 py-4 font-mono text-muted-foreground">#<?= $c['id'] ?></td>
                            <td class="px-6 py-4 font-medium flex items-center gap-2">
                                <i data-lucide="<?= $c['icon'] ?>" class="h-4 w-4 text-primary"></i> <?= htmlspecialchars($c['name']) ?>
                            </td>
                            <td class="px-6 py-4 text-muted-foreground"><?= htmlspecialchars($c['slug']) ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= $_ENV['BASE_URL'] ?>dashboard/categorias?edit=<?= $c['id'] ?>" class="grid h-8 w-8 place-items-center rounded-lg bg-secondary text-foreground hover:bg-primary transition-colors" title="Editar">
                                        <i data-lucide="edit-2" class="h-4 w-4"></i>
                                    </a>
                                    
                                    <!-- Formulario de eliminación con SweetAlert2 -->
                                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" onsubmit="confirmarEliminacion(event, this);" class="inline-block">
                                        <input type="hidden" name="admin_action" value="delete_category">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
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

<!-- SCRIPT SWEETALERT2 PARA CATEGORÍAS -->
<script>
    // Mostrar notificaciones de éxito o error al recargar la página
    <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Operación exitosa!',
            text: '<?= addslashes($success) ?>',
            confirmButtonColor: '#e0c112', // Color de tu tema (Primary)
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
        event.preventDefault(); // Detenemos el envío automático del formulario
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará la categoría permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f', // Color rojo destructivo
            cancelButtonColor: '#6c757d', // Color secundario
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: 'var(--card)',
            color: 'var(--foreground)'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Si el usuario confirma, enviamos el formulario
            }
        });
    }
</script>