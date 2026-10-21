<?php
// Si no se define qué pestaña está activa, por defecto marcamos 'inventario'
$activeTab = $activeTab ?? 'inventario';
$baseUrl = $_ENV['BASE_URL'];
?>

<aside class="w-full md:w-64 shrink-0 space-y-2">
    <h2 class="font-display text-lg font-bold uppercase mb-4 px-2">Administración</h2>
    
    <a href="<?= $baseUrl ?>dashboard" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-colors <?= $activeTab === 'inventario' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' ?>">
        <i data-lucide="package" class="h-5 w-5"></i> Inventario
    </a>
    
    <a href="<?= $baseUrl ?>dashboard/categorias" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-colors <?= $activeTab === 'categorias' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' ?>">
        <i data-lucide="layers" class="h-5 w-5"></i> Categorías
    </a>
    
    <a href="<?= $baseUrl ?>dashboard/marcas" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-colors <?= $activeTab === 'marcas' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' ?>">
        <i data-lucide="tag" class="h-5 w-5"></i> Marcas
    </a>

    <a href="<?= $baseUrl ?>dashboard/clientes" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-colors <?= $activeTab === 'clientes' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' ?>">
        <i data-lucide="users" class="h-5 w-5"></i> Clientes
    </a>
    
    <a href="<?= $baseUrl ?>dashboard/pedidos" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-colors <?= $activeTab === 'pedidos' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' ?>">
        <i data-lucide="shopping-bag" class="h-5 w-5"></i> Pedidos
    </a>
    <hr class="border-border my-2">

    <a href="<?= $baseUrl ?>dashboard/configuracion" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-colors <?= $activeTab === 'configuracion' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' ?>">
        <i data-lucide="settings" class="h-5 w-5"></i> Configuración
    </a>
</aside>