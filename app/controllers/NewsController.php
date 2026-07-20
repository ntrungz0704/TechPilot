<?php

class NewsController extends Controller
{
    private array $data;

    public function __construct()
    {
        $this->data = require ROOT_PATH . '/app/data/news-fixtures.php';
    }

    public function index(): void
    {
        $categories = $this->data['categories'];
        $articles = $this->data['articles'];

        $heroArticles = array_slice($articles, 0, 3);
        $listArticles = array_slice($articles, 3);

        // If list is empty (only 3 total), we can mock by reusing them for preview
        if (empty($listArticles)) {
            $listArticles = $articles;
        }

        $this->render('news/index', [
            'pageTitle' => 'Tin tức công nghệ',
            'metaDescription' => 'Cập nhật những tin tức công nghệ, thủ thuật, đánh giá sản phẩm mới nhất từ TechPilot.',
            'categories' => $categories,
            'heroArticles' => $heroArticles,
            'listArticles' => $listArticles,
        ]);
    }

    public function show(string $slug): void
    {
        $articles = $this->data['articles'];
        $article = null;
        
        foreach ($articles as $a) {
            if ($a['slug'] === $slug) {
                $article = $a;
                break;
            }
        }

        if (!$article) {
            $this->redirect('tin-tuc'); // Redirect to news list if not found
            return;
        }

        // Lấy bài viết liên quan
        $relatedArticles = array_filter($articles, function($a) use ($article) {
            return $a['id'] !== $article['id'] && $a['category']['slug'] === $article['category']['slug'];
        });
        
        // Cắt bớt chỉ lấy 3 bài
        $relatedArticles = array_slice($relatedArticles, 0, 3);

        // Lấy sản phẩm đề xuất (Mock - dùng best sellers laptop)
        $productModel = $this->model('Product');
        $recommendedProducts = [];
        if (!empty($article['recommended_products'])) {
            $recommendedProducts = $productModel->getBestSellersByTab('laptop', 4);
        }

        $this->render('news/show', [
            'pageTitle' => $article['title'],
            'metaDescription' => $article['excerpt'],
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }
}
