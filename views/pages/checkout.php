<?php
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/Izipay.php';

// Función para leer la llave pública de Izipay
function getPublicKey() {
    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
        return $env['IZIPAY_PUBLIC_KEY'] ?? '';
    }
    return '';
}

$allProducts = Producto::getAll();
$lines = [];
$subtotal = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        foreach ($allProducts as $p) {
            if ($p['id'] === $id) {
                $precioReal = !empty($p['sale_price']) ? floatval($p['sale_price']) : floatval($p['price']);
                $lines[] = ['product' => $p, 'qty' => $qty, 'precioReal' => $precioReal];
                $subtotal += $precioReal * $qty;
                break;
            }
        }
    }
}

$shipping = ($subtotal === 0 || $subtotal > 250) ? 0 : 9.95;
$total = $subtotal + $shipping;

// Variables de estado
$order_completed = false;
$mostrar_modal_izipay = false;
$formToken = null;
$order_reference = '';
$pago_total = 0;
$metodo_pago_display = '';
$fecha_pedido = '';
$error_pago = '';

// =========================================================================
// ESCENARIO 1: RETORNO DE IZIPAY DESPUÉS DEL PAGO
// =========================================================================
if (isset($_POST['kr-answer']) && isset($_POST['kr-hash'])) {
    if (Izipay::validarHashIPN($_POST)) {
        $answer = json_decode($_POST['kr-answer'], true);
        
        if ($answer['orderStatus'] === 'PAID') {
            $orderIdRaw = $answer['orderDetails']['orderId'];
            $pedido_id = (int) str_replace('DG-', '', $orderIdRaw);

            Pedido::actualizarEstado($pedido_id, 'completado');

            // NUEVO: Enviar el correo para Izipay antes de vaciar variables
            require_once __DIR__ . '/../../models/Mailer.php';
            $usr = $_SESSION['user'] ?? ['nombre' => 'Cliente', 'apellidos' => '', 'email' => 'Sin correo', 'telefono' => 'Sin teléfono'];
            Mailer::enviarAvisoAdminNuevoPedido($pedido_id, $total, 'izipay', $lines, $usr);

            $_SESSION['cart'] = [];
            
            $order_completed = true;
            $order_reference = str_pad($pedido_id, 4, "0", STR_PAD_LEFT);
            $pago_total = $answer['orderDetails']['orderTotalAmount'] / 100;
            $metodo_pago_display = 'Tarjeta de crédito / débito (Izipay)';
            
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $fecha_pedido = date('j') . ' de ' . $meses[date('n')-1] . ', ' . date('Y');
        } else {
            $error_pago = "El pago fue rechazado o cancelado por el banco.";
        }
    } else {
        $error_pago = "Firma de seguridad inválida. Intento de fraude detectado.";
    }
}

// =========================================================================
// ESCENARIO 2: EL CLIENTE ENVÍA EL FORMULARIO DE CHECKOUT
// =========================================================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($lines) && !isset($_POST['kr-answer'])) {
    $usuario_id = $_SESSION['user']['id'];
    $metodo_pago = $_POST['metodo_pago'] ?? 'transferencia';
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $direccion_envio = "$direccion, $ciudad, CP: $cp";

    $pedido_id = Pedido::crear($usuario_id, $total, $metodo_pago, $direccion_envio, $lines);

    if ($pedido_id) {
        if ($metodo_pago === 'izipay') {
            $email = $_SESSION['user']['email'] ?? 'contacto@dieselgarcia.com';
            $formToken = Izipay::generarTokenPago($pedido_id, $total, $email);
            
            if ($formToken) {
                $mostrar_modal_izipay = true;
                $order_reference = str_pad($pedido_id, 4, "0", STR_PAD_LEFT);
                $pago_total = $total;
            } else {
                $error_pago = "Error al conectar con la pasarela de pago. Inténtalo más tarde.";
            }
        } else {
            if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/comprobantes/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileExt = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
                $fileName = 'comprobante_pedido_' . $pedido_id . '.' . $fileExt;
                move_uploaded_file($_FILES['comprobante']['tmp_name'], $uploadDir . $fileName);
            }

            require_once __DIR__ . '/../../models/Mailer.php';
            Mailer::enviarAvisoAdminNuevoPedido($pedido_id, $total, $metodo_pago, $lines, $_POST);

            $_SESSION['cart'] = []; 
            $order_completed = true;
            $order_reference = str_pad($pedido_id, 4, "0", STR_PAD_LEFT); 
            $pago_total = $total;
            
            if ($metodo_pago === 'transferencia') $metodo_pago_display = 'Transferencia bancaria directa';
            else $metodo_pago_display = 'Pago por Yape o Plin';
            
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $fecha_pedido = date('j') . ' de ' . $meses[date('n')-1] . ', ' . date('Y');
        }
    }
}
?>

