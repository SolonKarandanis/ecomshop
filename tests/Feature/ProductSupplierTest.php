<?php

use App\Enums\RolesEnum;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

function createProductSupplierAdminUser(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_ADMIN->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createProductSupplierUser(): User
{
    $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_SUPPLIER->value]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('sets supplier_id to the acting Admin when creating a Product while the flag is disabled', function () {
    config(['features.suppliers_enabled' => false]);

    $admin = createProductSupplierAdminUser();
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    actingAs($admin);
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Disabled Flag Product',
            'slug' => 'disabled-flag-product',
            'price' => 49.99,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'productAttributeValues' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'disabled-flag-product')->firstOrFail();
    expect($product->supplier_id)->toBe($admin->id);
});

it('respects the selected Supplier when creating a Product while the flag is enabled', function () {
    config(['features.suppliers_enabled' => true]);

    $admin = createProductSupplierAdminUser();
    $supplier = createProductSupplierUser();
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    actingAs($admin);
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Enabled Flag Product',
            'slug' => 'enabled-flag-product',
            'price' => 59.99,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'productAttributeValues' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('slug', 'enabled-flag-product')->firstOrFail();
    expect($product->supplier_id)->toBe($supplier->id);
});

it('backfills supplier_id on pre-existing Products to the first Admin by id during migration', function () {
    $migrationPath = ['database/migrations/2026_07_30_041505_add_suplier_id_to_products_table.php'];

    Artisan::call('migrate:rollback', ['--path' => $migrationPath, '--force' => true]);

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $productId = DB::table('products')->insertGetId([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Legacy Product',
        'slug' => 'legacy-product',
        'price' => 19.99,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $firstAdmin = createProductSupplierAdminUser();
    createProductSupplierAdminUser();

    Artisan::call('migrate', ['--path' => $migrationPath, '--force' => true]);

    expect(DB::table('products')->where('id', $productId)->value('supplier_id'))
        ->toBe($firstAdmin->id);
});
