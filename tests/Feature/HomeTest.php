<?php
use App\Livewire\HomePage;
use function Pest\Livewire\livewire;

it('renders the home page', function () {
    $this->get('/')
        ->assertSeeLivewire(HomePage::class)
        ->assertStatus(200)
        ->assertSee(__('home.title'))
        ->assertSee(__('home.description'))
        ->assertSee(__('home.buttons.start'))
        ->assertSee(__('home.buttons.contact'))
        ->assertSee(__('home.brands_section.browse_popular'))
        ->assertSee(__('home.brands_section.brands'))
        ->assertSee(__('home.brands_section.description'))
        ->assertSee(__('home.categories_section.browse_popular'))
        ->assertSee(__('home.categories_section.description'))
        ->assertSee(__('home.categories_section.categories'))
        ->assertSee(__('home.customer_reviews_section.customer'))
        ->assertSee(__('home.customer_reviews_section.reviews'))
        ->assertSee(__('home.customer_reviews_section.description'));
});

it('contains livewire components', function () {
    livewire(HomePage::class)
        ->assertStatus(200)
        ->assertSeeLivewire('home.home-page-brands')
        ->assertSeeLivewire('home.home-page-categories');
});
