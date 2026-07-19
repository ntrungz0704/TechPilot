<?php

class AdminPostController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $posts = [];
        if ($db) {
            $stmt = $db->query('SELECT p.*, u.full_name as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.id DESC');
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->renderAdmin('admin/posts/index', [
            'pageTitle'  => 'Quản lý tin tức & bài viết',
            'activeMenu' => 'posts',
            'posts'      => $posts
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/posts/create', [
            'pageTitle'  => 'Viết bài viết mới',
            'activeMenu' => 'posts'
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin/posts');
        }

        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = trim($_POST['status'] ?? 'published');

        if ($title === '') {
            flash('error', 'Vui lòng nhập tiêu đề bài viết.');
            $this->redirect('admin/posts/create');
            return;
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-')) . '-' . time();
        $authorId = (int)($_SESSION['user']['id'] ?? null);

        // Xử lý upload ảnh đại diện bài viết
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $image = UploadService::uploadImage($_FILES['image'], 'posts');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload ảnh bài viết: ' . $e->getMessage());
                $this->redirect('admin/posts/create');
                return;
            }
        } else {
            $image = trim($_POST['image_text'] ?? '');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('INSERT INTO posts (author_id, title, slug, summary, content, image, status, published_at) VALUES (:author_id, :title, :slug, :summary, :content, :image, :status, NOW())');
            $success = $stmt->execute([
                ':author_id' => $authorId > 0 ? $authorId : null,
                ':title'     => $title,
                ':slug'      => $slug,
                ':summary'   => $summary,
                ':content'   => $content,
                ':image'     => $image,
                ':status'    => $status
            ]);

            if ($success) {
                flash('success', 'Đã thêm bài viết thành công!');
                $this->redirect('admin/posts');
            } else {
                flash('error', 'Không thể lưu bài viết.');
                $this->redirect('admin/posts/create');
            }
        }
    }

    public function edit(string $id = ''): void
    {
        $id = (int)$id;
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $post = null;
        if ($db) {
            $stmt = $db->prepare('SELECT * FROM posts WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$post) {
            flash('error', 'Bài viết không tồn tại.');
            $this->redirect('admin/posts');
            return;
        }

        $this->renderAdmin('admin/posts/edit', [
            'pageTitle'  => 'Sửa bài viết',
            'activeMenu' => 'posts',
            'post'       => $post
        ]);
    }

    public function update(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/posts');
        }

        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = trim($_POST['status'] ?? 'published');

        if ($title === '') {
            flash('error', 'Vui lòng nhập tiêu đề bài viết.');
            $this->redirect('admin/posts/edit/' . $id);
            return;
        }

        $image = $_POST['current_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                require_once ROOT_PATH . '/app/services/UploadService.php';
                $image = UploadService::uploadImage($_FILES['image'], 'posts');
            } catch (Exception $e) {
                flash('error', 'Lỗi upload ảnh bài viết: ' . $e->getMessage());
                $this->redirect('admin/posts/edit/' . $id);
                return;
            }
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('UPDATE posts SET title = :title, summary = :summary, content = :content, image = :image, status = :status WHERE id = :id');
            $success = $stmt->execute([
                ':title'   => $title,
                ':summary' => $summary,
                ':content' => $content,
                ':image'   => $image,
                ':status'  => $status,
                ':id'      => $id
            ]);

            if ($success) {
                flash('success', 'Đã cập nhật bài viết thành công!');
                $this->redirect('admin/posts');
            } else {
                flash('error', 'Không thể cập nhật bài viết.');
                $this->redirect('admin/posts/edit/' . $id);
            }
        }
    }

    public function delete(string $id = ''): void
    {
        $id = (int)$id;
        if (!$this->isPost()) {
            $this->redirect('admin/posts');
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        if ($db) {
            $stmt = $db->prepare('DELETE FROM posts WHERE id = :id');
            if ($stmt->execute([':id' => $id])) {
                flash('success', 'Xoá bài viết thành công!');
            } else {
                flash('error', 'Không thể xoá bài viết.');
            }
        }

        $this->redirect('admin/posts');
    }
}
