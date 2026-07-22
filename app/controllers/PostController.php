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

    /**
     * Chuẩn hóa trang được yêu cầu an toàn, chống warning Array to int conversion khi page là mảng.
     */
    public static function normalizeRequestedPage(mixed $rawPage): int
    {
        if (!is_scalar($rawPage)) {
            return 1;
        }

        return max(1, (int)$rawPage);
    }

    public function index()
    {
        $page     = self::normalizeRequestedPage($_GET['page'] ?? 1);
        $rawType  = $_GET['type'] ?? '';
        $type     = is_string($rawType) ? trim($rawType) : '';
        $rawCat   = $_GET['category'] ?? '';
        $category = is_string($rawCat) ? trim($rawCat) : '';
        $rawTag   = $_GET['tag'] ?? '';
        $tag      = is_string($rawTag) ? trim($rawTag) : '';
        $rawQ     = $_GET['q'] ?? '';
        $q        = is_string($rawQ) ? trim(mb_substr($rawQ, 0, 150)) : '';
        $limit    = 6;

        $featured = null;
        $heroPopular = [];
        $usedHeroIds = [];
        $excludeFeaturedId = null;

        // Chỉ hiển thị Featured / Hero Grid khi ở trang 1 và không có bộ lọc nào
        if ($page === 1 && empty($type) && empty($category) && empty($tag) && empty($q)) {
            $featured = $this->postModel->getFeatured();
            if ($featured) {
                $excludeFeaturedId = $featured['id'];
                $usedHeroIds[] = $featured['id'];
                // Lấy hero popular, loại featured ID ngay tại SQL
                $heroPool = $this->postModel->getPopularRecent(4, 30, $usedHeroIds);
                foreach ($heroPool as $p) {
                    if (!in_array($p['id'], $usedHeroIds)) {
                        $heroPopular[] = $p;
                        $usedHeroIds[] = $p['id'];
                    }
                    if (count($heroPopular) == 2) break;
                }
            }
        }

        // countAll và getAll dùng cùng bộ điều kiện và excludeId
        $total = $this->postModel->countAll($type, $category, $tag, $q, $excludeFeaturedId);
        $totalPages = $total > 0 ? max(1, (int)ceil($total / $limit)) : 1;

        if ($total > 0) {
            $page = min($page, $totalPages);
        }

        $offset = ($page - 1) * $limit;
        $posts  = $this->postModel->getAll($offset, $limit, $type, $category, $tag, $q, $excludeFeaturedId);

        // Sidebar: lấy popular gần đây, loại trừ tất cả ID đã dùng ở hero
        $popular = $this->postModel->getPopularRecent(8, 30, $usedHeroIds);
        $filteredPopular = array_slice($popular, 0, 4); // tối đa 4 cho sidebar

        require_once ROOT_PATH . '/app/services/NewsCommerceService.php';
        $commerceService = new NewsCommerceService();
        $genericCommerce = $commerceService->getConfig($category ?: 'default', $type);

        $commerceContext = [
            'category'  => $category,
            'post_type' => $type,
            'placement' => 'news-index-sidebar',
            'config'    => $genericCommerce['sidebar'] ?? null,
        ];

        $newsConfigFile = ROOT_PATH . '/config/news.php';
        $newsConfig     = file_exists($newsConfigFile) ? require $newsConfigFile : [];
        $hotTopics      = is_array($newsConfig['hot_topics'] ?? null) ? $newsConfig['hot_topics'] : [];

        $canonicalAbsolute = absoluteUrl('post');

        $this->render('post/index', [
            'pageTitle'       => 'Tin tức công nghệ',
            'title'           => 'Tin tức công nghệ',
            'metaDescription' => 'Cập nhật tin tức công nghệ, đánh giá sản phẩm, thủ thuật và hướng dẫn chuyên sâu.',
            'canonicalUrl'    => $canonicalAbsolute,
            'ogType'          => 'website',
            'ogTitle'         => 'Tin tức công nghệ - TechPilot',
            'ogDescription'   => 'Cập nhật tin tức công nghệ, đánh giá sản phẩm, thủ thuật và hướng dẫn chuyên sâu.',
            'ogUrl'           => $canonicalAbsolute,
            'twitterCard'     => 'summary',
            'twitterTitle'    => 'Tin tức công nghệ - TechPilot',
            'twitterDescription' => 'Cập nhật tin tức công nghệ, đánh giá sản phẩm, thủ thuật và hướng dẫn chuyên sâu.',
            'posts'           => $posts,
            'featured'        => $featured,
            'heroPopular'     => $heroPopular,
            'popular'         => $filteredPopular,
            'hotTopics'       => $hotTopics,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'currentType'     => $type,
            'currentCategory' => $category,
            'currentTag'      => $tag,
            'currentQ'        => $q,
            'commerceContext' => $commerceContext,
            'pageStyles'      => ['assets/css/news.css?v=2.2'],
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

        $postType     = strtolower(trim((string)($post['post_type'] ?? '')));
        $categorySlug = strtolower(trim((string)($post['category_slug'] ?? '')));

        require_once ROOT_PATH . '/app/services/NewsCommerceService.php';
        $commerceService = new NewsCommerceService();
        $commerceConfig  = $commerceService->getConfig($categorySlug, $postType);

        $commerceContext = [
            'category'  => $categorySlug,
            'post_type' => $postType,
            'placement' => 'article-sidebar',
            'config'    => $commerceConfig['sidebar'] ?? null,
        ];

        // Xử lý Markdown content
        $renderer = new MarkdownRenderer();
        $parsed   = $renderer->render($post['content'] ?? '');

        $renderedContent  = (string)($parsed['html'] ?? '');
        $articleHeadings  = is_array($parsed['headings'] ?? null) ? $parsed['headings'] : [];
        $articleBlocks    = is_array($parsed['blocks'] ?? null) ? $parsed['blocks'] : [];
        $quickSummaryHtml = (string)($parsed['quickSummaryHtml'] ?? '');
        $sourcesHtml      = (string)($parsed['sourcesHtml'] ?? '');

        $plainArticleText = trim(
            html_entity_decode(
                strip_tags($renderedContent),
                ENT_QUOTES,
                'UTF-8'
            )
        );

        preg_match_all(
            '/[\p{L}\p{N}]+/u',
            $plainArticleText,
            $wordMatches
        );
        $articleWordCount = count($wordMatches[0]);

        $articleH2Count = count(array_filter(
            $articleHeadings,
            static fn (array $heading): bool => (int)($heading['level'] ?? 0) === 2
        ));

        // ── SEO: Absolute URLs ──────────────────────────────────────────────
        $canonicalAbsolute = absoluteUrl('post/detail/' . $post['slug']);
        $imageAbsolute     = !empty($post['image'])
            ? $this->makeAbsoluteImageUrl(postImageUrl($post['image']))
            : absoluteUrl('assets/images/logo.png');

        // Phân biệt Author Person vs Organization dựa trên helper production Post::buildAuthorSchema
        $authorSchema = Post::buildAuthorSchema($post);

        $rawPubTime      = !empty($post['published_at']) ? strtotime($post['published_at']) : (!empty($post['created_at']) ? strtotime($post['created_at']) : false);
        $hasValidPubDate = ($rawPubTime !== false && $rawPubTime > 0);
        $publishedAt     = $hasValidPubDate ? date('c', $rawPubTime) : null;

        $rawUpdatedTime           = !empty($post['updated_at']) ? strtotime($post['updated_at']) : false;
        $hasValidUpdatedTimestamp = ($rawUpdatedTime !== false && $rawUpdatedTime > 0);

        $hasValidUpdatedAt = $hasValidPubDate
            && $hasValidUpdatedTimestamp
            && ($rawUpdatedTime >= $rawPubTime + 60);

        $modifiedAt = $hasValidUpdatedAt ? date('c', $rawUpdatedTime) : $publishedAt;

        $description = !empty($post['summary'])
            ? $post['summary']
            : ('Đọc bài viết ' . $post['title'] . ' tại TechPilot.');

        // ── JSON-LD: hardened encoding ──────────────────────────────────────
        $jsonFlags = JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT;

        $articleSchema = [
            '@context'          => 'https://schema.org',
            '@type'             => 'Article',
            'headline'          => $post['title'],
            'description'       => $description,
            'image'             => [$imageAbsolute],
            'author'            => $authorSchema,
            'publisher'         => [
                '@type'  => 'Organization',
                'name'   => 'TechPilot',
                'logo'   => [
                    '@type' => 'ImageObject',
                    'url'   => absoluteUrl('assets/images/logo.png'),
                ],
            ],
            'mainEntityOfPage'  => [
                '@type' => 'WebPage',
                '@id'   => $canonicalAbsolute,
            ],
        ];

        if ($publishedAt !== null) {
            $articleSchema['datePublished'] = $publishedAt;
            $articleSchema['dateModified']  = $modifiedAt;
        }

        $breadcrumbSchema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type'    => 'ListItem',
                    'position' => 1,
                    'name'     => 'Trang chủ',
                    'item'     => absoluteUrl(''),
                ],
                [
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'name'     => 'Tin tức công nghệ',
                    'item'     => absoluteUrl('post'),
                ],
                [
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => $post['title'],
                    'item'     => $canonicalAbsolute,
                ],
            ],
        ];

        $encodedStructuredData = json_encode([$articleSchema, $breadcrumbSchema], $jsonFlags);

        if ($encodedStructuredData === false) {
            // Log lỗi trong dev, không render JSON-LD bị hỏng
            if (defined('APP_ENV') && APP_ENV === 'development') {
                error_log('[TechPilot] JSON-LD encode failed: ' . json_last_error_msg());
            }
            $encodedStructuredData = null;
        }

        $this->render('post/detail', [
            'pageTitle'          => $post['title'],
            'title'              => $post['title'] . ' - TechPilot News',
            'metaDescription'    => $description,
            'canonicalUrl'       => $canonicalAbsolute,
            'ogType'             => 'article',
            'ogTitle'            => $post['title'],
            'ogDescription'      => $description,
            'ogUrl'              => $canonicalAbsolute,
            'ogImage'            => $imageAbsolute,
            'twitterCard'        => 'summary_large_image',
            'twitterTitle'       => $post['title'],
            'twitterDescription' => $description,
            'twitterImage'       => $imageAbsolute,
            'structuredData'     => $encodedStructuredData,
            'post'               => $post,
            'related'            => $related,
            'renderedContent'    => $renderedContent,
            'articleHeadings'    => $articleHeadings,
            'articleBlocks'      => $articleBlocks,
            'articleWordCount'   => $articleWordCount,
            'articleH2Count'     => $articleH2Count,
            'quickSummaryHtml'   => $quickSummaryHtml,
            'sourcesHtml'        => $sourcesHtml,
            'hasValidUpdatedAt'  => $hasValidUpdatedAt,
            'postType'           => $postType,
            'categorySlug'       => $categorySlug,
            'midCtaConfig'       => $commerceConfig['mid_cta'] ?? null,
            'endCtaConfig'       => $commerceConfig['end_cta'] ?? null,
            'commerceContext'    => $commerceContext,
            'pageStyles'         => ['assets/css/news.css?v=2.4'],
            'pageScripts'        => ['assets/js/news.js?v=2.3'],
        ]);
    }

    /**
     * Chuyển URL ảnh tương đối thành tuyệt đối.
     * Nếu đã là URL tuyệt đối thì giữ nguyên.
     */
    private function makeAbsoluteImageUrl(string $imageUrl): string
    {
        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }
        // Bỏ BASE_URL prefix nếu có để tránh duplicate
        if (defined('BASE_URL') && BASE_URL !== '' && str_starts_with($imageUrl, BASE_URL . '/')) {
            $imageUrl = substr($imageUrl, strlen(BASE_URL) + 1);
        }
        return absoluteUrl($imageUrl);
    }
}
