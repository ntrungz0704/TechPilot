<?php

class MarkdownRenderer
{
    private array $headings = [];
    private array $blocks = [];
    private array $usedHeadingIds = [];
    private array $codeBlocks = [];
    private array $summaryBlocks = [];
    private array $sourcesBlocks = [];
    private string $quickSummaryHtml = '';
    private string $sourcesHtml = '';

    /**
     * Render markdown string sang HTML + metadata.
     * Code fence và custom blocks được tokenize trước khi split
     * để tránh placeholder bị ghép vào paragraph hoặc vỡ block.
     */
    public function render(string $markdown): array
    {
        $this->headings         = [];
        $this->blocks           = [];
        $this->usedHeadingIds   = [];
        $this->codeBlocks       = [];
        $this->summaryBlocks    = [];
        $this->sourcesBlocks    = [];
        $this->quickSummaryHtml = '';
        $this->sourcesHtml      = '';

        $markdown = str_replace("\r\n", "\n", $markdown);

        // ── Step 1: Tokenize code fences TRƯỚC khi split ──────────────────
        $markdown = preg_replace_callback(
            '/^```([^\n]*)?\n(.*?)^```[ \t]*$/ms',
            function (array $matches): string {
                $lang  = preg_replace('/[^a-z0-9_+\-]/i', '', trim($matches[1]));
                $code  = rtrim($matches[2], "\n");
                $token = '__TECHPILOT_CODE_' . bin2hex(random_bytes(8)) . '__';
                $this->codeBlocks[$token] = [
                    'lang' => $lang,
                    'code' => $code,
                ];
                return "\n\n{$token}\n\n";
            },
            $markdown
        );

        // ── Step 1.1: Tokenize :::summary blocks TRƯỚC khi split ──────────
        $markdown = preg_replace_callback(
            '/^:::summary[ \t]*\n(.*?)\n:::[ \t]*$/ms',
            function (array $matches): string {
                $content = trim($matches[1]);
                $token   = '__TECHPILOT_SUMMARY_' . bin2hex(random_bytes(8)) . '__';
                $this->summaryBlocks[$token] = $content;
                return "\n\n{$token}\n\n";
            },
            $markdown
        );

        // ── Step 1.2: Tokenize :::sources blocks TRƯỚC khi split ──────────
        $markdown = preg_replace_callback(
            '/^:::sources[ \t]*\n(.*?)\n:::[ \t]*$/ms',
            function (array $matches): string {
                $content = trim($matches[1]);
                $token   = '__TECHPILOT_SOURCES_' . bin2hex(random_bytes(8)) . '__';
                $this->sourcesBlocks[$token] = $content;
                return "\n\n{$token}\n\n";
            },
            $markdown
        );

        // Split by 2+ newlines
        $rawBlocks = preg_split('/\n{2,}/', trim($markdown));

        $html = '';
        foreach ($rawBlocks as $block) {
            $block = trim($block);
            if ($block === '') continue;

            $renderedBlockHtml = $this->renderBlock($block);
            if ($renderedBlockHtml !== '') {
                $html .= $renderedBlockHtml . "\n";
            }
        }

        return [
            'html'             => $html,
            'headings'         => $this->headings,
            'blocks'           => $this->blocks,
            'quickSummaryHtml' => $this->quickSummaryHtml,
            'sourcesHtml'      => $this->sourcesHtml,
        ];
    }

