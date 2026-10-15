<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$pageTitle = $pageTitle ?? 'EcoRecycle';

// Lấy thông tin user đăng nhập
$user = $_SESSION['user'] ?? null;
$isLoggedIn = !empty($user);

// Xử lý tự động kiểm tra đường dẫn Avatar
$userAvatar = $user['avatar'] ?? '';
$avatarSrc = '';

if (!empty($userAvatar)) {
    if (file_exists(__DIR__ . '/../public/uploads/avatars/' . $userAvatar)) {
        $avatarSrc = '/eco_recycle_b2c/public/uploads/avatars/' . $userAvatar;
    } elseif (file_exists(__DIR__ . '/../public/assets/images/' . $userAvatar)) {
        $avatarSrc = '/eco_recycle_b2c/public/assets/images/' . $userAvatar;
    } elseif (filter_var($userAvatar, FILTER_VALIDATE_URL) || strpos($userAvatar, '/') === 0) {
        $avatarSrc = $userAvatar;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | EcoRecycle</title>

  <!-- Nhúng Google Font Inter & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Thư viện Font Awesome cho Icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/eco_recycle_b2c/public/assets/style.css?v=<?php echo time(); ?>">

  <style>
    /* Áp dụng Font chữ cho văn bản nhưng KHÔNG ghi đè lên Icon FontAwesome */
    body, input, button, select, textarea, p, a, span:not(.brand-icon), div, h1, h2, h3, h4, h5, h6 {
      font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* Giữ nguyên font chuyên dụng cho tất cả thẻ Icon FontAwesome */
    i, .fa, .fas, .far, .fab, .fal, .fad, .fa-solid, .fa-regular, .fa-brands {
      font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
    }

    /* Tinh chỉnh font cho các tiêu đề chính */
    h1, h2, h3, h4, h5, h6, .brand, .page-title, .product-detail-title {
      font-family: 'Plus Jakarta Sans', sans-serif;
      letter-spacing: -0.02em;
    }

    /* Tinh chỉnh độ đậm thanh menu & nút bấm */
    .nav-item, .auth-link, .btn-search, .btn-checkout, .btn-add-cart {
      font-weight: 600;
    }

    /* Style bổ sung cho User Avatar */
    .user-avatar-img {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      object-fit: cover;
      vertical-align: middle;
      border: 1px solid #2e7d32;
    }
    .user-avatar-fallback {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background-color: #2e7d32;
      color: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 15px;
      vertical-align: middle;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .nav-user-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }
  </style>
</head>
<body>
<header class="site-header">
  <div class="navbar-container">
    
    <!-- 0. Logo & Tên Web -->
    <a class="brand" href="/eco_recycle_b2c/public/index.php">
      <span class="brand-icon">♻</span> EcoRecycle
    </a>

    <!-- 1. Thanh tìm kiếm -->
    <div class="nav-search">
      <form action="/eco_recycle_b2c/public/products.php" method="GET" class="search-form">
        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." required>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
    </div>

    <!-- Thanh menu điều hướng -->
    <nav class="nav-menu">
      <!-- 2. Trang chủ -->
      <a href="/eco_recycle_b2c/public/index.php" class="nav-item">Trang chủ</a>

      <!-- Menu Quản trị (Dành riêng cho Admin) -->
      <?php if ($isLoggedIn && ($user['role'] ?? '') === 'admin'): ?>
        <a href="/eco_recycle_b2c/admin/index.php" class="nav-item admin-link">Quản trị</a>
      <?php endif; ?>

      <!-- 3. Yêu thích -->
      <a href="/eco_recycle_b2c/public/wishlist.php" class="nav-item">
        <i class="fa-regular fa-heart"></i> Yêu thích
      </a>

      <!-- 4. Giỏ hàng -->
      <a href="/eco_recycle_b2c/public/cart.php" class="nav-item">
        <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
      </a>

      <!-- 5 & 6. Đăng ký / Đăng nhập HOẶC Avatar + Đăng xuất -->
      <?php if (!$isLoggedIn): ?>
        <div class="nav-auth">
          <a href="/eco_recycle_b2c/public/register.php" class="auth-link">Đăng ký</a>
          <span class="divider">|</span>
          <a href="/eco_recycle_b2c/public/login.php" class="auth-link">Đăng nhập</a>
        </div>
      <?php else: ?>
        <div class="nav-user-profile">
          <a href="/eco_recycle_b2c/public/profile.php" class="avatar-link" title="<?= htmlspecialchars($user['name'] ?? $user['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($avatarSrc)): ?>
              <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="user-avatar-img">
            <?php else: ?>
              <div class="user-avatar-fallback">
                <?= strtoupper(substr($user['name'] ?? $user['username'] ?? 'U', 0, 1)) ?>
              </div>
            <?php endif; ?>
          </a>
          <a class="nav-button logout-btn" href="/eco_recycle_b2c/public/logout.php">Đăng xuất</a>
        </div>
      <?php endif; ?>
    </nav>

  </div>
</header>
<main class="container">
