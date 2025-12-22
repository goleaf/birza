<?php

namespace App\Livewire\Frontend\Seller\Transactions;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    public function render()
    {
        $seller = Auth::guard('seller')->user();

        $transactions = $seller->transactions()
            ->with(['order'])
            ->when(request()->get('type'), function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when(request()->get('date_from'), function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when(request()->get('date_to'), function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate(15);

        $filters = [
            'type' => request()->get('type'),
            'dateFrom' => request()->get('date_from'),
            'dateTo' => request()->get('date_to'),
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


