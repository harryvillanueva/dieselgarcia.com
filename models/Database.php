<?php

class Database {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            // Valores por defecto locales
            $host = 'localhost';
            $db   = 'dieselgarcia_db';
            $user = 'root';
            $pass = 'admin';

            // Leer archivo .env si existe en la raíz del proyecto
            $envPath = __DIR__ . '/../.env';
            if (file_exists($envPath)) {
                $env = parse_ini_file($envPath);
                $host = $env['DB_HOST'] ?? $host;
                $db   = $env['DB_NAME'] ?? $db;
                $user = $env['DB_USER'] ?? $user;
                $pass = $env['DB_PASS'] ?? $pass;
            }

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (\PDOException $e) {
                throw new \PDOException("Error de conexión: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}