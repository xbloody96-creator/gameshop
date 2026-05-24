<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

// Получение содержимого корзины
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.title, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
    
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    if (empty($cartItems)) {
        header('Location: cart.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Ошибка получения корзины';
    $cartItems = [];
    $total = 0;
}

// Обработка оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $deliveryMethod = $_POST['delivery_method'] ?? 'email';
    $paymentMethod = $_POST['payment_method'] ?? 'card';
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        // Генерация номера заказа
        $orderNumber = 'ORD-' . strtoupper(uniqid());
        
        // Создание заказа
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, delivery_method, 
                                customer_name, customer_email, customer_phone, notes) 
            VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $orderNumber,
            $total,
            $paymentMethod,
            $deliveryMethod,
            $user['full_name'],
            $user['email'],
            '', // телефон можно добавить в профиль
            $notes
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Добавление товаров в заказ
        foreach ($cartItems as $item) {
            // Генерация ключа игры (для примера)
            $gameKey = 'GK-' . strtoupper(bin2hex(random_bytes(8))) . '-' . strtoupper(bin2hex(random_bytes(8)));
            
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_title, price, quantity, game_key) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['title'],
                $item['price'],
                $item['quantity'],
                $gameKey
            ]);
        }
        
        // Очистка корзины
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Инициализация оплаты через NicePay
        require_once 'includes/nicepay.class.php';
        
        // Настройки NicePay (получите их в личном кабинете NicePay)
        // Для продакшена замените на реальные значения
        if (!defined('NICEPAY_MERCHANT_ID')) {
            define('NICEPAY_MERCHANT_ID', getenv('NICEPAY_MERCHANT_ID') ?: 'your_merchant_id_here');
        }
        if (!defined('NICEPAY_SECRET_KEY')) {
            define('NICEPAY_SECRET_KEY', getenv('NICEPAY_SECRET_KEY') ?: 'your_secret_key_here');
        }
        
        try {
            $nicePay = new NicePay(null, null, false); // false = боевой режим, true = тестовый
            $paymentUrl = $nicePay->initiatePayment($orderId, $pdo);
            
            // Перенаправляем пользователя на страницу оплаты NicePay
            header('Location: ' . $paymentUrl);
            exit;
        } catch (Exception $e) {
            // Если ошибка с платежкой, показываем сообщение но заказ сохраняем
            $error = 'Заказ создан, но произошла ошибка при подключении к платежной системе: ' . $e->getMessage();
            // Можно отправить email администратору о проблеме
            error_log('NicePay Error: ' . $e->getMessage());
        }
        
        // Отправка email подтверждения
        $subject = 'Подтверждение заказа #' . $orderNumber;
        $message = "
            <h2>Спасибо за заказ!</h2>
            <p>Ваш заказ #{$orderNumber} успешно оформлен.</p>
            <h3>Детали заказа:</h3>
            <ul>
        ";
        
        foreach ($cartItems as $item) {
            $message .= "<li>{$item['title']} - {$item['quantity']} шт. x " . formatPrice($item['price']) . "</li>";
        }
        
        $message .= "
            </ul>
            <p><strong>Общая сумма: " . formatPrice($total) . "</strong></p>
            <p>Статус заказа: Ожидает оплаты</p>
            <p>С уважением, команда " . SITE_NAME . "</p>
        ";
        
        sendEmail($user['email'], $subject, $message);
        
        $success = 'Заказ успешно оформлен! Подтверждение отправлено на ваш email.';
        
        // Редирект на страницу успеха
        header('Location: checkout.php?success=1');
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка оформления заказа';
    } catch (Exception $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}

