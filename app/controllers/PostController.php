<?php

class PostController extends Controller
{
    /** Danh sách bài viết: /post hoặc /post/index */
    public function index(): void
    {
        $postModel = $this->model('Post');
        
        // 1. Phân trang & Tag lọc
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
        $limit = 5;
        $offset = ($page - 1) * $limit;

        // 2. Lấy Featured Post (Bài viết tiêu điểm) ở trang 1 và không lọc tag
        $featured = null;
        if ($page === 1 && empty($tag)) {
            $featured = $postModel->getFeatured();
        }

        // 3. Lấy danh sách bài viết (loại trừ bài featured nếu có)
        $excludeId = $featured ? (int)$featured['id'] : null;
        $posts = $postModel->getAll($offset, $limit, $tag, $excludeId);

        // 4. Tính toán tổng số trang
        $totalCount = $postModel->countAll($tag);
        if ($featured !== null) {
            $totalCount = max(0, $totalCount - 1);
        }
        $totalPages = (int)ceil($totalCount / $limit);

        // 5. Lấy danh sách bài viết phổ biến (Popular)
        $popular = $postModel->getPopular(3);

        $this->render('post/index', [
            'pageTitle'   => 'Tin tức công nghệ',
            'featured'    => $featured,
            'posts'       => $posts,
            'popular'     => $popular,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'currentTag'  => $tag
        ]);
    }

    /** Chi tiết bài viết: /post/detail/{slug} */
    public function detail(string $slug = ''): void
    {
        if (empty($slug)) {
            $this->redirect('post');
            return;
        }

        $postModel = $this->model('Post');
        $post = $postModel->getBySlug($slug);

        if (!$post) {
            http_response_code(404);
            $this->render('home/404', ['pageTitle' => 'Không tìm thấy bài viết']);
            return;
        }

        // 1. Tăng lượt xem
        $postModel->incrementViews((int)$post['id']);

        // 2. Lấy bài viết liên quan (loại trừ bài hiện tại)
        $related = $postModel->getAll(0, 3, '', (int)$post['id']);

        $this->render('post/detail', [
            'pageTitle' => $post['title'],
            'post'      => $post,
            'related'   => $related
        ]);
    }
}
