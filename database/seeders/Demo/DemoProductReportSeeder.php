<?php

namespace Database\Seeders\Demo;

use App\Enums\ProductReportReason;
use App\Enums\ProductReportStatus;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoProductReportSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_reports')) {
            return;
        }

        $buyer = Buyer::query()->where('email', 'buyer@example.com')->firstOrFail();
        $admin = Admin::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->report('Demo Published Product', $buyer, ProductReportReason::WrongPrice, ProductReportStatus::Pending);
        $this->report('Demo Product Without Image', $buyer, ProductReportReason::MisleadingDescription, ProductReportStatus::Reviewing, $admin);
        $this->report('Demo Inactive Honey', $buyer, ProductReportReason::ProhibitedItem, ProductReportStatus::Resolved, $admin);
        $this->report('Demo Seller Two Bread', $buyer, ProductReportReason::DuplicateProduct, ProductReportStatus::Rejected, $admin);
        $this->guestReport('Demo High Price Product', ProductReportReason::Scam, ProductReportStatus::Dismissed, $admin);
        $this->paginationReports($buyer, $admin);
    }

    private function report(
        string $productName,
        Buyer $buyer,
        ProductReportReason $reason,
        ProductReportStatus $status,
        ?Admin $admin = null,
    ): ProductReport {
        $product = Product::withTrashed()->where('name', $productName)->firstOrFail();

        return ProductReport::query()->updateOrCreate([
            'product_id' => $product->id,
            'reporter_id' => $buyer->id,
            'reason' => $reason->value,
        ], $this->data($status, $admin) + [
            'reporter_email' => null,
            'reporter_fingerprint' => hash('sha256', 'buyer:'.$buyer->id.':'.$product->id),
            'message' => 'Demo product report for '.$productName,
        ]);
    }

    private function guestReport(
        string $productName,
        ProductReportReason $reason,
        ProductReportStatus $status,
        Admin $admin,
    ): ProductReport {
        $product = Product::withTrashed()->where('name', $productName)->firstOrFail();

        return ProductReport::query()->updateOrCreate([
            'product_id' => $product->id,
            'reporter_email' => 'guest-reporter@example.com',
            'reason' => $reason->value,
        ], $this->data($status, $admin) + [
            'reporter_id' => null,
            'reporter_fingerprint' => hash('sha256', 'guest:'.$product->id),
            'message' => 'Guest demo product report for '.$productName,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function data(ProductReportStatus $status, ?Admin $admin): array
    {
        $isPending = $status === ProductReportStatus::Pending;

        return [
            'status' => $status,
            'reviewed_by' => $isPending ? null : $admin?->id,
            'reviewed_at' => $isPending ? null : now()->subDay(),
            'admin_note' => $isPending ? null : 'Demo moderation note',
        ];
    }

    private function paginationReports(Buyer $buyer, Admin $admin): void
    {
        Product::query()
            ->where('name', 'like', 'Demo Pagination Product%')
            ->orderBy('id')
            ->limit(20)
            ->get(['id', 'name'])
            ->each(function (Product $product, int $index) use ($buyer, $admin): void {
                $status = match ($index % 5) {
                    0 => ProductReportStatus::Pending,
                    1 => ProductReportStatus::Reviewing,
                    2 => ProductReportStatus::Resolved,
                    3 => ProductReportStatus::Rejected,
                    default => ProductReportStatus::Dismissed,
                };

                $reason = ProductReportReason::cases()[$index % count(ProductReportReason::cases())];
                $guestEmail = sprintf('demo-report-%02d@example.com', $index + 1);

                ProductReport::query()->updateOrCreate([
                    'product_id' => $product->id,
                    'reporter_email' => $index % 2 === 0 ? null : $guestEmail,
                    'reporter_id' => $index % 2 === 0 ? $buyer->id : null,
                    'reason' => $reason->value,
                ], $this->data($status, $admin) + [
                    'reporter_fingerprint' => hash('sha256', 'pagination-report:'.$product->id),
                    'message' => 'Pagination demo report for '.$product->name,
                ]);
            });
    }
}
