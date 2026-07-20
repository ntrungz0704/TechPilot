<?php

class MarkdownRenderer
{
    private array $headings = [];
    private array $blocks = [];
    private array $usedHeadingIds = [];

    public function render(string $markdown): array
    {
        $this->headings = [];
        $this->blocks = [];
        $this->usedHeadingIds = [];

        $markdown = str_replace("\r\n", "\n", $markdown);
        // Split by 2 or more newlines to get blocks
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
            'html'     => $html,
            'headings' => $this->headings,
            'blocks'   => $this->blocks,
        ];
    }

    private function renderBlock(string $block): string
    {
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
        if (preg_match('/^\s*!\[([^\]]*)\]\(([^)\s]+)(?:\s+["\'](.*?)["\'])?\)\s*$/s', $block, $matches)) {
            $html = $this->renderImageBlock($matches);
            if ($html !== '') {
                $this->blocks[] = [
                    'type' => 'image',
                    'html' => $html,
                ];
            }
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

    private function renderList(string $block, string $type): string
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
            $html .= '  <li>' . $this->renderInline($line) . "</li>\n";
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

        // Validate separator line (line 1)
        $separator = $lines[1];
        if (!preg_match('/^\|?\s*:?-+:?\s*(\|\s*:?-+:?\s*)+\|?$/', $separator)) {
            return null; // Not a valid Markdown table separator
        }

        $html = '<div class="article-table-wrap" tabindex="0" role="region" aria-label="Bảng dữ liệu trong bài viết"><table class="table table-bordered">' . "\n";

        // Header (line 0)
        $headerCells = explode('|', trim($lines[0], '|'));
        $html .= "  <thead>\n    <tr>\n";
        foreach ($headerCells as $cell) {
            $html .= '      <th scope="col">' . $this->renderInline(trim($cell)) . "</th>\n";
        }
        $html .= "    </tr>\n  </thead>\n  <tbody>\n";

        // Body (lines 2+)
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

        if ($title !== '') {
            $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            return '<figure><img src="' . $sanitizedUrl . '" alt="' . $alt . '" loading="lazy" decoding="async"><figcaption>' . $escapedTitle . '</figcaption></figure>';
        }

        return '<img src="' . $sanitizedUrl . '" alt="' . $alt . '" loading="lazy" decoding="async">';
    }

    private function renderInline(string $text): string
    {
        // 1. Convert special chars first
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // 2. Images: ![alt](url "title")
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+&quot;(.*?)&quot;|\s+[\'"](.*?)[\'"])?\)/', function($m) {
            $alt = $m[1]; // already escaped
            $rawUrl = $m[2];
            $title = !empty($m[3]) ? $m[3] : (!empty($m[4]) ? $m[4] : '');

            $sanitizedUrl = $this->sanitizeUrl($rawUrl, false);
            if ($sanitizedUrl === null) {
                return $alt !== '' ? '<span>' . $alt . '</span>' : '';
            }

            if ($title !== '') {
                return '<figure><img src="' . $sanitizedUrl . '" alt="' . $alt . '" loading="lazy" decoding="async"><figcaption>' . $title . '</figcaption></figure>';
            }

            return '<img src="' . $sanitizedUrl . '" alt="' . $alt . '" loading="lazy" decoding="async">';
        }, $text);

        // 3. Links: [text](url)
        $text = preg_replace_callback('/\[([^\]]+)\]\(((?:[^\s()]+|\([^()\s]*\))+)\)/', function($m) {
            $linkText = $m[1]; // already escaped
            $rawUrl = $m[2];

            $sanitizedUrl = $this->sanitizeUrl($rawUrl, true);
            if ($sanitizedUrl === null) {
                return '<span>' . $linkText . '</span>';
            }

            // Distinguish internal vs external
            $decodedClean = strtolower(trim(html_entity_decode($rawUrl, ENT_QUOTES, 'UTF-8')));
            if (str_starts_with($decodedClean, 'http://') || str_starts_with($decodedClean, 'https://')) {
                return '<a href="' . $sanitizedUrl . '" target="_blank" rel="noopener noreferrer nofollow">' . $linkText . '</a>';
            }

            return '<a href="' . $sanitizedUrl . '">' . $linkText . '</a>';
        }, $text);

        // 4. Bold: **text**
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);

        // 5. Italic: *text*
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);

        // 6. Line breaks
        $text = nl2br($text);

        return $text;
    }

    private function sanitizeUrl(string $url, bool $allowFragment = true): ?string
    {
        $decoded = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
        if ($decoded === '') {
            return null;
        }

        // Strip control chars and whitespace to catch hidden schemes like java\nscript:
        $clean = preg_replace('/[\x00-\x1F\x7F\s]+/u', '', strtolower($decoded));

        // Block explicit forbidden schemes
        if (preg_match('/^(javascript|data|vbscript|file|blob):/i', $clean)) {
            return null;
        }

        // Check if there is a scheme (scheme:...)
        if (preg_match('/^([a-z0-9+\-.]+):/i', $clean, $m)) {
            $scheme = strtolower($m[1]);
            if (!in_array($scheme, ['http', 'https'], true)) {
                return null;
            }
        }

        if (str_starts_with($decoded, '#') && !$allowFragment) {
            return null;
        }

        return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
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
