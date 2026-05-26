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
            <h1>Доставка и оплата</h1>
            <div class="info-content">
                <h2>Способы доставки</h2>
                <p>Так как мы продаем цифровые ключи, доставка осуществляется мгновенно:</p>
                <ul>
                    <li><strong>Email:</strong> Ключ приходит на вашу электронную почту сразу после оплаты</li>
                    <li><strong>Личный кабинет:</strong> Ключ доступен в разделе "Мои заказы"</li>
                </ul>
                
                <h2>Способы оплаты</h2>
                <ul>
                    <li>Банковские карты (Visa, MasterCard, МИР)</li>
                    <li>Qiwi кошелек</li>
                    <li>Яндекс.Деньги</li>
                    <li>SberPay</li>
                </ul>
                
                <h2>Время обработки заказа</h2>
                <p>Заказы обрабатываются автоматически. Ключ поступает к вам сразу после подтверждения платежа.</p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
