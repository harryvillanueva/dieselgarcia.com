<?php
require_once __DIR__ . '/Database.php';

class Categoria {
    public static function getAll() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM categorias ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function crear($slug, $name, $icon) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO categorias (slug, name, icon) VALUES (?, ?, ?)");
        return $stmt->execute([$slug, $name, $icon]);
    }

    public static function actualizar($id, $slug, $name, $icon) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE categorias SET slug = ?, name = ?, icon = ? WHERE id = ?");
        return $stmt->execute([$slug, $name, $icon, $id]);
    }

    public static function eliminar($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        return $stmt->execute([$id]);
    }
}