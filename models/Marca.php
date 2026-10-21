<?php
require_once __DIR__ . '/Database.php';

class Marca {
    public static function getAll() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM marcas ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM marcas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function crear($name) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO marcas (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public static function actualizar($id, $name) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE marcas SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public static function eliminar($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM marcas WHERE id = ?");
        return $stmt->execute([$id]);
    }
}