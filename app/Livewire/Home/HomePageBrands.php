<?php

namespace App\Livewire\Home;

use App\Models\Brand;
use Livewire\Component;

class HomePageBrands extends Component
{
    public function render()
    {
        $brands=Brand::query()->where('is_active',1)->get();
        return view('livewire.home.home-page-brands',['brands'=>$brands]);
    }
}
