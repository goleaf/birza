<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\Users\Buyer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public function deleteBuyer(int $buyerId): void
    {
        Buyer::query()->findOrFail($buyerId)->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function render()
    {
        $request = request();

        $query = Buyer::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->search.'%';
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('company_name', 'like', $search)
                        ->orWhere('company_code', 'like', $search)
                        ->orWhere('vat_code', 'like', $search);
                });
            })
            ->when($request->filled('is_verified'), function ($q) use ($request) {
                $q->where('is_verified', $request->is_verified === 'true');
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->is_active === 'true');
            })
            ->when($request->filled('min_balance'), function ($q) use ($request) {
                $q->where('credit_balance', '>=', $request->min_balance);
            })
            ->when($request->filled('max_balance'), function ($q) use ($request) {
                $q->where('credit_balance', '<=', $request->max_balance);
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                [$column, $direction] = explode(',', $request->sort);
                $q->orderBy($column, $direction);
            }, function ($q) {
                $q->latest();
            });

        return view('backend.buyers.index', [
            'buyers' => $query->paginate(15)->withQueryString(),
        ]);
    }
}


