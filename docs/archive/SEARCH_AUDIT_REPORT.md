# SEARCH AUDIT REPORT

## 1. Thông tin chung

- **Tổng số sản phẩm active**: 137
- **Số lượng sản phẩm theo từng danh mục**:
  - Linh Kiện PC (ID: 4): 105 sản phẩm
  - Laptop Gaming (ID: 1): 8 sản phẩm
  - PC Build Sẵn (ID: 3): 8 sản phẩm
  - Laptop Văn Phòng (ID: 2): 8 sản phẩm
  - Gaming Gear (ID: 7): 4 sản phẩm
  - Màn Hình (ID: 5): 4 sản phẩm
- **Danh sách sản phẩm có category sai**: Không có (CSDL đã được import sạch từ file `techpilot.sql` gốc).

## 2. Bí danh danh mục (Aliases) đã hỗ trợ

```php
$categoryAliases = [
    'laptop gaming'     => ['laptop-gaming'],
    'laptop van phong'  => ['laptop-van-phong'],
    'laptop văn phòng'  => ['laptop-van-phong'],
    'máy tính xách tay' => ['laptop-gaming', 'laptop-van-phong'],
    'gaming gear'       => ['gaming-gear'],
    'linh kiện'         => ['linh-kien-pc'],
    'màn hình'          => ['man-hinh'],
    'laptop'            => ['laptop-gaming', 'laptop-van-phong'],
    'máy bộ'            => ['pc-build-san'],
    'lap'               => ['laptop-gaming', 'laptop-van-phong'],
    'pc'                => ['pc-build-san'],
];
```

## 3. So sánh SQL trước và sau khi sửa

### SQL trước khi sửa (Truy vấn cũ)
```sql
-- Dùng OR tràn lan trên description, specs, category name, brand name
SELECT p.*, b.name as brand_name, c.name as category_name
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active'
  AND (
    LOWER(p.name) LIKE :search
    OR LOWER(c.name) LIKE :search
    OR LOWER(b.name) LIKE :search
    OR LOWER(p.short_desc) LIKE :search
    OR LOWER(p.description) LIKE :search
    OR LOWER(p.specs) LIKE :search
  )
```

### SQL sau khi sửa (Truy vấn mới)
```sql
-- Phân tách rõ ràng: keyword chỉ tìm trong products.name & brands.name
-- Thừa hưởng tính điểm tương đồng (scoring) và sắp xếp tối ưu
SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug,
(CASE
    WHEN LOWER(p.name) = :exact_name THEN 100
    WHEN LOWER(p.name) LIKE :starts_name THEN 80
    WHEN LOWER(p.name) LIKE :contains_name THEN 60
    WHEN LOWER(b.name) = :exact_brand THEN 40
    WHEN LOWER(b.name) LIKE :contains_brand THEN 30
    ELSE 0
END) AS search_score
FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active'
  AND (c.slug IN (:catSlugs) OR c.parent_id IN (SELECT id FROM categories WHERE slug IN (:catSlugs))) -- Nếu có alias/danh mục
  AND (LOWER(p.name) LIKE :search_name OR LOWER(b.name) LIKE :search_brand) -- Nếu có keyword còn lại
ORDER BY search_score DESC, p.name ASC
```

## 4. Kết quả kiểm thử (Test Results)

