// Chỗ đặt các tương tác JavaScript phía trình duyệt.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[onsubmit]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const handler = form.getAttribute('onsubmit');
      if (handler && handler.includes('confirm(')) {
        // Xác nhận xóa được giữ ở inline handler trong trang quản trị.
      }
    });
  });
});
