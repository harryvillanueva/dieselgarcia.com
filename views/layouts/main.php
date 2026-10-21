<?php
// Calcular total inicial del carrito para la burbuja
$totalCartItems = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Especialistas en inyección diésel, turbos y equipos de diagnóstico. Repuestos probados en banco y enviados en 24/48 h.">
    <title>Diesel García | Potencia que no se detiene</title>
    <link rel="icon" type="image/png" href="<?= $_ENV['BASE_URL'] ?>assets/img/favicon.png">
    
    <!-- SEO & Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $_ENV['BASE_URL'] ?>favicon.ico">
    
    <!-- Fuentes de Google (Barlow) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- SweetAlert2 para alertas UI -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Compilador Tailwind v4 por CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <!-- Estilos de Tailwind integrados para el CDN -->
    <style type="text/tailwindcss">
        @theme inline {
            --radius-sm: calc(var(--radius) - 4px);
            --radius-md: calc(var(--radius) - 2px);
            --radius-lg: var(--radius);
            --radius-xl: calc(var(--radius) + 4px);
            --radius-2xl: calc(var(--radius) + 8px);
            --radius-3xl: calc(var(--radius) + 12px);
            
            --color-background: var(--background);
            --color-foreground: var(--foreground);
            --color-card: var(--card);
            --color-card-foreground: var(--card-foreground);
            --color-popover: var(--popover);
            --color-popover-foreground: var(--popover-foreground);
            
            --color-primary: var(--primary);
            --color-primary-foreground: var(--primary-foreground);
            --color-primary-deep: var(--primary-deep);
            --color-primary-glow: var(--primary-glow);
            
            --color-secondary: var(--secondary);
            --color-secondary-foreground: var(--secondary-foreground);
            --color-muted: var(--muted);
            --color-muted-foreground: var(--muted-foreground);
            
            --color-accent: var(--accent);
            --color-accent-foreground: var(--accent-foreground);
            --color-accent-hover: var(--accent-hover);
            
            --color-destructive: var(--destructive);
            --color-destructive-foreground: var(--destructive-foreground);
            --color-success: var(--success);
            
            --color-border: var(--border);
            --color-input: var(--input);
            --color-ring: var(--ring);
            
            --font-display: "Barlow Condensed", "Arial Narrow", sans-serif;
            --font-sans: "Barlow", system-ui, sans-serif;
            
            --shadow-soft: 0 2px 8px -2px oklch(0.25 0.06 258 / 0.12);
            --shadow-card: 0 10px 30px -12px oklch(0.25 0.06 258 / 0.22);
            --shadow-cta: 0 8px 22px -8px oklch(0.86 0.18 96 / 0.65);
        }

        :root {
            --radius: 0.875rem;
            --background: oklch(1 0 0);
            --foreground: oklch(0.24 0.03 258);
            --card: oklch(1 0 0);
            --card-foreground: oklch(0.24 0.03 258);
            --popover: oklch(1 0 0);
            --popover-foreground: oklch(0.24 0.03 258);
            --primary: oklch(0.35 0.13 262);
            --primary-foreground: oklch(0.99 0.005 250);
            --primary-deep: oklch(0.24 0.09 264);
            --primary-glow: oklch(0.55 0.18 258);
            --accent: oklch(0.87 0.18 95);
            --accent-foreground: oklch(0.23 0.06 262);
            --accent-hover: oklch(0.92 0.19 100);
            --secondary: oklch(0.96 0.008 258);
            --secondary-foreground: oklch(0.3 0.05 262);
            --muted: oklch(0.965 0.006 258);
            --muted-foreground: oklch(0.52 0.02 258);
            --destructive: oklch(0.58 0.22 25);
            --destructive-foreground: oklch(0.99 0.005 250);
            --success: oklch(0.62 0.15 152);
            --border: oklch(0.91 0.01 258);
            --input: oklch(0.91 0.01 258);
            --ring: oklch(0.55 0.18 258);
        }

        @layer base {
            * { border-color: var(--color-border); }
            body { background-color: var(--color-background); color: var(--color-foreground); font-family: var(--font-sans); -webkit-font-smoothing: antialiased; }
            h1, h2, h3, h4 { font-family: var(--font-display); letter-spacing: -0.01em; text-transform: none; }
        }

        @utility surface-card {
            background-color: var(--color-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-soft);
            transition: box-shadow 0.35s ease, transform 0.35s ease;
            &:hover { box-shadow: var(--shadow-card); transform: translateY(-4px); }
        }

        @utility btn-base {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 9999px;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.0625rem;
            letter-spacing: 0.02em;
            padding: 0.7rem 1.6rem;
            transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.25s ease, color 0.2s ease;
            cursor: pointer;
            &:active { transform: scale(0.97); }
        }

        @utility btn-cta {
            background-color: var(--color-accent);
            color: var(--color-accent-foreground);
            box-shadow: var(--shadow-cta);
            &:hover { background-color: var(--color-accent-hover); transform: translateY(-2px) scale(1.02); }
        }

        @utility btn-primary {
            background-color: var(--color-primary);
            color: var(--color-primary-foreground);
            &:hover { background-color: var(--color-primary-deep); transform: translateY(-2px); }
        }

        @utility btn-outline {
            background-color: transparent;
            color: var(--color-primary);
            border: 1.5px solid var(--color-primary);
            &:hover { background-color: var(--color-primary); color: var(--color-primary-foreground); }
        }

        @utility btn-ghost-light {
            background-color: color-mix(in oklab, var(--color-primary-foreground) 12%, transparent);
            color: var(--color-primary-foreground);
            border: 1.5px solid color-mix(in oklab, var(--color-primary-foreground) 45%, transparent);
            &:hover { background-color: color-mix(in oklab, var(--color-primary-foreground) 22%, transparent); }
        }

        @utility field-input {
            width: 100%;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--color-input);
            background-color: var(--color-card);
            padding: 0.7rem 0.95rem;
            font-size: 0.95rem;
            color: var(--color-foreground);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            &::placeholder { color: var(--color-muted-foreground); }
            &:focus { outline: none; border-color: var(--color-primary-glow); box-shadow: 0 0 0 4px color-mix(in oklab, var(--color-primary-glow) 18%, transparent); }
        }

        @utility badge-offer {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            background-color: var(--color-accent);
            color: var(--color-accent-foreground);
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.2rem 0.7rem;
        }

        @utility gradient-hero {
            background-image: linear-gradient(120deg, var(--color-primary-deep) 0%, var(--color-primary) 55%, var(--color-primary-glow) 100%);
        }

        @utility zoom-media {
            overflow: hidden;
            & img { transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
            &:hover img { transform: scale(1.08); }
        }

        @utility reveal-hidden { opacity: 0; transform: translateY(24px); }
        @utility reveal-shown { opacity: 1; transform: none; transition: opacity 0.7s ease, transform 0.7s cubic-bezier(0.22, 1, 0.36, 1); }
    </style>
</head>
<body class="bg-background text-foreground font-sans antialiased">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 border-b border-border/70 bg-background/85 backdrop-blur-lg">
        <div class="mx-auto grid max-w-7xl grid-cols-[minmax(0,1fr)_auto] items-center gap-4 px-4 py-3 md:grid-cols-[auto_1fr_auto] md:px-6">
            
            <a href="<?= $_ENV['BASE_URL'] ?>" class="flex min-w-0 items-center gap-2">
                <!-- NUEVO LOGO EN LA CABECERA -->
                <img src="<?= $_ENV['BASE_URL'] ?>assets/img/favicon.png" alt="Diesel García Logo" class="h-10 w-auto rounded object-contain">
                <span class="min-w-0 truncate font-display text-xl font-bold tracking-tight text-primary sm:text-2xl">
                    diesel<span class="text-accent-foreground">garcia</span><span class="text-muted-foreground">.com</span>
                </span>
            </a>

            <nav class="hidden justify-center gap-1 md:flex">
                <a href="<?= $_ENV['BASE_URL'] ?>" class="rounded-full px-4 py-2 font-display text-base font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Inicio</a>
                <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="rounded-full px-4 py-2 font-display text-base font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Tienda</a>
                <a href="<?= $_ENV['BASE_URL'] ?>contacto" class="rounded-full px-4 py-2 font-display text-base font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Contacto</a>
                <a href="<?= $_ENV['BASE_URL'] ?>legal" class="rounded-full px-4 py-2 font-display text-base font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Legal</a>
            </nav>

            <div class="flex items-center justify-end gap-1.5">
                <a href="<?= $_ENV['BASE_URL'] ?>mi-cuenta" aria-label="Mi cuenta" class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-primary transition-colors hover:bg-secondary">
                    <i data-lucide="user" class="h-5 w-5"></i>
                </a>
                <a href="<?= $_ENV['BASE_URL'] ?>carrito" aria-label="Ver carrito" class="relative grid h-10 w-10 shrink-0 place-items-center rounded-full text-primary transition-colors hover:bg-secondary">
                    <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                    <!-- Badge del carrito dinámico -->
                    <span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-accent px-1 font-display text-xs font-bold text-accent-foreground" id="cart-badge" style="<?= $totalCartItems > 0 ? '' : 'display: none;' ?>">
                        <?= $totalCartItems ?>
                    </span>
                </a>
                <button type="button" id="mobile-menu-btn" aria-label="Abrir menú" class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-primary transition-colors hover:bg-secondary md:hidden">
                    <i data-lucide="menu" class="h-5 w-5" id="menu-icon"></i>
                </button>
            </div>
        </div>

        <nav id="mobile-menu" class="hidden border-t border-border bg-background px-4 pb-4 pt-2 md:hidden">
            <a href="<?= $_ENV['BASE_URL'] ?>" class="block rounded-lg px-3 py-3 font-display text-lg font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Inicio</a>
            <a href="<?= $_ENV['BASE_URL'] ?>tienda" class="block rounded-lg px-3 py-3 font-display text-lg font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Tienda</a>
            <a href="<?= $_ENV['BASE_URL'] ?>contacto" class="block rounded-lg px-3 py-3 font-display text-lg font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Contacto</a>
            <a href="<?= $_ENV['BASE_URL'] ?>legal" class="block rounded-lg px-3 py-3 font-display text-lg font-semibold text-muted-foreground transition-colors hover:bg-secondary hover:text-primary">Legal</a>
        </nav>
    </header>

    <main class="min-h-screen">
        <?php 
        if (file_exists($view_content)) {
            require_once $view_content;
        } else {
            echo '<div class="flex items-center justify-center min-h-[50vh]"><h1 class="text-3xl font-display font-bold text-primary">404 - Página no encontrada</h1></div>';
        }
        ?>
    </main>

   <!-- ================= FOOTER GLOBAL ================= -->
    <footer class="mt-auto bg-[#0a1428] text-slate-300 border-t-4 border-primary">
        <div class="mx-auto max-w-7xl px-4 py-12 md:px-6 lg:py-16">
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                
                <!-- Columna 1: Marca y descripción -->
                <div class="space-y-4">
                    <a href="<?= $_ENV['BASE_URL'] ?>" class="flex items-center gap-2">
                         <!-- NUEVO LOGO EN EL FOOTER -->
                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-slate-700 bg-white p-1">
                            <img src="<?= $_ENV['BASE_URL'] ?>assets/img/favicon.png" alt="DG Logo" class="h-full w-full object-contain">
                        </div>
                        <span class="font-display text-xl font-bold tracking-tight text-white">dieselgarcia<span class="text-primary">.com</span></span>
                    </a>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Especialistas en inyección diésel, turbos y componentes de motor. Calidad probada en banco con garantía de taller. Tu potencia no se detiene.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#" class="text-slate-400 hover:text-primary transition-colors"><i data-lucide="facebook" class="h-5 w-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-primary transition-colors"><i data-lucide="instagram" class="h-5 w-5"></i></a>
                        <a href="#" class="text-slate-400 hover:text-primary transition-colors"><i data-lucide="youtube" class="h-5 w-5"></i></a>
                    </div>
                </div>

                <!-- Columna 2: Enlaces Rápidos -->
                <div>
                    <h3 class="mb-5 font-display text-sm font-bold uppercase tracking-wider text-white">Enlaces Rápidos</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="<?= $_ENV['BASE_URL'] ?>" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Inicio</a></li>
                        <li><a href="<?= $_ENV['BASE_URL'] ?>tienda" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Tienda</a></li>
                        <li><a href="<?= $_ENV['BASE_URL'] ?>mi-cuenta" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Mi Cuenta / Pedidos</a></li>
                        <li><a href="<?= $_ENV['BASE_URL'] ?>contacto" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Contacto</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Categorías -->
                <div>
                    <h3 class="mb-5 font-display text-sm font-bold uppercase tracking-wider text-white">Categorías</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="<?= $_ENV['BASE_URL'] ?>tienda?categoria=inyectores" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Inyección</a></li>
                        <li><a href="<?= $_ENV['BASE_URL'] ?>tienda?categoria=turbos" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Turbos</a></li>
                        <li><a href="<?= $_ENV['BASE_URL'] ?>tienda?categoria=bombas" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Bombas</a></li>
                        <li><a href="<?= $_ENV['BASE_URL'] ?>tienda?categoria=filtros" class="hover:text-primary transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="h-3 w-3"></i> Filtración</a></li>
                    </ul>
                </div>

                <!-- Columna 4: Contacto -->
                <div>
                    <h3 class="mb-5 font-display text-sm font-bold uppercase tracking-wider text-white">Atención al Cliente</h3>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="h-5 w-5 text-primary shrink-0"></i>
                            <span class="text-slate-400">Lima Metropolitana, Perú</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="h-5 w-5 text-primary shrink-0"></i>
                            <span class="text-slate-400">+51 999 070 362</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="h-5 w-5 text-primary shrink-0"></i>
                            <a href="mailto:info@dieselgarcia.com" class="text-slate-400 hover:text-white transition-colors">info@dieselgarcia.com</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-8 border-slate-800">
            
            <!-- Barra inferior Copyright -->
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row text-xs text-slate-500">
                <p>&copy; <?= date('Y') ?> Consorcio Diesel Garcia SAC. Todos los derechos reservados.</p>
                <p>Desarrollado por <a href="https://vigacode.com" target="_blank"> Vigacode</a></p>
                <div class="flex flex-wrap justify-center gap-4">
    <a href="<?= $_ENV['BASE_URL'] ?>aviso-legal" class="hover:text-white transition-colors">Aviso Legal</a>
    <a href="<?= $_ENV['BASE_URL'] ?>politica-privacidad" class="hover:text-white transition-colors">Política de Privacidad</a>
    <a href="<?= $_ENV['BASE_URL'] ?>terminos-condiciones" class="hover:text-white transition-colors">Términos y Condiciones</a>
</div>
            </div>
        </div>
    </footer>
    <!-- ================= FIN FOOTER ================= -->

    <script>
        window.BASE_URL = "<?= $_ENV['BASE_URL'] ?>";
    </script>

    <!-- Librerías Externas -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="<?= $_ENV['BASE_URL'] ?>assets/js/main.js?v=2"></script>

    <!-- Script AJAX: Directo al BASE_URL -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartForms = document.querySelectorAll('.js-add-to-cart-form');
            const cartBadge = document.getElementById('cart-badge');

            cartForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); 

                    // Enviamos por FormData para que el fetch maneje el multipart de manera nativa y transparente
                    const formData = new FormData(this);
                    formData.append('ajax', '1'); 

                    const btn = this.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>'; 
                    btn.disabled = true;

                    // IMPORTANTE: Disparamos directo a window.BASE_URL, que en index.php captará el $_POST global
                    fetch(window.BASE_URL, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            cartBadge.textContent = data.cart_count;
                            if(data.cart_count > 0) {
                                cartBadge.style.display = 'grid';
                            }
                            
                            Swal.fire({
                                toast: true,
                                position: 'bottom-end',
                                icon: 'success',
                                title: 'Añadido al carrito',
                                showConfirmButton: false,
                                timer: 1500,
                                background: 'var(--card)',
                                color: 'var(--foreground)'
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        lucide.createIcons(); 
                    });
                });
            });
        });
    </script>
</body>
</html>