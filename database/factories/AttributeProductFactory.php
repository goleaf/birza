<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeProduct;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeProduct>
 */
class AttributeProductFactory extends Factory
{
    protected $model = AttributeProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attribute = Attribute::factory()->create();
        $value = AttributeValue::factory()
            ->for($attribute)
            ->create();

        return [
            'attribute_id' => $attribute->getKey(),
            'product_id' => Product::factory(),
            'selected_value_id' => $value->getKey(),
        ];
    }

    public function forProductAttribute(Product $product, Attribute $attribute, ?AttributeValue $value = null): static
    {
        $value ??= AttributeValue::factory()
            ->for($attribute)
            ->create();

        return $this->state(fn (array $attributes): array => [
            'attribute_id' => $attribute->getKey(),
            'product_id' => $product->getKey(),
            'selected_value_id' => $value->getKey(),
        ]);
    }
}
