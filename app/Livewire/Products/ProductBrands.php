<?php

namespace App\Livewire\Products;

use App\Repositories\BrandRepository;
use Livewire\Component;

class ProductBrands extends Component
{
    public $selected_brands=[];

    protected BrandRepository $brandRepository;

    public function boot(BrandRepository $brandRepository,){
        $this->brandRepository = $brandRepository;
    }

    public function updatedSelectedBrands()
    {
        $this->dispatch('brandsUpdated', brands: $this->selected_brands);
    }

    public function render()
    {
        $brands=$this->brandRepository->getActiveBrands();
        return view('livewire.products.product-brands',compact('brands'));
    }
}
