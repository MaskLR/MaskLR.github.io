// 通用的表单提交处理函数
async function handleFormSubmit(url, formData) {
    try {
        console.log(`Submitting form to ${url}`, formData);

        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json" // 设置请求头为 JSON
            },
            body: JSON.stringify(formData) // 将数据转换为 JSON 格式
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        console.log(`Parsed JSON data from ${url}`, data);

        return data;
    } catch (error) {
        console.error(`Error during fetch to ${url}:`, error);
        throw error; // 继续抛出错误以便调用者处理
    }
}

// 登录表单的提交事件
document.getElementById("loginForm").addEventListener("submit", async function (event) {
    event.preventDefault(); // 阻止表单默认提交行为

    // 构造 JSON 数据
    const formData = {
        username: document.getElementById("loginUsername").value,
        password: document.getElementById("loginPassword").value
    };

    try {
        const data = await handleFormSubmit("api/loginUser.php", formData);

        if (data.success) { // 如果登录成功
            const nickname = data.nickname; // 获取昵称
            document.getElementById("welcomeMessage").innerText = `你好，${nickname}！`; // 设置欢迎消息
            document.getElementById("welcomeMessage").style.display = "block"; // 显示欢迎消息
        } else {
            alert(`登录失败：${data.message}`); // 弹出错误消息
        }
    } catch (error) {
        alert("登录时发生错误，请稍后再试！");
    }
});

// 注册表单的提交事件
document.getElementById("registerForm").addEventListener("submit", async function (event) {
    event.preventDefault(); // 阻止表单默认提交行为

    // 构造 JSON 数据
    const formData = {
        nickname: document.getElementById("registerNickname").value,
        username: document.getElementById("registerUsername").value,
        password: document.getElementById("registerPassword").value
    };

    try {
        const data = await handleFormSubmit("api/registerUser.php", formData);

        if (data.status === "success") {
            alert("注册成功！");
        } else {
            alert(`注册失败：${data.message}`);
        }
    } catch (error) {
        alert("注册时发生错误，请稍后再试！");
    }
});