    private function renderBlock(string $block): string
    {
        // 0.1 Summary Block placeholder
        if (isset($this->summaryBlocks[$block])) {
            $content   = $this->summaryBlocks[$block];
            $innerHtml = $this->renderSummaryInner($content);

            $boxHtml = '<section class="article-quick-summary" aria-label="Tóm tắt ý chính">'
                . '<h2 class="article-summary-title"><i class="fa-solid fa-bolt" aria-hidden="true"></i> Tóm tắt nhanh</h2>'
                . '<div class="summary-body">' . $innerHtml . '</div>'
                . '</section>';

            if ($this->quickSummaryHtml === '') {
                $this->quickSummaryHtml = $boxHtml;
                return ''; // Extracted from main body
            }

            if (defined('APP_ENV') && APP_ENV === 'development') {
                error_log('[TechPilot MarkdownRenderer] Duplicate :::summary block detected');
            }

            $html = '<div class="callout callout-info">' . $innerHtml . '</div>';
            $this->blocks[] = ['type' => 'callout', 'html' => $html];
            return $html;
        }

        // 0.2 Sources Block placeholder
        if (isset($this->sourcesBlocks[$block])) {
            $content   = $this->sourcesBlocks[$block];
            $innerHtml = $this->renderSourcesInner($content);

            $boxHtml = '<section class="article-sources-block" aria-label="Nguồn tham khảo">'
                . '<h2 class="article-sources-title"><i class="fa-solid fa-book-bookmark" aria-hidden="true"></i> Nguồn tham khảo</h2>'
                . $innerHtml
                . '</section>';

            if ($this->sourcesHtml === '') {
                $this->sourcesHtml = $boxHtml;
                return ''; // Extracted from main body
            }

            if (defined('APP_ENV') && APP_ENV === 'development') {
                error_log('[TechPilot MarkdownRenderer] Duplicate :::sources block detected');
            }

            $html = '<section class="article-sources-block article-sources-duplicate">'
                . '<h2 class="article-sources-title"><i class="fa-solid fa-book-bookmark" aria-hidden="true"></i> Nguồn tham khảo bổ sung</h2>'
                . $innerHtml
                . '</section>';
            $this->blocks[] = ['type' => 'sources', 'html' => $html];
            return $html;
        }

        // 1. Headings (##, ### - Single line only)
        if (preg_match('/^(#{2,3})[ \t]+([^\n]+)$/', $block, $matches)) {
            $html = $this->renderHeading($matches);
            $this->blocks[] = [
                'type'  => 'heading',
                'level' => strlen(trim($matches[1])),
                'html'  => $html,
            ];
            return $html;
        }

        // 1.5. Fenced Code Block placeholder
        if (isset($this->codeBlocks[$block])) {
            $lang         = $this->codeBlocks[$block]['lang'];
            $code         = $this->codeBlocks[$block]['code'];
            $escapedCode  = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $classAttr    = $lang !== ''
                ? ' class="language-' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '"'
                : '';
            $html = '<div class="news-code-block"><pre><code' . $classAttr . '>'
                . $escapedCode
                . '</code></pre></div>';
            $this->blocks[] = [
                'type' => 'code',
                'html' => $html,
            ];
            return $html;
        }

        // 2. Callout (:::info ... :::)
        if (preg_match('/^:::info[\r\n]+(.*?)[\r\n]+:::$/s', $block, $matches)) {
            $content = trim($matches[1]);
            $html = '<div class="callout callout-info">' . $this->renderInline($content) . '</div>';
            $this->blocks[] = [
                'type' => 'callout',
                'html' => $html,
            ];
            return $html;
        }

        // 2.1. Pros Block (:::pros ... :::)
        if (preg_match('/^:::pros[\r\n]+(.*?)[\r\n]+:::$/s', $block, $matches)) {
            $content   = trim($matches[1]);
            $innerHtml = preg_match('/^[-*]\s+/m', $content)
                ? $this->renderList($content, 'ul')
                : '<p>' . $this->renderInline($content) . '</p>';
            $html = '<div class="pros-cons-block pros-block">'
                . '<h4><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Ưu điểm</h4>'
                . $innerHtml . '</div>';
            $this->blocks[] = ['type' => 'pros', 'html' => $html];
            return $html;
        }

        // 2.2. Cons Block (:::cons ... :::)
        if (preg_match('/^:::cons[\r\n]+(.*?)[\r\n]+:::$/s', $block, $matches)) {
            $content   = trim($matches[1]);
            $innerHtml = preg_match('/^[-*]\s+/m', $content)
                ? $this->renderList($content, 'ul')
                : '<p>' . $this->renderInline($content) . '</p>';
            $html = '<div class="pros-cons-block cons-block">'
                . '<h4><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Nhược điểm</h4>'
                . $innerHtml . '</div>';
            $this->blocks[] = ['type' => 'cons', 'html' => $html];
            return $html;
        }

        // 3. Blockquote (> text)
        if (str_starts_with($block, '>')) {
            $lines = explode("\n", $block);
            $parsedLines = [];
            foreach ($lines as $line) {
                $parsedLines[] = $this->renderInline(preg_replace('/^>\s?/', '', $line));
            }
            $content = implode("<br>", $parsedLines);
            $html = '<blockquote><p>' . $content . '</p></blockquote>';
            $this->blocks[] = [
                'type' => 'blockquote',
                'html' => $html,
            ];
            return $html;
        }

        // 4. Unordered List (- or *)
        if (preg_match('/^[-*]\s+/m', $block)) {
            $html = $this->renderList($block, 'ul');
            $this->blocks[] = [
                'type' => 'ul',
                'html' => $html,
            ];
            return $html;
        }

        // 5. Ordered List (1., 2.)
        if (preg_match('/^\d+\.\s+/m', $block)) {
            $html = $this->renderList($block, 'ol');
            $this->blocks[] = [
                'type' => 'ol',
                'html' => $html,
            ];
            return $html;
        }

        // 6. Table (| Header 1 | Header 2 |)
        if (str_starts_with($block, '|') && str_contains($block, "\n|")) {
            $tableResult = $this->renderTable($block);
            if ($tableResult !== null) {
                $this->blocks[] = [
                    'type' => 'table',
                    'html' => $tableResult,
                ];
                return $tableResult;
            }
        }

        // 7. Image-only block
        if (preg_match('/^\s*!\[([^\]]*)\]\(([^)\s]+)(?:\s+["\']?(.*?)["\']?)?\)\s*$/s', $block, $matches)) {
            $html = $this->renderImageBlock($matches);
            if ($html !== '') {
                $this->blocks[] = [
                    'type' => 'image',
                    'html' => $html,
                ];
            }
            return $html;
        }

        // 7.5. YouTube Embed Block
        $videoId = $this->extractYouTubeId($block);
        if ($videoId !== null) {
            $escapedId = htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8');
            $html = '<div class="youtube-embed-wrapper">'
                . '<iframe'
                . ' width="560" height="315"'
                . ' src="https://www.youtube-nocookie.com/embed/' . $escapedId . '"'
                . ' title="Video YouTube: ' . $escapedId . '"'
                . ' loading="lazy"'
                . ' referrerpolicy="strict-origin-when-cross-origin"'
                . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"'
                . ' allowfullscreen'
                . '></iframe>'
                . '</div>';
            $this->blocks[] = [
                'type' => 'youtube',
                'html' => $html,
            ];
            return $html;
        }

        // 8. Fallback to Paragraph
        $html = '<p>' . $this->renderInline($block) . '</p>';
        $this->blocks[] = [
            'type' => 'paragraph',
            'html' => $html,
        ];
        return $html;
    }

