<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доставка и оплата - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <div class="info-header">
                <h1 class="info-title">Доставка и оплата</h1>
                <p class="info-subtitle">Быстро, удобно и безопасно</p>
            </div>
            
            <div class="info-content">
                <!-- Delivery Cards -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-card-icon">📧</div>
                        <h3 class="info-card-title">Email доставка</h3>
                        <p class="info-card-text">Ключ приходит на вашу электронную почту сразу после оплаты</p>
                        <ul class="info-card-list">
                            <li>Мгновенная отправка</li>
                            <li>Автоматическая система</li>
                            <li>Проверка спама при необходимости</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">👤</div>
                        <h3 class="info-card-title">Личный кабинет</h3>
                        <p class="info-card-text">Все ваши ключи доступны в разделе "Мои заказы"</p>
                        <ul class="info-card-list">
                            <li>История всех покупок</li>
                            <li>Вечный доступ к ключам</li>
                            <li>Удобное хранение</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Способы оплаты</h2>
                <div class="payment-methods">
                    <div class="payment-method">
                        <span class="payment-method-icon">💳</span>
                        <span class="payment-method-name">Банковские карты</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-method-icon">🔵</span>
                        <span class="payment-method-name">Visa</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-method-icon">🔴</span>
                        <span class="payment-method-name">MasterCard</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-method-icon">🟢</span>
                        <span class="payment-method-name">МИР</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-method-icon">🟣</span>
                        <span class="payment-method-name">Qiwi кошелек</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-method-icon">🔴</span>
                        <span class="payment-method-name">Яндекс.Деньги</span>
                    </div>
                    <div class="payment-method">
                        <span class="payment-method-icon">🟢</span>
                        <span class="payment-method-name">SberPay</span>
                    </div>
                </div>
                
                <!-- Process Feature -->
                <div class="info-feature">
                    <img src="https://images.unsplash.com/photo-1563986768609-322da13575f3?w=600" alt="Процесс покупки" class="info-feature-image">
                    <div class="info-feature-content">
                        <h2>Как это работает?</h2>
                        <p>Процесс покупки максимально упрощен и занимает всего несколько минут:</p>
                        <ol class="info-card-list" style="list-style: none; padding-left: 0;">
                            <li style="padding: 1rem 0; border-bottom: 1px solid var(--border-color);"><strong>1.</strong> Выберите товар и добавьте в корзину</li>
                            <li style="padding: 1rem 0; border-bottom: 1px solid var(--border-color);"><strong>2.</strong> Оформите заказ, указав email</li>
                            <li style="padding: 1rem 0; border-bottom: 1px solid var(--border-color);"><strong>3.</strong> Оплатите удобным способом</li>
                            <li style="padding: 1rem 0;"><strong>4.</strong> Получите ключ мгновенно</li>
                        </ol>
                    </div>
                </div>
                
                <!-- Info Cards for Processing Time -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-card-icon">⚡</div>
                        <h3 class="info-card-title">Мгновенная обработка</h3>
                        <p class="info-card-text">Заказы обрабатываются автоматически 24/7 без участия человека</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">🔒</div>
                        <h3 class="info-card-title">Безопасность платежей</h3>
                        <p class="info-card-text">Все платежи защищены по стандарту PCI DSS через NicePay</p>
                    </div>
                    <div class="info-card">
                        <div class="info-card-icon">✅</div>
                        <h3 class="info-card-title">Гарантия получения</h3>
                        <p class="info-card-text">Если ключ не пришел, мы вернем деньги или вышлем новый</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
