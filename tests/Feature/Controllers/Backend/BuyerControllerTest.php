<?php

namespace Tests\Feature\Controllers\Backend;

use App\Enums\OrderPaymentStatus;
use App\Livewire\Backend\Buyers\Credit as BuyerCreditPage;
use App\Livewire\Backend\Buyers\Form as BuyerForm;
use App\Livewire\Backend\Buyers\Index as BuyerIndex;
use App\Livewire\Backend\Buyers\Orders as BuyerOrdersPage;
use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use App\Models\Order;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BuyerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.buyers.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_buyer_index_displays_buyers(): void
    {
        $admin = Admin::factory()->create();
        Buyer::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.buyers.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_buyers'))
            ->assertSee(__('common_actions'))
            ->assertSee(__('common_balance'))
            ->assertSee(__('common_orders'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('common_delete'));
    }

    public function test_buyer_create_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.buyers.create'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_buyers'))
            ->assertSee(__('backend_buyers_fields_password'));
    }

    public function test_buyer_edit_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.buyers.edit', $buyer));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_buyers'));
    }

    public function test_buyer_orders_display_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create([
            'company_name' => 'Buyer Market',
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'order_total' => 150.25,
            'payment_status' => OrderPaymentStatus::Paid,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.buyers.orders', $buyer));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerOrdersPage::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_buyers'))
            ->assertSee(__('common_orders'))
            ->assertSee('Buyer Market')
            ->assertSee('#'.$order->id)
            ->assertSee('150.25');
    }

    public function test_buyer_credit_page_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create([
            'company_name' => 'Credit Buyer',
        ]);
        BuyerCreditHistory::factory()->create([
            'buyer_id' => $buyer->id,
            'admin_id' => $admin->id,
            'amount' => 75.5,
            'type' => 'add',
            'balance_after' => 200.0,
            'note' => 'Manual top-up',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.buyers.credit', $buyer));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerCreditPage::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_buyers'))
            ->assertSee(__('backend_buyers_credit_manage_credit'))
            ->assertSee('Credit Buyer')
            ->assertSee(__('backend_buyers_credit_note_hint'))
            ->assertSee(__('backend_buyers_credit_note_placeholder'))
            ->assertSee(__('common_optional_upload_supporting_document'))
            ->assertSee('Manual top-up')
            ->assertSee('75.50');
    }

    public function test_buyer_credit_submission_can_store_attachment(): void
    {
        Storage::fake('public');

        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create([
            'credit_balance' => 100,
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(BuyerCreditPage::class, ['buyer' => $buyer])
            ->call('selectAction', 'add')
            ->set('amount', 25)
            ->set('note', 'Receipt attached')
            ->set('attachment', UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf'))
            ->call('submitCredit')
            ->assertHasNoErrors()
            ->assertSet('selectedAction', null)
            ->assertSet('attachment', null);

        $buyer->refresh();

        $this->assertSame('125.00', number_format((float) $buyer->credit_balance, 2, '.', ''));

        $history = BuyerCreditHistory::query()
            ->where('buyer_id', $buyer->id)
            ->where('note', 'Receipt attached')
            ->firstOrFail();

        $attachment = CreditAttachment::query()
            ->where('credit_history_id', $history->id)
            ->firstOrFail();

        $this->assertSame('receipt.pdf', $attachment->original_name);
        Storage::disk('public')->assertExists($attachment->file_path);
    }
}
