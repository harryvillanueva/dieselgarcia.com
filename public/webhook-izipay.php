<?php
// Cargar el entorno y los modelos necesarios
require_once __DIR__ . '/models/Izipay.php';
require_once __DIR__ . '/models/Pedido.php';

// Izipay envía los datos por POST (application/x-www-form-urlencoded)
$postData = $_POST;

// 1. Validar que la petición viene de Izipay (usando tu clave HMAC)
if (Izipay::validarHashIPN($postData)) {
    
    $answer = json_decode($postData['kr-answer'], true);
    
    // 2. Comprobar si el pago fue aprobado
    if ($answer['orderStatus'] === 'PAID') {
        
        // Izipay nos devuelve el ID así: "DG-00014". Lo limpiamos para obtener el ID real (14)
        $orderIdRaw = $answer['orderDetails']['orderId'];
        $pedido_id = (int) str_replace('DG-', '', $orderIdRaw);
        
        // 3. Actualizar el estado en la base de datos
        Pedido::actualizarEstado($pedido_id, 'completado'); 
        
        // 4. Responder con código 200 a Izipay para que deje de enviar alertas
        http_response_code(200);
        echo "OK! Pedido actualizado.";
        exit;
    }
}

// Si la firma no coincide o es un intento de hackeo, bloqueamos
http_response_code(400);
echo "Invalid IPN Signature";