| Từ khóa | Phân loại / Tham số | Số kết quả | Chi tiết sản phẩm (ID, Tên, Category ID) | Trạng thái |
| --- | --- | --- | --- | --- |
| `lap` | Từ khóa / Alias | 16 | [ID: 33] Laptop Gaming Lenovo Legion Pro 5 (Cat ID: 1)<br>[ID: 32] Laptop ASUS Vivobook S 14 (Cat ID: 2)<br>[ID: 18] Dell XPS 13 Plus (Cat ID: 2)<br>[ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 12] LG Gram 16 (Cat ID: 2)<br>[ID: 11] Acer Aspire 5 (Cat ID: 2)<br>[ID: 10] Lenovo IdeaPad Slim 3 (Cat ID: 2)<br>[ID: 9] HP Pavilion 15 (Cat ID: 2)<br>[ID: 8] ASUS Vivobook 15 (Cat ID: 2)<br>[ID: 7] Dell Inspiron 5430 (Cat ID: 2)<br>*(và 6 sản phẩm khác)* | **PASS** |
| `laptop` | Từ khóa / Alias | 16 | [ID: 33] Laptop Gaming Lenovo Legion Pro 5 (Cat ID: 1)<br>[ID: 32] Laptop ASUS Vivobook S 14 (Cat ID: 2)<br>[ID: 18] Dell XPS 13 Plus (Cat ID: 2)<br>[ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 12] LG Gram 16 (Cat ID: 2)<br>[ID: 11] Acer Aspire 5 (Cat ID: 2)<br>[ID: 10] Lenovo IdeaPad Slim 3 (Cat ID: 2)<br>[ID: 9] HP Pavilion 15 (Cat ID: 2)<br>[ID: 8] ASUS Vivobook 15 (Cat ID: 2)<br>[ID: 7] Dell Inspiron 5430 (Cat ID: 2)<br>*(và 6 sản phẩm khác)* | **PASS** |
| `laptop gaming` | Từ khóa / Alias | 8 | [ID: 33] Laptop Gaming Lenovo Legion Pro 5 (Cat ID: 1)<br>[ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 6] Dell G16 7630 (Cat ID: 1)<br>[ID: 5] HP Omen 16 (Cat ID: 1)<br>[ID: 4] Lenovo Legion Pro 5i (Cat ID: 1)<br>[ID: 3] Acer Predator Helios Neo 16 (Cat ID: 1)<br>[ID: 2] MSI Vector GP68 HX (Cat ID: 1)<br>[ID: 1] ASUS ROG Zephyrus G16 (Cat ID: 1) | **PASS** |
| `máy tính xách tay` | Từ khóa / Alias | 16 | [ID: 33] Laptop Gaming Lenovo Legion Pro 5 (Cat ID: 1)<br>[ID: 32] Laptop ASUS Vivobook S 14 (Cat ID: 2)<br>[ID: 18] Dell XPS 13 Plus (Cat ID: 2)<br>[ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 12] LG Gram 16 (Cat ID: 2)<br>[ID: 11] Acer Aspire 5 (Cat ID: 2)<br>[ID: 10] Lenovo IdeaPad Slim 3 (Cat ID: 2)<br>[ID: 9] HP Pavilion 15 (Cat ID: 2)<br>[ID: 8] ASUS Vivobook 15 (Cat ID: 2)<br>[ID: 7] Dell Inspiron 5430 (Cat ID: 2)<br>*(và 6 sản phẩm khác)* | **PASS** |
| `asus laptop` | Từ khóa / Alias | 4 | [ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 1] ASUS ROG Zephyrus G16 (Cat ID: 1)<br>[ID: 8] ASUS Vivobook 15 (Cat ID: 2)<br>[ID: 32] Laptop ASUS Vivobook S 14 (Cat ID: 2) | **PASS** |
| `hp laptop` | Từ khóa / Alias | 2 | [ID: 5] HP Omen 16 (Cat ID: 1)<br>[ID: 9] HP Pavilion 15 (Cat ID: 2) | **PASS** |
| `dell g16` | Từ khóa / Alias | 1 | [ID: 6] Dell G16 7630 (Cat ID: 1) | **PASS** |
| `zephyrus` | Từ khóa / Alias | 1 | [ID: 1] ASUS ROG Zephyrus G16 (Cat ID: 1) | **PASS** |
| `pc` | Từ khóa / Alias | 8 | [ID: 34] PC All-in-One ASUS A3402 (Cat ID: 3)<br>[ID: 24] PC Office Giá Rẻ (Cat ID: 3)<br>[ID: 23] PC Gaming AMD All-Red (Cat ID: 3)<br>[ID: 22] PC Workstation Đồ Họa (Cat ID: 3)<br>[ID: 21] PC TechPilot High-End Gaming (Cat ID: 3)<br>[ID: 20] PC TechPilot Advanced Gaming (Cat ID: 3)<br>[ID: 19] PC TechPilot Basic Gaming (Cat ID: 3)<br>[ID: 14] PC Gaming TechPilot Extreme V1 (Cat ID: 3) | **PASS** |
| `pcie5` | Từ khóa / Alias | 1 | [ID: 103] Nguồn máy tính MSI MAG A750GL PCIe5 750W (Cat ID: 4) | **PASS** |
| `nguồn` | Từ khóa / Alias | 10 | [ID: 98] Nguồn máy tính Corsair CV450 450W (Cat ID: 4)<br>[ID: 101] Nguồn máy tính Corsair CV650 650W (Cat ID: 4)<br>[ID: 104] Nguồn máy tính Corsair RM750e 750W (Cat ID: 4)<br>[ID: 106] Nguồn máy tính Corsair RM850x 850W (Cat ID: 4)<br>[ID: 99] Nguồn máy tính Deepcool PF550 550W (Cat ID: 4)<br>[ID: 102] Nguồn máy tính Deepcool PK750D 750W (Cat ID: 4)<br>[ID: 100] Nguồn máy tính MSI MAG A650BN 650W (Cat ID: 4)<br>[ID: 103] Nguồn máy tính MSI MAG A750GL PCIe5 750W (Cat ID: 4)<br>[ID: 107] Nguồn máy tính Seasonic Focus GX-1000 1000W (Cat ID: 4)<br>[ID: 105] Nguồn máy tính Seasonic Focus GX-850 850W (Cat ID: 4) | **PASS** |
| `i3` | Từ khóa / Alias | 1 | [ID: 38] CPU Intel Core i3-12100 (Cat ID: 4) | **PASS** |
| `rtx 4060` | Từ khóa / Alias | 2 | [ID: 90] Card màn hình ASUS Dual RTX 4060 Ti OC (Cat ID: 4)<br>[ID: 89] Card màn hình MSI RTX 4060 Ventus 2X OC (Cat ID: 4) | **PASS** |
| `laptop&cat=laptop-gaming` | `cat=laptop-gaming` | 8 | [ID: 33] Laptop Gaming Lenovo Legion Pro 5 (Cat ID: 1)<br>[ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 6] Dell G16 7630 (Cat ID: 1)<br>[ID: 5] HP Omen 16 (Cat ID: 1)<br>[ID: 4] Lenovo Legion Pro 5i (Cat ID: 1)<br>[ID: 3] Acer Predator Helios Neo 16 (Cat ID: 1)<br>[ID: 2] MSI Vector GP68 HX (Cat ID: 1)<br>[ID: 1] ASUS ROG Zephyrus G16 (Cat ID: 1) | **PASS** |
| `asus&cat=laptop-gaming` | `cat=laptop-gaming` | 2 | [ID: 13] ASUS ROG Ally X (Cat ID: 1)<br>[ID: 1] ASUS ROG Zephyrus G16 (Cat ID: 1) | **PASS** |
| `chuỗi không tồn tại` | Từ khóa / Alias | 0 | *Không tìm thấy* | **PASS** |