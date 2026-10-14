<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRecycle - Đăng Nhập & Đăng Ký</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Cột trái: Giới thiệu thương hiệu -->
            <div class="card-brand">
                <div class="brand-badge"><i class="fa-solid fa-leaf"></i> EcoRecycle System</div>
                <h2>Xây dựng tương lai xanh & tái chế tuần hoàn</h2>
                <p>Nền tảng thương mại điện tử kết hợp logistics khép kín và phân loại rác thông minh.</p>
                <div class="feature-pills">
                    <span><i class="fa-solid fa-recycle"></i> Thu gom & tích điểm</span>
                    <span><i class="fa-solid fa-shield-halved"></i> Bảo mật tài khoản</span>
                    <span><i class="fa-solid fa-truck-fast"></i> Vận hành trực tuyến</span>
                </div>
            </div>

            <!-- Cột phải: Form nhập liệu -->
            <div class="card-form">
                <div class="tabs">
                    <button class="tab-btn active" id="tab-login" onclick="switchForm('login')">Đăng Nhập</button>
                    <button class="tab-btn" id="tab-register" onclick="switchForm('register')">Đăng Ký</button>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form Đăng Nhập -->
                <form id="login-form" class="auth-form" action="login_process.php" method="POST">
                    <div class="field-group">
                        <label>Địa chỉ Email</label>
                        <div class="input-box">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" placeholder="ten@vidu.com" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Mật khẩu</label>
                        <div class="input-box">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="login-password" name="password" placeholder="Nhập mật khẩu" required>
                            <i class="fa-regular fa-eye toggle-eye" onclick="togglePass('login-password', this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Đăng nhập</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <!-- Form Đăng Ký -->
                <form id="register-form" class="auth-form hidden" action="register_process.php" method="POST" onsubmit="return checkPasswordMatch()">
                    <div class="field-group">
                        <label>Họ và tên</label>
                        <div class="input-box">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="fullname" placeholder="Nguyễn Văn A" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Địa chỉ Email</label>
                        <div class="input-box">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" placeholder="ten@vidu.com" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Mật khẩu</label>
                        <div class="input-box">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg-password" name="password" minlength="6" placeholder="Ít nhất 6 ký tự" required>
                            <i class="fa-regular fa-eye toggle-eye" onclick="togglePass('reg-password', this)"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Xác nhận mật khẩu</label>
                        <div class="input-box">
                            <i class="fa-solid fa-shield-halved"></i>
                            <input type="password" id="reg-repassword" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Tạo tài khoản</span>
                        <i class="fa-solid fa-check"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>