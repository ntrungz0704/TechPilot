<?php

class MarkdownRenderer
{
    private array $headings = [];
    private array $blocks = [];

    public function render(string $markdown): array
    {
        $this->headings = [];
        $this->blocks = [];
        $markdown = str_replace("\r\n", "\n", $markdown);
        // Split by 2 or more newlines to get blocks
        $rawBlocks = preg_split('/\n{2,}/', trim($markdown));
        
        $html = '';
        foreach ($rawBlocks as $block) {
            $block = trim($block);
            if ($block === '') continue;
            
            $renderedBlockHtml = $this->renderBlock($block);
            $html .= $renderedBlockHtml . "\n";
        }

        return [
            'html' => $html,
            'headings' => $this->headings,
            'blocks' => $this->blocks,
        ];
    }

    private function renderBlock(string $block): string
    {
        // 1. Headings (##, ###)
        if (preg_match('/^(#{2,3})\s+(.+)$/s', $block, $matches)) {
            $html = $this->renderHeading($matches);
            $this->blocks[] = [
                'type' => 'heading',
                'level' => strlen(trim($matches[1])),
                'html' => $html
            ];
            return $html;
        }

        // 2. Callout (:::info)
        if (str_starts_with($block, ':::info')) {
            $content = trim(substr($block, 7));
            if (str_ends_with($content, ':::')) {
                $content = trim(substr($content, 0, -3));
            }
            $html = '<div class="callout callout-info">' . $this->renderInline($content) . '</div>';
            $this->blocks[] = [
                'type' => 'callout',
                'html' => $html
            ];
            return $html;
        }

        // 3. Blockquote (> text)
        if (str_starts_with($block, '> ')) {
            $lines = explode("\n", $block);
            $parsedLines = [];
            foreach ($lines as $line) {
                $parsedLines[] = ltrim($line, '> ');
            }
            $content = implode("<br>", $parsedLines);
            $html = '<blockquote><p>' . $this->renderInline($content) . '</p></blockquote>';
            $this->blocks[] = [
                'type' => 'blockquote',
                'html' => $html
            ];
            return $html;
        }

        // 4. Unordered List (- or *)
        if (preg_match('/^[-*]\s+/m', $block)) {
            $html = $this->renderList($block, 'ul');
            $this->blocks[] = [
                'type' => 'ul',
                'html' => $html
            ];
            return $html;
        }

        // 5. Ordered List (1., 2.)
        if (preg_match('/^\d+\.\s+/m', $block)) {
            $html = $this->renderList($block, 'ol');
            $this->blocks[] = [
                'type' => 'ol',
                'html' => $html
            ];
            return $html;
        }

        // 6. Table (| Header 1 | Header 2 |)
        if (str_starts_with($block, '|') && str_contains($block, "\n|")) {
            $html = $this->renderTable($block);
            $this->blocks[] = [
                'type' => 'table',
                'html' => $html
            ];
            return $html;
        }

        // 7. Fallback to Paragraph
        $html = '<p>' . $this->renderInline($block) . '</p>';
        $this->blocks[] = [
            'type' => 'paragraph',
            'html' => $html
        ];
        return $html;
    }

    private function renderHeading(array $matches): string
    {
        $level = strlen(trim($matches[1]));
        $text = trim($matches[2]);
        $id = $this->createId($text);
        
        $this->headings[] = [
            'level' => $level,
            'id' => $id,
            'text' => $text,
        ];

        return "<h{$level} id=\"{$id}\">" . $this->renderInline($text) . "</h{$level}>";
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

    private function renderTable(string $block): string
    {
        $lines = explode("\n", trim($block));
        $html = '<div class="table-responsive"><table class="table table-bordered">' . "\n";
        
        $isHeader = true;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/^\|[\s\-\|]+\|$/', $line)) {
                // Skip separator row (e.g. |---|---|)
                $isHeader = false;
                continue;
            }
            
            // Clean up leading and trailing |
            $line = trim($line, '|');
            $cells = explode('|', $line);
            
            if ($isHeader) {
                $html .= "  <thead>\n    <tr>\n";
                foreach ($cells as $cell) {
                    $html .= '      <th>' . $this->renderInline(trim($cell)) . "</th>\n";
                }
                $html .= "    </tr>\n  </thead>\n  <tbody>\n";
                $isHeader = false;
            } else {
                $html .= "    <tr>\n";
                foreach ($cells as $cell) {
                    $html .= '      <td>' . $this->renderInline(trim($cell)) . "</td>\n";
                }
                $html .= "    </tr>\n";
            }
        }
        $html .= "  </tbody>\n</table></div>";
        return $html;
    }

    private function renderInline(string $text): string
    {
        // XSS Protection first
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Images: ![alt](url "title")
        // After htmlspecialchars, quotes in title become &quot;
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+&quot;(.*?)&quot;)?\)/', function($m) {
            $alt = $m[1];
            $url = $m[2];
            $title = isset($m[3]) ? $m[3] : '';
            
            $img = '<img src="' . $url . '" alt="' . $alt . '"';
            if ($title) {
                $img .= ' title="' . $title . '"';
            }
            $img .= ' loading="lazy">';
            
            if ($title) {
                return '<figure>' . $img . '<figcaption>' . $title . '</figcaption></figure>';
            }
            return $img;
        }, $text);

        // Links: [text](url)
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text);

        // Bold: **text**
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);

        // Italic: *text*
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);

        // Line breaks for inline paragraphs
        $text = nl2br($text);

        return $text;
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
        $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
        return trim($text, '-');
    }
}
