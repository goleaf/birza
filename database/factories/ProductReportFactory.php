<?php

namespace Database\Factories;

use App\Enums\ProductReportReason;
use App\Enums\ProductReportStatus;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReport>
 */
class ProductReportFactory extends Factory
{
    protected $model = ProductReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'reporter_id' => Buyer::factory(),
            'reporter_email' => null,
            'reporter_fingerprint' => $this->faker->sha256(),
            'reason' => $this->faker->randomElement(ProductReportReason::cases()),
            'message' => $this->faker->optional()->paragraph(),
            'status' => ProductReportStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_note' => null,
        ];
    }

    public function pending(): static
    {
        return $this->status(ProductReportStatus::Pending);
    }

    public function reviewing(?Admin $admin = null): static
    {
        return $this->status(ProductReportStatus::Reviewing, $admin);
    }

    public function resolved(?Admin $admin = null): static
    {
        return $this->status(ProductReportStatus::Resolved, $admin);
    }

    public function rejected(?Admin $admin = null): static
    {
        return $this->status(ProductReportStatus::Rejected, $admin);
    }

    public function dismissed(?Admin $admin = null): static
    {
        return $this->status(ProductReportStatus::Dismissed, $admin);
    }

    public function guest(?string $email = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'reporter_id' => null,
            'reporter_email' => $email ?? $this->faker->safeEmail(),
            'reporter_fingerprint' => $this->faker->sha256(),
        ]);
    }

    public function reason(ProductReportReason $reason): static
    {
        return $this->state(fn (array $attributes): array => [
            'reason' => $reason,
        ]);
    }

    public function softDeleted(): static
    {
        return $this->afterCreating(function (ProductReport $report): void {
            $report->delete();
        });
    }

    private function status(ProductReportStatus $status, ?Admin $admin = null): static
    {
        $isPending = $status === ProductReportStatus::Pending;

        return $this->state(fn (array $attributes): array => [
            'status' => $status,
            'reviewed_by' => $isPending ? null : ($admin?->id ?? Admin::factory()),
            'reviewed_at' => $isPending ? null : now(),
            'admin_note' => $isPending ? null : $this->faker->sentence(),
        ]);
    }
}
