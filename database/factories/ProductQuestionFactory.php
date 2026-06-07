<?php

namespace Database\Factories;

use App\Enums\ProductQuestionStatus;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductQuestion>
 */
class ProductQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'product_id' => fn (array $attributes): int => Product::factory()->create([
                'seller_id' => $attributes['seller_id'],
            ])->id,
            'buyer_id' => Buyer::factory(),
            'question' => $this->faker->sentence(12),
            'answer' => null,
            'answered_by_seller_id' => null,
            'answered_at' => null,
            'status' => ProductQuestionStatus::Pending,
            'is_public' => false,
            'guest_name' => null,
            'guest_email' => null,
            'moderated_by_admin_id' => null,
            'moderated_at' => null,
            'moderation_reason' => null,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
        ]);
    }

    public function byGuest(?string $name = null, ?string $email = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'buyer_id' => null,
            'guest_name' => $name ?? $this->faker->name(),
            'guest_email' => $email ?? $this->faker->safeEmail(),
        ]);
    }

    public function answered(?string $answer = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer' => $answer ?? $this->faker->paragraph(),
            'answered_by_seller_id' => $attributes['seller_id'] ?? Seller::factory(),
            'answered_at' => now(),
            'status' => ProductQuestionStatus::Answered,
            'is_public' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer' => null,
            'answered_by_seller_id' => null,
            'answered_at' => null,
            'status' => ProductQuestionStatus::Pending,
            'is_public' => false,
        ]);
    }

    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductQuestionStatus::Rejected,
            'is_public' => false,
            'moderated_at' => now(),
            'moderation_reason' => $reason ?? $this->faker->sentence(),
        ]);
    }

    public function hidden(?string $reason = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductQuestionStatus::Hidden,
            'is_public' => false,
            'moderated_at' => now(),
            'moderation_reason' => $reason ?? $this->faker->sentence(),
        ]);
    }
}
