<?php
require_once __DIR__ . '/auth.php';
require_authenticated_user();

$currentPath = $_SERVER['REQUEST_URI'];
$basePath = app_base_path();
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">

        <div class="logo">
            <i class="fa-solid fa-gem"></i>
        </div>

        <div class="brand-name">
            ANAJAK JEWELRY
        </div>

    </div>


    <nav class="sidebar-menu">
        <p class="menu-title">MAIN</p>
        <a href="<?php echo $basePath; ?>/dashboards/" class="menu-item <?php echo strpos($currentPath, '/dashboards/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-house"></i></span>
            <span class="menu-text">Dashboard</span>
        </a>

        <a href="<?php echo $basePath; ?>/products/" class="menu-item <?php echo strpos($currentPath, '/products/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-industry"></i></span>
            <span class="menu-text">Products</span>
        </a>

        <a href="<?php echo $basePath; ?>/categories/" class="menu-item <?php echo strpos($currentPath, '/categories/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-layer-group"></i></span>
            <span class="menu-text">Categories</span>
        </a>

        <a href="<?php echo $basePath; ?>/brands/" class="menu-item <?php echo strpos($currentPath, '/brands/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-regular fa-gem"></i></span>
            <span class="menu-text">Brands</span>
        </a>

        <a href="<?php echo $basePath; ?>/suppliers/" class="menu-item <?php echo strpos($currentPath, '/suppliers/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-truck-fast"></i></span>
            <span class="menu-text">Suppliers</span>
        </a>

        <a href="<?php echo $basePath; ?>/customer/" class="menu-item <?php echo strpos($currentPath, '/customer/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-user-group"></i></span>
            <span class="menu-text">Customers</span>
        </a>

        <p class="menu-title">OPERATIONS</p>
        <a href="<?php echo $basePath; ?>/inventory/" class="menu-item <?php echo strpos($currentPath, '/inventory/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-box-open"></i></span>
            <span class="menu-text">Inventory</span>
        </a>

        <a href="<?php echo $basePath; ?>/sales/" class="menu-item <?php echo strpos($currentPath, '/sales/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-sack-dollar"></i></span>
            <span class="menu-text">POS/Sales</span>
        </a>

        <a href="<?php echo $basePath; ?>/custom-orders/" class="menu-item <?php echo strpos($currentPath, '/custom-orders/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-list"></i></span>
            <span class="menu-text">Customer Orders</span>
        </a>

        <p class="menu-title">REPORTS</p>
        <a href="<?php echo $basePath; ?>/reports/" class="menu-item <?php echo strpos($currentPath, '/reports/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-chart-line"></i></span>
            <span class="menu-text">Reports</span>
        </a>

        <p class="menu-title">SYSTEM</p>
        <a href="<?php echo $basePath; ?>/settings/" class="menu-item <?php echo strpos($currentPath, '/settings/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-gear"></i></span>
            <span class="menu-text">Settings</span>
        </a>

        <a href="<?php echo $basePath; ?>/profile/" class="menu-item <?php echo strpos($currentPath, '/profile/') !== false ? 'active' : ''; ?>">
            <span><i class="fa-solid fa-user"></i></span>
            <span class="menu-text">Profile</span>
        </a>

    </nav>

    

</aside>


<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    //JAVASCRIPT បង្កើតក្នុង navbar​ ផ្ទាល់ព្រោះយើង include វាគ្រប់ page ចឹងបើយើងបង្កើតតែក្នុង dashboard.js វានឹងមិនដំណើរការគ្រប់ page ទេ
    const menuBtn = document.getElementById("menuBtn");
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    
    if (!menuBtn || !sidebar || !sidebarOverlay) {
        console.log("Something is missing!");
        return;
    }

    menuBtn.addEventListener("click", function () {

        console.log("☰ CLICKED!");

        sidebar.classList.toggle("active");
        sidebarOverlay.classList.toggle("active");

        console.log("Sidebar class:", sidebar.className);

    });

    sidebarOverlay.addEventListener("click", function () {
        sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
    });

});
</script>