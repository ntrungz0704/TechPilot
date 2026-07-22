<?php
/**
 * app/services/PostPublishingValidator.php
 * Production validation service for post creation and editing.
 * Guarantees field-level validation for title and published content before any side effects.
 */

require_once ROOT_PATH . '/app/models/Post.php';

class PostPublishingValidator
{
    /**
     * Validates input data for post store or update.
     *
     * @param array $input Form data input ($_POST array)
     * @return array ['valid' => bool, 'errors' => array<string, string>]
     */
    public static function validate(array $input): array
    {
        $errors  = [];
        $title   = trim((string)($input['title'] ?? ''));
        $content = trim((string)($input['content'] ?? ''));
        $status  = (string)($input['status'] ?? 'draft');

        if ($title === '') {
            $errors['title'] = 'Vui lòng nhập tiêu đề bài viết.';
        }

        if ($status === 'published') {
            $contentValidation = Post::validatePublishedContent($content, 'published');
            if (!$contentValidation['valid']) {
                $errors['content'] = implode(' ', $contentValidation['errors']);
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }
}
