<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Products\Create as ProductCreate;
use App\Livewire\Backend\Products\Edit as ProductEdit;
use App\Livewire\Backend\Products\Index as ProductIndex;
use App\Livewire\Backend\Products\Show as ProductShow;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_products(): void
    {
        $admin = Admin::factory()->create();
        Product::factory()->create([
            'product_image' => '',
            'product_additional_image' => '',
        ]);
        Product::factory()->count(4)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.products.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(ProductIndex::class)
            ->assertSee(__('common_actions'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('common_delete'))
            ->assertSee('!rounded-box', false);
    }

    public function test_product_delete_confirmation_uses_mary_modal_flow(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(ProductIndex::class)
            ->call('confirmDeleteProduct', $product->id)
            ->assertSet('confirmModal', true)
            ->assertSet('confirmModalMethod', 'deleteProduct')
            ->assertSet('confirmModalAcceptLabel', __('common_delete'))
            ->call('runConfirmedAction')
            ->assertSet('confirmModal', false);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_create_form_displays(): void
    {
        $admin = Admin::factory()->create();
        Seller::factory()->create([
            'company_name' => 'Baltic Farm',
            'name' => 'Jonas Seller',
            'email' => 'seller@example.com',
        ]);
        Category::factory()->create([
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pieno'],
        ]);
        $country = Country::factory()->create([
            'country_name' => ['en' => 'Lithuania', 'lt' => 'Lietuva'],
            'alpha2' => 'LT',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.products.create'));

        $response->assertStatus(200)
            ->assertSeeLivewire(ProductCreate::class)
            ->assertSee('Baltic Farm')
            ->assertSee('seller@example.com')
            ->assertSee($country->getTranslation('country_name', app()->getLocale()))
            ->assertSee('LT')
            ->assertSee('No results found.')
            ->assertSee('type="range"', false)
            ->assertSee(__('common_add_images'))
            ->assertSee(__('backend_products_image_library_hint'))
            ->assertSee(__('backend_products_description_hint'))
            ->assertSee(__('backend_products_description_placeholder'))
            ->assertSee(__('backend_products_temperature_range_hint'))
            ->assertSee(__('backend_products_shelf_life_range_hint'))
            ->assertSee(__('product_use_until'))
            ->assertSee(__('product_total_shelf_life'))
            ->assertSee(__('units_unit_kg'))
            ->assertSee(__('backend_products_fields_is_organic'))
            ->assertSee(__('backend_products_fields_is_active'));
    }

    public function test_product_edit_form_displays(): void
    {
        $admin = Admin::factory()->create();
        $seller = Seller::factory()->create([
            'company_name' => 'Forest Goods',
            'name' => 'Asta Seller',
            'email' => 'forest@example.com',
        ]);
        $category = Category::factory()->create([
            'category_name' => ['en' => 'Mushrooms', 'lt' => 'Grybai'],
        ]);
        $country = Country::factory()->create([
            'country_name' => ['en' => 'Latvia', 'lt' => 'Latvija'],
            'alpha2' => 'LV',
        ]);
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'country_of_origin' => $country->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.products.edit', $product));

        $response->assertStatus(200)
            ->assertSeeLivewire(ProductEdit::class)
            ->assertSee('Forest Goods')
            ->assertSee('forest@example.com')
            ->assertSee($country->getTranslation('country_name', app()->getLocale()))
            ->assertSee('LV')
            ->assertSee('No results found.')
            ->assertSee('type="range"', false)
            ->assertSee(__('common_add_images'))
            ->assertSee(__('backend_products_image_library_hint'))
            ->assertSee(__('backend_products_description_hint'))
            ->assertSee(__('backend_products_description_placeholder'))
            ->assertSee(__('backend_products_temperature_range_hint'))
            ->assertSee(__('backend_products_shelf_life_range_hint'))
            ->assertSee(__('product_use_until'))
            ->assertSee(__('product_total_shelf_life'))
            ->assertSee(__('units_unit_pack'))
            ->assertSee(__('backend_products_fields_is_organic'))
            ->assertSee(__('backend_products_fields_is_active'));
    }

    public function test_product_create_persists_toggle_states(): void
    {
        Storage::fake('public');

        $admin = Admin::factory()->create();
        $seller = Seller::factory()->create();
        $category = Category::factory()->create();
        $country = Country::factory()->create();

        $this->actingAs($admin, 'admin');

        $primaryImage = UploadedFile::fake()->image('product.jpg');
        $secondaryImage = UploadedFile::fake()->image('product-detail.jpg');

        Livewire::test(ProductCreate::class)
            ->set('name', 'Toggle Product')
            ->set('seller_id', $seller->id)
            ->set('category_id', $category->id)
            ->set('country_of_origin', $country->id)
            ->set('price', 12.5)
            ->set('pack_type', 'Box')
            ->set('unit', 'kg')
            ->set('stock', 8)
            ->set('min_order_count', 1)
            ->set('temperature_conditions_from', 2)
            ->set('temperature_conditions_to', 6)
            ->set('use_until', '2026-12-31')
            ->set('total_shelf_life', 30)
            ->set('description', 'Toggle-ready product')
            ->set('is_organic', true)
            ->set('is_active', false)
            ->set('imageFiles.*', [$primaryImage, $secondaryImage])
            ->call('refreshMediaSources', 'imageFiles', 'imageLibrary')
            ->call('save')
            ->assertRedirect(route('backend.products.index'));

        $product = Product::query()->where('name', 'Toggle Product')->firstOrFail();

        $this->assertTrue($product->is_organic);
        $this->assertFalse($product->is_active);
        $this->assertSame(2, $product->temperature_conditions_from);
        $this->assertSame(6, $product->temperature_conditions_to);
        $this->assertSame('2026-12-31', $product->use_until?->format('Y-m-d'));
        $this->assertSame(30, $product->total_shelf_life);
        $this->assertCount(2, $product->image_library);
        $this->assertSame(basename($product->image_library->first()['path']), $product->product_image);
        $this->assertSame(basename($product->image_library->get(1)['path']), $product->product_additional_image);
        $this->assertNotEmpty($product->image_library->pluck('path')->filter()->all());
    }

    public function test_product_edit_updates_toggle_states(): void
    {
        Storage::fake('public');

        $admin = Admin::factory()->create();
        $product = Product::factory()->create([
            'is_organic' => false,
            'is_active' => true,
            'image_library' => null,
            'product_image' => 'legacy-primary.webp',
            'product_additional_image' => 'legacy-secondary.webp',
        ]);

        Storage::disk('public')->put('products/legacy-primary.webp', 'legacy-primary');
        Storage::disk('public')->put('products/legacy-secondary.webp', 'legacy-secondary');

        $this->actingAs($admin, 'admin');

        Livewire::test(ProductEdit::class, ['product' => $product])
            ->set('is_organic', true)
            ->set('is_active', false)
            ->set('temperature_conditions_from', 1)
            ->set('temperature_conditions_to', 4)
            ->set('use_until', '2026-08-15')
            ->set('total_shelf_life', 21)
            ->call('save')
            ->assertRedirect(route('backend.products.index'));

        $product->refresh();

        $this->assertTrue($product->is_organic);
        $this->assertFalse($product->is_active);
        $this->assertSame(1, $product->temperature_conditions_from);
        $this->assertSame(4, $product->temperature_conditions_to);
        $this->assertSame('2026-08-15', $product->use_until?->format('Y-m-d'));
        $this->assertSame(21, $product->total_shelf_life);
        $this->assertCount(2, $product->image_library);
        $this->assertSame('legacy-primary.webp', basename($product->image_library->first()['path']));
        $this->assertSame('legacy-secondary.webp', basename($product->image_library->get(1)['path']));
    }

    public function test_product_show_displays(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Creamy Butter',
            'is_active' => false,
            'product_image' => '',
            'product_additional_image' => '',
            'temperature_conditions_from' => 1,
            'temperature_conditions_to' => 5,
            'use_until' => '2026-09-01',
            'total_shelf_life' => 14,
        ]);
        $attribute = Attribute::factory()->create([
            'name' => ['en' => 'Texture', 'lt' => 'Tekstūra'],
        ]);
        $attributeValue = AttributeValue::factory()->for($attribute)->create([
            'value' => ['en' => 'Soft', 'lt' => 'Minkšta'],
        ]);
        $product->attributeValues()->attach($attributeValue->id);
        $expectedAttributeName = $attribute->getTranslation('name', app()->getLocale());
        $expectedAttributeValue = $attributeValue->getTranslation('value', app()->getLocale());

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.products.show', $product));

        $response->assertStatus(200)
            ->assertSeeLivewire(ProductShow::class)
            ->assertSee('Creamy Butter')
            ->assertSee($expectedAttributeName)
            ->assertSee($expectedAttributeValue)
            ->assertSee(__('product_use_until'))
            ->assertSee('2026-09-01')
            ->assertSee(__('product_total_shelf_life'))
            ->assertSee('14')
            ->assertSee(__('backend_products_show_inactive_alert'))
            ->assertSee(__('backend_products_show_no_images_alert'));
    }
}
