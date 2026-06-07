<?php

namespace Tests\Feature\Marketplace;

use App\Actions\ProductReports\CreateProductReportAction;
use App\Actions\ProductReports\ResolveProductReportAction;
use App\Enums\ProductReportReason;
use App\Enums\ProductReportStatus;
use App\Livewire\Backend\ProductReports\Index as AdminProductReportIndex;
use App\Livewire\Backend\ProductReports\Show as AdminProductReportShow;
use App\Livewire\Frontend\Buyer\Products\Show as ProductShow;
use App\Models\ProductReport;
use App\Notifications\Marketplace\ProductHiddenDueToReportNotification;
use App\Notifications\Marketplace\ProductReportCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class ProductReportFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_buyer_can_report_active_product(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $buyer = $this->createBuyer();
        $product = $this->createProduct();

        $report = app(CreateProductReportAction::class)->handle(
            product: $product,
            reason: ProductReportReason::MisleadingDescription,
            message: 'The product details are misleading.',
            buyer: $buyer,
        );

        $this->assertSame(ProductReportStatus::Pending, $report->status);
        $this->assertDatabaseHas('product_reports', [
            'product_id' => $product->id,
            'reporter_id' => $buyer->id,
            'reason' => ProductReportReason::MisleadingDescription->value,
            'status' => ProductReportStatus::Pending->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => $buyer::class,
            'actor_id' => $buyer->id,
            'action' => 'product_report.created',
            'auditable_type' => ProductReport::class,
            'auditable_id' => $report->id,
        ]);

        Notification::assertSentTo($admin, ProductReportCreatedNotification::class);
    }

    public function test_product_detail_shows_report_button_and_buyer_can_submit_report_via_livewire(): void
    {
        Notification::fake();

        $this->createAdmin();
        $buyer = $this->createBuyer();
        $product = $this->createProduct(['name' => 'Report Button Product']);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.show', $product))
            ->assertOk()
            ->assertSee(__('reports.product.button'));

        Livewire::actingAs($buyer, 'buyer')
            ->test(ProductShow::class, ['product' => $product])
            ->set('reportReason', ProductReportReason::Scam->value)
            ->set('reportMessage', 'This listing looks suspicious.')
            ->call('submitReport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_reports', [
            'product_id' => $product->id,
            'reporter_id' => $buyer->id,
            'reason' => ProductReportReason::Scam->value,
            'status' => ProductReportStatus::Pending->value,
        ]);
    }

    public function test_guest_can_report_product_when_guest_reports_are_enabled(): void
    {
        config()->set('marketplace.product_reports.allow_guest_reports', true);
        Notification::fake();

        $this->createAdmin();
        $product = $this->createProduct();

        Livewire::test(ProductShow::class, ['product' => $product])
            ->set('reportReason', ProductReportReason::WrongPrice->value)
            ->set('reporterEmail', 'Guest@Example.com')
            ->call('submitReport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_reports', [
            'product_id' => $product->id,
            'reporter_id' => null,
            'reporter_email' => 'guest@example.com',
            'reason' => ProductReportReason::WrongPrice->value,
        ]);
    }

    public function test_guest_cannot_report_product_when_guest_reports_are_disabled(): void
    {
        config()->set('marketplace.product_reports.allow_guest_reports', false);
        Notification::fake();

        $this->createAdmin();
        $product = $this->createProduct();

        Livewire::test(ProductShow::class, ['product' => $product])
            ->set('reportReason', ProductReportReason::WrongCategory->value)
            ->set('reporterEmail', 'guest@example.com')
            ->call('submitReport')
            ->assertHasErrors(['reporterEmail']);

        $this->assertDatabaseCount('product_reports', 0);
    }

    public function test_guest_report_requires_email(): void
    {
        $product = $this->createProduct();

        $this->expectException(ValidationException::class);

        try {
            app(CreateProductReportAction::class)->handle(
                product: $product,
                reason: ProductReportReason::Other,
                message: 'Please review this listing.',
            );
        } finally {
            $this->assertDatabaseCount('product_reports', 0);
        }
    }

    public function test_inactive_and_deleted_products_cannot_be_reported(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $inactiveProduct = $this->createProduct(['is_active' => false]);
        $deletedProduct = $this->createProduct();
        $deletedProduct->delete();

        foreach ([$inactiveProduct, $deletedProduct] as $product) {
            try {
                app(CreateProductReportAction::class)->handle(
                    product: $product,
                    reason: ProductReportReason::ProhibitedItem,
                    buyer: $buyer,
                );
                $this->fail('Inactive or deleted product report was not rejected.');
            } catch (ValidationException) {
                $this->assertDatabaseMissing('product_reports', [
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    public function test_invalid_reason_and_message_max_length_are_enforced(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct();

        Livewire::actingAs($buyer, 'buyer')
            ->test(ProductShow::class, ['product' => $product])
            ->set('reportReason', 'invalid-reason')
            ->call('submitReport')
            ->assertHasErrors(['reportReason']);

        Livewire::actingAs($buyer, 'buyer')
            ->test(ProductShow::class, ['product' => $product])
            ->set('reportReason', ProductReportReason::Other->value)
            ->set('reportMessage', str_repeat('a', 1001))
            ->call('submitReport')
            ->assertHasErrors(['reportMessage']);
    }

    public function test_duplicate_open_report_is_rejected_for_same_buyer(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $product = $this->createProduct();

        app(CreateProductReportAction::class)->handle(
            product: $product,
            reason: ProductReportReason::WrongPrice,
            buyer: $buyer,
        );

        $this->expectException(ValidationException::class);

        try {
            app(CreateProductReportAction::class)->handle(
                product: $product,
                reason: ProductReportReason::WrongCategory,
                buyer: $buyer,
            );
        } finally {
            $this->assertDatabaseCount('product_reports', 1);
        }
    }

    public function test_duplicate_open_report_is_rejected_for_same_guest_email(): void
    {
        Notification::fake();

        $this->createAdmin();
        $product = $this->createProduct();

        app(CreateProductReportAction::class)->handle(
            product: $product,
            reason: ProductReportReason::DuplicateProduct,
            reporterEmail: 'guest@example.com',
        );

        $this->expectException(ValidationException::class);

        try {
            app(CreateProductReportAction::class)->handle(
                product: $product,
                reason: ProductReportReason::Scam,
                reporterEmail: 'guest@example.com',
            );
        } finally {
            $this->assertDatabaseCount('product_reports', 1);
        }
    }

    public function test_blocked_buyer_cannot_report_product(): void
    {
        $buyer = $this->createBuyer(['is_active' => false]);
        $product = $this->createProduct();

        $this->expectException(ValidationException::class);

        app(CreateProductReportAction::class)->handle(
            product: $product,
            reason: ProductReportReason::OffensiveContent,
            buyer: $buyer,
        );
    }

    public function test_seller_cannot_report_own_product(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
        ]);

        $this->expectException(ValidationException::class);

        app(CreateProductReportAction::class)->handle(
            product: $product,
            reason: ProductReportReason::Other,
            seller: $seller,
        );
    }

    public function test_admin_can_resolve_product_report(): void
    {
        $admin = $this->createAdmin();
        $report = ProductReport::factory()
            ->pending()
            ->create();

        $resolvedReport = app(ResolveProductReportAction::class)->handle(
            report: $report,
            admin: $admin,
            adminNote: 'Resolved after seller correction.',
        );

        $this->assertSame(ProductReportStatus::Resolved, $resolvedReport->status);
        $this->assertSame($admin->id, $resolvedReport->reviewed_by);
        $this->assertSame('Resolved after seller correction.', $resolvedReport->admin_note);
        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => $admin::class,
            'actor_id' => $admin->id,
            'action' => 'product_report.resolved',
            'auditable_type' => ProductReport::class,
            'auditable_id' => $report->id,
            'reason' => 'Resolved after seller correction.',
        ]);
    }

    public function test_admin_can_view_report_list_and_detail_pages(): void
    {
        $admin = $this->createAdmin();
        $report = ProductReport::factory()
            ->pending()
            ->for($this->createProduct(['name' => 'Moderation Queue Product']))
            ->for($this->createBuyer(), 'reporter')
            ->create();

        $this->actingAs($admin, 'admin')
            ->get(route('backend.reports.index'))
            ->assertOk()
            ->assertSee(__('admin.reports.title'));

        $this->actingAs($admin, 'admin')
            ->get(route('backend.reports.show', $report))
            ->assertOk()
            ->assertSee('Moderation Queue Product');
    }

    public function test_admin_can_dismiss_reject_and_hide_reported_product(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        $dismissedReport = ProductReport::factory()
            ->pending()
            ->for($product)
            ->for($this->createBuyer(), 'reporter')
            ->create();

        Livewire::actingAs($admin, 'admin')
            ->test(AdminProductReportShow::class, ['productReport' => $dismissedReport])
            ->set('adminNote', 'False positive.')
            ->call('dismissReport')
            ->assertHasNoErrors();

        $this->assertSame(ProductReportStatus::Dismissed, $dismissedReport->refresh()->status);

        $rejectedReport = ProductReport::factory()
            ->pending()
            ->for($product)
            ->for($this->createBuyer(), 'reporter')
            ->create(['reason' => ProductReportReason::WrongCategory]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminProductReportShow::class, ['productReport' => $rejectedReport])
            ->set('adminNote', 'No violation found.')
            ->call('rejectReport')
            ->assertHasNoErrors();

        $this->assertSame(ProductReportStatus::Rejected, $rejectedReport->refresh()->status);

        $hiddenReport = ProductReport::factory()
            ->pending()
            ->for($product)
            ->for($this->createBuyer(), 'reporter')
            ->create(['reason' => ProductReportReason::Scam]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminProductReportShow::class, ['productReport' => $hiddenReport])
            ->set('adminNote', 'Confirmed scam report.')
            ->call('hideProduct')
            ->assertHasNoErrors();

        $this->assertFalse((bool) $product->refresh()->is_active);
        $this->assertSame(ProductReportStatus::Resolved, $hiddenReport->refresh()->status);
        Notification::assertSentTo($seller, ProductHiddenDueToReportNotification::class);
    }

    public function test_non_admins_cannot_access_or_moderate_reports(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $report = ProductReport::factory()
            ->pending()
            ->for($this->createProduct(['seller_id' => $seller->id]))
            ->for($buyer, 'reporter')
            ->create();

        $this->actingAs($buyer, 'buyer')
            ->get(route('backend.reports.index'))
            ->assertRedirect(route('home'));

        Livewire::actingAs($seller, 'seller')
            ->test(AdminProductReportShow::class, ['productReport' => $report])
            ->assertForbidden();
    }

    public function test_seller_product_pages_do_not_expose_reporter_private_data(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        ProductReport::factory()
            ->guest('private-reporter@example.com')
            ->for($product)
            ->pending()
            ->create();

        $this->actingAs($seller, 'seller')
            ->get(route('seller.products.edit', $product))
            ->assertOk()
            ->assertDontSee('private-reporter@example.com');
    }

    public function test_admin_report_list_paginates_many_reports(): void
    {
        $admin = $this->createAdmin();

        ProductReport::factory()
            ->count(20)
            ->pending()
            ->create();

        Livewire::actingAs($admin, 'admin')
            ->test(AdminProductReportIndex::class)
            ->assertViewHas('reports', fn ($reports): bool => $reports->perPage() === 15 && $reports->total() === 20);
    }

    public function test_product_report_translation_keys_exist(): void
    {
        $requiredKeys = [
            'reports.product.title',
            'reports.product.button',
            'reports.product.reason',
            'reports.product.message',
            'reports.product.submit',
            'reports.product.created_successfully',
            'reports.product.already_reported',
            'reports.product.status.pending',
            'reports.product.status.reviewing',
            'reports.product.status.resolved',
            'reports.product.status.dismissed',
            'reports.product.reasons.scam',
            'reports.product.reasons.fake_product',
            'reports.product.reasons.wrong_price',
            'reports.product.reasons.wrong_category',
            'reports.product.reasons.prohibited_item',
            'reports.product.reasons.offensive_content',
            'reports.product.reasons.copyright_issue',
            'reports.product.reasons.duplicate_product',
            'reports.product.reasons.misleading_description',
            'reports.product.reasons.other',
            'admin.reports.title',
            'admin.reports.resolve',
            'admin.reports.dismiss',
            'admin.reports.hide_product',
            'notifications.reports.product_created.title',
            'notifications.reports.product_created.message',
        ];

        foreach (['en', 'lt'] as $locale) {
            $translations = json_decode((string) file_get_contents(lang_path("{$locale}.json")), true, 512, JSON_THROW_ON_ERROR);

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $translations);
                $this->assertNotSame($key, $translations[$key]);
            }
        }
    }
}
