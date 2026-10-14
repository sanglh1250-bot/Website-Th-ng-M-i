<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$pageTitle = $pageTitle ?? 'EcoRecycle';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | EcoRecycle</title>
  <link rel="stylesheet" href="/eco_recycle_b2c/public/assets/style.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="/eco_recycle_b2c/public/index.php"><span class="brand-icon">♻</span> EcoRecycle</a>
  <nav>
    <a href="/eco_recycle_b2c/public/index.php">Trang chủ</a>
    <a href="/eco_recycle_b2c/public/products.php">Sản phẩm</a>
    <?php if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin'): ?>
      <a href="/eco_recycle_b2c/admin/index.php">Quản trị</a>
    <?php endif; ?>
    <?php if (!empty($_SESSION['user'])): ?>
      <span class="nav-user">Xin chào, <?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?></span>
      <a class="nav-button" href="/eco_recycle_b2c/public/logout.php">Đăng xuất</a>
    <?php else: ?>
      <a href="/eco_recycle_b2c/public/login.php">Đăng nhập</a>
      <a class="nav-button" href="/eco_recycle_b2c/public/register.php">Đăng ký</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
