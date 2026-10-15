<?php
// 1. Nhúng file kết nối CSDL và Header
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Hàm chuyển chuỗi Tiếng Việt có dấu thành không dấu
if (!function_exists('removeVietnameseTones')) {
    function removeVietnameseTones($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "a", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "e", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "i", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "o", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "u", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "y", $str);
        $str = preg_replace("/(Đ)/", "d", $str);
        return mb_strtolower($str, 'UTF-8');
    }
}

// Lấy tham số từ URL (nhận cả 'search' lẫn 'keyword')
$keyword     = isset($_GET['search']) ? trim($_GET['search']) : (isset($_GET['keyword']) ? trim($_GET['keyword']) : '');
$sort        = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Xây dựng câu truy vấn SQL
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($category_id > 0) {
    $sql .= " AND category_id = ?";
    $params[] = $category_id;
}

// Xử lý logic tìm kiếm thông minh (Hoa/thường, không dấu, có dấu, lỗi từ)
if (!empty($keyword)) {
    $keyword_no_tone = removeVietnameseTones($keyword);
    
    $words = array_filter(explode(' ', $keyword));
    $words_no_tone = array_filter(explode(' ', $keyword_no_tone));

    $search_conditions = [];

    // 1. Tìm nguyên cụm từ người dùng nhập (Có dấu)
    $search_conditions[] = "name LIKE ?";
    $params[] = '%' . $keyword . '%';
    
    $search_conditions[] = "description LIKE ?";
    $params[] = '%' . $keyword . '%';

    // 2. Tìm nguyên cụm từ dạng không dấu
    if ($keyword !== $keyword_no_tone) {
        $search_conditions[] = "name LIKE ?";
        $params[] = '%' . $keyword_no_tone . '%';

        $search_conditions[] = "description LIKE ?";
        $params[] = '%' . $keyword_no_tone . '%';
    }

    // 3. Tìm kiếm tách theo từng từ đơn lẻ (Tăng độ linh hoạt khi gõ sai/thiếu từ)
    foreach ($words as $w) {
        if (mb_strlen($w) > 1) {
            $search_conditions[] = "name LIKE ?";
            $params[] = '%' . $w . '%';
        }
    }

    foreach ($words_no_tone as $w_nt) {
        if (mb_strlen($w_nt) > 1) {
            $search_conditions[] = "name LIKE ?";
            $params[] = '%' . $w_nt . '%';
        }
    }

    $sql .= " AND (" . implode(" OR ", $search_conditions) . ")";
}

switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY id ASC";
        break;
    case 'latest':
    default:
        $sql .= " ORDER BY id DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* Style tùy chỉnh cho trang Products */
.products-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 15px;
    font-family: Arial, sans-serif;
}

.page-title {
    font-size: 24px;
    font-weight: bold;
    color: #2e7d32;
    margin-bottom: 20px;
}

/* Thanh bộ lọc */
.filter-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 25px;
}

.filter-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-input {
    flex: 2;
    min-width: 200px;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
}

.filter-select {
    flex: 1;
    min-width: 150px;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
}

.btn-search {
    padding: 10px 20px;
    background-color: #2e7d32;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.btn-search:hover {
    background-color: #1b5e20;
}

.btn-reset {
    padding: 10px 16px;
    background-color: #6c757d;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
}

/* Menu danh mục */
.category-menu {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
}

.category-item {
    padding: 8px 16px;
    border: 1px solid #2e7d32;
    color: #2e7d32;
    text-decoration: none;
    border-radius: 20px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.category-item:hover, .category-item.active {
    background-color: #2e7d32;
    color: #fff;
}

/* Grid sản phẩm */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.product-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 12px;
}

.product-name {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin: 0 0 10px 0;
    line-height: 1.3;
}

.product-price {
    font-size: 18px;
    color: #d32f2f;
    font-weight: bold;
    margin-bottom: 15px;
}

.btn-detail {
    display: block;
    text-align: center;
    background-color: #2e7d32;
    color: white;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
}

.btn-detail:hover {
    background-color: #1b5e20;
}
</style>

<div class="products-container">
    <h1 class="page-title">Danh sách sản phẩm</h1>

    <!-- Form Tìm kiếm & Lọc -->
    <div class="filter-card">
        <form action="products.php" method="GET" class="filter-form">
            <?php if ($category_id > 0): ?>
                <input type="hidden" name="category_id" value="<?= $category_id ?>">
            <?php endif; ?>

            <input
                type="text"
                name="search"
                class="filter-input"
                placeholder="Tìm kiếm sản phẩm..."
                value="<?= htmlspecialchars($keyword) ?>">

            <select name="sort" class="filter-select">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
            </select>

            <button type="submit" class="btn-search">Lọc & Tìm kiếm</button>

            <?php if (!empty($keyword) || $sort !== 'latest' || $category_id > 0): ?>
                <a href="products.php" class="btn-reset">Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Menu Danh mục -->
    <div class="category-menu">
        <a href="products.php" class="category-item <?= $category_id === 0 ? 'active' : '' ?>">Tất cả</a>
        <a href="products.php?category_id=1" class="category-item <?= $category_id === 1 ? 'active' : '' ?>">Thời trang & Phụ kiện tái chế</a>
        <a href="products.php?category_id=2" class="category-item <?= $category_id === 2 ? 'active' : '' ?>">Nội thất & Trang trí nhà cửa</a>
        <a href="products.php?category_id=3" class="category-item <?= $category_id === 3 ? 'active' : '' ?>">Văn phòng phẩm & Quà tặng</a>
        <a href="products.php?category_id=4" class="category-item <?= $category_id === 4 ? 'active' : '' ?>">Đồ luyện tập & Lối sống xanh</a>
        <a href="products.php?category_id=5" class="category-item <?= $category_id === 5 ? 'active' : '' ?>">Góc đồ cũ tái chế lại</a>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div>
                        <?php 
                        // Lấy tên file ảnh từ một trong các cột phổ biến trong DB
                        $img_name = $product['image'] ?? $product['image_url'] ?? $product['thumbnail'] ?? $product['img'] ?? '';

                        // Định nghĩa danh sách các thư mục có thể chứa ảnh
                        $img_src = '';
                        if (!empty($img_name)) {
                            if (file_exists(__DIR__ . '/assets/images/' . $img_name)) {
                                $img_src = 'assets/images/' . $img_name;
                            } elseif (file_exists(__DIR__ . '/uploads/' . $img_name)) {
                                $img_src = 'uploads/' . $img_name;
                            } elseif (file_exists(__DIR__ . '/../uploads/' . $img_name)) {
                                $img_src = '../uploads/' . $img_name;
                            } else {
                                $img_src = $img_name; 
                            }
                        }
                        ?>

                        <?php if (!empty($img_src)): ?>
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                        <?php else: ?>
                            <div class="product-img" style="background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 14px; text-align: center; border-radius: 6px; margin-bottom: 12px;">
                                Chưa có hình ảnh
                            </div>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                    </div>
                    <div>
                        <div class="product-price"><?= number_format($product['price'], 0, ',', '.') ?> VNĐ</div>
                        <a href="product.php?id=<?= $product['id'] ?>" class="btn-detail">Xem chi tiết</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Không tìm thấy sản phẩm nào phù hợp.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// 3. Nhúng Footer
require_once __DIR__ . '/../includes/footer.php';
?>
