<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ tất cả thông tin.";
        header("Location: auth.php");
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = "Mật khẩu xác nhận không trùng khớp.";
        header("Location: auth.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email này đã được sử dụng.";
        header("Location: auth.php");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $insert = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (:fullname, :email, :password)");
    $success = $insert->execute([
        ':fullname' => $fullname,
        ':email'    => $email,
        ':password' => $hashedPassword
    ]);

    if ($success) {
        $_SESSION['success'] = "Đăng ký thành công! Hãy đăng nhập ngay.";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra, vui lòng thử lại sau.";
    }

    header("Location: auth.php");
    exit();
}