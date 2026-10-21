document.addEventListener("DOMContentLoaded", () => {
    // Inicializar iconos Lucide
    lucide.createIcons();

    // Lógica del menú móvil
    const mobileBtn = document.getElementById("mobile-menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");
    const menuIcon = document.getElementById("menu-icon");
    let isMenuOpen = false;

    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener("click", () => {
            isMenuOpen = !isMenuOpen;
            if (isMenuOpen) {
                mobileMenu.classList.remove("hidden");
                menuIcon.setAttribute("data-lucide", "x");
            } else {
                mobileMenu.classList.add("hidden");
                menuIcon.setAttribute("data-lucide", "menu");
            }
            lucide.createIcons(); // Recargar el icono para aplicar el cambio
        });
    }

    // Lógica del Newsletter usando SweetAlert2 (cumpliendo Regla 4)
    const newsletterForm = document.getElementById("newsletter-form");
    if (newsletterForm) {
        newsletterForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const emailInput = document.getElementById("newsletter-email");
            
            Swal.fire({
                title: '¡Suscripción exitosa!',
                text: `Hemos registrado el correo ${emailInput.value}`,
                icon: 'success',
                confirmButtonColor: '#e0c112', // Color de tu variable --accent
                confirmButtonText: 'Genial'
            });
            
            emailInput.value = '';
        });
    }

    // --- CONTINUACIÓN DEL ARCHIVO main.js ---

    // 1. Lógica del Reveal (Sustituye al componente Reveal.tsx de React)
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.replace('reveal-hidden', 'reveal-shown');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });

    document.querySelectorAll('.reveal-hidden').forEach((el, index) => {
        // Añadir un pequeño retraso en cascada opcional
        el.style.transitionDelay = `${(index % 4) * 80}ms`;
        observer.observe(el);
    });

    // 2. Lógica de "Añadir al carrito" con persistencia en PHP
    const addCartButtons = document.querySelectorAll('.js-add-cart, #btn-add-detail');
    const cartBadge = document.getElementById('cart-badge');

    addCartButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const qtyInput = document.getElementById('qty-value');
            const qty = qtyInput ? parseInt(qtyInput.textContent) : 1;

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('qty', qty);

            // Usamos la variable global definida en el HTML
            fetch(window.BASE_URL + 'ajax-add-cart.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                if (cartBadge) {
                    let currentCount = parseInt(cartBadge.textContent || '0');
                    currentCount += qty;
                    cartBadge.textContent = currentCount;
                    cartBadge.style.display = 'grid';
                }

                Swal.fire({
                    toast: true,
                    position: 'bottom',
                    icon: 'success',
                    title: `Añadido al carrito`,
                    text: `${qty} × "${productName}"`,
                    showConfirmButton: false,
                    timer: 2200,
                    timerProgressBar: true,
                    background: 'var(--color-primary-deep)',
                    color: 'var(--color-primary-foreground)',
                    iconColor: 'var(--color-accent)'
                });
            });
        });
    });
// --- LÓGICA DE LA TIENDA (FILTROS) ---
    
    // 1. Menú de filtros en móvil
    const filterBtn = document.getElementById('mobile-filter-btn');
    const filterSidebar = document.getElementById('filter-sidebar');
    if (filterBtn && filterSidebar) {
        filterBtn.addEventListener('click', () => {
            filterSidebar.classList.toggle('hidden');
            filterSidebar.classList.toggle('block');
        });
    }

    // 2. Envío automático al hacer clic en un Checkbox (Marca o Categoría)
    const filterForm = document.getElementById('filter-form');
    const filterCheckboxes = document.querySelectorAll('.js-filter-checkbox');
    filterCheckboxes.forEach(chk => {
        chk.addEventListener('change', () => {
            filterForm.submit();
        });
    });

    // 3. Barra deslizante de precio (Slider)
    const priceSlider = document.getElementById('price-slider');
    const priceLabel = document.getElementById('price-label');
    if (priceSlider && priceLabel) {
        // Evento 'input': Cambia el texto visualmente mientras arrastras (sin recargar la web)
        priceSlider.addEventListener('input', (e) => {
            const formateado = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(e.target.value);
            priceLabel.textContent = `Hasta ${formateado}`;
        });
        
        // Evento 'change': Solo envía el formulario cuando sueltas el ratón
        priceSlider.addEventListener('change', () => {
            filterForm.submit();
        });
    }

    // --- LÓGICA DE LA PÁGINA DE PRODUCTO ---
    
    // 1. Selector de Cantidad
    const btnMinus = document.getElementById('btn-qty-minus');
    const btnPlus = document.getElementById('btn-qty-plus');
    const qtyValue = document.getElementById('qty-value');
    
    if (btnMinus && btnPlus && qtyValue) {
        let currentQty = 1;
        
        btnMinus.addEventListener('click', () => {
            if (currentQty > 1) {
                currentQty--;
                qtyValue.textContent = currentQty;
            }
        });
        
        btnPlus.addEventListener('click', () => {
            currentQty++;
            qtyValue.textContent = currentQty;
        });
    }

    // 2. Galería de Imágenes
    const mainImage = document.getElementById('main-gallery-image');
    const thumbnails = document.querySelectorAll('.js-gallery-thumb');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                // Cambiar imagen principal
                const newSrc = this.getAttribute('data-src');
                mainImage.setAttribute('src', newSrc);
                
                // Actualizar bordes activos
                thumbnails.forEach(t => {
                    t.classList.remove('border-accent');
                    t.classList.add('border-transparent', 'hover:border-primary/40');
                });
                this.classList.remove('border-transparent', 'hover:border-primary/40');
                this.classList.add('border-accent');
            });
        });
    }

    // 3. Añadir al carrito desde el detalle (leyendo la cantidad)
    const btnAddDetail = document.getElementById('btn-add-detail');
    if (btnAddDetail) {
        btnAddDetail.addEventListener('click', function() {
            const productName = this.getAttribute('data-name');
            const qty = qtyValue ? parseInt(qtyValue.textContent) : 1;
            
            cartCount += qty;
            cartBadge.textContent = cartCount;
            cartBadge.style.display = 'grid';

            Swal.fire({
                toast: true,
                position: 'bottom',
                icon: 'success',
                title: `Añadido al carrito`,
                text: `${qty} × "${productName}"`,
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true,
                background: 'var(--color-primary-deep)',
                color: 'var(--color-primary-foreground)',
                iconColor: 'var(--color-accent)'
            });
        });
    }


});