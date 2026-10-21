<div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
    <header class="mb-10 text-center max-w-2xl mx-auto">
        <h1 class="font-display text-4xl font-bold uppercase md:text-5xl">Asesoría Técnica y Contacto</h1>
        <p class="mt-3 text-muted-foreground">
            ¿Tienes dudas sobre una referencia, necesitas comprobar la compatibilidad de un inyector o verificar un turbo? Nuestros mecánicos especializados te atienden directamente.
        </p>
    </header>

    <div class="grid gap-10 lg:grid-cols-2">
        <!-- Formulario de contacto -->
        <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)]">
            <h2 class="font-display text-2xl font-bold uppercase">Envíanos tu consulta</h2>
            <form id="contact-form" class="mt-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="contact-nombre" class="text-sm font-medium">Nombre o Taller</label>
                        <input type="text" id="contact-nombre" required placeholder="Taller Mecánico" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                    <div class="space-y-2">
                        <label for="contact-email" class="text-sm font-medium">Correo electrónico</label>
                        <input type="email" id="contact-email" required placeholder="contacto@taller.com" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="contact-ref" class="text-sm font-medium">Referencia de la pieza (Opcional)</label>
                    <input type="text" id="contact-ref" placeholder="Ej. 0445110252 o 1234-ABC" class="w-full h-11 rounded-xl border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="space-y-2">
                    <label for="contact-msg" class="text-sm font-medium">Mensaje o consulta técnica</label>
                    <textarea id="contact-msg" rows="4" required placeholder="Describe el recambio exacto que necesitas..." class="w-full rounded-xl border border-input bg-background p-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                </div>
                <button type="submit" class="btn-base btn-cta !py-3.5 w-full !text-lg shadow-[0_0_20px_rgba(224,193,18,0.4)] transition-shadow hover:shadow-[0_0_30px_rgba(224,193,18,0.7)]">
                    Enviar Consulta
                </button>
            </form>
        </div>

        <!-- Información de soporte -->
        <div class="space-y-6 flex flex-col justify-between">
            <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] space-y-6">
                <h2 class="font-display text-2xl font-bold uppercase">Atención directa</h2>
                <p class="text-muted-foreground text-sm leading-relaxed">
                    Trabajamos codo a codo con profesionales del motor. Si requieres informes de banco específicos o asesoramiento en bombas common rail, contáctanos por vía directa.
                </p>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary">
                            <i data-lucide="map-pin" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="font-semibold">Dirección</p>
                            <p class="text-muted-foreground">Av. Iquitos 143 105B, La Victoria</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary">
                            <i data-lucide="phone" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="font-semibold">Teléfono técnico</p>
                            <p class="text-muted-foreground">+51 999 070 362</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary">
                            <i data-lucide="mail" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="font-semibold">Correo electrónico</p>
                            <p class="text-muted-foreground">Cgarciasac@gmail.com</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary">
                            <i data-lucide="truck" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="font-semibold">Logística</p>
                            <p class="text-muted-foreground">Envíos express a nivel nacional</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const contactForm = document.getElementById("contact-form");
    if (contactForm) {
        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();
            Swal.fire({
                title: '¡Mensaje enviado!',
                text: 'Un técnico diésel se pondrá en contacto contigo a la brevedad.',
                icon: 'success',
                confirmButtonColor: '#e0c112',
                confirmButtonText: 'Entendido'
            });
            contactForm.reset();
        });
    }
});
</script>