<?php
/**
 * Класс для работы с платежной системой NicePay
 * Интеграция с JustKey.ru
 */

class NicePay {
    
    private $merchantId;
    private $secretKey;
    private $apiUrl;
    private $returnUrl;
    private $callbackUrl;
    
    /**
     * Конструктор
     * 
     * @param string $merchantId ID мерчанта в NicePay
     * @param string $secretKey Секретный ключ API
     * @param bool $testMode Режим тестирования
     */
    public function __construct($merchantId = null, $secretKey = null, $testMode = false) {
        // Получаем из параметров, или из констант, или из переменных окружения
        $this->merchantId = $merchantId ?: 
            (defined('NICEPAY_MERCHANT_ID') ? NICEPAY_MERCHANT_ID : getenv('NICEPAY_MERCHANT_ID') ?: '');
        $this->secretKey = $secretKey ?: 
            (defined('NICEPAY_SECRET_KEY') ? NICEPAY_SECRET_KEY : getenv('NICEPAY_SECRET_KEY') ?: '');
        
        // URLs в зависимости от режима
        if ($testMode) {
            $this->apiUrl = 'https://test-api.nicepay.ru/v1';
        } else {
            $this->apiUrl = 'https://api.nicepay.ru/v1';
        }
        
        $this->returnUrl = SITE_URL . '/payment_return.php';
        $this->callbackUrl = SITE_URL . '/nicepay_callback.php';
    }
    
    /**
     * Создание платежа
     * 
     * @param array $orderData Данные заказа
     * @return array Результат создания платежа
     */
    public function createPayment($orderData) {
        $paymentData = [
            'merchantId' => $this->merchantId,
            'orderNumber' => $orderData['orderNumber'],
            'amount' => number_format($orderData['amount'], 2, '.', ''),
            'currency' => $orderData['currency'] ?? 'RUB',
            'description' => $orderData['description'] ?? 'Оплата заказа на ' . SITE_NAME,
            'customerEmail' => $orderData['customerEmail'] ?? '',
            'customerPhone' => $orderData['customerPhone'] ?? '',
            'returnUrl' => $this->returnUrl,
            'callbackUrl' => $this->callbackUrl,
            'timestamp' => time(),
        ];
        
        // Генерация подписи
        $paymentData['signature'] = $this->generateSignature($paymentData);
        
        // Отправка запроса к API NicePay
        $result = $this->sendRequest('/payment/create', $paymentData);
        
        return $result;
    }
    
    /**
     * Получение URL для перенаправления на оплату
     * 
     * @param array $orderData Данные заказа
     * @return string URL для оплаты
     */
    public function getPaymentUrl($orderData) {
        $result = $this->createPayment($orderData);
        
        if (isset($result['status']) && $result['status'] === 'success' && isset($result['paymentUrl'])) {
            return $result['paymentUrl'];
        }
        
        throw new Exception('Не удалось создать платеж: ' . ($result['message'] ?? 'Неизвестная ошибка'));
    }
    
    /**
     * Проверка статуса платежа
     * 
     * @param string $transactionId ID транзакции
     * @return array Статус платежа
     */
    public function checkPaymentStatus($transactionId) {
        $data = [
            'merchantId' => $this->merchantId,
            'transactionId' => $transactionId,
            'timestamp' => time(),
        ];
        
        $data['signature'] = $this->generateSignature($data);
        
        return $this->sendRequest('/payment/status', $data);
    }
    
    /**
     * Возврат средств
     * 
     * @param string $transactionId ID транзакции
     * @param float $amount Сумма возврата
     * @param string $reason Причина возврата
     * @return array Результат возврата
     */
    public function refund($transactionId, $amount, $reason = '') {
        $data = [
            'merchantId' => $this->merchantId,
            'transactionId' => $transactionId,
            'amount' => number_format($amount, 2, '.', ''),
            'reason' => $reason,
            'timestamp' => time(),
        ];
        
        $data['signature'] = $this->generateSignature($data);
        
        return $this->sendRequest('/payment/refund', $data);
    }
    
    /**
     * Генерация подписи для запроса
     * 
     * @param array $data Данные для подписи
     * @return string Подпись
     */
    private function generateSignature($data) {
        // Сортируем параметры по алфавиту
        ksort($data);
        
        // Формируем строку для подписи
        $signString = '';
        foreach ($data as $key => $value) {
            $signString .= $key . '=' . $value . '&';
        }
        $signString = rtrim($signString, '&');
        
        // Вычисляем HMAC-SHA256 подпись
        return hash_hmac('sha256', $signString, $this->secretKey);
    }
    
    /**
     * Отправка HTTP запроса к API
     * 
     * @param string $endpoint Эндпоинт API
     * @param array $data Данные запроса
     * @return array Ответ API
     */
    private function sendRequest($endpoint, $data) {
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'status' => 'error',
                'message' => 'Ошибка соединения: ' . $error
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            return [
                'status' => 'error',
                'message' => $result['message'] ?? 'Ошибка API'
            ];
        }
        
        return $result;
    }
    
    /**
     * Инициализация оплаты для заказа
     * 
     * @param int $orderId ID заказа в базе данных
     * @param PDO $pdo PDO соединение с базой данных
     * @return string URL для перенаправления на оплату
     */
    public function initiatePayment($orderId, $pdo) {
        // Получаем данные заказа
        $stmt = $pdo->prepare("
            SELECT o.*, u.email, u.full_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception('Заказ не найден');
        }
        
        if ($order['status'] !== 'pending') {
            throw new Exception('Заказ уже обработан (статус: ' . $order['status'] . ')');
        }
        
        // Формируем данные для платежа
        $paymentData = [
            'orderNumber' => $order['order_number'],
            'amount' => floatval($order['total_amount']),
            'currency' => 'RUB',
            'description' => 'Оплата заказа #' . $order['order_number'] . ' на ' . SITE_NAME,
            'customerEmail' => $order['email'],
            'customerPhone' => $order['customer_phone'] ?? '',
        ];
        
        // Создаем платеж и получаем URL
        return $this->getPaymentUrl($paymentData);
    }
}
