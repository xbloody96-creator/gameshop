<?php
/**
 * Страница возврата после оплаты через NicePay
 * Перенаправляет пользователя в зависимости от статуса оплаты
 */

require_once 'config.php';
require_once 'includes/nicepay.class.php';

// Получаем параметры от NicePay
$transactionId = $_GET['transactionId'] ?? $_POST['transactionId'] ?? '';
$orderNumber = $_GET['orderNumber'] ?? $_POST['orderNumber'] ?? '';
$status = $_GET['status'] ?? $_POST['status'] ?? '';

$user = getCurrentUser();

// Если пользователь не авторизован, перенаправляем на вход
if (!$user) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат оплаты - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-result {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .payment-result.success {
            border-top: 5px solid #28a745;
        }
        .payment-result.error {
            border-top: 5px solid #dc3545;
        }
        .payment-result.pending {
            border-top: 5px solid #ffc107;
        }
        .payment-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .payment-result h1 {
            margin-bottom: 20px;
        }
        .payment-result p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: left;
        }
        .order-details h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .order-details p {
            margin: 10px 0;
            color: #333;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        @media (max-width: 600px) {
            .payment-result {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="payment-return-page">
        <div class="container">
            <?php if ($status === 'SUCCESS' || $status === 'COMPLETED' || $status === 'PAID'): ?>
                
                <div class="payment-result success">
                    <div class="payment-icon"><svg class="payment-status-icon-svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="8 11 12 15 16 11"/></svg></div>
                    <h1>Оплата прошла успешно!</h1>
                    <p>
                        Спасибо за покупку! Ваш заказ оплачен и уже обрабатывается.
                        Ключи активации отправлены на ваш email.
                    </p>
                    
                    <?php if ($orderNumber): ?>
                        <div class="order-details">
                            <h3>Детали заказа</h3>
                            <p><strong>Номер заказа:</strong> <?= escape($orderNumber) ?></p>
                            <p><strong>Статус:</strong> <span style="color: #28a745;">Оплачен</span></p>
                            <p><strong>Дата:</strong> <?= date('d.m.Y H:i') ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="btn-group">
                        <a href="profile.php" class="btn btn-primary">В личный кабинет</a>
                        <a href="products.php" class="btn btn-secondary">Продолжить покупки</a>
                    </div>
                </div>
                
            <?php elseif ($status === 'FAILED' || $status === 'CANCELLED' || $status === 'DECLINED'): ?>
                
                <div class="payment-result error">
                    <div class="payment-icon"><svg class="payment-status-icon-svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
                    <h1>Оплата не прошла</h1>
                    <p>
                        К сожалению, оплата вашего заказа не была завершена.
                        Это могло произойти по следующим причинам:
                    </p>
                    <ul style="text-align: left; display: inline-block; color: #666;">
                        <li>Недостаточно средств на карте</li>
                        <li>Истекло время ожидания оплаты</li>
                        <li>Платеж был отменен вами</li>
                        <li>Технические проблемы на стороне банка</li>
                    </ul>
                    
                    <?php if ($orderNumber): ?>
                        <div class="order-details">
                            <h3>Детали заказа</h3>
                            <p><strong>Номер заказа:</strong> <?= escape($orderNumber) ?></p>
                            <p><strong>Статус:</strong> <span style="color: #dc3545;">Отменен</span></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="btn-group">
                        <a href="checkout.php" class="btn btn-primary">Попробовать снова</a>
                        <a href="profile.php" class="btn btn-secondary">В личный кабинет</a>
                    </div>
                </div>
                
            <?php elseif ($status === 'PENDING' || $status === 'PROCESSING'): ?>
                
                <div class="payment-result pending">
                    <div class="payment-icon">⏳</div>
                    <h1>Оплата в обработке</h1>
                    <p>
                        Ваш платеж находится в обработке. 
                        Обычно это занимает несколько минут.
                        Как только оплата подтвердится, вы получите уведомление на email.
                    </p>
                    
                    <?php if ($orderNumber): ?>
                        <div class="order-details">
                            <h3>Детали заказа</h3>
                            <p><strong>Номер заказа:</strong> <?= escape($orderNumber) ?></p>
                            <p><strong>Статус:</strong> <span style="color: #ffc107;">Обработка</span></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="btn-group">
                        <a href="profile.php" class="btn btn-primary">В личный кабинет</a>
                        <a href="products.php" class="btn btn-secondary">Продолжить покупки</a>
                    </div>
                </div>
                
            <?php else: ?>
                
                <div class="payment-result error">
                    <div class="payment-icon">⚠️</div>
                    <h1>Статус платежа неизвестен</h1>
                    <p>
                        Не удалось определить статус вашего платежа.
                        Пожалуйста, проверьте статус заказа в личном кабинете 
                        или свяжитесь с нашей службой поддержки.
                    </p>
                    
                    <div class="btn-group">
                        <a href="profile.php" class="btn btn-primary">В личный кабинет</a>
                        <a href="mailto:<?= ADMIN_EMAIL ?>" class="btn btn-secondary">Написать в поддержку</a>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
