<?php
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/models/Post.php';
require_once ROOT_PATH . '/app/core/MarkdownRenderer.php';

class PostController extends Controller
{
    private Post $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

    public function index()
    {
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $type     = isset($_GET['type']) ? trim($_GET['type']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $tag      = isset($_GET['tag']) ? trim($_GET['tag']) : '';
        $limit    = 6;
        $offset   = ($page - 1) * $limit;

        $featured = null;
        $heroPopular = [];
        $excludeFeaturedId = null;

        // Chỉ hiển thị Featured / Hero Grid khi ở trang 1 và không có bộ lọc nào
        if ($page === 1 && empty($type) && empty($category) && empty($tag)) {
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

        // countAll và getAll dùng cùng bộ điều kiện và excludeId
        $total = $this->postModel->countAll($type, $category, $tag, $excludeFeaturedId);
        $posts = $this->postModel->getAll($offset, $limit, $type, $category, $tag, $excludeFeaturedId);

        $popular = $this->postModel->getPopular(5);
        $filteredPopular = [];
        foreach ($popular as $p) {
            if ($featured && $p['id'] === $featured['id']) continue;
            $filteredPopular[] = $p;
            if (count($filteredPopular) == 4) break; // chỉ lấy 4 bài popular sidebar
        }

        require_once ROOT_PATH . '/app/services/NewsCommerceService.php';
        $commerceService = new NewsCommerceService();
        $genericCommerce = $commerceService->getConfig($category ?: 'default', $type);

        $commerceContext = [
            'category'  => $category,
            'post_type' => $type,
            'placement' => 'news-index-sidebar',
            'config'    => $genericCommerce['sidebar'] ?? null,
        ];

        $this->render('post/index', [
            'pageTitle'       => 'Tin tức công nghệ',
            'title'           => 'Tin tức công nghệ',
            'posts'           => $posts,
            'featured'        => $featured,
            'heroPopular'     => $heroPopular,
            'popular'         => $filteredPopular,
            'currentPage'     => $page,
            'totalPages'      => (int)ceil($total / $limit),
            'currentType'     => $type,
            'currentCategory' => $category,
            'currentTag'      => $tag,
            'commerceContext' => $commerceContext,
            'pageStyles'      => ['assets/css/news.css?v=1.2'],
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

        require_once ROOT_PATH . '/app/services/NewsCommerceService.php';
        $commerceService = new NewsCommerceService();
        $commerceConfig  = $commerceService->getConfig(
            $post['category_slug'] ?? '',
            $post['post_type'] ?? ''
        );

        $commerceContext = [
            'category'  => $post['category_slug'] ?? '',
            'post_type' => $post['post_type'] ?? '',
            'placement' => 'article-sidebar',
            'config'    => $commerceConfig['sidebar'] ?? null,
        ];

        // Xử lý Markdown content
        $renderer = new MarkdownRenderer();
        $parsed   = $renderer->render($post['content'] ?? '');

        $this->render('post/detail', [
            'pageTitle'       => $post['title'],
            'title'           => $post['title'] . ' - TechPilot News',
            'post'            => $post,
            'related'         => $related,
            'safeContent'     => $parsed['html'],
            'headings'        => $parsed['headings'] ?? [],
            'blocks'          => $parsed['blocks'] ?? [],
            'midCtaConfig'    => $commerceConfig['mid_cta'] ?? null,
            'endCtaConfig'    => $commerceConfig['end_cta'] ?? null,
            'commerceContext' => $commerceContext,
            'pageStyles'      => ['assets/css/news.css?v=1.2'],
            'pageScripts'     => ['assets/js/news.js?v=1.1'],
        ]);
    }
}
