/**
 * Global cart actions for all client pages.
 * Cung cấp hàm thêm vào giỏ và mua ngay toàn cục cho ứng dụng.
 */
const CART_ENDPOINT = `${window.BASE_URL || ""}index.php?url=add-to-cart`;

// Xử lý dữ liệu trả về từ server, đề phòng server quăng lỗi parse JSON
const parseCartResponse = async (res) => {
  const raw = await res.text();
  try {
    return JSON.parse(raw);
  } catch (err) {
    throw new Error(`Phản hồi không hợp lệ từ server: ${raw.slice(0, 140)}`);
  }
};

// Hàm wrapper để dùng Toast nếu có, không thì dùng alert mặc định
const notifyCartError = (message) => {
  if (typeof window.showToast === "function") {
    window.showToast({
      title: "Lỗi",
      message,
      type: "error",
    });
    return;
  }
  alert("Lỗi: " + message);
};

// Cập nhật con số hiển thị trên biểu tượng giỏ hàng (header/menu)
const updateCartBadges = (count) => {
  const nextCount = Number(count ?? 0).toString();
  const badges = document.querySelectorAll(
    ".cart-badge-pill, .cart-count, [data-cart-count]",
  );
  badges.forEach((b) => {
    b.innerText = nextCount;
    b.textContent = nextCount;
  });
};

/**
 * Thêm sản phẩm vào giỏ hàng
 * Gửi request dạng FormData lên controller.
 * @param {string|number} productId - ID của sản phẩm
 */
window.addToCart = function (productId) {
  if (!productId) {
    notifyCartError("Thiếu ID sản phẩm.");
    return;
  }

  const qtyInput = document.getElementById("quantity_input");
  let quantity = 1;
  if (qtyInput) {
    quantity = Math.max(1, parseInt(qtyInput.value, 10) || 1);
  }

  const formData = new FormData();
  formData.append("id", productId);
  formData.append("qty", quantity);
  formData.append("action", "add");

  fetch(CART_ENDPOINT, {
    method: "POST",
    body: formData,
    credentials: "same-origin",
  })
    .then((res) => parseCartResponse(res))
    .then((data) => {
      if (data.status === "success") {
        updateCartBadges(data.total_items ?? data.total_qty ?? 0);

        if (typeof window.showToast === "function") {
          window.showToast({
            title: "Thành công",
            message: "Đã thêm sản phẩm vào giỏ!",
            type: "success",
          });
        }
        return;
      }

      notifyCartError(data.message || "Không thể thêm sản phẩm vào giỏ.");
    })
    .catch((err) => {
      console.error("Lỗi kết nối:", err);
      notifyCartError(err.message || "Lỗi kết nối khi thêm vào giỏ.");
    });
};

/**
 * Mua ngay: Thêm sản phẩm vào giỏ hàng sau đó chuyển hướng sang trang thanh toán luôn.
 *
 * @param {string|number} productId - ID của sản phẩm
 */
window.buyNow = function (productId) {
  if (!productId) {
    notifyCartError("Thiếu ID sản phẩm.");
    return;
  }

  const qtyInput = document.getElementById("quantity_input");
  let quantity = 1;
  if (qtyInput) {
    quantity = Math.max(1, parseInt(qtyInput.value, 10) || 1);
  }

  const formData = new FormData();
  formData.append("id", productId);
  formData.append("qty", quantity);
  formData.append("action", "add");

  fetch(CART_ENDPOINT, {
    method: "POST",
    body: formData,
    credentials: "same-origin",
  })
    .then((res) => parseCartResponse(res))
    .then((data) => {
      if (data.status === "success") {
        updateCartBadges(data.total_items ?? data.total_qty ?? 0);
        window.location.href = `${window.BASE_URL || ""}index.php?url=payment`;
        return;
      }

      notifyCartError(data.message || "Không thể mua ngay lúc này.");
    })
    .catch((err) => {
      console.error("Lỗi mua ngay:", err);
      notifyCartError(err.message || "Lỗi kết nối khi mua ngay.");
    });
};
