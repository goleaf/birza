<?php

namespace Database\Seeders\Demo;

use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoCreditSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('buyer_credit_history')) {
            return;
        }

        $buyer = Buyer::query()->where('email', 'buyer@example.com')->firstOrFail();
        $admin = Admin::query()->where('email', 'admin@example.com')->firstOrFail();

        $add = $this->history($buyer, $admin, 'add', 200.00, 200.00, 'Demo opening buyer credit');
        $this->attachment($add);

        $this->history($buyer, $admin, 'deduct', 50.00, 150.00, 'Demo checkout credit deduction');

        $buyer->forceFill(['credit_balance' => 150.00])->save();
    }

    private function history(
        Buyer $buyer,
        Admin $admin,
        string $type,
        float $amount,
        float $balanceAfter,
        string $note,
    ): BuyerCreditHistory {
        return BuyerCreditHistory::query()->updateOrCreate([
            'buyer_id' => $buyer->id,
            'type' => $type,
            'note' => $note,
        ], [
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'admin_id' => $admin->id,
        ]);
    }

    private function attachment(BuyerCreditHistory $history): void
    {
        if (! Schema::hasTable('credit_attachments')) {
            return;
        }

        CreditAttachment::query()->updateOrCreate([
            'credit_history_id' => $history->id,
            'original_name' => 'demo-credit-note.pdf',
        ], [
            'file_path' => 'attachments/demo-credit-note.pdf',
        ]);
    }
}
