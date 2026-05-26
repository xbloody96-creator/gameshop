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
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
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
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        <span class="payment-method-name">Банковские карты</span>
                    </div>
                    <div class="payment-method">
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/>
                        </svg>
                        <span class="payment-method-name">Visa</span>
                    </div>
                    <div class="payment-method">
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="payment-method-name">MasterCard</span>
                    </div>
                    <div class="payment-method">
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 11h8a4 4 0 0 0 0-8H9v18"/>
                            <line x1="6" y1="15" x2="14" y2="15"/>
                        </svg>
                        <span class="payment-method-name">МИР</span>
                    </div>
                    <div class="payment-method">
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span class="payment-method-name">Qiwi кошелек</span>
                    </div>
                    <div class="payment-method">
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span class="payment-method-name">Яндекс.Деньги</span>
                    </div>
                    <div class="payment-method">
                        <svg class="payment-method-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="8 11 12 15 16 11"/>
                        </svg>
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
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <h3 class="info-card-title">Мгновенная обработка</h3>
                        <p class="info-card-text">Заказы обрабатываются автоматически 24/7 без участия человека</p>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <h3 class="info-card-title">Безопасность платежей</h3>
                        <p class="info-card-text">Все платежи защищены по стандарту PCI DSS через NicePay</p>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="8 11 12 15 16 11"/>
                        </svg>
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
