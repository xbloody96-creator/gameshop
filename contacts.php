<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <div class="info-header">
                <h1 class="info-title">Контакты</h1>
                <p class="info-subtitle">Мы всегда на связи и готовы помочь</p>
            </div>
            
            <div class="info-content">
                <!-- Contact Grid -->
                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-info">
                            <h3>Адрес</h3>
                            <p>Россия, Стерлитамак</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-info">
                            <h3>Телефон</h3>
                            <p><?= SUPPORT_PHONE ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-info">
                            <h3>Email</h3>
                            <p><a href="mailto:<?= SUPPORT_EMAIL ?>"><?= SUPPORT_EMAIL ?></a></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div class="contact-info">
                            <h3>Режим работы</h3>
                            <p>Поддержка 24/7</p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Links -->
                <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Мы в социальных сетях</h2>
                <div class="social-links-grid">
                    <a href="https://t.me/justkey_support" class="social-link-card telegram">
                        <span>✈️</span>
                        <span>Telegram Support</span>
                    </a>
                    <a href="https://discord.gg/justkey" class="social-link-card discord">
                        <span>🎮</span>
                        <span>Discord Server</span>
                    </a>
                    <a href="https://vk.com/justkey" class="social-link-card vk">
                        <span>💙</span>
                        <span>VKontakte</span>
                    </a>
                    <a href="https://youtube.com/@justkey" class="social-link-card youtube">
                        <span>▶️</span>
                        <span>YouTube</span>
                    </a>
                </div>
                
                <!-- Contact Feature -->
                <div class="info-feature">
                    <div class="info-feature-content">
                        <h2>Свяжитесь с нами</h2>
                        <p>Наша команда поддержки готова ответить на любые вопросы:</p>
                        <ul class="info-card-list">
                            <li>Помощь с выбором товара</li>
                            <li>Техническая поддержка</li>
                            <li>Вопросы по оплате</li>
                            <li>Проблемы с активацией ключей</li>
                            <li>Сотрудничество и партнерство</li>
                        </ul>
                        <p style="margin-top: 1.5rem;"><strong>Среднее время ответа:</strong> менее 5 минут</p>
                    </div>
                    <img src="https://images.unsplash.com/photo-1544717297-fa95b6ee9643?w=600" alt="Поддержка клиентов" class="info-feature-image">
                </div>
                
                <!-- Info Cards for Contact Methods -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-card-icon">💬</div>
                        <h3 class="info-card-title">Онлайн чат</h3>
                        <p class="info-card-text">Быстрый способ получить ответ на свой вопрос в режиме реального времени</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">📧</div>
                        <h3 class="info-card-title">Email поддержка</h3>
                        <p class="info-card-text">Для сложных вопросов отправьте письмо, ответим в течение часа</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">📱</div>
                        <h3 class="info-card-title">Мессенджеры</h3>
                        <p class="info-card-text">Удобная связь через Telegram, Discord и другие популярные платформы</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
