<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Kiểm tra nếu chưa đăng nhập thì chuyển hướng sang trang login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user =$_SESSION['user'];
$success_msg = '';$error_msg = '';

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email)) {$error_msg = 'Vui lòng điền đầy đủ Họ tên và Email.';
    } else {
        try {
            // Cập nhật CSDL
            $stmt =$pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $email,$phone, $address,$user['id']]);

            // Cập nhật lại Session
            $_SESSION['user']['name'] =$name;
            $_SESSION['user']['email'] =$email;
            $_SESSION['user']['phone'] =$phone;
            $_SESSION['user']['address'] =$address;
            $user =$_SESSION['user'];

            $success_msg = 'Cập nhật thông tin cá nhân thành công!';
        } catch (PDOException $e) {$error_msg = 'Có lỗi xảy ra khi lưu thông tin: ' . $e->getMessage();
        }
    }
}
?>

<!-- Nhúng thư viện Bản đồ Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.profile-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: Arial, sans-serif;
}

.profile-card {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 25px;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #2e7d32;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
}

.profile-info h2 {
    margin: 0 0 5px 0;
    color: #333;
}

.profile-info p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    color: #444;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 15px;
}

/* Style Khung Bản đồ */
#map {
    height: 280px;
    width: 100%;
    border-radius: 8px;
    margin-top: 10px;
    border: 1px solid #ccc;
    z-index: 1;
}

.btn-locate {
    background-color: #17a2b8;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    margin-top: 6px;
}

.btn-save {
    background-color: #2e7d32;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 15px;
}

.btn-save:hover {
    background-color: #1b5e20;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}
</style>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'] ?? $user['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['name'] ?? $user['username'] ?? 'Người dùng') ?></h2>
                <p>Vai trò: <strong><?= ($user['role'] ?? '') === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?></strong></p>
            </div>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name">Họ và tên</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Địa chỉ Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Chưa cập nhật">
            </div>

            <div class="form-group">
                <label for="address">Địa chỉ giao hàng (Nhập tay hoặc kéo ghim trên bản đồ)</label>
                <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Kéo ghim bản đồ để lấy địa chỉ chuẩn">
                <button type="button" class="btn-locate" onclick="getCurrentLocation()">📍 Chọn vị trí hiện tại của tôi</button>
                
                <!-- Khung hiển thị Bản đồ -->
                <div id="map"></div>
            </div>

            <button type="submit" class="btn-save">Lưu thay đổi</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Tọa độ mặc định (TP. Hồ Chí Minh / Phan Thiết)
    let defaultLat = 10.776889;
    let defaultLng = 106.700806;

    // 1. Khởi tạo bản đồ
    const map = L.map('map', {
        center: [defaultLat, defaultLng],
        zoom: 13,
        trackResize: true
    });

    // 2. Nguồn Tile bản đồ Esri (Cực kỳ mượt, sắc nét và không bao giờ bị xám hay đòi Key)
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri'
    }).addTo(map);

    // 3. Khởi tạo Ghim (Marker) kéo thả
    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    // Hàm ép bản đồ tính toán lại kích thước hiển thị
    function fixMapSize() {
        setTimeout(function() {
            map.invalidateSize();
        }, 300);
    }

    // Tự động fix size ngay sau khi trang load xong
    fixMapSize();
    window.addEventListener('load', fixMapSize);

    // Tự động fix size khi di chuột vào vùng bản đồ
    document.getElementById('map').addEventListener('mouseenter', fixMapSize);

    // Hàm chuyển đổi Tọa độ sang Tên địa chỉ
    function updateAddressFromLatLng(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('address').value = data.display_name;
                }
            })
            .catch(err => console.error("Lỗi lấy địa chỉ:", err));
    }

    // Bắt sự kiện kéo thả ghim
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        updateAddressFromLatLng(position.lat, position.lng);
    });

    // Bắt sự kiện click trên bản đồ
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateAddressFromLatLng(e.latlng.lat, e.latlng.lng);
    });

    // Gán sự kiện cho nút định vị vị trí hiện tại
    window.getCurrentLocation = function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
                updateAddressFromLatLng(lat, lng);
                fixMapSize();
            }, () => {
                alert("Không thể lấy vị trí. Vui lòng bật định vị GPS trên trình duyệt.");
            });
        } else {
            alert("Trình duyệt của bạn không hỗ trợ định vị GPS.");
        }
    };
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
