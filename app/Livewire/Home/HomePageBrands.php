<?php

namespace App\Livewire\Home;

use App\Repositories\BrandRepository;
use Livewire\Component;

class HomePageBrands extends Component
{

    protected BrandRepository $brandRepository;

    public function boot(BrandRepository $brandRepository): void
    {
        $this->brandRepository = $brandRepository;
    }
    public function render()
    {
        $brands=$this->brandRepository->getActiveBrands();
        return view('livewire.home.home-page-brands',compact('brands'));
    }
}