if (isset($_GET['success'])) {
    $success = 'Заказ успешно оформлен! Подтверждение отправлено на ваш email.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Редизайн страницы оплаты */
        .checkout-page {
            padding: 40px 0;
            min-height: 70vh;
        }
        
        .checkout-page h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
            color: var(--text-primary);
        }
        
        .checkout-layout {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 30px;
        }
        
        @media (max-width: 900px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
        }
        
        .checkout-form-wrapper {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .checkout-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .checkout-section:last-of-type {
            border-bottom: none;
        }
        
        .checkout-section h2 {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: var(--text-primary);
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .form-group input[readonly] {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            padding: 14px 16px;
            border-radius: 10px;
            width: 100%;
            font-size: 1rem;
            color: var(--text-primary);
        }
        
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .radio-label {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .radio-label:hover {
            border-color: var(--primary-color);
            background: var(--bg-hover);
        }
        
        .radio-label input[type="radio"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
        }
        
        .radio-label input[type="radio"]:checked + span {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .radio-label:has(input:checked) {
            border-color: var(--primary-color);
            background: rgba(var(--primary-rgb), 0.05);
        }
        
        textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s ease;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .checkout-summary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 16px;
            padding: 30px;
            color: white;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .checkout-summary h2 {
            font-size: 1.6rem;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .order-items {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .order-item-summary {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-bottom: 12px;
        }
        
        .order-item-summary img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item-info h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }
        
        .order-item-info p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .order-total {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .total-price {
            font-size: 1.6rem;
        }
        
        .btn-block.btn-lg {
            padding: 18px 30px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 12px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-block.btn-lg:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .alert {
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 1rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .payment-card {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .payment-card:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }
        
        .payment-card.selected {
            border-color: white;
            background: rgba(255,255,255,0.2);
        }
        
        .payment-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="checkout-page">
        <div class="container">
            <h1>Оформление заказа</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= escape($success) ?></div>
                <a href="profile.php" class="btn btn-primary">Перейти в личный кабинет</a>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= escape($error) ?></div>
                <?php endif; ?>
                
                <div class="checkout-layout">
                    <div class="checkout-form-wrapper">
                        <form method="POST" action="" class="checkout-form">
                            <section class="checkout-section">
                                <h2>Данные покупателя</h2>
                                <div class="form-group">
                                    <label>ФИО</label>
                                    <input type="text" value="<?= escape($user['full_name']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?= escape($user['email']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Никнейм</label>
                                    <input type="text" value="<?= escape($user['nickname']) ?>" readonly>
                                </div>
                            </section>
                            
                            <section class="checkout-section">
                                <h2>Способ получения</h2>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="delivery_method" value="email" checked>
                                        <span>Email (цифровой ключ)</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="delivery_method" value="account">
                                        <span>Личный кабинет</span>
                                    </label>
                                </div>
                            </section>
                            
                            <section class="checkout-section">
                                <h2>Способ оплаты</h2>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="card" checked>
                                        <span>Банковская карта</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="qiwi">
                                        <span>Qiwi</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="yandex">
                                        <span>Яндекс.Деньги</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="crypto">
                                        <span>Криптовалюта</span>
                                    </label>
                                </div>
                            </section>
                            
                            <section class="checkout-section">
                                <h2>Комментарий к заказу</h2>
                                <textarea name="notes" rows="3" placeholder="Дополнительная информация..."></textarea>
                            </section>
                            
                            <button type="submit" class="btn btn-primary btn-block btn-lg">Подтвердить заказ</button>
                        </form>
                    </div>
                    
                    <div class="checkout-summary">
                        <h2>Ваш заказ</h2>
                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item-summary">
                                    <img src="images/uploads/<?= escape($item['image']) ?>" alt="<?= escape($item['title']) ?>" onerror="this.src='https://via.placeholder.com/60x60?text=<?= urlencode($item['title']) ?>'">
                                    <div class="order-item-info">
                                        <h4><?= escape($item['title']) ?></h4>
                                        <p><?= $item['quantity'] ?> шт. x <?= formatPrice($item['price']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-total">
                            <span>Итого:</span>
                            <span class="total-price"><?= formatPrice($total) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
