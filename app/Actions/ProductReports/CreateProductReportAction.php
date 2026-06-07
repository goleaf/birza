<?php

namespace App\Actions\ProductReports;

use App\Actions\Notifications\SendProductReportNotificationAction;
use App\Enums\ProductReportReason;
use App\Enums\ProductReportStatus;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateProductReportAction
{
    public function __construct(
        private readonly SendProductReportNotificationAction $sendNotifications,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handle(
        Product $product,
        ProductReportReason $reason,
        ?string $message = null,
        ?Buyer $buyer = null,
        ?Seller $seller = null,
        ?string $reporterEmail = null,
        ?Request $request = null,
    ): ProductReport {
        $request ??= app()->bound('request') ? request() : null;
        $reporterEmail = $this->normalizeEmail($reporterEmail);

        $this->assertReportable($product);
        $this->assertActorCanReport($product, $buyer, $seller, $reporterEmail);
        $this->assertNotDuplicate($product, $buyer, $reporterEmail);
        $this->assertRateLimit($product, $buyer, $reporterEmail, $request);

        $report = ProductReport::query()->create([
            'product_id' => $product->getKey(),
            'reporter_id' => $buyer?->getKey(),
            'reporter_email' => $buyer ? null : $reporterEmail,
            'reporter_fingerprint' => $this->fingerprint($request),
            'reason' => $reason,
            'message' => $this->cleanText($message),
            'status' => ProductReportStatus::Pending,
        ]);

        $report->loadMissing(['product.seller', 'reporter']);

        $this->sendNotifications->newReportCreated($report);

        $this->auditLogService->log(
            actor: $buyer,
            action: 'product_report.created',
            auditable: $report,
            oldValues: null,
            newValues: [
                'product_id' => $report->product_id,
                'reporter_id' => $report->reporter_id,
                'reporter_email' => $report->reporter_email,
                'reason' => $report->reason->value,
                'status' => $report->status->value,
            ],
            metadata: [
                'product_id' => $product->getKey(),
                'seller_id' => $product->seller_id,
                'guest' => $buyer === null,
            ],
            reason: $reason->value,
            request: $request,
        );

        return $report;
    }

    private function assertReportable(Product $product): void
    {
        if ($product->trashed() || ! (bool) $product->is_active) {
            throw ValidationException::withMessages([
                'product' => __('reports.product.not_reportable'),
            ]);
        }
    }

    private function assertActorCanReport(Product $product, ?Buyer $buyer, ?Seller $seller, ?string $reporterEmail): void
    {
        if ($buyer !== null && ! (bool) $buyer->is_active) {
            throw ValidationException::withMessages([
                'product' => __('reports.product.blocked_reporter'),
            ]);
        }

        if ($seller !== null && (int) $seller->getKey() === (int) $product->seller_id) {
            throw ValidationException::withMessages([
                'product' => __('reports.product.seller_own_product'),
            ]);
        }

        if ($buyer === null && ! (bool) config('marketplace.product_reports.allow_guest_reports', true)) {
            throw ValidationException::withMessages([
                'reporterEmail' => __('reports.product.guest_reports_disabled'),
            ]);
        }

        if ($buyer === null && blank($reporterEmail)) {
            throw ValidationException::withMessages([
                'reporterEmail' => __('validation.required', ['attribute' => __('reports.product.reporter_email')]),
            ]);
        }
    }

    private function assertNotDuplicate(Product $product, ?Buyer $buyer, ?string $reporterEmail): void
    {
        $duplicateExists = ProductReport::query()
            ->where('product_id', $product->getKey())
            ->whereIn('status', ProductReportStatus::openValues())
            ->when(
                $buyer !== null,
                fn ($query) => $query->where('reporter_id', $buyer->getKey()),
                fn ($query) => $query->where('reporter_email', $reporterEmail),
            )
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'product' => __('reports.product.already_reported'),
            ]);
        }
    }

    private function assertRateLimit(Product $product, ?Buyer $buyer, ?string $reporterEmail, ?Request $request): void
    {
        $maxAttempts = max(1, (int) config('marketplace.product_reports.rate_limit_per_hour', 5));
        $key = 'product-report:'.$product->getKey().':'.$this->reporterRateKey($buyer, $reporterEmail, $request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'product' => __('reports.product.rate_limited'),
            ]);
        }

        RateLimiter::hit($key, 3600);
    }

    private function reporterRateKey(?Buyer $buyer, ?string $reporterEmail, ?Request $request): string
    {
        if ($buyer !== null) {
            return 'buyer:'.$buyer->getKey();
        }

        if (filled($reporterEmail)) {
            return 'guest-email:'.hash('sha256', (string) $reporterEmail);
        }

        return 'guest-ip:'.hash('sha256', (string) $request?->ip());
    }

    private function fingerprint(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        return hash_hmac(
            'sha256',
            implode('|', [(string) $request->ip(), (string) $request->userAgent()]),
            (string) config('app.key'),
        );
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = Str::of((string) $email)->trim()->lower()->toString();

        return $email === '' ? null : $email;
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
