<?php
require_once __DIR__ . '/Database.php';

class Producto {
    private static function parseImages($imgData) {
        if (empty($imgData)) return ['default.jpg'];
        $decoded = json_decode($imgData, true);
        return is_array($decoded) ? $decoded : [$imgData];
    }

    public static function getAll() {
        try {
            $pdo = Database::connect();
            $stmt = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
            $products = $stmt->fetchAll();
            foreach ($products as &$p) {
                $p['images'] = self::parseImages($p['images']);
            }
            return $products;
        } catch (\PDOException $e) {
            return [];
        }
    }

    public static function getFeatured() {
        try {
            $pdo = Database::connect();
            $stmt = $pdo->query("SELECT * FROM productos WHERE featured = 1");
            $products = $stmt->fetchAll();
            foreach ($products as &$p) {
                $p['images'] = self::parseImages($p['images']);
            }
            return $products;
        } catch (\PDOException $e) {
            return [];
        }
    }

    public static function getBySlug($id) {
        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            if ($product) {
                $product['images'] = self::parseImages($product['images']);
                return $product;
            }
            return null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public static function getById($id) {
        return self::getBySlug($id);
    }

    public static function formatPrice($precio) {
        return 'S/ ' . number_format((float)$precio, 2, '.', ',');
    }

    public static function buscarFiltrados($busqueda = '', $categoria = '') {
        $pdo = Database::connect();
        $sql = "SELECT * FROM productos WHERE 1=1";
        $params = [];
        if (!empty($busqueda)) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $busqueda . '%';
        }
        if (!empty($categoria)) {
            $sql .= " AND category = ?";
            $params[] = $categoria;
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productos as &$p) {
            $p['images'] = self::parseImages($p['images']);
        }
        return $productos;
    }

    // ACTUALIZADO: Añadido $stock
    public static function crear($nombre, $marca, $descripcion, $precio, $sale_price, $categoria, $imagenes, $destacado, $origen, $certificacion, $modelo, $stock) {
        $pdo = Database::connect();
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
        if (empty($slug)) { $slug = 'producto'; }
        $id = $slug . '-' . substr(uniqid(), -4); 
        
        $stmt = $pdo->prepare("INSERT INTO productos (id, name, brand, description, price, sale_price, category, images, featured, origen, certificacion, modelo, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $nombre, $marca, $descripcion, $precio, $sale_price, $categoria, json_encode($imagenes), $destacado, $origen, $certificacion, $modelo, $stock]);
    }

    // ACTUALIZADO: Añadido $stock
    public static function actualizar($id, $nombre, $marca, $descripcion, $precio, $sale_price, $categoria, $imagenes, $destacado, $origen, $certificacion, $modelo, $stock) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE productos SET name = ?, brand = ?, description = ?, price = ?, sale_price = ?, category = ?, images = ?, featured = ?, origen = ?, certificacion = ?, modelo = ?, stock = ? WHERE id = ?");
        return $stmt->execute([$nombre, $marca, $descripcion, $precio, $sale_price, $categoria, json_encode($imagenes), $destacado, $origen, $certificacion, $modelo, $stock, $id]);
    }

    public static function eliminar($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}