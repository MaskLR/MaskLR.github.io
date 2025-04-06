<?php
function getDBConnection() {
    $servername = "localhost"; // 数据库主机名
    $port = "3306"; // 数据库端口
    $username = "admin"; // 数据库用户名
    $password = "root"; // 数据库密码
    $dbname = "Mask"; // 数据库名称

    try {
        // 创建 PDO 连接
        $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8";
        $conn = new PDO($dsn, $username, $password);

        // 设置 PDO 错误模式为异常模式
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    } catch (PDOException $e) {
        // 捕获连接错误并输出
        die("连接失败: " . $e->getMessage());
    }
}
?>