<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng nhập cả email và mật khẩu.";
        header("Location: auth.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];

        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Sai tài khoản hoặc mật khẩu.";
        header("Location: auth.php");
        exit();
    }
}