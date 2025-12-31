<?php

namespace App\Livewire\Home;

use App\Models\Category;
use Livewire\Component;

class HomePageCategories extends Component
{
    public function render()
    {
        $categories = Category::query()->where('is_active', 1)->get();
        return view('livewire.home.home-page-categories', compact('categories'));
    }
}
