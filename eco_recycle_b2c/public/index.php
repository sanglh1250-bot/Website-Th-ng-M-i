<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Trang chủ';

// Lấy category_id từ URL (nếu có)
$currentCat = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Cập nhật câu truy vấn SQL để lọc sản phẩm theo danh mục khi bấm chọn
if ($currentCat > 0) {
    $stmt = $pdo->prepare("SELECT id, name, description, price, image_url, stock FROM products WHERE status = 'active' AND category_id = ? ORDER BY created_at DESC LIMIT 6");
    $stmt->execute([$currentCat]);
} else {
    $stmt = $pdo->query("SELECT id, name, description, price, image_url, stock FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
}
$products = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow">SỐNG XANH · TIÊU DÙNG CÓ Ý THỨC</span>
    <h1>Rác thải hôm nay,<br><em>tài nguyên ngày mai.</em></h1>
    <p>Khám phá sản phẩm tái chế hữu ích, giảm rác thải và cùng xây dựng lối sống bền vững.</p>
    <div class="hero-actions">
      <a class="button button-primary" href="products.php">Khám phá sản phẩm ↗</a>
      <!-- Tìm thẻ chứa nút này trong public/index.php -->
      <a href="about.php" style="
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
          padding: 11px 22px;
          background-color: #ffffff;
          color: #2e7d32;
          font-weight: 700;
          font-size: 14.5px;
          text-decoration: none;
          border-radius: 10px;
          border: 1.5px solid #2e7d32;
          box-shadow: 0 2px 8px rgba(46, 125, 50, 0.08);
          transition: all 0.25s ease;
          margin-left: 10px;
          vertical-align: middle;
      " onmouseover="this.style.backgroundColor='#2e7d32'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#2e7d32';">
          <i class="fa-solid fa-circle-info" style="font-size: 14px;"></i> Tìm hiểu dự án
      </a>
    </div>
    <div class="hero-note"><span>●</span> Mỗi lựa chọn xanh tạo nên khác biệt</div>
  </div>
  <div class="hero-art" aria-label="Minh họa tái chế">
    <div class="orbit orbit-one"></div>
    <div class="orbit orbit-two"></div>
    <div class="leaf leaf-one">♻</div>
    <div class="leaf leaf-two">🌱</div>
    <div class="art-card"><span>GREEN CHOICE</span><strong>Reuse.<br>Recycle.<br>Renew.</strong><small>Hành tinh xanh bắt đầu từ bạn.</small></div>
    <div class="floating-tag tag-top">100% ý nghĩa</div>
    <div class="floating-tag tag-bottom">🌍 Vì một Trái Đất tốt hơn</div>
  </div>
</section>

<section class="stats">
  <div><strong>♻</strong><span>Ưu tiên vật liệu tái chế</span></div>
  <div><strong>🌿</strong><span>Tiêu dùng bền vững</span></div>
  <div><strong>🤝</strong><span>Cộng đồng sống xanh</span></div>
</section>

<section class="section" id="mission">
  <div class="section-heading">
    <div>
      <span class="eyebrow">MUA SẮM CÓ MỤC ĐÍCH</span>
      <h2>Sản phẩm xanh nổi bật</h2>
    </div>
    <a class="text-link" href="products.php">Xem tất cả sản phẩm →</a>
  </div>

  <!-- BỘ LỌC DANH MỤC (ĐẶT Ở ĐÂY ĐỂ HIỂN THỊ PHÍA TRÊN SẢN PHẨM) -->
  <div class="category-filter-bar">
    <?php
    $categoriesList = [
      1 => 'Thời trang & Phụ kiện',
      2 => 'Nội thất & Trang trí',
      3 => 'Văn phòng phẩm & Quà tặng',
      4 => 'Đồ luyện tập & Lối sống xanh',
      5 => 'Góc đồ cũ tái chế'
    ];
    ?>
    <a href="index.php#mission" class="cat-btn <?= $currentCat === 0 ? 'active' : '' ?>">Tất cả</a>
    <?php foreach ($categoriesList as $id => $name): ?>
      <a href="index.php?category_id=<?= $id ?>#mission" class="cat-btn <?= $currentCat === $id ? 'active' : '' ?>">
        <?= htmlspecialchars($name) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- DANH SÁCH SẢN PHẨM -->
  <div class="product-grid">
    <?php foreach ($products as $p): ?>
      <article class="product-card">
        <a class="product-image" href="product.php?id=<?= (int)$p['id'] ?>">
          <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" onerror="this.src='https://placehold.co/600x420/e8f2e8/245a3b?text=EcoRecycle'">
          <span class="product-label">Lựa chọn xanh</span>
        </a>
        <div class="product-info">
          <h3><a href="product.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a></h3>
          <p><?= e(mb_strimwidth($p['description'], 0, 90, '…')) ?></p>
          <div class="product-bottom"><strong><?= money($p['price']) ?></strong><span>Còn <?= (int)$p['stock'] ?></span></div>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if (!$products): ?>
      <p style="grid-column: 1/-1; text-align: center; color: var(--muted); padding: 30px 0;">Không tìm thấy sản phẩm thuộc danh mục này.</p>
    <?php endif; ?>
  </div>
</section>

<section class="mission-banner">
  <div>
    <span class="eyebrow">HÀNH ĐỘNG NHỎ · TÁC ĐỘNG LỚN</span>
    <h2>Chọn sản phẩm tái chế.<br>Chọn tương lai bền vững.</h2>
  </div>
  <a class="button button-primary" href="products.php">Mua sắm xanh ↗</a>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
