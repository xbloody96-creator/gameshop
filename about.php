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
                        <div class="info-card-icon">
                            <svg class="icon icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.85.71-2.24 0-3-.71-.76-2.29-2.29-3-3z"/>
                                <path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
                                <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
                                <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
                            </svg>
                        </div>
                        <h3 class="info-card-title">Мгновенная доставка</h3>
                        <p class="info-card-text">Ключи приходят сразу после оплаты на email и в личный кабинет</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">
                            <svg class="icon icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <h3 class="info-card-title">Официальные ключи</h3>
                        <p class="info-card-text">Работаем только с прямыми поставщиками и официальными дистрибьюторами</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">
                            <svg class="icon icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </div>
                        <h3 class="info-card-title">Безопасная оплата</h3>
                        <p class="info-card-text">Платежная система NicePay с защитой всех ваших данных</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">
                            <svg class="icon icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
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
