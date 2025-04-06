<?php
// 引入数据库连接文件
require_once('config.php');
$conn = getDBConnection();

// 数据验证和清理函数
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// 统一的 JSON 响应函数
function jsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array('success' => $success, 'message' => $message, 'data' => $data));
    exit;
}

// 设置响应头为 JSON
header('Content-Type: application/json');

// 检查是否收到 POST 请求
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResponse(false, '无效请求！', null, 405); // 405 Method Not Allowed
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 检查 JSON 数据是否成功解码
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonResponse(false, '无效的请求体格式。', null, 400); // 400 Bad Request
}

// 检查是否提供了用户名和密码
if (empty($data['username']) || empty($data['password'])) {
    jsonResponse(false, '用户名或密码未提供。', null, 400); // 400 Bad Request
}

$username = sanitizeInput($data['username']);
$password = sanitizeInput($data['password']);

try {
    // 使用准备好的语句查询用户
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 验证密码
        if (password_verify($password, $row['password'])) {
            jsonResponse(true, '登录成功。', array('nickname' => $row['nickname']));
        } else {
            jsonResponse(false, '密码无效。', null, 401); // 401 Unauthorized
        }
    } else {
        jsonResponse(false, '用户名无效。', null, 401); // 401 Unauthorized
    }
} catch (PDOException $e) {
    // 捕获数据库错误并记录日志
    error_log("Database error: " . $e->getMessage());
    jsonResponse(false, '服务器内部错误，请稍后再试。', null, 500); // 500 Internal Server Error
}
?>