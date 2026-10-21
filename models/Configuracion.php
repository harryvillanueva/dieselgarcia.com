<?php
require_once __DIR__ . '/Database.php';

class Configuracion {
    public static function get($clave) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $stmt->execute([$clave]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['valor'] : null;
    }

    public static function actualizar($clave, $valor) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
        return $stmt->execute([$valor, $clave]);
    }
}