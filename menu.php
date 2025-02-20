<?php
session_start();
?>

<nav class="bg-blue-700 text-white shadow-lg">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="index.php" class="text-2xl font-bold hover:text-gray-200">Meu Sistema</a>

        <!-- Links do Menu -->
        <div class="hidden md:flex space-x-6">
            <a href="index.php" class="hover:text-gray-300 text-lg font-medium">Home</a>
            <a href="add_product.php" class="hover:text-gray-300 text-lg font-medium">Adicionar Produto</a>
            <a href="product_cards.php" class="hover:text-gray-300 text-lg font-medium">Cartões QR Code</a>
            <a href="scan_qr.php" class="hover:text-gray-300 text-lg font-medium">Escanear QR Code</a>

            <!-- Link para registrar usuário (somente para admins) -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="register.php" class="hover:text-gray-300 text-lg font-medium">Registrar Usuário</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="manage_users.php" class="hover:text-gray-300 text-lg font-medium">Gerenciar Usuários</a>
<?php endif; ?>


            <a href="logout.php" class="hover:text-gray-300 text-lg font-medium">Logout</a>
        </div>

        <!-- Menu Mobile -->
        <button id="menuToggle" class="md:hidden text-xl focus:outline-none">
            ☰
        </button>
    </div>

    <!-- Links para Mobile -->
    <div id="mobileMenu" class="hidden md:hidden bg-blue-600">
        <a href="index.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Home</a>
        <a href="add_product.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Adicionar Produto</a>
        <a href="product_cards.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Cartões QR Code</a>
        <a href="scan_qr.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Escanear QR Code</a>

        <!-- Link para registrar usuário (somente para admins) -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="register.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Registrar Usuário</a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <a href="manage_users.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Gerenciar Usuários</a>
<?php endif; ?>


        <a href="logout.php" class="block px-4 py-2 text-lg font-medium hover:bg-blue-500">Logout</a>
    </div>
</nav>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
