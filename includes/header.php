<?php
$currentUser = isLoggedIn() ? getCurrentUser() : null;
?>
<header class="header">
    <div class="header-container container">
        <a href="index.php" class="logo">
            <div class="logo-icon">🎮</div>
            <span class="logo-text"><?= SITE_NAME ?></span>
        </a>
        
        <nav class="nav">
            <ul class="nav-menu">
                <?php if (!isLoggedIn()): ?>
                    <li><a href="register.php" class="nav-link">Регистрация</a></li>
                    <li><a href="login.php" class="nav-link">Авторизация</a></li>
                <?php else: ?>
                    <li><a href="profile.php" class="nav-link">Личный кабинет</a></li>
                <?php endif; ?>
                <li><a href="#about" class="nav-link">О нас</a></li>
                <li><a href="#promotions" class="nav-link">Акции</a></li>
                <li><a href="#search" class="nav-link">Поиск</a></li>
                <li><a href="#footer" class="nav-link">Контакты</a></li>
            </ul>
        </nav>
        
        <div class="header-actions">
            <button id="theme-toggle" class="btn-icon" title="Переключить тему">🌙</button>
            <button id="accessibility-toggle" class="btn-icon" title="Режим для слабовидящих">🔍</button>
            
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline" style="margin-right:10px;" title="Админ-панель">🛡️ Админка</a>
                <?php endif; ?>
                <a href="cart.php" class="btn-icon" title="Корзина">
                    🛒
                    <span id="cart-badge" class="badge" style="display: none;">0</span>
                </a>
                <a href="logout.php" class="btn btn-outline" title="Выйти">🚪 Выход</a>
            <?php endif; ?>
        </div>
    </div>
</header>
