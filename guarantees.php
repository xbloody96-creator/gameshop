<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Гарантии - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <div class="info-header">
                <h1 class="info-title">Гарантии</h1>
                <p class="info-subtitle">Ваша безопасность и уверенность в каждой покупке</p>
            </div>
            
            <div class="info-content">
                <!-- Guarantee Cards -->
                <div class="info-cards">
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="8 11 12 15 16 11"/></svg>
                        <h3 class="info-card-title">100% работоспособность</h3>
                        <p class="info-card-text">Все ключи проверяются перед продажей и гарантированно работают</p>
                        <ul class="info-card-list">
                            <li>Предварительная проверка</li>
                            <li>Только рабочие ключи</li>
                            <li>Гарантия активации</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                        <h3 class="info-card-title">Официальные ключи</h3>
                        <p class="info-card-text">Работаем только с официальными поставщиками и дистрибьюторами</p>
                        <ul class="info-card-list">
                            <li>Прямые контракты</li>
                            <li>Лицензионные ключи</li>
                            <li>Полная легальность</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H9"/><line x1="12" y1="18" x2="12" y2="22"/></svg>
                        <h3 class="info-card-title">Возврат средств</h3>
                        <p class="info-card-text">Если ключ не работает, вернем деньги в течение 30 дней</p>
                        <ul class="info-card-list">
                            <li>30 дней на возврат</li>
                            <li>Быстрая обработка</li>
                            <li>Без лишних вопросов</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <h3 class="info-card-title">Безопасность данных</h3>
                        <p class="info-card-text">Ваши персональные данные надежно защищены и не передаются третьим лицам</p>
                        <ul class="info-card-list">
                            <li>Шифрование данных</li>
                            <li>Защита информации</li>
                            <li>Конфиденциальность</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        <h3 class="info-card-title">Поддержка 24/7</h3>
                        <p class="info-card-text">Наша команда поддержки всегда готова помочь с любым вопросом</p>
                        <ul class="info-card-list">
                            <li>Круглосуточно</li>
                            <li>Быстрые ответы</li>
                            <li>Профессиональная помощь</li>
                        </ul>
                    </div>
                    <div class="info-card">
                        <svg class="info-card-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        <h3 class="info-card-title">Мгновенная доставка</h3>
                        <p class="info-card-text">Ключи приходят сразу после подтверждения оплаты</p>
                        <ul class="info-card-list">
                            <li>Автоматическая система</li>
                            <li>Без задержек</li>
                            <li>Email + Личный кабинет</li>
                        </ul>
                    </div>
                </div>
                
                <!-- FAQ Section -->
                <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Часто задаваемые вопросы</h2>
                <div class="info-faq">
                    <div class="faq-item">
                        <div class="faq-question">Что делать если ключ не работает?</div>
                        <div class="faq-answer">
                            <p>1. Проверьте правильность ввода ключа<br>
                            2. Убедитесь, что ключ подходит для вашего региона<br>
                            3. Проверьте системные требования игры<br>
                            4. Если проблема не решена, свяжитесь с поддержкой support@justkey.ru</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Как быстро я получу ключ?</div>
                        <div class="faq-answer">
                            <p>Ключ приходит мгновенно после подтверждения платежа. В среднем это занимает от 30 секунд до 2 минут. Ключ отправляется на email и становится доступен в личном кабинете.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Могу ли я вернуть ключ?</div>
                        <div class="faq-answer">
                            <p>Да, если ключ не активирован и с момента покупки прошло не более 30 дней, вы можете вернуть средства. Для цифровых товаров это стандартная практика защиты покупателей.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Безопасно ли оплачивать на сайте?</div>
                        <div class="faq-answer">
                            <p>Абсолютно безопасно! Мы используем платежную систему NicePay, которая соответствует стандарту безопасности PCI DSS. Все данные передаются по защищенному протоколу HTTPS с шифрованием.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Какие гарантии вы предоставляете?</div>
                        <div class="faq-answer">
                            <p>Мы гарантируем 100% работоспособность всех ключей, их официальное происхождение, конфиденциальность ваших данных и быстрый возврат средств в случае проблем.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Trust Stats -->
                <div class="info-stats">
                    <div class="info-stat">
                        <div class="info-stat-number">100%</div>
                        <div class="info-stat-label">Рабочих ключей</div>
                    </div>
                    <div class="info-stat">
                        <div class="info-stat-number">30 дней</div>
                        <div class="info-stat-label">На возврат</div>
                    </div>
                    <div class="info-stat">
                        <div class="info-stat-number">24/7</div>
                        <div class="info-stat-label">Поддержка</div>
                    </div>
                    <div class="info-stat">
                        <div class="info-stat-number">0%</div>
                        <div class="info-stat-label">Скрытых комиссий</div>
                    </div>
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
