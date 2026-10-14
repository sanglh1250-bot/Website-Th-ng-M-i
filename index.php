<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Chủ - EcoRecycle</title>
</head>
<body style="font-family: sans-serif; text-align: center; padding-top: 60px;">
    <h1>Xin chào, <?= htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
    <p>Quyền hạn của bạn: <strong><?= htmlspecialchars($_SESSION['user_role']); ?></strong></p>
    <p>Đăng nhập thành công vào hệ thống EcoRecycle chạy bằng Docker!</p>
    <br>
    <a href="logout.php" style="color: #dc2626; text-decoration: none; font-weight: bold;">Đăng xuất</a>
</body>
</html>