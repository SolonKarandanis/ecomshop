<?php

namespace App\Livewire\Products;

use App\Repositories\CategoryRepository;
use Livewire\Component;

class ProductCategories extends Component
{
    public $selected_categories = [];
    protected CategoryRepository $categoryRepository;

    public function boot(CategoryRepository $categoryRepository){
        $this->categoryRepository = $categoryRepository;
    }

    public function updatedSelectedCategories()
    {
        $this->dispatch('categoriesUpdated', categories: $this->selected_categories);
    }

    public function render()
    {
        $categories = $this->categoryRepository->getActiveCategories();
        return view('livewire.products.product-categories',compact('categories'));
    }
}
