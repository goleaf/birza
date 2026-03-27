<?php

namespace App\Livewire\Backend\Sellers;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Users\Seller;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithWireUi;

    public function confirmDeleteSeller(int $sellerId): void
    {
        $this->confirmDelete(method: 'deleteSeller', params: $sellerId);
    }

    public function deleteSeller(int $sellerId): void
    {
        Seller::query()->findOrFail($sellerId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function render()
    {
        $request = request();

        $query = Seller::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->search.'%';
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('company_name', 'like', $search)
                        ->orWhere('vat_code', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->is_active === 'true');
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                [$column, $direction] = explode(',', $request->sort);
                $q->orderBy($column, $direction);
            }, function ($q) {
                $q->latest();
            });

        return view('backend.sellers.index', [
            'sellers' => $query->paginate(15)->withQueryString(),
        ]);
    }
}


