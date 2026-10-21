<?php
class Izipay {
    // Lee las credenciales directamente del archivo .env
    private static function getCredential($key) {
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
            return $env[$key] ?? '';
        }
        return '';
    }

    private static function getAuthHeader() {
        $shopId = self::getCredential('IZIPAY_SHOP_ID');
        $password = self::getCredential('IZIPAY_PASSWORD');
        return 'Basic ' . base64_encode($shopId . ':' . $password);
    }

    public static function generarTokenPago($pedido_id, $monto, $email_cliente) {
        $url = "https://api.micuentaweb.pe/api-payment/V4/Charge/CreatePayment";
        $montoCentimos = round($monto * 100);

        $body = [
            "amount" => $montoCentimos,
            "currency" => "PEN",
            "orderId" => "DG-" . str_pad($pedido_id, 5, "0", STR_PAD_LEFT),
            "customer" => [
                "email" => $email_cliente
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . self::getAuthHeader()
        ]);
        
        // IMPORTANTE: Evita el bloqueo de SSL en localhost (XAMPP)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data['status'] === 'SUCCESS') {
                return $data['answer']['formToken'];
            }
        }
        
        return false;
    }

    public static function validarHashIPN($postData) {
        $clave_hmac = self::getCredential('IZIPAY_HMAC_KEY');
        if (empty($postData['kr-hash']) || empty($postData['kr-answer'])) {
            return false;
        }

        $hashCalculado = hash_hmac('sha256', $postData['kr-answer'], $clave_hmac);
        return hash_equals($postData['kr-hash'], $hashCalculado);
    }
}