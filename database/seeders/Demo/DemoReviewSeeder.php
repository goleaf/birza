<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('reviews') || ! Schema::hasTable('users')) {
            return;
        }

        $buyer = User::query()->where('email', 'buyer@example.com')->firstOrFail();
        $hybrid = User::query()->where('email', 'buyer-seller@example.com')->firstOrFail();

        $this->review('Demo Active Apples', $buyer, 5, true, 'Reliable apples', 'Fresh, well packed, and easy to reorder.');
        $this->review('Demo Published Product', $hybrid, 4, true, 'Good demo product', null);
        $this->review('Demo Seller Two Bread', $buyer, 2, false, 'Needs review', 'Seeded unapproved review for admin filters.');
        $this->softDeletedReview('Demo Changed Price Cheese', $hybrid);
    }

    private function review(
        string $productName,
        User $user,
        int $rating,
        bool $isApproved,
        ?string $title,
        ?string $body,
    ): Review {
        $product = Product::withTrashed()->where('name', $productName)->firstOrFail();
        $review = Review::withTrashed()->firstOrNew([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $review->fill([
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'is_approved' => $isApproved,
        ]);

        if ($review->trashed()) {
            $review->restore();
        }

        $review->save();

        return $review;
    }

    private function softDeletedReview(string $productName, User $user): void
    {
        $review = $this->review(
            productName: $productName,
            user: $user,
            rating: 3,
            isApproved: true,
            title: 'Soft deleted demo review',
            body: 'This review exists to test soft deleted review handling.',
        );

        $review->delete();
    }
}
