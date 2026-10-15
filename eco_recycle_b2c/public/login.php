<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,name,email,password,role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
        header('Location: ' . ($user['role'] === 'admin' ? '../admin/index.php' : 'index.php'));
        exit;
    }
    $error = 'Email hoặc mật khẩu chưa chính xác.';
}
$pageTitle = 'Đăng nhập';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
    <div class="auth-aside"><span class="auth-symbol">♻</span><span class="eyebrow">WELCOME BACK</span>
        <h2>Mỗi lựa chọn xanh đều đáng giá.</h2>
        <p>Đăng nhập để tiếp tục khám phá những sản phẩm mang vòng đời mới.</p>
        <div class="auth-leaf">🌎</div>
    </div>
    <form class="auth-card" method="post"><span class="eyebrow">RẤT VUI ĐƯỢC GẶP LẠI</span>
        <h1>Đăng nhập</h1>
        <p class="muted">Nhập thông tin tài khoản của bạn.</p>
        <?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Đăng ký thành công. Bạn có thể đăng nhập ngay.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <label>Email<input type="email" name="email" required value="<?= e($email) ?>" placeholder="ban@example.com"></label>
        <label>Mật khẩu<input type="password" name="password" required placeholder="Nhập mật khẩu"></label>
        <button class="button button-primary button-full" type="submit">Đăng nhập →</button>
        <p class="auth-switch">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
