<?php
// 允许跨域访问
header("Access-Control-Allow-Origin: https://masklr.github.io");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 预检请求处理
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
//
require_once('config.php');
$conn = getDBConnection();

// 数据验证和清理
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// 获取用户IP地址的函数
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'UNKNOWN';
}


// 统一的 JSON 响应函数
function jsonResponse($status, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array('status' => $status, 'message' => $message, 'data' => $data));
    exit;
}


try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        jsonResponse("error", "无效的请求方法。", null, 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse("error", "无效的请求体格式。", null, 400);
    }

    $username = isset($input['username']) ? sanitizeInput($input['username']) : null;
    $nickname = isset($input['nickname']) ? sanitizeInput($input['nickname']) : null;
    $password = isset($input['password']) ? sanitizeInput($input['password']) : null;

    if (!$username || !$nickname || !$password) {
        jsonResponse("error", "未提供用户名、密码或昵称。", null, 400);
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        jsonResponse("error", "用户名长度必须在3到50个字符之间。", null, 400);
    }

    if (strlen($password) < 6) {
        jsonResponse("error", "密码长度必须至少为6个字符。", null, 400);
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        jsonResponse("error", "用户名已存在。", null, 409);
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $ip_address = getUserIP();
	$current_time = date('Y-m-d H:i:s');  // Add this line to get the current time
	$stmt = $conn->prepare("INSERT INTO users (username, password, nickname, ip_address, created_at) VALUES (:username, :password, :nickname, :ip_address, :created_at)");
	$stmt->bindParam(':username', $username, PDO::PARAM_STR);
	$stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
	$stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
	$stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
	$stmt->bindParam(':created_at', $current_time, PDO::PARAM_STR);

    if ($stmt->execute()) {
        jsonResponse("success", "注册成功！");
    } else {
        jsonResponse("error", "无法插入数据。", null, 500);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    jsonResponse("error", "服务器内部错误，请稍后再试。", null, 500);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    jsonResponse("error", "发生未知错误，请稍后再试。", null, 500);
}
?>
