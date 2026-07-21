<?php
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/models/Post.php';
require_once ROOT_PATH . '/app/services/UploadService.php';

class AdminPostController extends Controller
{
    private Post $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

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
        
        $this->renderAdmin('posts/index', [
            'title' => 'Quản lý bài viết',
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $this->renderAdmin('posts/create', [
            'title' => 'Thêm bài viết mới'
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $title = trim($_POST['title'] ?? '');
                $summary = trim($_POST['summary'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $status = $_POST['status'] ?? 'draft';
                $categorySlug = $_POST['category_slug'] ?? 'cong-nghe';
                $postType = $_POST['post_type'] ?? 'news';
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $readingMinutes = !empty($_POST['reading_minutes']) ? (int)$_POST['reading_minutes'] : null;
                
                if (empty($title) || empty($content)) {
                    throw new Exception('Vui lòng nhập đầy đủ tiêu đề và nội dung');
                }

                $image = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image = UploadService::uploadImage($_FILES['image'], 'posts');
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
                    ':author_id' => $_SESSION['user']['id'],
                    ':title' => $title,
                    ':slug' => $slug,
                    ':summary' => $summary,
                    ':content' => $content,
                    ':image' => $image,
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

            } catch (Exception $e) {
                $this->renderAdmin('posts/create', [
                    'title' => 'Thêm bài viết mới',
                    'error' => $e->getMessage()
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

        $this->renderAdmin('posts/edit', [
            'title' => 'Chỉnh sửa bài viết',
            'post' => $post
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = Database::getConnection();
                
                $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $post = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$post) {
                    throw new Exception('Không tìm thấy bài viết');
                }

                $title = trim($_POST['title'] ?? '');
                $summary = trim($_POST['summary'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $status = $_POST['status'] ?? 'draft';
                $categorySlug = $_POST['category_slug'] ?? 'cong-nghe';
                $postType = $_POST['post_type'] ?? 'news';
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $readingMinutes = !empty($_POST['reading_minutes']) ? (int)$_POST['reading_minutes'] : null;

                if (empty($title) || empty($content)) {
                    throw new Exception('Vui lòng nhập đầy đủ tiêu đề và nội dung');
                }

                $image = $post['image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    if ($image) {
                        // Xoá ảnh cũ nếu tồn tại (tìm cả posts/ lẫn news/ cho backward compat)
                        $oldPaths = [
                            ROOT_PATH . '/public/assets/images/posts/' . basename($image),
                            ROOT_PATH . '/public/assets/images/news/' . basename($image),
                        ];
                        foreach ($oldPaths as $oldPath) {
                            if (file_exists($oldPath)) { @unlink($oldPath); break; }
                        }
                    }
                    $image = UploadService::uploadImage($_FILES['image'], 'posts');
                }

                // Không tự động đổi slug khi edit để tránh hỏng SEO (chỉ đổi nếu explicit update slug form - tạm bỏ qua)
                $slug = $post['slug']; 

                $publishedAt = $post['published_at'];
                if ($status === 'published' && empty($publishedAt)) {
                    $publishedAt = date('Y-m-d H:i:s');
                } elseif ($status !== 'published') {
                    // Tùy chọn: clear published_at nếu unpublish. Ở đây giữ nguyên cho history.
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
                    ':image' => $image,
                    ':status' => $status,
                    ':category_slug' => $categorySlug,
                    ':post_type' => $postType,
                    ':is_featured' => $isFeatured,
                    ':reading_minutes' => $readingMinutes,
                    ':published_at' => $publishedAt,
                    ':id' => $id
                ]);

                $_SESSION['success'] = 'Cập nhật bài viết thành công';
                header('Location: ' . url('admin/posts'));
                exit;

            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: ' . url('admin/posts/edit/' . $id));
                exit;
            }
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            
            $stmt = $db->prepare('SELECT image FROM posts WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post) {
                if ($post['image']) {
                    UploadService::deleteImage($post['image'], 'news');
                }
                
                $stmt = $db->prepare('DELETE FROM posts WHERE id = :id');
                $stmt->execute([':id' => $id]);
                
                $_SESSION['success'] = 'Đã xóa bài viết';
            }
            
            header('Location: ' . url('admin/posts'));
            exit;
        }
    }
}
