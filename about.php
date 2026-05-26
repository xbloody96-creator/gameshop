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
            <h1>О компании <?= SITE_NAME ?></h1>
            <div class="info-content">
                <p><?= SITE_NAME ?> — это современный магазин цифровых ключей для игр. Мы работаем на рынке с 2020 года и за это время обслужили более 10 000 довольных клиентов.</p>
                
                <h2>Наши преимущества</h2>
                <ul>
                    <li>Мгновенная доставка ключей после оплаты</li>
                    <li>Только официальные ключи от прямых поставщиков</li>
                    <li>Безопасная оплата через платежную систему NicePay</li>
                    <li>Поддержка 24/7</li>
                    <li>Гарантия возврата средств</li>
                </ul>
                
                <h2>Наша миссия</h2>
                <p>Мы стремимся сделать покупку игровых ключей максимально простой, безопасной и выгодной для наших клиентов.</p>
                
                <h2>Контакты</h2>
                <p>Адрес: Россия, Стерлитамак</p>
                <p>Телефон: <?= SUPPORT_PHONE ?></p>
                <p>Email: <?= SUPPORT_EMAIL ?></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
