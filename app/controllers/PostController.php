<?php
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/models/Post.php';

class PostController extends Controller
{
    private Post $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
        $limit = 6;
        $offset = ($page - 1) * $limit;

        $featured = null;
        $heroPopular = [];
        $excludeFeaturedId = null;

        if ($page === 1 && empty($tag)) {
            $featured = $this->postModel->getFeatured();
            if ($featured) {
                $excludeFeaturedId = $featured['id'];
                // Nếu có featured, lấy 2 bài popular hero grid
                $allPop = $this->postModel->getPopular(3);
                foreach ($allPop as $p) {
                    if ($p['id'] !== $featured['id']) {
                        $heroPopular[] = $p;
                    }
                    if (count($heroPopular) == 2) break;
                }
            }
        }

        // countAll và getAll dùng cùng bộ excludeId để tránh lặp bài giữa trang 1 và trang 2
        $total = $this->postModel->countAll($tag, $excludeFeaturedId);
        $posts = $this->postModel->getAll($offset, $limit, $tag, $excludeFeaturedId);

        $popular = $this->postModel->getPopular(5);
        $filteredPopular = [];
        foreach ($popular as $p) {
            if ($featured && $p['id'] === $featured['id']) continue;
            $filteredPopular[] = $p;
            if (count($filteredPopular) == 4) break; // chỉ lấy 4 bài popular sidebar
        }

        $this->render('post/index', [
            'pageTitle'   => 'Tin tức công nghệ',
            'title'       => 'Tin tức công nghệ',
            'posts'       => $posts,
            'featured'    => $featured,
            'heroPopular' => $heroPopular,
            'popular'     => $filteredPopular,
            'currentPage' => $page,
            'totalPages'  => (int)ceil($total / $limit),
            'currentTag'  => $tag,
            'pageStyles'  => ['assets/css/news.css?v=1.1'],
        ]);
    }

    public function detail($slug)
    {
        $post = $this->postModel->getBySlug($slug);

        if (!$post) {
            $this->render('home/404', [
                'pageTitle' => 'Không tìm thấy bài viết',
                'title'     => 'Không tìm thấy bài viết',
            ]);
            return;
        }

        $this->postModel->incrementViews($post['id']);

        $related = $this->postModel->getRelatedPosts(
            $post['id'],
            $post['category_slug'] ?? '',
            $post['post_type'] ?? '',
            4
        );

        // Xử lý content an toàn: tách theo dòng trắng thành các đoạn văn
        $paragraphs = preg_split('/\n+/', trim($post['content'] ?? ''));
        $safeContent = '';
        foreach ($paragraphs as $p) {
            if (trim($p) !== '') {
                $safeContent .= '<p>' . nl2br(htmlspecialchars(trim($p), ENT_QUOTES, 'UTF-8')) . '</p>';
            }
        }

        $this->render('post/detail', [
            'pageTitle'   => $post['title'],
            'title'       => $post['title'] . ' - TechPilot News',
            'post'        => $post,
            'related'     => $related,
            'safeContent' => $safeContent,
            'pageStyles'  => ['assets/css/news.css?v=1.1'],
            'pageScripts' => ['assets/js/news.js?v=1.1'],
        ]);
    }
}
