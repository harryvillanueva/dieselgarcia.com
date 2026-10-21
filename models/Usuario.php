<?php
require_once __DIR__ . '/Database.php';

class Usuario {
    public static function autenticar($email, $password) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // ACTUALIZADO: Ahora acepta teléfono y dirección
    public static function registrar($nombre, $email, $password, $telefono = null, $direccion = null) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "El correo electrónico ya está registrado.";
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES (?, ?, ?, 'cliente', ?, ?)");
        if ($stmt->execute([$nombre, $email, $hash, $telefono, $direccion])) {
            return true;
        }
        return "Error al registrar el usuario.";
    }

    public static function obtenerPorId($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, nombre, email, telefono, direccion, rol FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function actualizarPerfil($id, $nombre, $telefono, $direccion) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?");
        return $stmt->execute([$nombre, $telefono, $direccion, $id]);
    }

    public static function actualizarPassword($id, $passwordActual, $passwordNueva) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && password_verify($passwordActual, $user['password'])) {
            $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            return $update->execute([$nuevoHash, $id]);
        }
        return false;
    }

    public static function generarTokenRecuperacion($email) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return "No existe ningún usuario registrado con ese correo.";
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); 

        $update = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_expires = ? WHERE email = ?");
        if ($update->execute([$token, $expires, $email])) {
            return $token; 
        }
        return "Error al generar el token de recuperación.";
    }

    public static function verificarToken($token) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function restablecerConToken($token, $passwordNueva) {
        $user = self::verificarToken($token);
        if (!$user) {
            return false;
        }

        $pdo = Database::connect();
        $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        return $stmt->execute([$hash, $user['id']]);
    }

    public static function getClientes() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT id, nombre, email, telefono, direccion, created_at FROM usuarios WHERE rol = 'cliente' ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Función para que el admin actualice clientes sin necesitar la contraseña actual
    public static function adminActualizarUsuario($id, $nombre, $telefono, $direccion, $nueva_password = null) {
        $pdo = Database::connect();
        if (!empty($nueva_password)) {
            $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ?, password = ? WHERE id = ?");
            return $stmt->execute([$nombre, $telefono, $direccion, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?");
            return $stmt->execute([$nombre, $telefono, $direccion, $id]);
        }
    }

    // NUEVO: Función para eliminar usuario
    public static function eliminar($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}