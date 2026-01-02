<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.backend.app')]
class CreditHistory extends Component
{
    public Buyer $buyer;

    public function mount(Buyer $buyer): void
    {
        $this->buyer = $buyer;
    }

    public function downloadAttachment(int $attachmentId)
    {
        $attachment = CreditAttachment::whereKey($attachmentId)
            ->whereHas('creditHistory', function ($query) {
                $query->where('buyer_id', $this->buyer->id);
            })
            ->firstOrFail();

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
        $buyerId = $this->buyer->id;

        $type = request('type');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="credit_history_buyer_'.$buyerId.'.csv"',
        ];

        $callback = function () use ($buyerId, $type, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                __('backend.buyers.credit_history.table.buyer'),
                __('backend.buyers.credit_history.table.amount'),
                __('backend.buyers.credit_history.table.type'),
                __('backend.buyers.credit_history.table.balance_after'),
                __('backend.buyers.credit_history.table.admin'),
                __('backend.buyers.credit_history.table.note'),
                __('backend.buyers.credit_history.table.date'),
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
                        $record->admin?->name ?? __('backend.buyers.credit_history.table.system'),
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
        $query = $this->buyer->creditHistory()->with(['admin', 'attachments']);

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

