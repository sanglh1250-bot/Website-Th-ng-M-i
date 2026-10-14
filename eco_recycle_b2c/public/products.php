<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Sản phẩm';
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status='active' AND (name LIKE ? OR description LIKE ?) ORDER BY created_at DESC");
    $term = "%{$q}%"; $stmt->execute([$term, $term]);
} else {
    $stmt = $pdo->query("SELECT * FROM products WHERE status='active' ORDER BY created_at DESC");
}
$products = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">CATALOGUE XANH</span><h1>Sản phẩm tái chế</h1><p>Những món đồ hữu ích, góp phần kéo dài vòng đời vật liệu.</p></section>
<form class="search-form" method="get"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm chai tái chế, túi vải, đồ trang trí..."><button class="button button-primary" type="submit">Tìm kiếm</button></form>
<div class="product-grid">
<?php foreach ($products as $p): ?>
<article class="product-card">
  <a class="product-image" href="product.php?id=<?= (int)$p['id'] ?>"><img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" onerror="this.src='https://placehold.co/600x420/e8f2e8/245a3b?text=EcoRecycle'"><span class="product-label">♻ Tái chế</span></a>
  <div class="product-info"><h3><a href="product.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a></h3><p><?= e(mb_strimwidth($p['description'], 0, 100, '…')) ?></p><div class="product-bottom"><strong><?= money($p['price']) ?></strong><span><?= (int)$p['stock'] > 0 ? 'Còn hàng' : 'Hết hàng' ?></span></div><a class="button button-outline button-full" href="product.php?id=<?= (int)$p['id'] ?>">Xem chi tiết</a></div>
</article>
<?php endforeach; ?>
</div>
<?php if (!$products): ?><div class="empty-state"><span>🌱</span><h3>Chưa tìm thấy sản phẩm</h3><p>Thử từ khóa khác hoặc quay lại sau nhé.</p></div><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
