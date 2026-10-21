<?php
session_start();

$_ENV['BASE_URL'] = 'http://localhost/dieselgarcia/';

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Usuario.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['product_id'])) {
    $id = $_POST['product_id'];
    $action = $_POST['action'];

    if ($action === 'update' && isset($_POST['qty'])) {
        $newQty = max(1, (int)$_POST['qty']);
        $_SESSION['cart'][$id] = $newQty;
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
    }
    
    if (isset($_POST['ajax'])) {
        ob_clean();
        $cartCount = array_sum($_SESSION['cart']);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
        exit;
    }

    header("Location: " . $_ENV['BASE_URL'] . "carrito");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_action'])) {
    $action = $_POST['auth_action'];
    
    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';
        $user = Usuario::autenticar($email, $pass);
        
        if ($user) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'rol' => $user['rol']
            ];

            $redirect = $_SESSION['redirect_after_login'] ?? 'mi-cuenta';
            unset($_SESSION['redirect_after_login']);

            header("Location: " . $_ENV['BASE_URL'] . $redirect);
            exit;
        } else {
            $_SESSION['auth_error'] = "Credenciales incorrectas.";
            header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
            exit;
        }
    } elseif ($action === 'register') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        
        $result = Usuario::registrar($nombre, $email, $pass, $telefono, $direccion);
        if ($result === true) {
            $user = Usuario::autenticar($email, $pass);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'rol' => $user['rol']
            ];

            $redirect = $_SESSION['redirect_after_login'] ?? 'mi-cuenta';
            unset($_SESSION['redirect_after_login']);

            header("Location: " . $_ENV['BASE_URL'] . $redirect);
            exit;
        } else {
            $_SESSION['auth_error'] = $result;
            header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
            exit;
        }
    } elseif ($action === 'update_profile') {
        if (!isset($_SESSION['user'])) {
            header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
            exit;
        }
        $id = $_SESSION['user']['id'];
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';

        if (Usuario::actualizarPerfil($id, $nombre, $telefono, $direccion)) {
            $_SESSION['user']['nombre'] = $nombre; 
            $_SESSION['auth_success'] = "Datos de perfil actualizados correctamente.";
        } else {
            $_SESSION['auth_error'] = "No se pudieron actualizar los datos.";
        }
        header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
        exit;
    }
    elseif ($action === 'update_password') {
        if (!isset($_SESSION['user'])) {
            header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
            exit;
        }
        $id = $_SESSION['user']['id'];
        $actual = $_POST['password_actual'] ?? '';
        $nueva = $_POST['password_nueva'] ?? '';

        if (Usuario::actualizarPassword($id, $actual, $nueva)) {
            $_SESSION['auth_success'] = "Contraseña modificada con éxito.";
        } else {
            $_SESSION['auth_error'] = "La contraseña actual no es correcta.";
        }
        header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
        exit;
    }
    elseif ($action === 'recover') {
        $email = $_POST['email'] ?? '';
        $resultado = Usuario::generarTokenRecuperacion($email);
        
        $env = parse_ini_file(__DIR__ . '/../.env');
        $isLocal = ($env['APP_ENV'] ?? 'local') === 'local';

        if (strlen($resultado) > 50) { 
            $enlace = $_ENV['BASE_URL'] . "mi-cuenta?token=" . $resultado;
            
            if ($isLocal) {
                $_SESSION['auth_success'] = "Enlace de recuperación generado correctamente (Entorno local):<br><br><a href='$enlace' class='underline font-bold text-primary'>Haz clic aquí para restablecer tu contraseña</a>";
            } else {
                require_once __DIR__ . '/../models/Mailer.php';
                $enviado = Mailer::enviarCorreoRecuperacion($email, $enlace);
                
                if ($enviado) {
                    $_SESSION['auth_success'] = "Hemos enviado las instrucciones de recuperación a tu bandeja de entrada.";
                } else {
                    $_SESSION['auth_error'] = "No se pudo enviar el correo electrónico. Inténtalo más tarde.";
                }
            }
        } else {
            $_SESSION['auth_error'] = $resultado;
        }
        header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta?recuperar=1");
        exit;
    }
    elseif ($action === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';

        if (Usuario::restablecerConToken($token, $passwordNueva)) {
            $_SESSION['auth_success'] = "Contraseña restablecida con éxito. Ya puedes iniciar sesión.";
            header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
            exit;
        } else {
            $_SESSION['auth_error'] = "El enlace de recuperación es inválido o ha caducado.";
            header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
            exit;
        }
    }   elseif ($action === 'logout') {
        unset($_SESSION['user']);
        header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
        header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
        exit;
    }

    $action = $_POST['admin_action'];

    if ($action === 'create_product' || $action === 'edit_product') {
        $nombre = $_POST['name'] ?? '';
        $descripcion = $_POST['description'] ?? '';
        $precio = floatval($_POST['price'] ?? 0);
        $en_oferta = isset($_POST['is_sale']) ? true : false;
        $sale_price = ($en_oferta && !empty($_POST['sale_price'])) ? floatval($_POST['sale_price']) : null;
        $categoria = $_POST['category'] ?? '';
        $marca = $_POST['brand'] ?? ''; 
        $destacado = isset($_POST['featured']) ? 1 : 0;
        
        $origen = !empty($_POST['origen']) ? $_POST['origen'] : null;
        $certificacion = !empty($_POST['certificacion']) ? $_POST['certificacion'] : null;
        $modelo = !empty($_POST['modelo']) ? $_POST['modelo'] : null;
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0; // NUEVO: Capturar stock

        $imagenes = []; 
        if ($action === 'edit_product') {
            $prodActual = Producto::getById($_POST['id']);
            $imagenes = $prodActual['images']; 
        }

        if (isset($_FILES['images']) && $_FILES['images']['error'][0] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $nuevasImagenes = [];
            $totalFiles = count($_FILES['images']['name']);
            
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileExt = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                    $fileName = uniqid('prod_') . '_' . $i . '.' . $fileExt;
                    
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $fileName)) {
                        $nuevasImagenes[] = $_ENV['BASE_URL'] . 'uploads/products/' . $fileName;
                    }
                }
            }
            if (!empty($nuevasImagenes)) {
                $imagenes = $nuevasImagenes;
            }
        }

        if (empty($imagenes)) $imagenes = ['https://via.placeholder.com/600'];

        if ($action === 'create_product') {
            Producto::crear($nombre, $marca, $descripcion, $precio, $sale_price, $categoria, $imagenes, $destacado, $origen, $certificacion, $modelo, $stock);
            $_SESSION['admin_success'] = "Producto creado correctamente.";
        } else {
            $id = $_POST['id'];
            Producto::actualizar($id, $nombre, $marca, $descripcion, $precio, $sale_price, $categoria, $imagenes, $destacado, $origen, $certificacion, $modelo, $stock);
            $_SESSION['admin_success'] = "Producto actualizado correctamente.";
        }
        header("Location: " . $_ENV['BASE_URL'] . "dashboard");
        exit;
    } elseif ($action === 'delete_product') {
        $id = $_POST['id'];
        Producto::eliminar($id);
        $_SESSION['admin_success'] = "Producto eliminado correctamente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard");
        exit;
    }

    elseif ($action === 'create_category' || $action === 'edit_category') {
        require_once __DIR__ . '/../models/Categoria.php';
        $name = $_POST['name'] ?? '';
        $icon = $_POST['icon'] ?? 'box';
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if ($action === 'create_category') {
            Categoria::crear($slug, $name, $icon);
            $_SESSION['admin_success'] = "Categoría creada correctamente.";
        } else {
            $id = $_POST['id'];
            Categoria::actualizar($id, $slug, $name, $icon);
            $_SESSION['admin_success'] = "Categoría actualizada correctamente.";
        }
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/categorias");
        exit;
    } elseif ($action === 'delete_category') {
        require_once __DIR__ . '/../models/Categoria.php';
        $id = $_POST['id'];
        Categoria::eliminar($id);
        $_SESSION['admin_success'] = "Categoría eliminada correctamente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/categorias");
        exit;
    }

    elseif ($action === 'create_brand' || $action === 'edit_brand') {
        require_once __DIR__ . '/../models/Marca.php';
        $name = $_POST['name'] ?? '';

        if ($action === 'create_brand') {
            Marca::crear($name);
            $_SESSION['admin_success'] = "Marca creada correctamente.";
        } else {
            $id = $_POST['id'];
            Marca::actualizar($id, $name);
            $_SESSION['admin_success'] = "Marca actualizada correctamente.";
        }
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/marcas");
        exit;
    } elseif ($action === 'delete_brand') {
        require_once __DIR__ . '/../models/Marca.php';
        $id = $_POST['id'];
        Marca::eliminar($id);
        $_SESSION['admin_success'] = "Marca eliminada correctamente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/marcas");
        exit;
    }
    
    elseif ($action === 'edit_client') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $password = $_POST['password'] ?? '';

        Usuario::adminActualizarUsuario($id, $nombre, $telefono, $direccion, $password);
        $_SESSION['admin_success'] = "Datos del cliente actualizados correctamente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/clientes");
        exit;
    } elseif ($action === 'delete_client') {
        $id = $_POST['id'];
        Usuario::eliminar($id);
        $_SESSION['admin_success'] = "Cliente eliminado permanentemente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/clientes");
        exit;
    }

    elseif ($action === 'update_config') {
        require_once __DIR__ . '/../models/Configuracion.php';
        $whatsapp = $_POST['whatsapp'] ?? '';
        
        Configuracion::actualizar('whatsapp', $whatsapp);
        
        $_SESSION['admin_success'] = "Configuración actualizada correctamente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/configuracion");
        exit;
    }

    // --- LÓGICA DE PEDIDOS ---
    elseif ($action === 'update_order_status') {
        require_once __DIR__ . '/../models/Pedido.php';
        $id = $_POST['id'];
        $estado = $_POST['estado'];
        
        Pedido::actualizarEstado($id, $estado);
        $_SESSION['admin_success'] = "Estado del pedido actualizado correctamente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/pedidos/detalle?id=" . $id);
        exit;
    } 
    elseif ($action === 'delete_order') {
        require_once __DIR__ . '/../models/Pedido.php';
        $id = $_POST['id'];
        
        Pedido::eliminar($id);
        $_SESSION['admin_success'] = "Pedido eliminado permanentemente.";
        header("Location: " . $_ENV['BASE_URL'] . "dashboard/pedidos");
        exit;
    }
}

