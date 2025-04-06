<?php
require_once('config.php');  // 引入数据库配置

// 获取用户IP地址的函数
function getUserIP() {
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } else {
        return 'UNKNOWN';
    }
}

// 数据验证和清理
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// 设置时区并获取当前时间
date_default_timezone_set('Asia/Shanghai');  // 设置为中国标准时间
$current_time = date('Y-m-d H:i:s');

$conn = getDBConnection();  // 获取数据库连接
$response = array("status" => "", "message" => "");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取并清理用户输入
    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : null;
    $nickname = isset($_POST['nickname']) ? sanitizeInput($_POST['nickname']) : null;
    $password = isset($_POST['password']) ? sanitizeInput($_POST['password']) : null;

    if ($username && $nickname && $password) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $ip_address = getUserIP();

            // 插入新用户数据，包括当前时间
            $stmt = $conn->prepare("INSERT INTO users (username, password, nickname, ip_address, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $hashed_password, $nickname, $ip_address, $current_time);

            if ($stmt->execute()) {
                $response["status"] = "success";
                $response["message"] = "注册成功！";
            } else {
                $response["status"] = "error";
                $response["message"] = "无法插入数据。";
                error_log("Database insert error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $response["status"] = "error";
            $response["message"] = "用户名已存在。";
            $stmt->close();
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "未提供用户名、密码或昵称。";
    }
} else {
    $response["status"] = "error";
    $response["message"] = "无效的请求方法。";
}

$conn->close();  // 关闭数据库连接
echo json_encode($response);  // 返回JSON格式响应
?>
