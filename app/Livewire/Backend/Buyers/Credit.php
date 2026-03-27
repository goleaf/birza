<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.backend.app')]
class Credit extends Component
{
    use WithFileUploads;

    public Buyer $buyer;

    public ?string $selectedAction = null; // 'add'|'deduct'
    public ?float $amount = null;
    public ?string $note = null;
    public $attachment = null;

    public function mount(Buyer $buyer): void
    {
        $this->buyer = $buyer;
    }

    public function selectAction(string $action): void
    {
        $this->selectedAction = $action;
        $this->resetValidation();
        $this->amount = null;
        $this->note = null;
        $this->attachment = null;
    }

    public function submitCredit(): void
    {
        if (! in_array($this->selectedAction, ['add', 'deduct'], true)) {
            $this->addError('action', __('common_error_occurred'));
            return;
        }

        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:5120'],
        ]);

        $buyer = $this->buyer->refresh();

        if ($this->selectedAction === 'deduct' && $validated['amount'] > $buyer->credit_balance) {
            $this->addError('amount', __('backend_credit_insufficient_funds'));
            return;
        }

        DB::transaction(function () use ($buyer, $validated) {
            $newBalance = $this->selectedAction === 'add'
                ? ($buyer->credit_balance + $validated['amount'])
                : ($buyer->credit_balance - $validated['amount']);

            $creditHistory = BuyerCreditHistory::create([
                'buyer_id' => $buyer->id,
                'amount' => $validated['amount'],
                'type' => $this->selectedAction === 'add' ? 'add' : 'deduct',
                'balance_after' => $newBalance,
                'note' => $validated['note'] ?? null,
                'admin_id' => auth()->id(),
            ]);

            if ($this->attachment) {
                $path = $this->attachment->store('credit-attachments', 'public');

                CreditAttachment::create([
                    'credit_history_id' => $creditHistory->id,
                    'file_path' => $path,
                    'original_name' => $this->attachment->getClientOriginalName(),
                ]);
            }

            $buyer->update(['credit_balance' => $newBalance]);
        });

        $this->buyer->refresh();
        $this->reset(['selectedAction', 'amount', 'note', 'attachment']);

        session()->flash('success', __('backend_common_success_message'));
    }

    public function render()
    {
        $creditHistory = $this->buyer->creditHistory()
            ->with('admin')
            ->latest()
            ->paginate(10);

        return view('backend.buyers.credit', [
            'buyer' => $this->buyer,
            'creditHistory' => $creditHistory,
        ]);
    }
}


