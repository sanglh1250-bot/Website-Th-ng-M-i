<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$error = ''; $name=''; $email='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $email = trim(strtolower($_POST['email'] ?? '')); $password = $_POST['password'] ?? ''; $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Vui lòng nhập họ tên và email hợp lệ.';
    elseif (strlen($password) < 8) $error = 'Mật khẩu cần có ít nhất 8 ký tự.';
    elseif ($password !== $confirm) $error = 'Mật khẩu xác nhận không khớp.';
    else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $check->execute([$email]);
        if ($check->fetch()) $error = 'Email này đã được đăng ký.';
        else {
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
            $stmt->execute([$name,$email,password_hash($password, PASSWORD_DEFAULT),'customer']);
            header('Location: login.php?registered=1'); exit;
        }
    }
}
$pageTitle='Đăng ký'; require __DIR__.'/../includes/header.php';
?>
<div class="auth-wrap"><div class="auth-aside"><span class="auth-symbol">♻</span><span class="eyebrow">JOIN THE GREEN MOVEMENT</span><h2>Bắt đầu hành trình sống xanh.</h2><p>Tạo tài khoản để khám phá các sản phẩm tái chế và đồng hành cùng môi trường.</p><div class="auth-leaf">🌿</div></div><form class="auth-card" method="post"><span class="eyebrow">TÀI KHOẢN MỚI</span><h1>Đăng ký</h1><p class="muted">Điền thông tin để tạo tài khoản EcoRecycle.</p>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<label>Họ và tên<input name="name" required maxlength="100" value="<?= e($name) ?>" placeholder="Nguyễn Văn A"></label>
<label>Email<input type="email" name="email" required value="<?= e($email) ?>" placeholder="ban@example.com"></label>
<label>Mật khẩu<input type="password" name="password" required minlength="8" placeholder="Ít nhất 8 ký tự"></label>
<label>Nhập lại mật khẩu<input type="password" name="confirm_password" required minlength="8" placeholder="Nhập lại mật khẩu"></label>
<button class="button button-primary button-full" type="submit">Tạo tài khoản →</button><p class="auth-switch">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p></form></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
