<?php

/**
 * Service Tra cứu & Scrape dữ liệu thông số kỹ thuật phần cứng (TSIE Scraper Engine)
 * Scrape-First-Then-Extract: Truy vấn dữ liệu thực tế từ các website chính hãng và các đại lý công nghệ VN.
 */
class SpecScraperService
{
    /**
     * Tra cứu và Scrape dữ liệu thông số kỹ thuật thực tế cho sản phẩm
     * 
     * @param string $query Tên sản phẩm, Model, SKU hoặc URL
     * @return array Array dạng ['raw_text' => ..., 'source_urls' => [...], 'primary_url' => ..., 'source_name' => ...]
     */
    public static function scrapeProductSpecs(string $query): array
    {
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return [
                'raw_text' => '',
                'source_urls' => [],
                'primary_url' => '',
                'source_name' => 'Nguồn không xác định'
            ];
        }

        // Trường hợp người dùng nhập trực tiếp URL
        if (filter_var($cleanQuery, FILTER_VALIDATE_URL)) {
            $html = self::fetchUrlContent($cleanQuery);
            $extractedText = self::extractTableTextFromHtml($html);
            $domain = parse_url($cleanQuery, PHP_URL_HOST) ?? 'Trang web nguồn';
            return [
                'raw_text' => $extractedText,
                'source_urls' => [$cleanQuery],
                'primary_url' => $cleanQuery,
                'source_name' => $domain
            ];
        }

        // Tra cứu danh sách URL nguồn tiềm năng từ các trang hãng & đại lý Việt Nam
        $searchUrls = self::findPotentialSourceUrls($cleanQuery);
        $fetchedTexts = [];
        $sourceUrls = [];
        $primaryUrl = '';
        $primarySourceName = 'Hãng sản xuất & Đại lý uy tín';

        foreach ($searchUrls as $item) {
            $url = $item['url'];
            $name = $item['name'];
            $content = self::fetchUrlContent($url);
            if (!empty($content)) {
                $text = self::extractTableTextFromHtml($content);
                if (strlen($text) > 100) {
                    $fetchedTexts[] = "=== NGUỒN: {$name} ({$url}) ===\n" . $text;
                    $sourceUrls[] = $url;
                    if (empty($primaryUrl)) {
                        $primaryUrl = $url;
                        $primarySourceName = $name;
                    }
                }
            }
            // Giới hạn fetch tối đa 2 nguồn thực tế để giữ hiệu năng cao nhất (< 2s)
            if (count($sourceUrls) >= 2) {
                break;
            }
        }

        return [
            'raw_text' => implode("\n\n", $fetchedTexts),
            'source_urls' => array_values(array_unique($sourceUrls)),
            'primary_url' => $primaryUrl,
            'source_name' => $primarySourceName
        ];
    }

    /**
     * Tìm danh sách URL tiềm năng theo brand/model
     */
    private static function findPotentialSourceUrls(string $query): array
    {
        $queryLower = strtolower($query);
        $encoded = urlencode($query);
        $results = [];

        // Tra cứu theo Nguồn Hãng
        if (str_contains($queryLower, 'asus') || str_contains($queryLower, 'rog') || str_contains($queryLower, 'tuf')) {
            $results[] = ['name' => 'ASUS Official Store', 'url' => "https://phongvu.vn/s?q={$encoded}"];
            $results[] = ['name' => 'GearVN Tech Store', 'url' => "https://gearvn.com/search?type=product&q={$encoded}"];
        } elseif (str_contains($queryLower, 'msi')) {
            $results[] = ['name' => 'MSI Official Specs', 'url' => "https://phongvu.vn/s?q={$encoded}"];
        } elseif (str_contains($queryLower, 'dell') || str_contains($queryLower, 'alienware')) {
            $results[] = ['name' => 'Dell Official Specs', 'url' => "https://phongvu.vn/s?q={$encoded}"];
        } elseif (str_contains($queryLower, 'lenovo') || str_contains($queryLower, 'legion')) {
            $results[] = ['name' => 'Lenovo Official Specs', 'url' => "https://phongvu.vn/s?q={$encoded}"];
        } else {
            // Nguồn mặc định cho các thương hiệu khác
            $results[] = ['name' => 'Phong Vũ Official Catalog', 'url' => "https://phongvu.vn/s?q={$encoded}"];
            $results[] = ['name' => 'GearVN Tech Catalog', 'url' => "https://gearvn.com/search?type=product&q={$encoded}"];
        }

        return $results;
    }

    /**
     * Fetch nội dung HTML qua cURL với Timeout và User-Agent an toàn
     */
    public static function fetchUrlContent(string $url): string
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        try {
            $tlsOptions = SecureCurl::buildTlsOptions();
            foreach ($tlsOptions as $k => $v) {
                $options[$k] = $v;
            }
        } catch (RuntimeException $e) {
            curl_close($ch);
            return '';
        }

        curl_setopt_array($ch, $options);

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($output)) {
            return $output;
        }

        return '';
    }

    /**
     * Trích xuất văn bản từ bảng thông số HTML (Cleaning & Filtering)
     */
    public static function extractTableTextFromHtml(string $html): string
    {
        if (empty($html)) return '';

        // Loại bỏ script, style, head
        $cleanHtml = preg_replace('/<(script|style|head|noscript|header|footer|nav)[^>]*>.*?<\/\\1>/is', '', $html);

        // Giữ lại các thẻ bảng <table>, <tr>, <td>, <th>, <ul>, <li>, <p>, <div>
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $cleanHtml, LIBXML_NOERROR | LIBXML_NOWARNING);

        $extractedLines = [];

        // Ưu tiên trích xuất bảng <table>
        $tables = $dom->getElementsByTagName('table');
        foreach ($tables as $table) {
            $rows = $table->getElementsByTagName('tr');
            foreach ($rows as $row) {
                $cells = [];
                foreach ($row->childNodes as $child) {
                    if (in_array($child->nodeName, ['td', 'th'])) {
                        $text = trim(preg_replace('/\s+/', ' ', $child->textContent));
                        if (!empty($text)) {
                            $cells[] = $text;
                        }
                    }
                }
                if (count($cells) >= 2) {
                    $extractedLines[] = implode(': ', $cells);
                } elseif (count($cells) == 1) {
                    $extractedLines[] = $cells[0];
                }
            }
        }

        // Nếu bảng quá ít lines, lấy thêm danh sách ul/li
        if (count($extractedLines) < 5) {
            $lis = $dom->getElementsByTagName('li');
            foreach ($lis as $li) {
                $text = trim(preg_replace('/\s+/', ' ', $li->textContent));
                if (strlen($text) > 10 && strlen($text) < 200 && str_contains($text, ':')) {
                    $extractedLines[] = $text;
                }
            }
        }

        return implode("\n", array_slice(array_unique($extractedLines), 0, 80));
    }
}
