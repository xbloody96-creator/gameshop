<nav class="admin-sidebar" id="sidebar">
    <button class="sidebar-toggle-top" id="sidebarToggleTop" onclick="toggleSidebarDesktop()" title="Свернуть/развернуть меню">
        <span class="toggle-icon-top">⌄</span>
    </button>
    
    <div class="admin-logo">
        <h2><span class="logo-icon">🎮</span> <span class="logo-text">JustKey Admin</span></h2>
    </div>
    
    <ul class="admin-menu">
        <li>
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <span class="menu-icon">📊</span>
                Dashboard
            </a>
        </li>
        <li>
            <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                <span class="menu-icon">🎮</span>
                Товары
            </a>
        </li>
        <li>
            <a href="news.php" class="<?= basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : '' ?>">
                <span class="menu-icon">📰</span>
                Новости
            </a>
        </li>
        <li>
            <a href="services.php" class="<?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : '' ?>">
                <span class="menu-icon">🛠️</span>
                Услуги
            </a>
        </li>
        <li>
            <a href="promotions.php" class="<?= basename($_SERVER['PHP_SELF']) == 'promotions.php' ? 'active' : '' ?>">
                <span class="menu-icon">🏷️</span>
                Акции
            </a>
        </li>
        <li>
            <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                <span class="menu-icon">👥</span>
                Пользователи
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <span class="menu-icon">🛒</span>
                Заказы
            </a>
        </li>
        <li>
            <a href="reviews.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
                <span class="menu-icon">💬</span>
                Отзывы
                <?php
                // Подсчет ожидающих отзывов
                try {
                    $pending_stmt = $pdo->query("SELECT COUNT(*) as count FROM reviews WHERE is_approved = 0");
                    $pending_count = $pending_stmt->fetch()['count'];
                    if ($pending_count > 0) {
                        echo '<span class="badge">' . $pending_count . '</span>';
                    }
                } catch(Exception $e) {}
                ?>
            </a>
        </li>
        <li style="margin-top: 20px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="../index.php" target="_blank">
                <span class="menu-icon">🌐</span>
                На сайт
            </a>
        </li>
        <li>
            <a href="../logout.php" class="logout">
                <span class="menu-icon">🚪</span>
                Выход
            </a>
        </li>
    </ul>
</nav>

<button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleSidebar()">
    <span class="hamburger">☰</span>
</button>
