<?php

namespace App\Livewire;

use App\Repositories\CategoryRepository;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Categories Page - EcomShop")]
class CategoriesPage extends Component
{
    protected CategoryRepository $categoryRepository;

    public function boot(CategoryRepository $categoryRepository){
        $this->categoryRepository = $categoryRepository;
    }
    public function render()
    {
        $categories = $this->categoryRepository->getActiveCategories();
        return view('livewire.categories-page',compact('categories'));
    }
}
