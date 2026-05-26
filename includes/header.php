<?php
$currentUser = isLoggedIn() ? getCurrentUser() : null;
?>
<header class="header">
    <div class="header-container container">
        <a href="index.php" class="logo">
            <svg class="logo-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="6" width="20" height="12" rx="2"/>
                <circle cx="12" cy="12" r="2"/>
                <line x1="6" y1="12" x2="6" y2="12"/>
                <line x1="18" y1="12" x2="18" y2="12"/>
            </svg>
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
                <li><a href="about.php" class="nav-link">О нас</a></li>
                <li><a href="promotions.php" class="nav-link">Акции</a></li>
                <li><a href="search.php" class="nav-link">Поиск</a></li>
                <li><a href="contacts.php" class="nav-link">Контакты</a></li>
            </ul>
        </nav>
        
        <div class="header-actions">
            <button id="theme-toggle" class="btn-icon" title="Переключить тему">
                <svg class="icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
            <button id="accessibility-toggle" class="btn-icon" title="Режим для слабовидящих">
                <svg class="icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>
            
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline" style="margin-right:10px;" title="Админ-панель">
                        <svg class="icon-svg-inline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Админка
                    </a>
                <?php endif; ?>
                <a href="cart.php" class="btn-icon" title="Корзина">
                    <svg class="icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span id="cart-badge" class="badge" style="display: none;">0</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
