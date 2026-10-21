<?php
require_once __DIR__ . '/../../models/Configuracion.php';

$whatsappActual = Configuracion::get('whatsapp');
$success = $_SESSION['admin_success'] ?? null;
unset($_SESSION['admin_success']);
?>

<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 flex flex-col md:flex-row gap-8">
    
    <!-- MENU LATERAL -->
    <?php 
    $activeTab = 'configuracion';
    include __DIR__ . '/../components/dashboard-sidebar.php'; 
    ?>

    <main class="flex-1 space-y-6">
        <div>
            <h1 class="font-display text-2xl font-bold uppercase text-foreground">Configuración de la Tienda</h1>
            <p class="text-sm text-muted-foreground mt-1">Gestiona las variables globales de Diesel García.</p>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 md:p-8 shadow-[var(--shadow-soft)] max-w-2xl">
            <h2 class="font-display text-lg font-bold uppercase border-b border-border pb-3 mb-6 flex items-center gap-2">
                <i data-lucide="message-circle" class="h-5 w-5 text-[#25D366]"></i>
                Contacto y WhatsApp
            </h2>

            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>dashboard" class="space-y-6">
                <input type="hidden" name="admin_action" value="update_config">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium">Número de WhatsApp para pedidos</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-muted-foreground">
                            <i data-lucide="phone" class="h-4 w-4"></i>
                        </div>
                        <input type="text" name="whatsapp" value="<?= htmlspecialchars($whatsappActual ?? '') ?>" placeholder="Ej: 51900000000" required class="w-full h-11 rounded-xl border border-input bg-background pl-10 pr-3 text-sm focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">Incluye el código de país sin el signo "+". Ej: 34600123456 (España) o 51900123456 (Perú).</p>
                </div>

                <div class="pt-4 border-t border-border">
                    <button type="submit" class="btn-base btn-cta !py-2.5 px-8 rounded-xl shadow-[0_0_15px_rgba(224,193,18,0.3)]">
                        Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- SCRIPT SWEETALERT2 -->
<script>
    <?php if ($success): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Actualizado!',
            text: '<?= addslashes($success) ?>',
            confirmButtonColor: '#e0c112',
            background: 'var(--card)',
            color: 'var(--foreground)'
        });
    <?php endif; ?>
</script>