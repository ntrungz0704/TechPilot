/**
 * FILE: shopping-cart.js
 * CHUC NANG: Tang/giam/xoa san pham trong gio hang (AJAX + Session)
 */

document.addEventListener("DOMContentLoaded", () => {
  const cartTable = document.querySelector(".cart-table");
  if (!cartTable) return;

  const updateBadge = (data) => {
    const nextCount = Number(data.total_items ?? data.total_qty ?? 0).toString();
    const badges = document.querySelectorAll(
      ".cart-badge-pill, .cart-count, [data-cart-count]",
    );
    badges.forEach((b) => (b.textContent = nextCount));
  };

  const requestCart = (payload) => {
    const formData = new FormData();
    Object.keys(payload).forEach((k) => formData.append(k, payload[k]));

    return fetch("index.php?url=add-to-cart", {
      method: "POST",
      body: formData,
    }).then((res) => res.json());
  };

  cartTable.addEventListener("click", async (e) => {
    const trigger = e.target.closest("button");
    if (!trigger) return;

    const row = trigger.closest("tr[data-id]");
    if (!row) return;
    const id = row.dataset.id;

    try {
      let data = null;

      if (trigger.classList.contains("btn-increase")) {
        data = await requestCart({ id, action: "update", qty: 1, mode: "delta" });
      }

      if (trigger.classList.contains("btn-decrease")) {
        data = await requestCart({ id, action: "update", qty: -1, mode: "delta" });
      }

      if (trigger.classList.contains("btn-remove")) {
        if (!window.confirm("Ban co chac chan muon xoa san pham nay?")) return;
        data = await requestCart({ id, action: "remove" });
      }

      if (data && data.status === "success") {
        updateBadge(data);
        window.location.reload();
        return;
      }

      if (data && data.status === "error") {
        if (typeof window.showToast === "function") {
          window.showToast({
            title: "Khong cap nhat duoc",
            message: data.message || "Khong the cap nhat gio hang luc nay.",
            type: "warning",
          });
        } else {
          window.alert(data.message || "Khong the cap nhat gio hang luc nay.");
        }
      }
    } catch (err) {
      if (typeof window.showToast === "function") {
        window.showToast({
          title: "Loi ket noi",
          message: "Khong the cap nhat gio hang luc nay.",
          type: "error",
        });
      } else {
        window.alert("Khong the cap nhat gio hang luc nay.");
      }
    }
  });
});
