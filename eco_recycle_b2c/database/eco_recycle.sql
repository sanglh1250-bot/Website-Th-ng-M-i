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

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `receiver_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` VARCHAR(300) NOT NULL,
  `note` VARCHAR(300) NOT NULL DEFAULT '',
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_orders_user` (`user_id`, `created_at`),
  INDEX `idx_orders_status` (`status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(180) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  INDEX `idx_items_order` (`order_id`),
  INDEX `idx_items_product` (`product_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_review_user_product` (`product_id`, `user_id`),
  INDEX `idx_reviews_product` (`product_id`, `created_at`),
  INDEX `fk_reviews_user` (`user_id`),
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
