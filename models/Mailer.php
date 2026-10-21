<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Mailer {
    
    // Método privado para generar una instancia de correo segura mediante SMTP
    private static function getMailer() {
        $envPath = __DIR__ . '/../.env';
        $env = file_exists($envPath) ? parse_ini_file($envPath) : [];
        
        $mail = new PHPMailer(true);
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail->isSMTP();
            $mail->Host       = $env['SMTP_HOST'] ?? 'localhost';
            $mail->SMTPAuth   = true;
            $mail->Username   = $env['SMTP_USER'] ?? '';
            $mail->Password   = $env['SMTP_PASS'] ?? '';
            // Si tu servidor requiere SSL (puerto 465) en lugar de TLS (587), cambiar a ENCRYPTION_SMTPS
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port       = $env['SMTP_PORT'] ?? 587;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($env['SMTP_USER'] ?? 'no-reply@dieselgarcia.com', 'Diesel García');
        }
        return $mail;
    }

    public static function enviarCorreoRecuperacion($destinatario, $enlace) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;
        
        $mail = self::getMailer();
        try {
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = 'Restablece tu contraseña - Diesel García';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #e0c112;'>Diesel García - Recuperación de Contraseña</h2>
                    <p>Has solicitado restablecer el acceso a tu cuenta.</p>
                    <p>Haz clic en el siguiente botón para cambiar tu contraseña:</p>
                    <a href='{$enlace}' style='display: inline-block; padding: 12px 24px; background-color: #e0c112; color: #000; text-decoration: none; font-weight: bold; border-radius: 8px; margin-top: 10px;'>Restablecer Contraseña</a>
                </div>
            ";
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function enviarAvisoAdminNuevoPedido($pedido_id, $total, $metodo_pago, $lineas, $cliente) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;

        $destinatarios = ['Cgarciasac@gmail.com', 'harryvillanueva0814@gmail.com'];
        $asunto = "NUEVO PEDIDO #DG-" . str_pad($pedido_id, 5, "0", STR_PAD_LEFT);
        
        $tablaProductos = '';
        foreach ($lineas as $item) {
            $nombre = htmlspecialchars($item['product']['name']);
            $cantidad = $item['qty'];
            $precio = number_format($item['precioReal'] * $cantidad, 2);
            $tablaProductos .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #e2e8f0;'>{$nombre}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: center;'>{$cantidad}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: right;'>S/ {$precio}</td>
                </tr>
            ";
        }

        $nombreCliente = htmlspecialchars(trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellidos'] ?? '')));
        $emailCliente = htmlspecialchars($cliente['email'] ?? 'No proporcionado');
        $telefonoCliente = htmlspecialchars($cliente['telefono'] ?? 'No proporcionado');
        $metodoDisplay = strtoupper(str_replace('_', ' ', $metodo_pago));

        $mensaje = "
        <html>
        <body style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; margin: 0;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #0a1428; padding: 25px; text-align: center;'>
                    <h1 style='margin: 0; color: #ffffff; font-size: 24px; letter-spacing: 1px;'>
                        <span style='background-color: #e0c112; color: #0a1428; padding: 4px 8px; border-radius: 4px; margin-right: 8px;'>DG</span> 
                        DIESEL GARCÍA
                    </h1>
                </div>
                <div style='padding: 30px;'>
                    <h2 style='color: #0a1428; margin-top: 0; border-bottom: 2px solid #e0c112; padding-bottom: 10px;'>¡Nueva Venta Registrada!</h2>
                    <div style='display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px;'>
                        <div><strong>Pedido:</strong> DG-" . str_pad($pedido_id, 5, "0", STR_PAD_LEFT) . "</div>
                        <div><strong>Método:</strong> {$metodoDisplay}</div>
                    </div>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px;'>
                        <thead>
                            <tr style='background-color: #f1f5f9; text-align: left;'>
                                <th style='padding: 10px; color: #475569;'>Producto</th>
                                <th style='padding: 10px; text-align: center; color: #475569;'>Cant.</th>
                                <th style='padding: 10px; text-align: right; color: #475569;'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$tablaProductos}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='2' style='padding: 15px 10px; text-align: right; font-weight: bold;'>TOTAL A COBRAR:</td>
                                <td style='padding: 15px 10px; text-align: right; font-weight: bold; color: #e0c112; font-size: 18px;'>S/ " . number_format($total, 2) . "</td>
                            </tr>
                        </tfoot>
                    </table>
                    <h3 style='color: #0a1428; font-size: 16px; margin-bottom: 10px;'>Datos del Cliente</h3>
                    <div style='background-color: #f8fafc; padding: 15px; border-radius: 6px; font-size: 14px; color: #475569; line-height: 1.6;'>
                        <strong>Nombre:</strong> {$nombreCliente}<br>
                        <strong>Email:</strong> {$emailCliente}<br>
                        <strong>Teléfono:</strong> {$telefonoCliente}
                    </div>
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='https://www.dieselgarcia.com/dashboard/pedidos/detalle?id={$pedido_id}' style='display:inline-block; padding:12px 24px; background-color:#0a1428; color:#ffffff; text-decoration:none; font-weight:bold; border-radius:6px; text-transform: uppercase; font-size: 14px;'>Gestionar Pedido</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail = self::getMailer();
        try {
            foreach ($destinatarios as $correo) {
                $mail->addAddress($correo);
            }
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->send();
            return true;
        } catch (Exception $e) { 
            return false; 
        }
    }
}