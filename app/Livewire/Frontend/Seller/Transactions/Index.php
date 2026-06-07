<?php

namespace App\Livewire\Frontend\Seller\Transactions;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    #[Url(as: 'type')]
    public string $type = '';

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_to')]
    public ?string $dateTo = null;

    public function applyFilters(): void
    {
        //
    }

    public function render()
    {
        $seller = Auth::guard('seller')->user();

        $transactions = $seller->transactions()
            ->with(['order'])
            ->when($this->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($this->dateFrom, function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($this->dateTo, function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate(15);

        $filters = [
            'type' => $this->type,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];

        $stats = [
            'total_deductions' => $seller->transactions()->where('type', 'deduction')->sum('amount'),
            'total_refunds' => $seller->transactions()->where('type', 'refund')->sum('amount'),
            'current_balance' => $seller->balance,
        ];

        return view('frontend.seller.transactions.index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }
}
