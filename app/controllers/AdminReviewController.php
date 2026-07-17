<?php

class AdminReviewController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $rating = trim($_GET['rating'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $reviews = [];
        $limit = 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $totalReviews = 0;

        if ($db) {
            $sql = 'SELECT r.*, p.name as product_name, p.slug as product_slug
                    FROM reviews r
                    LEFT JOIN products p ON r.product_id = p.id
                    WHERE 1=1';
            $countSql = 'SELECT COUNT(*) FROM reviews r WHERE 1=1';
            $params = [];

            if ($search !== '') {
                $sql .= ' AND (r.reviewer_name LIKE :search OR r.comment LIKE :search)';
                $countSql .= ' AND (r.reviewer_name LIKE :search OR r.comment LIKE :search)';
                $params[':search'] = '%' . $search . '%';
            }

            if ($rating !== '') {
                $sql .= ' AND r.rating = :rating';
                $countSql .= ' AND r.rating = :rating';
                $params[':rating'] = (int)$rating;
            }

            if ($status !== '') {
                $sql .= ' AND r.status = :status';
                $countSql .= ' AND r.status = :status';
                $params[':status'] = $status;
            }

            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $totalReviews = (int)$countStmt->fetchColumn();

            $sql .= ' ORDER BY r.id DESC LIMIT :limit OFFSET :offset';
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $totalPages = ceil($totalReviews / $limit);

        $this->renderAdmin('admin/reviews/index', [
            'pageTitle'    => 'Kiểm duyệt đánh giá sản phẩm',
            'activeMenu'   => 'reviews',
            'reviews'      => $reviews,
            'search'       => $search,
            'rating'       => $rating,
            'status'       => $status,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'totalReviews' => $totalReviews
        ]);
    }

    public function approve(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/reviews');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('UPDATE reviews SET status = \'approved\' WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Đã phê duyệt hiển thị đánh giá thành công!');
            } else {
                flash('error', 'Không thể phê duyệt đánh giá.');
            }
        }

        $this->redirect('admin/reviews');
    }

    public function hide(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/reviews');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('UPDATE reviews SET status = \'hidden\' WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Đã ẩn đánh giá khỏi storefront thành công!');
            } else {
                flash('error', 'Không thể ẩn đánh giá.');
            }
        }

        $this->redirect('admin/reviews');
    }
}