    private function renderSummaryInner(string $content): string
    {
        $subBlocks = preg_split('/\n{2,}/', trim($content));
        $html = '';
        foreach ($subBlocks as $sub) {
            $sub = trim($sub);
            if ($sub === '') continue;

            if (preg_match('/^[-*]\s+/m', $sub)) {
                $html .= $this->renderList($sub, 'ul');
            } elseif (preg_match('/^\d+\.\s+/m', $sub)) {
                $html .= $this->renderList($sub, 'ol');
            } else {
                $html .= '<p>' . $this->renderInline($sub) . '</p>';
            }
        }
        return $html;
    }

    private function renderSourcesInner(string $content): string
    {
        $subBlocks = preg_split('/\n{2,}/', trim($content));
        $html = '';
        foreach ($subBlocks as $sub) {
            $sub = trim($sub);
            if ($sub === '') continue;

            $listType = preg_match('/^\d+\.\s+/m', $sub) ? 'ol' : 'ul';
            if (preg_match('/^[-*]\s+/m', $sub) || preg_match('/^\d+\.\s+/m', $sub)) {
                $html .= $this->renderList($sub, $listType, true);
            } else {
                $html .= '<p>' . $this->renderInline($sub, true) . '</p>';
            }
        }
        return $html;
    }

    /**
     * Extract YouTube video ID from a block string.
     */
    private function extractYouTubeId(string $block): ?string
    {
        $block = trim($block);

        if (preg_match('/^@\[youtube\]\(([a-zA-Z0-9_\-]{11})\)$/i', $block, $m)) {
            return $m[1];
        }

        if (preg_match(
            '/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_\-]{11})(?:[&?#\s].*)?\/?\ *$/i',
            $block,
            $m
        )) {
            if (strlen($m[1]) === 11) {
                return $m[1];
            }
        }

        return null;
    }

    private function renderHeading(array $matches): string
    {
        $level = strlen(trim($matches[1]));
        $rawText = trim($matches[2]);
        $id = $this->createId($rawText);
        $renderedText = $this->renderInline($rawText);

        $this->headings[] = [
            'level' => $level,
            'id'    => $id,
            'text'  => strip_tags($renderedText),
        ];

        return "<h{$level} id=\"{$id}\">{$renderedText}</h{$level}>";
    }

