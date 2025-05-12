// 通用的表单提交处理函数
async function handleFormSubmit(url, formData) {
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! Status: ${response.status}`);
        }

        return data;
    } catch (error) {
        console.error(`Error during fetch to ${url}:`, error);
        throw error;
    }
}

// 登录表单提交事件
document.getElementById("loginForm").addEventListener("submit", async function (event) {
    event.preventDefault();

    const formData = {
        username: document.getElementById("loginUsername").value,
        password: document.getElementById("loginPassword").value
    };

    try {
        const data = await handleFormSubmit("https://mask.ddns.net:808/api/loginUser.php", formData);

        // 检查后端返回的数据结构
        console.log("Login response data:", data);

        if (data.success) {
            const nickname = data.data?.nickname || "用户"; // 从 data.data 中获取 nickname
            alert(`登录成功！欢迎回来，${nickname}！`);
        } else {
            alert(`登录失败：${data.message}`);
        }
    } catch (error) {
        alert(`登录时发生错误：${error.message}`);
    }
});

// 注册表单提交事件
document.getElementById("registerForm").addEventListener("submit", async function (event) {
    event.preventDefault();

    const formData = {
        nickname: document.getElementById("registerNickname").value,
        username: document.getElementById("registerUsername").value,
        password: document.getElementById("registerPassword").value
    };

    try {
        const data = await handleFormSubmit("https://mask.ddns.net:808/api/registerUser.php", formData);

        if (data.status === "success") {
            alert("注册成功！");
        } else {
            alert(`注册失败：${data.message}`);
        }
    } catch (error) {
        alert(`注册时发生错误：${error.message}`);
    }
});
