<?php
function getDBConnection() {
    $servername = "localhost"; // 数据库主机名
    $port = "3306"; // 数据库端口
    $username = "admin"; // 数据库用户名
    $password = "admin"; // 数据库密码
    $dbname = "Mask"; // 数据库名称

    try {
        // 创建 PDO 连接
        $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4";
        $conn = new PDO($dsn, $username, $password);

        // 设置 PDO 错误模式为异常模式
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    } catch (PDOException $e) {
        // 捕获连接错误并记录日志
        error_log("Database connection error: " . $e->getMessage());
        die(json_encode(array("status" => "error", "message" => "数据库错误，请稍后再试。")));
    }
}
?>