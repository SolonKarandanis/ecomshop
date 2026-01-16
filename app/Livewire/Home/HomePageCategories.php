<?php

namespace App\Livewire\Home;

use App\Repositories\CategoryRepository;
use Livewire\Component;

class HomePageCategories extends Component
{

    protected CategoryRepository $categoryRepository;

    public function placeholder(){
        return view('livewire.home.home-page-categories-placeholder');
    }

    public function boot(CategoryRepository $categoryRepository){
        $this->categoryRepository = $categoryRepository;
    }
    public function render()
    {
        $categories = $this->categoryRepository->getActiveCategories();
        return view('livewire.home.home-page-categories', compact('categories'));
    }
}
