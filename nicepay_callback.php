<?php
/**
 * Обработчик платежей NicePay для JustKey.ru
 * Этот файл принимает callback от платежной системы NicePay
 */

require_once 'config.php';

// Логирование всех входящих запросов (для отладки)
$logFile = __DIR__ . '/logs/nicepay_callback.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

logMessage("=== Входящий запрос ===", $logFile);
logMessage("POST данные: " . json_encode($_POST), $logFile);
logMessage("GET данные: " . json_encode($_GET), $logFile);

// Получаем данные от NicePay
$transactionId = $_POST['transactionId'] ?? '';
$orderNumber = $_POST['orderNumber'] ?? '';
$amount = $_POST['amount'] ?? 0;
$status = $_POST['status'] ?? '';
$paymentMethod = $_POST['paymentMethod'] ?? '';
$signature = $_POST['signature'] ?? '';
$timestamp = $_POST['timestamp'] ?? '';

// Секретный ключ для проверки подписи (получите его в личном кабинете NicePay)
$secretKey = defined('NICEPAY_SECRET_KEY') ? NICEPAY_SECRET_KEY : (getenv('NICEPAY_SECRET_KEY') ?: 'your_secret_key_here');

// Проверка подписи
function verifySignature($data, $signature, $secretKey) {
    // Сортируем параметры по алфавиту
    ksort($data);
    
    // Формируем строку для подписи
    $signString = '';
    foreach ($data as $key => $value) {
        if ($key !== 'signature') {
            $signString .= $key . '=' . $value . '&';
        }
    }
    $signString = rtrim($signString, '&');
    
    // Вычисляем подпись
    $calculatedSignature = hash_hmac('sha256', $signString, $secretKey);
    
    return hash_equals($calculatedSignature, $signature);
}

// Проверяем подпись (если она есть)
$isSignatureValid = true;
if ($signature && !empty($secretKey) && $secretKey !== 'your_secret_key_here') {
    $signData = $_POST;
    unset($signData['signature']);
    $isSignatureValid = verifySignature($signData, $signature, $secretKey);
    
    if (!$isSignatureValid) {
        logMessage("ОШИБКА: Неверная подпись!", $logFile);
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        exit;
    }
} else {
    logMessage("ПРЕДУПРЕЖДЕНИЕ: Подпись не проверяется (секретный ключ не настроен)", $logFile);
}

// Проверяем обязательные поля
if (empty($transactionId) || empty($orderNumber) || empty($status)) {
    logMessage("ОШИБКА: Отсутствуют обязательные поля", $logFile);
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

logMessage("Обработка заказа: $orderNumber, статус: $status, сумма: $amount", $logFile);

try {
    // Находим заказ в базе данных
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    
    if (!$order) {
        logMessage("ОШИБКА: Заказ $orderNumber не найден", $logFile);
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }
    
    // Проверяем сумму платежа
    if (abs(floatval($amount) - floatval($order['total_amount'])) > 0.01) {
        logMessage("ОШИБКА: Сумма платежа не совпадает. Ожидалось: {$order['total_amount']}, получено: $amount", $logFile);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Amount mismatch']);
        exit;
    }
    
    // Обработка в зависимости от статуса
    switch ($status) {
        case 'SUCCESS':
        case 'COMPLETED':
        case 'PAID':
            // Успешная оплата
            if ($order['status'] !== 'paid' && $order['status'] !== 'completed') {
                // Обновляем статус заказа
                $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$order['id']]);
                
                logMessage("УСПЕХ: Заказ $orderNumber оплачен. Сумма: $amount", $logFile);
                
                // Отправляем email покупателю
                $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $userStmt->execute([$order['user_id']]);
                $user = $userStmt->fetch();
                
                if ($user) {
                    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $itemsStmt->execute([$order['id']]);
                    $items = $itemsStmt->fetchAll();
                    
                    $subject = 'Оплата заказа #' . $orderNumber . ' подтверждена';
                    $message = "
                        <h2>Оплата подтверждена!</h2>
                        <p>Ваш заказ #{$orderNumber} успешно оплачен.</p>
                        <h3>Детали заказа:</h3>
                        <ul>
                    ";
                    
                    foreach ($items as $item) {
                        $message .= "<li>{$item['product_title']} - {$item['quantity']} шт. x " . formatPrice($item['price']) . "</li>";
                        if (!empty($item['game_key'])) {
                            $message .= "<p><strong>Ваш ключ активации:</strong> <code style='background: #f0f0f0; padding: 5px; display: inline-block;'>{$item['game_key']}</code></p>";
                        }
                    }
                    
                    $message .= "
                        </ul>
                        <p><strong>Общая сумма: " . formatPrice($order['total_amount']) . "</strong></p>
                        <p>Статус заказа: Оплачен</p>
                        <p>Спасибо за покупку в " . SITE_NAME . "!</p>
                        <p>С уважением, команда " . SITE_NAME . "</p>
                    ";
                    
                    sendEmail($user['email'], $subject, $message);
                    logMessage("Email отправлен пользователю: {$user['email']}", $logFile);
                }
                
                // Здесь можно добавить автоматическую выдачу товара
                // Например, обновить статус ключей или отправить их пользователю
                
            } else {
                logMessage("ИНФО: Заказ $orderNumber уже был оплачен ранее", $logFile);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully']);
            break;
            
        case 'FAILED':
        case 'CANCELLED':
        case 'DECLINED':
            // Неудачная оплата
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$order['id']]);
            
            logMessage("НЕУДАЧА: Оплата заказа $orderNumber не прошла. Статус: $status", $logFile);
            
            // Можно отправить уведомление пользователю о неудачной оплате
            echo json_encode(['status' => 'success', 'message' => 'Payment failure recorded']);
            break;
            
        case 'PENDING':
        case 'PROCESSING':
            // Ожидает подтверждения
            $stmt = $pdo->prepare("UPDATE orders SET status = 'processing', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$order['id']]);
            
            logMessage("ОЖИДАНИЕ: Заказ $orderNumber в обработке", $logFile);
            echo json_encode(['status' => 'success', 'message' => 'Payment is processing']);
            break;
            
        default:
            logMessage("НЕИЗВЕСТНЫЙ СТАТУС: $status для заказа $orderNumber", $logFile);
            echo json_encode(['status' => 'error', 'message' => 'Unknown status']);
            break;
    }
    
} catch (PDOException $e) {
    logMessage("ОШИБКА БАЗЫ ДАННЫХ: " . $e->getMessage(), $logFile);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

logMessage("=== Конец обработки ===\n", $logFile);