<!-- VISTAS HTML -->
<?php if ($order_completed): ?>
    <div class="mx-auto max-w-4xl px-4 py-16 md:px-6 text-center">
        <h1 class="font-display text-4xl font-bold text-foreground">Checkout</h1>
        <p class="mt-4 text-muted-foreground text-lg">Gracias. Tu pedido ha sido procesado exitosamente.</p>
        
        <ul class="mt-8 flex flex-wrap justify-center gap-y-6 gap-x-12 border-y border-border py-6 text-sm text-left">
            <li class="flex flex-col gap-1 md:border-r border-border md:pr-12">
                <span class="text-xs uppercase tracking-wider text-muted-foreground font-semibold">Número de pedido:</span>
                <strong class="font-bold text-base text-foreground"><?= $order_reference ?></strong>
            </li>
            <li class="flex flex-col gap-1 md:border-r border-border md:pr-12">
                <span class="text-xs uppercase tracking-wider text-muted-foreground font-semibold">Total:</span>
                <strong class="font-bold text-base text-primary"><?= Producto::formatPrice($pago_total) ?></strong>
            </li>
            <li class="flex flex-col gap-1">
                <span class="text-xs uppercase tracking-wider text-muted-foreground font-semibold">Método de pago:</span>
                <strong class="font-bold text-base text-foreground"><?= $metodo_pago_display ?></strong>
            </li>
        </ul>

        <div class="mt-14">
            <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="btn-base btn-cta !px-8 !py-3.5 !text-lg shadow-[0_0_20px_rgba(224,193,18,0.4)]">
                Seguir comprando
            </a>
        </div>
    </div>

