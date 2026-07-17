/**
 * DỰ ÁN: PC PRO
 * FILE: product-detail.js
 * CHỨC NĂNG: Xử lý giao diện trang chi tiết (Thumbnail, Nút tăng giảm)
 */

document.addEventListener("DOMContentLoaded", () => {
  // 1. XỬ LÝ ĐỔI ẢNH THUMBNAIL
  const mainImg = document.getElementById("detail-img");
  const thumbnails = document.querySelectorAll(".thumbnail-item");

  if (mainImg && thumbnails.length > 0) {
    thumbnails.forEach((thumb) => {
      thumb.addEventListener("click", function () {
        mainImg.src = this.src; // Đổi ảnh chính
        thumbnails.forEach((t) => t.classList.remove("active")); // Xóa active cũ
        this.classList.add("active"); // Thêm active mới
      });
    });
  }

  // 2. XỬ LÝ NÚT TĂNG GIẢM SỐ LƯỢNG (+ / -)
  // Lưu ý: Input phải có id="quantity_input" để khớp với file products.js
  const quantityInput = document.getElementById("quantity_input");
  const btnPlus = document.querySelector(".btn-qty-plus");
  const btnMinus = document.querySelector(".btn-qty-minus");

  if (quantityInput) {
    const min = parseInt(quantityInput.min || "1", 10);
    const max = parseInt(quantityInput.max || "9999", 10);

    const clamp = (value) => {
      const n = parseInt(value || "1", 10);
      if (Number.isNaN(n)) return min;
      return Math.min(max, Math.max(min, n));
    };

    const syncButtonState = () => {
      const current = clamp(quantityInput.value);
      if (btnMinus) btnMinus.disabled = current <= min;
      if (btnPlus) btnPlus.disabled = current >= max;
    };

    // Nút Cộng
    if (btnPlus) {
      btnPlus.onclick = function () {
        const current = clamp(quantityInput.value);
        quantityInput.value = clamp(current + 1);
        syncButtonState();
      };
    }

    // Nút Trừ
    if (btnMinus) {
      btnMinus.onclick = function () {
        const current = clamp(quantityInput.value);
        quantityInput.value = clamp(current - 1);
        syncButtonState();
      };
    }

    quantityInput.addEventListener("input", () => {
      quantityInput.value = clamp(quantityInput.value);
      syncButtonState();
    });

    syncButtonState();
  }
});
