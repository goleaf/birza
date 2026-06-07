<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.backend.app')]
class CreditHistory extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Buyer $buyer;

    #[Url(as: 'type', except: '')]
    public string $typeFilter = '';

    #[Url(as: 'date_from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'date_to', except: '')]
    public string $dateTo = '';

    public bool $drawer = false;

    public function mount(Buyer $buyer): void
    {
        $this->authorize('view', $buyer);
        $this->authorize('viewAny', BuyerCreditHistory::class);

        $this->buyer = $buyer;
    }

    public function applyFilters(): void
    {
        $this->drawer = false;
        $this->resetPage();
    }

    public function clear(): void
    {
        $this->reset('typeFilter', 'dateFrom', 'dateTo');
        $this->drawer = false;
        $this->resetPage();
    }

    public function downloadAttachment(int $attachmentId)
    {
        $attachment = CreditAttachment::whereKey($attachmentId)
            ->whereHas('creditHistory', function ($query) {
                $query->where('buyer_id', $this->buyer->id);
            })
            ->firstOrFail();

        $this->authorize('view', $attachment);

        if (! Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('view', $this->buyer);
        $this->authorize('viewAny', BuyerCreditHistory::class);

        $buyerId = $this->buyer->id;

        $type = $this->typeFilter;
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="credit_history_buyer_'.$buyerId.'.csv"',
        ];

        $callback = function () use ($buyerId, $type, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                __('backend_buyers_credit_history_table_buyer'),
                __('backend_buyers_credit_history_table_amount'),
                __('backend_buyers_credit_history_table_type'),
                __('backend_buyers_credit_history_table_balance_after'),
                __('backend_buyers_credit_history_table_admin'),
                __('backend_buyers_credit_history_table_note'),
                __('backend_buyers_credit_history_table_date'),
            ]);

            $query = BuyerCreditHistory::with(['buyer', 'admin'])
                ->where('buyer_id', $buyerId)
                ->orderByDesc('created_at');

            if ($type) {
                $query->where('type', $type);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $query->chunk(1000, function ($records) use ($file) {
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->buyer?->name ?? '',
                        $record->amount,
                        $record->type,
                        $record->balance_after,
                        $record->admin?->name ?? __('backend_buyers_credit_history_table_system'),
                        $record->note,
                        $record->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }

    public function render()
    {
        $query = $this->buyer->creditHistory()->with([
            'admin:id,name',
            'attachments:id,credit_history_id,original_name,file_path',
        ]);

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return view('backend.buyers.credit_history', [
            'buyer' => $this->buyer,
            'creditHistory' => $query->latest('created_at')->paginate(15)->withQueryString(),
            'typeOptions' => [
                ['id' => 'add', 'name' => __('backend_buyers_credit_history_table_credit')],
                ['id' => 'deduct', 'name' => __('backend_buyers_credit_history_table_debit')],
            ],
        ]);
    }
}
