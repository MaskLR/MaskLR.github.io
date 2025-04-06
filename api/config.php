<?php
function getDBConnection() {
    $servername = "localhost:3306"; // 数据库主机名
    $username = "admin"; // 数据库用户名
    $password = "root"; // 数据库密码
    $dbname = "Mask"; // 数据库名称

    // 创建连接
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 检查连接
    if ($conn->connect_error) {
        die("连接失败 " . $conn->connect_error);
    }


    // 设置数据库字符集
    mysqli_set_charset($conn, "utf8");

    return $conn;
}
?>
