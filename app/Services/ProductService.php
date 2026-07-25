<?php

namespace App\Services;

use App\Dtos\ProductSearchFilterDto;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService
{

    public function __construct(
        private readonly ProductRepository $productRepository
    ){}

    public function getProductById(int $id): Product{
        return $this->productRepository->getProductById($id);
    }

    public function getProductBySlug($slug): Product{
        return $this->productRepository->getProductBySlug($slug);
    }

    public function searchProducts(ProductSearchFilterDto $dto): LengthAwarePaginator|array{
        return $this->productRepository->searchProducts($dto);
    }

    public function findProductsByIds(array $productIds): Collection{
        return $this->productRepository->findProductsByIds($productIds);
    }

    public function findProductsForCart(array $productIds): Collection{
        return $this->productRepository->findProductsForCart($productIds);
    }

    public function updateRatingStats(int $productId, ?float $averageRating, int $reviewsCount): bool{
        return $this->updateRatingStats($productId, $averageRating, $reviewsCount);
    }

    public function lockForUpdate(int $productId): Product{
        return $this->productRepository->lockForUpdate($productId);
    }
}
