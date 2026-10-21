<?php
require_once __DIR__ . '/Database.php';

class Pedido {
    public static function crear($usuario_id, $total, $metodo_pago, $direccion_envio, $lineas) {
        $pdo = Database::connect();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, metodo_pago, direccion_envio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $total, $metodo_pago, $direccion_envio]);
            $pedido_id = $pdo->lastInsertId();

            $stmtDetalle = $pdo->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            foreach ($lineas as $linea) {
                $stmtDetalle->execute([
                    $pedido_id,
                    $linea['product']['id'],
                    $linea['qty'],
                    $linea['precioReal']
                ]);
            }

            $pdo->commit();
            return $pedido_id; 
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public static function getAll() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getDetalles($pedido_id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT d.*, p.name as producto_nombre, p.images FROM detalles_pedido d JOIN productos p ON d.producto_id = p.id WHERE d.pedido_id = ?");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function actualizarEstado($id, $estado) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public static function eliminar($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getByUsuarioId($usuario_id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}