<?php

class ProductController extends Controller
{
    /** Trang chi tiết sản phẩm: /product/detail/{slug} */
    public function detail(string $slug = ''): void
    {
        $productModel = $this->model('Product');
        $reviewModel  = $this->model('Review');
        $product = $productModel->getBySlug($slug);

        if (!$product) {
            http_response_code(404);
            $this->render('home/404', ['pageTitle' => 'Không tìm thấy sản phẩm']);
            return;
        }

        $specs = json_decode($product['specs'] ?? '{}', true) ?: [];
        $related = $productModel->getRelated((int)$product['category_id'], (int)$product['id'], 6);
        $productImages = $productModel->getProductImages((int)$product['id']);
        $reviews = $reviewModel->getByProduct((int)$product['id']);

        $this->render('product/detail', [
            'pageTitle'     => $product['name'],
            'product'       => $product,
            'specs'         => $specs,
            'related'       => $related,
            'productImages' => $productImages,
            'reviews'       => $reviews,
        ]);
    }
}
