<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

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
            'product_id' => Product::factory(),
            'attribute_id' => $attribute->getKey(),
            'attribute_value_id' => $value->getKey(),
        ];
    }

    public function forProductAttribute(Product $product, Attribute $attribute, ?AttributeValue $value = null): static
    {
        $value ??= AttributeValue::factory()
            ->for($attribute)
            ->create();

        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->getKey(),
            'attribute_id' => $attribute->getKey(),
            'attribute_value_id' => $value->getKey(),
        ]);
    }
}
