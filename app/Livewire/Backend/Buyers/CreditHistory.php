<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\Users\Buyer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class CreditHistory extends Component
{
    public Buyer $buyer;

    public function mount(Buyer $buyer): void
    {
        $this->buyer = $buyer;
    }

    public function render()
    {
        $query = $this->buyer->creditHistory()->with('admin');

        if (request('type')) {
            $query->where('type', request('type'));
        }

        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        return view('backend.buyers.credit_history', [
            'buyer' => $this->buyer,
            'creditHistory' => $query->latest('created_at')->paginate(15)->withQueryString(),
        ]);
    }
}


