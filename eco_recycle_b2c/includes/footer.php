</main> <!-- Đóng tag main container từ header -->

<footer class="site-footer">
  <div class="footer-container">
    <div class="footer-grid">
      
      <!-- Cột 1: Thương hiệu & Slogan -->
      <div class="footer-col brand-col">
        <a href="/eco_recycle_b2c/public/index.php" class="footer-logo">
          <span class="logo-icon">♻</span> EcoRecycle
        </a>
        
        <p class="footer-tagline">
          Trao vòng đời mới cho vật liệu – mua sắm xanh hơn mỗi ngày.
        </p>

        <div class="footer-contact-info">
          <p><i class="fa-solid fa-location-dot"></i> TDH Giao Thông Vận Tải, P.25, Q. Bình Thạnh, TP.HCM</p>
          <p><i class="fa-solid fa-phone"></i> 1900 6789 - 0909 123 456</p>
          <p><i class="fa-solid fa-envelope"></i> cskh@ecorecycle.vn</p>
        </div>
      </div>

      <!-- Cột 2: Danh mục -->
      <div class="footer-col">
        <h4 class="footer-title">Danh Mục</h4>
        <ul class="footer-links">
          <li><a href="/eco_recycle_b2c/public/products.php?category_id=1">Thời trang tái chế</a></li>
          <li><a href="/eco_recycle_b2c/public/products.php?category_id=2">Nội thất & Trang trí</a></li>
          <li><a href="/eco_recycle_b2c/public/products.php?category_id=3">Văn phòng phẩm xanh</a></li>
          <li><a href="/eco_recycle_b2c/public/products.php?category_id=4">Lối sống bền vững</a></li>
          <li><a href="/eco_recycle_b2c/public/products.php?category_id=5">Góc đồ cũ tái chế</a></li>
        </ul>
      </div>

      <!-- Cột 3: Hỗ trợ -->
      <div class="footer-col">
        <h4 class="footer-title">Hỗ Trợ Khách Hàng</h4>
        <ul class="footer-links">
          <li><a href="#">Chính sách đổi trả & Hoàn tiền</a></li>
          <li><a href="#">Chính sách bảo mật thông tin</a></li>
          <li><a href="#">Điều khoản dịch vụ</a></li>
          <li><a href="#">Hướng dẫn mua hàng</a></li>
          <li><a href="#">Quy trình thu gom vật liệu</a></li>
        </ul>
      </div>

      <!-- Cột 4: Kết nối -->
      <div class="footer-col">
        <h4 class="footer-title">Kết Nối</h4>
        <p class="newsletter-desc">Đăng ký để nhận thông tin mới nhất.</p>
        <form class="footer-newsletter-form" action="#" method="POST" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký!');">
          <input type="email" placeholder="Nhập email..." required class="newsletter-input">
          <button type="submit" class="newsletter-btn"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div class="footer-socials">
          <a href="#" class="social-icon" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" class="social-icon" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="social-icon" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
          <a href="#" class="social-icon" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>

    </div>

    <!-- Thanh Bản quyền phía dưới -->
    <div class="footer-bottom">
      <p>© 2026 <strong>EcoRecycle</strong>. Dự án thương mại điện tử B2C.</p>
      <div class="payment-badges">
        <span><i class="fa-brands fa-cc-visa"></i></span>
        <span><i class="fa-brands fa-cc-mastercard"></i></span>
        <span><i class="fa-solid fa-qrcode"></i> MoMo / QR</span>
      </div>
    </div>
  </div>
</footer>

<style>
/* CSS Tinh Chỉnh Cột 1 Siêu Chuẩn & Sang Trọng */
.site-footer {
  background-color: #0d2112;
  color: #e0e0e0;
  padding: 42px 0 20px 0;
  margin-top: 50px;
  font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  box-sizing: border-box;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.35fr 0.85fr 1fr 1fr;
  gap: 36px;
  padding-bottom: 28px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

/* Căn lề trái thẳng tắp cho Cột 1 */
.brand-col {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
}

.footer-logo {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.logo-icon {
  color: #4caf50;
  font-size: 22px;
}

/* Kiểu dáng khối câu Tagline sống xanh */
.footer-tagline {
  font-size: 13px;
  line-height: 1.5;
  color: #a4c2ab;
  margin: 0 0 16px 0;
  padding-left: 10px;
  border-left: 2px solid #2e7d32;
  max-width: 310px;
  text-align: left;
}

.footer-contact-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

.footer-contact-info p {
  font-size: 12.5px;
  color: #c0d4c4;
  margin: 0;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  line-height: 1.4;
  text-align: left;
}

.footer-contact-info i {
  color: #4caf50;
  font-size: 13px;
  margin-top: 2px;
  flex-shrink: 0;
  width: 14px;
  text-align: center;
}

.footer-title {
  color: #ffffff;
  font-size: 14.5px;
  font-weight: 700;
  margin: 0 0 14px 0;
  letter-spacing: 0.2px;
  text-align: left;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
}

.footer-links li {
  margin-bottom: 8px;
}

.footer-links a {
  color: #a0b8a5;
  text-decoration: none;
  font-size: 13px;
  transition: color 0.2s ease;
  display: inline-block;
}

.footer-links a:hover {
  color: #ffffff;
}

.newsletter-desc {
  font-size: 12.5px;
  color: #a0b8a5;
  margin: 0 0 10px 0;
  text-align: left;
}

.footer-newsletter-form {
  display: flex;
  margin-bottom: 14px;
  max-width: 260px;
}

.newsletter-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 6px 0 0 6px;
  outline: none;
  background-color: rgba(255, 255, 255, 0.04);
  color: #fff;
  font-size: 12.5px;
}

.newsletter-input::placeholder {
  color: #667d6c;
}

.newsletter-btn {
  background-color: #2e7d32;
  color: white;
  border: none;
  padding: 0 14px;
  border-radius: 0 6px 6px 0;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.2s;
}

.newsletter-btn:hover {
  background-color: #388e3c;
}

.footer-socials {
  display: flex;
  gap: 8px;
}

.social-icon {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background-color: rgba(255, 255, 255, 0.05);
  color: #c0d4c4;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.2s ease;
  font-size: 13px;
}

.social-icon:hover {
  background-color: #2e7d32;
  color: #ffffff;
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 18px;
  font-size: 12px;
  color: #728a77;
}

.payment-badges {
  display: flex;
  gap: 12px;
  align-items: center;
  font-size: 15px;
}

@media (max-width: 900px) {
  .footer-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 550px) {
  .footer-grid {
    grid-template-columns: 1fr;
  }
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }
}
</style>

</body>
</html>
