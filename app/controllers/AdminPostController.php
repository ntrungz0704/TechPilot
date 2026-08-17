<?php
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/models/Post.php';
require_once ROOT_PATH . '/app/services/UploadService.php';
require_once ROOT_PATH . '/app/services/PostPublishingValidator.php';

class AdminPostController extends Controller
{
    private Post $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

    // =========================================================================
    // ===== Chức năng Admin Quản lý Bài viết Tin tức (UC33) =====
    // =========================================================================
    public function index()
    {
        $this->requireAdmin();
        $db = Database::getConnection();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $stmt = $db->query('SELECT COUNT(*) FROM posts');
        $total = $stmt->fetchColumn();
        
        $stmt = $db->prepare('
            SELECT p.*, u.full_name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC 
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->renderAdmin('admin/posts/index', [
            'pageTitle' => 'Quản lý bài viết',
            'activeMenu' => 'posts',
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $this->renderAdmin('admin/posts/create', [
            'pageTitle' => 'Thêm bài viết mới',
            'activeMenu' => 'posts'
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Phiên làm việc hết hạn, vui lòng thử lại.';
                header('Location: ' . url('admin/posts'));
                exit;
            }

            $validation = PostPublishingValidator::validate($_POST);
            if (!$validation['valid']) {
                $this->renderAdmin('admin/posts/create', [
                    'pageTitle' => 'Thêm bài viết mới',
                    'activeMenu' => 'posts',
                    'errors' => $validation['errors'],
                    'post' => [
                        'title' => $_POST['title'] ?? '',
                        'summary' => $_POST['summary'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'status' => $_POST['status'] ?? 'draft',
                        'category_slug' => $_POST['category_slug'] ?? 'cong-nghe',
                        'post_type' => $_POST['post_type'] ?? 'news',
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                        'reading_minutes' => $_POST['reading_minutes'] ?? '',
                    ]
                ]);
                return;
            }

            $uploadedImage = null;
            try {
                $title = trim($_POST['title'] ?? '');
                $summary = trim($_POST['summary'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $status = $_POST['status'] ?? 'draft';
                $categorySlug = $_POST['category_slug'] ?? 'cong-nghe';
                $postType = $_POST['post_type'] ?? 'news';
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $readingMinutes = !empty($_POST['reading_minutes']) ? (int)$_POST['reading_minutes'] : null;

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadedImage = UploadService::uploadImage($_FILES['image'], 'posts');
                }

                $slug = Post::slugify($title);
                $originalSlug = $slug;
                $counter = 1;
                while ($this->postModel->isSlugExists($slug)) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

                $db = Database::getConnection();
                $sql = 'INSERT INTO posts (author_id, title, slug, summary, content, image, status, category_slug, post_type, is_featured, reading_minutes, published_at) 
                        VALUES (:author_id, :title, :slug, :summary, :content, :image, :status, :category_slug, :post_type, :is_featured, :reading_minutes, :published_at)';
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':author_id' => $_SESSION['user']['id'] ?? null,
                    ':title' => $title,
                    ':slug' => $slug,
                    ':summary' => $summary,
                    ':content' => $content,
                    ':image' => $uploadedImage,
                    ':status' => $status,
                    ':category_slug' => $categorySlug,
                    ':post_type' => $postType,
                    ':is_featured' => $isFeatured,
                    ':reading_minutes' => $readingMinutes,
                    ':published_at' => $publishedAt
                ]);

                $_SESSION['success'] = 'Thêm bài viết thành công';
                header('Location: ' . url('admin/posts'));
                exit;

            } catch (Throwable $e) {
                if ($uploadedImage) {
                    $cleaned = UploadService::deleteImage($uploadedImage, 'posts');
                    if (!$cleaned) {
                        error_log('[AdminPostController::store] Failed to clean up uploaded image on insert failure: ' . $uploadedImage);
                    }
                }

                $this->renderAdmin('admin/posts/create', [
                    'pageTitle' => 'Thêm bài viết mới',
                    'activeMenu' => 'posts',
                    'error' => $e->getMessage(),
                    'post' => [
                        'title' => $_POST['title'] ?? '',
                        'summary' => $_POST['summary'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'status' => $_POST['status'] ?? 'draft',
                        'category_slug' => $_POST['category_slug'] ?? 'cong-nghe',
                        'post_type' => $_POST['post_type'] ?? 'news',
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                        'reading_minutes' => $_POST['reading_minutes'] ?? '',
                    ]
                ]);
            }
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết';
            header('Location: ' . url('admin/posts'));
            exit;
        }

        $this->renderAdmin('admin/posts/edit', [
            'pageTitle' => 'Chỉnh sửa bài viết',
            'activeMenu' => 'posts',
            'post' => $post
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Phiên làm việc hết hạn, vui lòng thử lại.';
                header('Location: ' . url('admin/posts'));
                exit;
            }

            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                $_SESSION['error'] = 'Không tìm thấy bài viết';
                header('Location: ' . url('admin/posts'));
                exit;
            }

            $validation = PostPublishingValidator::validate($_POST);
            if (!$validation['valid']) {
                $this->renderAdmin('admin/posts/edit', [
                    'pageTitle' => 'Chỉnh sửa bài viết',
                    'activeMenu' => 'posts',
                    'errors' => $validation['errors'],
                    'post' => array_merge($post, [
                        'id' => $id,
                        'title' => $_POST['title'] ?? '',
                        'summary' => $_POST['summary'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'status' => $_POST['status'] ?? 'draft',
                        'category_slug' => $_POST['category_slug'] ?? 'cong-nghe',
                        'post_type' => $_POST['post_type'] ?? 'news',
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                        'reading_minutes' => $_POST['reading_minutes'] ?? '',
                    ])
                ]);
                return;
            }

            $newImage = null;
            try {
                $title = trim($_POST['title'] ?? '');
                $summary = trim($_POST['summary'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $status = $_POST['status'] ?? 'draft';
                $categorySlug = $_POST['category_slug'] ?? 'cong-nghe';
                $postType = $_POST['post_type'] ?? 'news';
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $readingMinutes = !empty($_POST['reading_minutes']) ? (int)$_POST['reading_minutes'] : null;

                $imageToSave = $post['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $newImage = UploadService::uploadImage($_FILES['image'], 'posts');
                    $imageToSave = $newImage;
                }

                $publishedAt = $post['published_at'];
                if ($status === 'published' && empty($publishedAt)) {
                    $publishedAt = date('Y-m-d H:i:s');
                }

                $sql = 'UPDATE posts SET 
                        title = :title, 
                        summary = :summary, 
                        content = :content, 
                        image = :image, 
                        status = :status,
                        category_slug = :category_slug,
                        post_type = :post_type,
                        is_featured = :is_featured,
                        reading_minutes = :reading_minutes,
                        published_at = :published_at
                        WHERE id = :id';
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':title' => $title,
                    ':summary' => $summary,
                    ':content' => $content,
                    ':image' => $imageToSave,
                    ':status' => $status,
                    ':category_slug' => $categorySlug,
                    ':post_type' => $postType,
                    ':is_featured' => $isFeatured,
                    ':reading_minutes' => $readingMinutes,
                    ':published_at' => $publishedAt,
                    ':id' => $id
                ]);

            } catch (Throwable $e) {
                // If upload or DB update failed, cleanup $newImage if created
                if ($newImage) {
                    $cleaned = UploadService::deleteImage($newImage, 'posts');
                    if (!$cleaned) {
                        error_log('[AdminPostController::update] Failed to clean up new image on update failure: ' . $newImage);
                    }
                }

                $this->renderAdmin('admin/posts/edit', [
                    'pageTitle' => 'Chỉnh sửa bài viết',
                    'activeMenu' => 'posts',
                    'error' => $e->getMessage(),
                    'post' => array_merge($post, [
                        'id' => $id,
                        'title' => $_POST['title'] ?? '',
                        'summary' => $_POST['summary'] ?? '',
                        'content' => $_POST['content'] ?? '',
                        'status' => $_POST['status'] ?? 'draft',
                        'category_slug' => $_POST['category_slug'] ?? 'cong-nghe',
                        'post_type' => $_POST['post_type'] ?? 'news',
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                        'reading_minutes' => $_POST['reading_minutes'] ?? '',
                    ])
                ]);
                return;
            }

            // DB update succeeded! Best-effort delete old image OUTSIDE main try/catch block
            if ($newImage && !empty($post['image']) && $post['image'] !== $newImage) {
                try {
                    $cleaned = UploadService::deleteImage($post['image'], 'posts');
                    if (!$cleaned) {
                        error_log('[AdminPostController::update] Best-effort deletion returned false for old image: ' . $post['image']);
                    }
                } catch (Throwable $cleanupError) {
                    error_log('[AdminPostController::update] Exception during old image deletion: ' . $cleanupError->getMessage());
                }
            }

            $_SESSION['success'] = 'Cập nhật bài viết thành công';
            header('Location: ' . url('admin/posts'));
            exit;
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Phiên làm việc hết hạn, vui lòng thử lại.';
                header('Location: ' . url('admin/posts'));
                exit;
            }

            try {
                $db = Database::getConnection();
                $fetchStmt = $db->prepare('SELECT image FROM posts WHERE id = :id LIMIT 1');
                $fetchStmt->execute([':id' => $id]);
                $post = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                /* DELETE FROM posts WHERE id = :id */
                $stmt = $db->prepare("UPDATE posts SET status = 'hidden' WHERE id = :id");
                if ($stmt->execute([':id' => $id])) {
                    $_SESSION['warning'] = 'Hệ thống đã khóa tính năng xóa cứng. Đã tự động chuyển trạng thái bài viết sang Tạm ẩn.';
                    $cleaned = UploadService::deleteImage($post['image'] ?? '');
                    if (!$cleaned) {
                        error_log("Cảnh báo: Không thể dọn dẹp ảnh cho bài viết ID {$id}");
                    }
                } else {
                    $_SESSION['error'] = 'Không thể ẩn bài viết.';
                }
            } catch (Throwable $e) {
                $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            }
            
            header('Location: ' . url('admin/posts'));
            exit;
        }
    }

    /** Toggle trạng thái Bật/Tắt bài viết (POST /admin/posts/toggle-status/{id}) */
    public function toggleStatus(string $id = ''): void
    {
        $adminUser = $this->requireApiAdmin();
        $id = (int)$id;

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            exit;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if (!$db) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL.']);
            exit;
        }

        $stmt = $db->prepare("SELECT status, title FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $pst = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pst) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại.']);
            exit;
        }

        $newStatus = ($pst['status'] === 'published') ? 'hidden' : 'published';
        $upStmt = $db->prepare("UPDATE posts SET status = :status WHERE id = :id");
        $upStmt->execute([':status' => $newStatus, ':id' => $id]);

        echo json_encode([
            'success'      => true,
            'message'      => 'Đã ' . ($newStatus === 'published' ? 'xuất bản' : 'tạm ẩn') . ' bài viết ' . $pst['title'],
            'new_status'   => $newStatus,
            'status_label' => $newStatus === 'published' ? 'Đã đăng' : 'Ẩn'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Admin Quản lý Bài viết (UC33) =====
    // =========================================================================
}
