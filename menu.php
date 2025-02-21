<?php
session_start();
?>

<!-- Menu Desktop (Sidebar) -->
<nav id="sidebar" class="bg-blue-700 text-white w-64 h-screen fixed left-0 top-0 overflow-y-auto shadow-lg transition-transform duration-300 hidden md:block -translate-x-full">
    <div class="px-4 py-6">
        <a href="index.php" class="text-2xl font-bold block mb-4 hover:text-gray-200">Meu Sistema</a>
        <ul>
            <li><a href="index.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">🏠 Home</a></li>
            <li><a href="stock_update_logs.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">📜 Registro Estoque</a></li>
            <li><a href="add_product.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">➕ Adicionar Produto</a></li>
            <li><a href="product_cards.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">🖨️ Cartões QR Code</a></li>
            <li><a href="scan_update_stock.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">🔄 Atualizar Estoque</a></li>
            <li><a href="scan_qr.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">📷 Escanear QR Code</a></li>
            <li><a href="dashboard.php" class="hidden lock px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">📊 Dashboard</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="register.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">⚙️ Registrar Usuário</a></li>
                <li><a href="manage_users.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">🔧 Gerenciar Usuários</a></li>
            <?php endif; ?>

            <li><a href="logout.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500 rounded">🚪 Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Botão de Toggle para Desktop -->
<button id="menuToggleDesktop" class="fixed top-5 left-4 p-2 bg-blue-700 text-white rounded-full shadow-lg transition-all duration-300 hidden md:block">
    ☰
</button>

<!-- Menu Mobile -->
<nav class="bg-blue-700 text-white shadow-lg md:hidden">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="index.php" class="text-2xl font-bold hover:text-gray-200">Meu Sistema</a>

        <!-- Botão de Menu Mobile -->
        <button id="menuToggleMobile" class="text-xl focus:outline-none">
            ☰
        </button>
    </div>

    <!-- Links para Mobile -->
    <div id="mobileMenu" class="hidden bg-blue-600">
        <a href="index.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">🏠 Home</a>
        <a href="stock_update_logs.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">📜 Registro Estoque</a>
        <a href="add_product.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">➕ Adicionar Produto</a>
        <a href="product_cards.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">🖨️ Cartões QR Code</a>
        <a href="scan_update_stock.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">🔄 Atualizar Estoque</a>
        <a href="scan_qr.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">📷 Escanear QR Code</a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="register.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">⚙️ Registrar Usuário</a>
            <a href="manage_users.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">🔧 Gerenciar Usuários</a>
        <?php endif; ?>
        <a href="dashboard.php" class="hidden block px-4 py-2 text-lg font-medium hover:bg-blue-500">📊 Dashboard</a>

        <a href="logout.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">🚪 Logout</a>
    </div>
</nav>

<!-- Script para Controle dos Menus -->
<script>
    const sidebar = document.getElementById('sidebar');
    const menuToggleDesktop = document.getElementById('menuToggleDesktop');
    const menuToggleMobile = document.getElementById('menuToggleMobile');
    const mobileMenu = document.getElementById('mobileMenu');

    // Toggle do Menu Desktop
    menuToggleDesktop.addEventListener('click', () => {
        const isOpen = !sidebar.classList.contains('-translate-x-full');

        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            menuToggleDesktop.style.left = "16px"; // Posição ao fechar
        } else {
            sidebar.classList.remove('-translate-x-full');
            menuToggleDesktop.style.left = "260px"; // Posição ao abrir
        }
    });

    // Toggle do Menu Mobile
    menuToggleMobile.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>