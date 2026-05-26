<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О нас - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <div class="info-header">
                <h1 class="info-title">О компании <?= SITE_NAME ?></h1>
                <p class="info-subtitle">Ваш надежный партнер в мире цифровых развлечений</p>
            </div>
            
            <div class="info-content">
                <!-- Stats Section -->
                <div class="info-stats">
                    <div class="info-stat">
                        <div class="info-stat-number">10,000+</div>
                        <div class="info-stat-label">Довольных клиентов</div>
                    </div>
                    <div class="info-stat">
                        <div class="info-stat-number">50,000+</div>
                        <div class="info-stat-label">Проданных ключей</div>
                    </div>
                    <div class="info-stat">
                        <div class="info-stat-number">24/7</div>
                        <div class="info-stat-label">Поддержка</div>
                    </div>
                    <div class="info-stat">
                        <div class="info-stat-number">4+</div>
                        <div class="info-stat-label">Года на рынке</div>
                    </div>
                </div>
                
                <!-- About Cards -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-card-icon">🚀</div>
                        <h3 class="info-card-title">Мгновенная доставка</h3>
                        <p class="info-card-text">Ключи приходят сразу после оплаты на email и в личный кабинет</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">🔒</div>
                        <h3 class="info-card-title">Официальные ключи</h3>
                        <p class="info-card-text">Работаем только с прямыми поставщиками и официальными дистрибьюторами</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">💳</div>
                        <h3 class="info-card-title">Безопасная оплата</h3>
                        <p class="info-card-text">Платежная система NicePay с защитой всех ваших данных</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">🎧</div>
                        <h3 class="info-card-title">Поддержка 24/7</h3>
                        <p class="info-card-text">Наша команда всегда готова помочь с любым вопросом</p>
                    </div>
                </div>
                
                <!-- Mission Feature -->
                <div class="info-feature">
                    <div class="info-feature-content">
                        <h2>Наша миссия</h2>
                        <p>Мы стремимся сделать покупку игровых ключей максимально простой, безопасной и выгодной для наших клиентов.</p>
                        <p>Каждый день мы работаем над улучшением сервиса, расширением ассортимента и повышением качества обслуживания.</p>
                        <ul class="info-card-list">
                            <li>Честные цены без скрытых комиссий</li>
                            <li>Гарантия возврата средств</li>
                            <li>Постоянное обновление ассортимента</li>
                        </ul>
                    </div>
                    <img src="https://images.unsplash.com/photo-1556438064-2d764616693e?w=600" alt="Наша миссия" class="info-feature-image">
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
    <script>
    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const item = question.parentElement;
            item.classList.toggle('active');
        });
    });
    </script>
</body>
</html>
