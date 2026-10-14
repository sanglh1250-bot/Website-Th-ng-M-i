# EcoRecycle B2C – Website thương mại tái chế rác thải

Website mẫu PHP thuần + MySQL dành cho dự án thương mại B2C về thu gom/tái chế rác thải.
Khách hàng có thể đăng ký, đăng nhập, xem sản phẩm tái chế; quản trị viên có thể thêm, sửa, xóa sản phẩm.

## Công nghệ
- PHP 8+
- MySQL / MariaDB
- HTML, CSS, JavaScript cơ bản
- PDO prepared statements, `password_hash()` và `password_verify()`

## Cài đặt bằng XAMPP
1. Giải nén thư mục `eco_recycle_b2c` vào `C:\xampp\htdocs\`.
2. Mở XAMPP, bật **Apache** và **MySQL**.
3. Mở `http://localhost/phpmyadmin`, tạo/import database bằng file `database/eco_recycle.sql`.
4. Mở `config/database.php` và kiểm tra thông tin kết nối. Cấu hình mặc định XAMPP thường là user `root`, password để trống.
5. Truy cập `http://localhost/eco_recycle_b2c/public/`.

## Tạo tài khoản admin
1. Đăng ký tài khoản bình thường trên website.
2. Trong phpMyAdmin, mở database `eco_recycle_b2c`, bảng `users`.
3. Tìm email vừa đăng ký và đổi cột `role` từ `customer` thành `admin`.
4. Đăng xuất rồi đăng nhập lại. Mục **Quản trị** sẽ xuất hiện.

## Các chức năng có sẵn
- Trang chủ / danh sách sản phẩm
- Đăng ký, đăng nhập, đăng xuất
- Phân quyền `customer` và `admin`
- Admin: thêm, sửa, xóa sản phẩm
- Tìm kiếm sản phẩm đơn giản
- Trang chi tiết sản phẩm
- Giao diện responsive cơ bản

## Cấu trúc thư mục
- `config/`: cấu hình kết nối database
- `database/`: SQL tạo database và dữ liệu mẫu
- `includes/`: hàm dùng chung, header/footer
- `public/`: các trang người dùng và tài nguyên tĩnh
- `admin/`: trang quản trị sản phẩm
- `uploads/`: thư mục dành cho ảnh sản phẩm (hiện dùng URL ảnh để đơn giản hóa)

Lưu ý: Đây là bộ khung học tập. Trước khi triển khai thật cần bổ sung CSRF protection, giới hạn đăng nhập, kiểm tra ảnh tải lên, nhật ký quản trị, giỏ hàng/đặt hàng và quy trình thanh toán.
