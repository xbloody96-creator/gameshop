<nav class="admin-sidebar">
    <div class="admin-logo">
        <h2>🎮 GamesKey Admin</h2>
    </div>
    
    <ul class="admin-menu">
        <li>
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                📊 Dashboard
            </a>
        </li>
        <li>
            <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                🎮 Товары
            </a>
        </li>
        <li>
            <a href="news.php" class="<?= basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : '' ?>">
                📰 Новости
            </a>
        </li>
        <li>
            <a href="services.php" class="<?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : '' ?>">
                🛠️ Услуги
            </a>
        </li>
        <li>
            <a href="promotions.php" class="<?= basename($_SERVER['PHP_SELF']) == 'promotions.php' ? 'active' : '' ?>">
                🏷️ Акции
            </a>
        </li>
        <li>
            <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                👥 Пользователи
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                🛒 Заказы
            </a>
        </li>
        <li>
            <a href="reviews.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
                💬 Отзывы
            </a>
        </li>
        <li>
            <a href="../index.php" target="_blank">
                🌐 На сайт
            </a>
        </li>
        <li>
            <a href="../logout.php" class="logout">
                🚪 Выход
            </a>
        </li>
    </ul>
</nav>