<?php elseif ($mostrar_modal_izipay && $formToken): ?>
    <div class="mx-auto max-w-4xl px-4 py-16 md:px-6 text-center">
        <script 
            src="https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js"
            kr-public-key="<?= getPublicKey() ?>"
            kr-post-url-success="<?= $_ENV['BASE_URL'] ?>checkout"
        ></script>
        <link rel="stylesheet" href="https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic-reset.css">
        <script src="https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.js"></script>

        <h1 class="font-display text-4xl font-bold text-foreground mb-4">Completa tu pago seguro</h1>
        <p class="text-muted-foreground mb-8">Por favor, introduce los datos de tu tarjeta para finalizar el pedido <strong>#<?= $order_reference ?></strong>.</p>
        
        <div class="flex justify-center">
            <div class="kr-embedded" kr-form-token="<?= $formToken ?>">
                <div class="kr-pan"></div>
                <div class="kr-expiry"></div>
                <div class="kr-security-code"></div>
                <button class="kr-payment-button"></button>
                <div class="kr-form-error"></div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="mx-auto max-w-7xl px-4 py-12 md:px-6 relative">
        <h1 class="font-display text-4xl font-bold uppercase md:text-5xl">Finalizar compra</h1>
        
        <?php if (!empty($error_pago)): ?>
            <div class="mt-6 rounded-xl border border-destructive bg-destructive/10 p-4 text-destructive font-semibold">
                <i data-lucide="alert-triangle" class="h-5 w-5 inline-block mr-2"></i> <?= htmlspecialchars($error_pago) ?>
            </div>
        <?php endif; ?>

        <form id="checkout-form" method="POST" action="<?= $_ENV['BASE_URL'] ?>checkout" enctype="multipart/form-data" class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-8">
                <!-- Paso 1: Datos de envío -->
                <section class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
                    <h2 class="flex items-center gap-2 font-display text-xl font-bold uppercase">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-primary/10 text-primary text-sm font-semibold">1</span>
                        <i data-lucide="map-pin" class="h-5 w-5 text-primary"></i> Datos de envío
                    </h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <?php 
                        $usr = $_SESSION['user'] ?? [];
                        $parts = explode(' ', $usr['nombre'] ?? '', 2);
                        $nombre = $parts[0] ?? '';
                        $apellidos = $parts[1] ?? '';
                        ?>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Apellidos</label>
                            <input type="text" name="apellidos" value="<?= htmlspecialchars($apellidos) ?>" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($usr['email'] ?? '') ?>" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Teléfono</label>
                            <input type="text" name="telefono" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="sm:col-span-2 space-y-2">
                            <label class="text-sm font-medium">Dirección</label>
                            <input type="text" name="direccion" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Ciudad</label>
                            <input type="text" name="ciudad" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Código postal</label>
                            <input type="text" name="cp" required class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                    </div>
                </section>

                <!-- Paso 2: Método de pago -->
                <section class="rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
                    <h2 class="flex items-center gap-2 font-display text-xl font-bold uppercase mb-6">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-primary/10 text-primary text-sm font-semibold">2</span>
                        <i data-lucide="credit-card" class="h-5 w-5 text-primary"></i> Método de pago
                    </h2>
                    
                    <div class="space-y-4">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-4 transition-colors hover:bg-secondary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="metodo_pago" value="transferencia" class="mt-1 h-4 w-4 text-primary accent-primary" onchange="togglePayment(this.value)" checked>
                            <div class="w-full">
                                <span class="block font-semibold">Transferencia bancaria directa</span>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-4 transition-colors hover:bg-secondary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="metodo_pago" value="yape_plin" class="mt-1 h-4 w-4 text-primary accent-primary" onchange="togglePayment(this.value)">
                            <div class="w-full">
                                <span class="block font-semibold">Pago por Yape o Plin</span>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-4 transition-colors hover:bg-secondary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="metodo_pago" value="izipay" class="mt-1 h-4 w-4 text-primary accent-primary" onchange="togglePayment(this.value)">
                            <div class="w-full">
                                <span class="block font-semibold">Tarjeta de Crédito / Débito Seguro</span>
                                <div class="mt-2 text-xs text-muted-foreground">Pago 100% seguro a través de Izipay.</div>
                            </div>
                        </label>
                    </div>

                    <script>
                        function togglePayment(metodo) { }
                    </script>
                </section>
            </div>

            <!-- Resumen lateral -->
            <aside class="h-max rounded-2xl border border-border bg-card p-6 shadow-[var(--shadow-soft)] lg:sticky lg:top-24">
                <h2 class="font-display text-xl font-bold uppercase">Tu pedido</h2>
                <hr class="my-4 border-border">
                
                <?php if (empty($lines)): ?>
                    <p class="text-sm text-muted-foreground">El carrito está vacío.</p>
                <?php else: ?>
                    <ul class="space-y-3 text-sm">
                        <?php foreach ($lines as $item): ?>
                            <li class="flex justify-between gap-3">
                                <span class="min-w-0 truncate text-muted-foreground">
                                    <?= $item['qty'] ?> × <?= htmlspecialchars($item['product']['name']) ?>
                                </span>
                                <span class="font-semibold"><?= Producto::formatPrice($item['precioReal'] * $item['qty']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <hr class="my-4 border-border">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Subtotal</dt>
                        <dd class="font-semibold"><?= Producto::formatPrice($subtotal) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">Envío</dt>
                        <dd class="font-semibold"><?= $shipping === 0 ? "Gratis" : Producto::formatPrice($shipping) ?></dd>
                    </div>
                </dl>
                <hr class="my-4 border-border">
                <div class="flex items-baseline justify-between">
                    <span class="font-semibold uppercase">Total</span>
                    <span class="font-display text-3xl font-bold text-primary">
                        <?= Producto::formatPrice($total) ?>
                    </span>
                </div>
                <button type="submit" id="main-submit-btn" class="btn-base btn-cta !py-3.5 w-full !text-lg mt-6 shadow-[0_0_20px_rgba(224,193,18,0.4)]" <?= empty($lines) ? 'disabled' : '' ?>>
                    Realizar el pedido
                </button>
            </aside>

            <!-- MODAL YAPE / PLIN OCULTO -->
            <div id="modal-yape" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-sm rounded-3xl bg-white shadow-2xl relative overflow-hidden flex flex-col">
                    <button type="button" onclick="closeYapeModal()" class="absolute right-3 top-3 z-10 grid h-8 w-8 place-items-center rounded-full bg-white text-gray-500 shadow-md hover:text-black">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                    <!-- PASO 1: CÓDIGO QR -->
                    <div id="yape-step-1" class="flex flex-col items-center p-6 pt-10 text-center">
                        <div class="h-2 w-full bg-[#742284] absolute top-0 left-0"></div>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=999070362&color=742284" alt="QR Yape" class="mb-4 h-32 w-32 border-4 border-white shadow-sm rounded-xl">
                        <p class="text-sm font-semibold text-[#742284] mb-4">Consorcio Diesel Garcia Sac</p>
                        <div class="w-full bg-gray-200 text-gray-800 font-medium py-2 rounded-lg mb-4 text-sm">Agregar contacto: 999070362</div>
                        <p class="text-sm text-gray-500">Monto a pagar</p>
                        <p class="text-3xl font-bold text-gray-900 mb-4"><?= Producto::formatPrice($total) ?></p>
                        <button type="button" onclick="goNextYapeStep()" class="w-full rounded-lg bg-[#00a99d] py-3 text-white font-bold">Continuar</button>
                    </div>
                    <!-- PASO 2: SUBIR COMPROBANTE -->
                    <div id="yape-step-2" class="hidden flex flex-col items-center p-6 text-center">
                        <h3 class="font-bold text-lg mb-4">Adjuntar Comprobante</h3>
                        <label class="w-full cursor-pointer rounded-xl border-2 border-dashed border-[#00a99d] bg-gray-50 p-8 flex flex-col items-center justify-center gap-3 mb-6">
                            <i data-lucide="upload-cloud" class="h-8 w-8 text-[#00a99d]"></i>
                            <span class="text-sm font-medium text-gray-600" id="file-name-display">Seleccionar Archivo</span>
                            <input type="file" name="comprobante" id="comprobante-input" accept="image/*,.pdf" class="hidden" onchange="document.getElementById('file-name-display').innerText = this.files[0] ? this.files[0].name : 'Archivo seleccionado'">
                        </label>
                        <button type="button" onclick="finishYape()" class="w-full rounded-lg bg-[#00a99d] py-3 text-white font-bold">Finalizar Compra</button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            const form = document.getElementById('checkout-form');
            const modalYape = document.getElementById('modal-yape');
            let bypassYapeModal = false;

            form.addEventListener('submit', function(e) {
                const metodo = document.querySelector('input[name="metodo_pago"]:checked').value;
                if (metodo === 'yape_plin' && !bypassYapeModal) {
                    e.preventDefault();
                    modalYape.classList.remove('hidden');
                    modalYape.classList.add('flex');
                }
            });

            function goNextYapeStep() {
                document.getElementById('yape-step-1').classList.add('hidden');
                document.getElementById('yape-step-2').classList.remove('hidden');
                document.getElementById('yape-step-2').classList.add('flex');
            }

            function closeYapeModal() {
                modalYape.classList.add('hidden');
                modalYape.classList.remove('flex');
                document.getElementById('yape-step-1').classList.remove('hidden');
                document.getElementById('yape-step-2').classList.add('hidden');
                document.getElementById('yape-step-2').classList.remove('flex');
            }

            function finishYape() {
                if (document.getElementById('comprobante-input').files.length === 0) {
                    alert('Sube el comprobante primero.'); return;
                }
                bypassYapeModal = true;
                form.submit();
            }
        </script>
    </div>
<?php endif; ?>