<?php
// 引入数据库连接文件
require_once('config.php');
$conn = getDBConnection();

// 数据验证和清理函数
function sanitizeInput($data) {
    // 移除额外的空格和特殊字符，并对 HTML 特殊字符进行转义
    return htmlspecialchars(stripslashes(trim($data)));
}

// 设置响应头为 JSON
header('Content-Type: application/json');

// 检查是否收到 POST 请求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 读取 POST 请求中的 JSON 数据
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // 检查 JSON 数据是否成功解码
    if (json_last_error() === JSON_ERROR_NONE) {
        // 检查 JSON 数据中是否包含用户名和密码
        if (isset($data['username']) && isset($data['password'])) {
            $username = sanitizeInput($data['username']);
            $password = sanitizeInput($data['password']);

            // 使用准备好的语句来查询数据库以验证用户
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            if ($stmt === false) {
                // 准备语句失败，记录错误并返回错误消息
                error_log("Prepared statement error: " . $conn->error);
                echo json_encode(array('success' => false, 'message' => '服务器内部错误，请稍后再试。'));
                exit;
            }

            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                // 语句执行失败，记录错误并返回错误消息
                error_log("Statement execution error: " . $stmt->error);
                echo json_encode(array('success' => false, 'message' => '服务器内部错误，请稍后再试。'));
                exit;
            }

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                // 找到用户，验证密码
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    // 用户名和密码匹配，登录成功
                    echo json_encode(array('success' => true, 'nickname' => $row['nickname']));
                } else {
                    // 密码不匹配，登录失败
                    echo json_encode(array('success' => false, 'message' => '密码无效。'));
                }
            } else {
                // 用户名不存在，登录失败
                echo json_encode(array('success' => false, 'message' => '用户名无效。'));
            }

            // 关闭准备好的语句
            $stmt->close();
        } else {
            // 缺少用户名或密码，返回错误消息
            echo json_encode(array('success' => false, 'message' => '用户名或密码未提供。'));
        }
    } else {
        // JSON 解码出错，返回错误消息
        echo json_encode(array('success' => false, 'message' => '无效的请求体格式。'));
    }
} else {
    // 不是 POST 请求，返回错误消息
    echo json_encode(array('success' => false, 'message' => '无效请求！'));
}

// 关闭数据库连接
$conn->close();
?>
