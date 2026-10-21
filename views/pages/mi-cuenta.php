<?php
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/Producto.php';

$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);

$success = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_success']);

$sessionUser = $_SESSION['user'] ?? null;
$user = $sessionUser ? Usuario::obtenerPorId($sessionUser['id']) : null;

// Obtener pedidos del usuario si está logueado y es cliente
$misPedidos = $user ? Pedido::getByUsuarioId($user['id']) : [];

$tokenParam = $_GET['token'] ?? null;
$tokenValido = $tokenParam ? Usuario::verificarToken($tokenParam) : null;
$showRecovery = isset($_GET['recuperar']) && $_GET['recuperar'] == '1';

$badgesEstado = [
    'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'procesando' => 'bg-blue-100 text-blue-800 border-blue-200',
    'completado' => 'bg-green-100 text-green-800 border-green-200',
    'cancelado' => 'bg-red-100 text-red-800 border-red-200',
];
?>

<div class="mx-auto max-w-5xl px-4 py-12 md:px-6">
    <?php if ($user): ?>
        <!-- PANEL DE USUARIO LOGUEADO -->
        <div class="space-y-8">
            <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-primary/10 text-primary font-display text-2xl font-bold uppercase">
                        <?= substr($user['nombre'], 0, 2) ?>
                    </div>
                    <div>
                        <h1 class="font-display text-2xl font-bold uppercase">Hola, <?= htmlspecialchars($user['nombre']) ?></h1>
                        <p class="text-sm text-muted-foreground"><?= htmlspecialchars($user['email']) ?> · <span class="capitalize font-semibold text-primary"><?= $user['rol'] ?></span></p>
                    </div>
                </div>
                <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta">
                    <input type="hidden" name="auth_action" value="logout">
                    <button type="submit" class="btn-base bg-destructive/10 text-destructive hover:bg-destructive/20 !py-2 !px-4 text-sm font-semibold rounded-xl">
                        Cerrar sesión
                    </button>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="rounded-2xl bg-destructive/10 p-4 text-sm text-destructive font-medium text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="rounded-2xl bg-primary/10 p-4 text-sm text-primary font-medium text-center">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <div class="grid gap-8 md:grid-cols-2">
                <!-- Actualizar Datos y Dirección del Taller -->
                <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] space-y-6">
                    <h2 class="font-display text-xl font-bold uppercase border-b border-border pb-3">Datos de Envío y Taller</h2>
                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="space-y-4">
                        <input type="hidden" name="auth_action" value="update_profile">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre o Taller</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Teléfono de contacto</label>
                            <input type="text" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" placeholder="+34 600 000 000" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Dirección del taller</label>
                            <textarea name="direccion" rows="3" placeholder="Calle del Motor 12, Polígono Industrial..." class="w-full rounded-xl border border-input bg-background p-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"><?= htmlspecialchars($user['direccion'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn-base btn-cta !py-3 w-full shadow-[0_0_15px_rgba(224,193,18,0.3)]">
                            Guardar Cambios
                        </button>
                    </form>
                </div>

                <!-- Cambiar Contraseña y Acceso Admin -->
                <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] space-y-6">
                    <h2 class="font-display text-xl font-bold uppercase border-b border-border pb-3">Seguridad y Contraseña</h2>
                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="space-y-4">
                        <input type="hidden" name="auth_action" value="update_password">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Contraseña Actual</label>
                            <input type="password" name="password_actual" required placeholder="••••••••" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nueva Contraseña</label>
                            <input type="password" name="password_nueva" required placeholder="••••••••" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <button type="submit" class="btn-base btn-outline !py-3 w-full">
                            Actualizar Contraseña
                        </button>
                    </form>

                    <?php if ($user['rol'] === 'admin'): ?>
                        <div class="pt-4 border-t border-border">
                            <a href="<?= $_ENV['BASE_URL'] ?>dashboard" class="btn-base btn-cta !py-3 w-full text-center block">
                                Acceder al Panel de Administración
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECCIÓN: HISTORIAL DE PEDIDOS DEL CLIENTE -->
            <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] space-y-6">
                <h2 class="font-display text-xl font-bold uppercase border-b border-border pb-3">Historial de Pedidos</h2>
                
                <?php if (empty($misPedidos)): ?>
                    <p class="text-sm text-muted-foreground py-4 text-center">Aún no has realizado ningún pedido en la tienda.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-secondary/50 border-b border-border uppercase text-xs font-semibold text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Referencia</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Método de pago</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <?php foreach ($misPedidos as $p): ?>
                                    <tr class="hover:bg-secondary/20 transition-colors">
                                        <td class="px-4 py-3 font-mono font-medium text-foreground">
                                            DG-<?= str_pad($p['id'], 5, "0", STR_PAD_LEFT) ?>-ES
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            <?= date('d/m/Y H:i', strtotime($p['fecha'])) ?>
                                        </td>
                                        <td class="px-4 py-3 uppercase text-xs font-semibold text-muted-foreground">
                                            <?= str_replace('_', ' ', $p['metodo_pago']) ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $badgesEstado[$p['estado']] ?? 'bg-gray-100' ?>">
                                                <?= ucfirst($p['estado']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-primary">
                                            <?= Producto::formatPrice($p['total']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($tokenParam): ?>
        <!-- FORMULARIO PARA NUEVA CONTRASEÑA (VÍA TOKEN) -->
        <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] max-w-md mx-auto">
            <h1 class="font-display text-2xl font-bold uppercase text-center">Restablecer Contraseña</h1>
            
            <?php if (!$tokenValido): ?>
                <div class="mt-6 rounded-xl bg-destructive/10 p-4 text-sm text-destructive font-medium text-center">
                    El enlace de recuperación no es válido o ya ha expirado.
                </div>
                <div class="mt-6 text-center">
                    <a href="<?= $_ENV['BASE_URL'] ?>mi-cuenta?recuperar=1" class="btn-base btn-cta !py-2.5 px-4 text-sm inline-block">Solicitar nuevo enlace</a>
                </div>
            <?php else: ?>
                <p class="text-center text-sm text-muted-foreground mt-2">Introduce tu nueva contraseña de acceso.</p>
                <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="mt-6 space-y-4">
                    <input type="hidden" name="auth_action" value="reset_password">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($tokenParam) ?>">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Nueva Contraseña</label>
                        <input type="password" name="password_nueva" required placeholder="••••••••" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                    <button type="submit" class="btn-base btn-cta !py-3 w-full shadow-[0_0_15px_rgba(224,193,18,0.3)]">
                        Actualizar Contraseña
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php elseif ($showRecovery): ?>
        <!-- VISTA DE SOLICITUD DE RECUPERACIÓN -->
        <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] max-w-md mx-auto">
            <h1 class="font-display text-2xl font-bold uppercase text-center">Recuperar Contraseña</h1>
            <p class="text-center text-sm text-muted-foreground mt-2">Introduce tu correo electrónico para generar el enlace de restablecimiento.</p>

            <?php if ($error): ?>
                <div class="mt-4 rounded-xl bg-destructive/10 p-4 text-sm text-destructive font-medium text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mt-4 rounded-xl bg-primary/10 p-4 text-sm text-primary font-medium text-center leading-relaxed">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="mt-6 space-y-4">
                <input type="hidden" name="auth_action" value="recover">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Email registrado</label>
                    <input type="email" name="email" required placeholder="tu@email.com" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <button type="submit" class="btn-base btn-cta !py-3 w-full shadow-[0_0_15px_rgba(224,193,18,0.3)]">
                    Generar Enlace
                </button>
            </form>
            <div class="text-center pt-4">
                <a href="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="text-xs text-muted-foreground hover:text-primary underline">Volver al inicio de sesión</a>
            </div>
        </div>
    <?php else: ?>
        <!-- FORMULARIO DE LOGIN / REGISTRO -->
        <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)]">
            <h1 class="font-display text-3xl font-bold uppercase text-center">Mi Cuenta / Acceso</h1>
            <p class="text-center text-sm text-muted-foreground mt-2">Inicia sesión o regístrate para proceder con tus compras profesionales.</p>

            <?php if ($error): ?>
                <div class="mt-6 rounded-xl bg-destructive/10 p-4 text-sm text-destructive font-medium text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mt-6 rounded-xl bg-primary/10 p-4 text-sm text-primary font-medium text-center">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <div class="mt-8 grid gap-8 md:grid-cols-2">
                <!-- Login -->
                <div class="space-y-4">
                    <h2 class="font-display text-xl font-bold uppercase border-b border-border pb-2">Iniciar Sesión</h2>
                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="space-y-4">
                        <input type="hidden" name="auth_action" value="login">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Email</label>
                            <input type="email" name="email" required placeholder="tu@email.com" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Contraseña</label>
                                <a href="<?= $_ENV['BASE_URL'] ?>mi-cuenta?recuperar=1" class="text-xs text-muted-foreground hover:text-primary underline">¿Olvidaste tu contraseña?</a>
                            </div>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <button type="submit" class="btn-base btn-cta !py-3 w-full shadow-[0_0_15px_rgba(224,193,18,0.3)]">
                            Entrar
                        </button>
                    </form>
                </div>

                <!-- Registro -->
                <div class="space-y-4">
                    <h2 class="font-display text-xl font-bold uppercase border-b border-border pb-2">Crear Cuenta</h2>
                    <form method="POST" action="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="space-y-4">
                        <input type="hidden" name="auth_action" value="register">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre / Taller</label>
                            <input type="text" name="nombre" required placeholder="Taller Mecánico" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Email</label>
                            <input type="email" name="email" required placeholder="tu@email.com" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Teléfono</label>
                                <input type="text" name="telefono" placeholder="Opcional" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Dirección</label>
                                <input type="text" name="direccion" placeholder="Opcional" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Contraseña</label>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <button type="submit" class="btn-base btn-outline !py-3 w-full">
                            Registrarse
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>