    private function renderList(string $block, string $type, bool $isSourceLink = false): string
    {
        $lines = explode("\n", $block);
        $html = "<{$type}>\n";
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if ($type === 'ul') {
                $line = preg_replace('/^[-*]\s+/', '', $line);
            } else {
                $line = preg_replace('/^\d+\.\s+/', '', $line);
            }
            $html .= '  <li>' . $this->renderInline($line, $isSourceLink) . "</li>\n";
        }
        $html .= "</{$type}>";
        return $html;
    }

    private function renderTable(string $block): ?string
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", trim($block)))));
        if (count($lines) < 2) {
            return null;
        }

        $separator = $lines[1];
        if (!preg_match('/^\|?\s*:?-+:?\s*(\|\s*:?-+:?\s*)+\|?$/', $separator)) {
            return null;
        }

        $html = '<div class="article-table-wrap" tabindex="0" role="region" aria-label="Bảng dữ liệu trong bài viết"><table class="table table-bordered">' . "\n";

        $headerCells = explode('|', trim($lines[0], '|'));
        $html .= "  <thead>\n    <tr>\n";
        foreach ($headerCells as $cell) {
            $html .= '      <th scope="col">' . $this->renderInline(trim($cell)) . "</th>\n";
        }
        $html .= "    </tr>\n  </thead>\n  <tbody>\n";

        for ($i = 2; $i < count($lines); $i++) {
            $line = $lines[$i];
            if ($line === '') continue;

            $cells = explode('|', trim($line, '|'));
            $html .= "    <tr>\n";
            foreach ($cells as $cell) {
                $html .= '      <td>' . $this->renderInline(trim($cell)) . "</td>\n";
            }
            $html .= "    </tr>\n";
        }

        $html .= "  </tbody>\n</table></div>";
        return $html;
    }

    private function renderImageBlock(array $matches): string
    {
        $alt = htmlspecialchars(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        $rawUrl = trim($matches[2]);
        $title = isset($matches[3]) ? trim($matches[3]) : '';

        $sanitizedUrl = $this->sanitizeUrl($rawUrl, false);
        if ($sanitizedUrl === null) {
            return $alt !== '' ? '<span>' . $alt . '</span>' : '';
        }

        $escapedUrl = htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8');

        if ($title !== '') {
            $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            return '<figure><img src="' . $escapedUrl . '" alt="' . $alt . '" loading="lazy" decoding="async"><figcaption>' . $escapedTitle . '</figcaption></figure>';
        }

        return '<img src="' . $escapedUrl . '" alt="' . $alt . '" loading="lazy" decoding="async">';
    }

    private function renderInline(string $text, bool $isSourceLink = false): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+&quot;(.*?)&quot;|\s+[\'\"](.*?)[\'\"])?\)/', function($m) {
            $alt = $m[1];
            $rawUrl = $m[2];
            $title = !empty($m[3]) ? $m[3] : (!empty($m[4]) ? $m[4] : '');

            $sanitizedUrl = $this->sanitizeUrl($rawUrl, false);
            if ($sanitizedUrl === null) {
                return $alt !== '' ? '<span>' . $alt . '</span>' : '';
            }

            $escapedUrl = htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8');
            $img = '<img src="' . $escapedUrl . '" alt="' . $alt . '"';
            if ($title !== '') {
                $img .= ' title="' . $title . '"';
            }
            $img .= ' loading="lazy" decoding="async">';
            return $img;
        }, $text);

        $text = preg_replace_callback('/\[([^\]]+)\]\(((?:[^\s()]+|\([^()\s]*\))+)\)/', function($m) use ($isSourceLink) {
            $linkText = $m[1];
            $rawUrl = $m[2];

            $sanitizedUrl = $this->sanitizeUrl($rawUrl, true);
            if ($sanitizedUrl === null) {
                return '<span>' . $linkText . '</span>';
            }

            $escapedUrl = htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8');

            $decodedClean = strtolower(trim(html_entity_decode($rawUrl, ENT_QUOTES, 'UTF-8')));
            if (str_starts_with($decodedClean, 'http://') || str_starts_with($decodedClean, 'https://')) {
                $rel = $isSourceLink ? 'noopener noreferrer' : 'noopener noreferrer nofollow';
                return '<a href="' . $escapedUrl . '" target="_blank" rel="' . $rel . '">' . $linkText . '</a>';
            }

            return '<a href="' . $escapedUrl . '">' . $linkText . '</a>';
        }, $text);

        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
        $text = nl2br($text);

        return $text;
    }

    private function sanitizeUrl(string $url, bool $allowFragment = true): ?string
    {
        $decoded = trim($url);
        while (($prev = $decoded) && ($decoded = html_entity_decode($decoded, ENT_QUOTES, 'UTF-8')) !== $prev) {
        }

        if ($decoded === '') {
            return null;
        }

        $clean = preg_replace('/[\x00-\x1F\x7F\s]+/u', '', strtolower($decoded));

        if (str_starts_with($clean, '//')) {
            return null;
        }

        if (preg_match('/^(javascript|data|vbscript|file|blob):/i', $clean)) {
            return null;
        }

        if (preg_match('/^([a-z0-9+\-.]+):/i', $clean, $m)) {
            $scheme = strtolower($m[1]);
            if (!in_array($scheme, ['http', 'https'], true)) {
                return null;
            }
        }

        if (str_starts_with($decoded, '#') && !$allowFragment) {
            return null;
        }

        return $decoded;
    }

    private function createId(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'đ' => 'd'
        ]);
        $slug = preg_replace('/[^a-z0-9]+/u', '-', $text);
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'section';
        }

        $id = $slug;
        $count = 2;
        while (isset($this->usedHeadingIds[$id])) {
            $id = $slug . '-' . $count;
            $count++;
        }
        $this->usedHeadingIds[$id] = true;
        return $id;
    }
}
