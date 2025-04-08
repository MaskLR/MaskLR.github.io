<?php
require_once('config.php');
$conn = getDBConnection();

// 数据验证和清理函数
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// 获取用户 IP 地址的函数
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
function jsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array('success' => $success, 'message' => $message, 'data' => $data));
    exit;
}

// 设置响应头为 JSON
header('Content-Type: application/json');

try {
    // 检查是否收到 POST 请求
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        jsonResponse(false, '无效请求！', null, 405); // 405 Method Not Allowed
    }

    // 获取并解析 JSON 数据
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(false, '无效的请求体格式。', null, 400); // 400 Bad Request
    }

    $username = isset($input['username']) ? sanitizeInput($input['username']) : null;
    $password = isset($input['password']) ? sanitizeInput($input['password']) : null;

    if (!$username || !$password) {
        jsonResponse(false, '用户名或密码未提供。', null, 400); // 400 Bad Request
    }

    // 查询用户
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $row['password'])) {
            // 更新最后一次登录 IP 地址
            $lastLoginIP = getUserIP();
            $updateStmt = $conn->prepare("UPDATE users SET last_login_ip = :last_login_ip WHERE id = :id");
            $updateStmt->bindParam(':last_login_ip', $lastLoginIP, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            jsonResponse(true, '登录成功。', array('nickname' => $row['nickname']));
        } else {
            jsonResponse(false, '密码无效。', null, 401); // 401 Unauthorized
        }
    } else {
        jsonResponse(false, '用户名无效。', null, 401); // 401 Unauthorized
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    jsonResponse(false, '服务器内部错误，请稍后再试。', null, 500); // 500 Internal Server Error
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    jsonResponse(false, '发生未知错误，请稍后再试。', null, 500); // 500 Internal Server Error
}
?>