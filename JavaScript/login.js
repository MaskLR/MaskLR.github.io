// 登录表单的提交事件
document.getElementById("loginForm").addEventListener("submit", function (event) {
    event.preventDefault(); // 阻止表单默认提交行为
    var formData = new FormData(this); // 获取表单数据
    console.log('Submitting login form', formData);
    
    // 发送登录请求到 login.php
    fetch("http://mask.ddns.net:808/api/loginUser.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        console.log('Response received', response);
        return response.json();
    }) // 解析响应数据为 JSON 格式
    .then(data => {
        console.log('Parsed JSON data', data);
        if (data.success) { // 如果登录成功
            var nickname = data.nickname; // 获取昵称
            document.getElementById("welcomeMessage").innerText = "你好，" + nickname + "！"; // 设置欢迎消息
            document.getElementById("welcomeMessage").style.display = "block"; // 显示欢迎消息
        } else {
            alert("登录失败：" + data.message); // 弹出错误消息
        }
    })
    .catch(error => console.error('Fetch error:', error)); // 捕获并输出错误
});

// 注册表单的提交事件
document.getElementById("registerForm").addEventListener("submit", function (event) {
    event.preventDefault(); // 阻止表单默认提交行为
    var formData = new FormData(this); // 获取表单数据
    console.log('Submitting register form', formData);
    
    // 发送注册请求到 register.php
    fetch("api/registerUser.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        console.log('Response received', response);
        return response.text();
    }) // 解析响应数据为文本
    .then(data => alert(data)) // 弹出响应数据
    .catch(error => console.error('Fetch error:', error)); // 捕获并输出错误
});
