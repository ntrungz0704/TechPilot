<?php

/**
 * NewsController - Redirect alias controller
 *
 * Route /tin-tuc và /tin-tuc/{slug} đã được chuyển thành
 * alias redirect sang hệ thống chính /post và /post/detail/{slug}.
 * Không render UI riêng, không dùng fixture.
 */
class NewsController extends Controller
{
    public function index(): void
    {
        // Redirect /tin-tuc → /post
        $this->redirect('post');
    }

    public function show(string $slug): void
    {
        // Redirect /tin-tuc/{slug} → /post/detail/{slug}
        $this->redirect('post/detail/' . rawurlencode($slug));
    }
}
