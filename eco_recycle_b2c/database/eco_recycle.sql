CREATE DATABASE IF NOT EXISTS eco_recycle_b2c CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eco_recycle_b2c;

-- 1. Tạo bảng categories (nếu chưa có)
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Thêm 5 danh mục chính thức
INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Thời trang và phụ kiện tái chế', 'thoi-trang-va-phu-kien-tai-che'),
(2, 'Nội thất và trang trí nhà cửa', 'noi-that-va-trang-tri-nha-cua'),
(3, 'Văn phòng phẩm và quà tặng', 'van-phong-pham-va-qua-tang'),
(4, 'Đồ luyện tập và lối sống xanh', 'do-luyen-tap-va-loi-song-xanh'),
(5, 'Góc đồ cũ tái chế lại', 'goc-do-cu-tai-che-lai');

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  image_url VARCHAR(1000) DEFAULT '',
  status ENUM('active','hidden') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_status (status),
  INDEX idx_products_name (name)
) ENGINE=InnoDB;

INSERT INTO products (name, description, price, stock, image_url, status) VALUES
('Túi tote từ vải tái sử dụng', 'Túi tote tiện dụng, phù hợp đi học, đi làm và mua sắm hằng ngày. Khuyến khích tái sử dụng để giảm túi nhựa dùng một lần.', 89000, 25, 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?auto=format&fit=crop&w=900&q=80', 'active'),
('Chậu cây từ nhựa tái chế', 'Chậu cây nhỏ gọn được giới thiệu như một lựa chọn trang trí góc học tập và ban công. Mỗi chậu góp phần lan tỏa thói quen tái sử dụng vật liệu.', 59000, 18, 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=900&q=80', 'active'),
('Sổ tay giấy tái chế', 'Sổ tay cho ghi chú và lên kế hoạch hằng ngày, phù hợp với học sinh, sinh viên và nhân viên văn phòng yêu thích lối sống xanh.', 45000, 40, 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?auto=format&fit=crop&w=900&q=80', 'active'),
('Bình trang trí từ thủy tinh tái sử dụng', 'Bình trang trí tối giản, có thể dùng làm bình hoa hoặc vật dụng decor trong nhà.', 119000, 10, 'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?auto=format&fit=crop&w=900&q=80', 'active');