$allProducts = Producto::getAll();
$cart_lines = [];
$cart_subtotal = 0;

foreach ($_SESSION['cart'] as $id => $qty) {
    foreach ($allProducts as $p) {
        if ($p['id'] === $id) {
            $cart_lines[] = ['product' => $p, 'qty' => $qty];
            $precioReal = !empty($p['sale_price']) ? floatval($p['sale_price']) : floatval($p['price']);
            $cart_subtotal += $precioReal * $qty;
            break;
        }
    }
}
$cart_shipping = ($cart_subtotal === 0 || $cart_subtotal > 250) ? 0 : 9.95;
$cart_total = $cart_subtotal + $cart_shipping;

$route = isset($_GET['route']) && !empty($_GET['route']) ? $_GET['route'] : 'inicio';
$route = rtrim($route, '/');

if ($route === 'checkout' && !isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = 'checkout';
    header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
    exit;
}

$params = [];

if (strpos($route, 'producto/') === 0) {
    $parts = explode('/', $route);
    $params['slug'] = $parts[1] ?? null;
    $view_content = "../views/pages/producto.php";
} elseif (strpos($route, 'dashboard') === 0) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
        header("Location: " . $_ENV['BASE_URL'] . "mi-cuenta");
        exit;
    }
    
    if ($route === 'dashboard/crear' || $route === 'dashboard/editar') {
        $view_content = "../views/pages/dashboard-form.php";
    } elseif ($route === 'dashboard/categorias') {
        $view_content = "../views/pages/dashboard-categorias.php";
    } elseif ($route === 'dashboard/marcas') {
        $view_content = "../views/pages/dashboard-marcas.php";
    } elseif ($route === 'dashboard/clientes') {
        $view_content = "../views/pages/dashboard-clientes.php";
    } elseif ($route === 'dashboard/pedidos') {
        $view_content = "../views/pages/dashboard-pedidos.php";
    } elseif ($route === 'dashboard/pedidos/detalle') {
        $view_content = "../views/pages/dashboard-pedido-detalle.php";
    } elseif ($route === 'dashboard/configuracion') { 
        $view_content = "../views/pages/dashboard-configuracion.php";
    } else {
        $view_content = "../views/pages/dashboard.php"; 
    }
} else {
    $view_content = "../views/pages/{$route}.php";
}

require_once __DIR__ . '/../views/layouts/main.php';