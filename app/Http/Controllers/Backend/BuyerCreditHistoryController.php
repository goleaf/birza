<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Users\Buyer;
use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BuyerCreditHistoryController extends Controller
{
    /**
     * Display the credit history for a specific buyer.
     */
    public function index(Buyer $buyer)
    {
        $query = $buyer->creditHistory()->with('admin');

        // Apply type filter
        if (request('type')) {
            $query->where('type', request('type'));
        }

        // Apply date filters
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $creditHistory = $query->latest('created_at')->paginate(15);

        return view('backend.buyers.credit_history', [
            'buyer' => $buyer,
            'creditHistory' => $creditHistory
        ]);
    }

    /**
     * Add credit to buyer's account.
     */
    public function addCredit(Request $request, Buyer $buyer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120'
        ]);

        DB::transaction(function () use ($buyer, $validated, $request) {
            $newBalance = $buyer->credit_balance + $validated['amount'];
            
            $creditHistory = BuyerCreditHistory::create([
                'buyer_id' => $buyer->id,
                'amount' => $validated['amount'],
                'type' => 'add',
                'balance_after' => $newBalance,
                'note' => $validated['note'],
                'admin_id' => auth()->id()
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('credit-attachments', 'public');
                
                CreditAttachment::create([
                    'credit_history_id' => $creditHistory->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }

            $buyer->update(['credit_balance' => $newBalance]);
        });

        return back()->with('success', __('backend.buyers.credit.messages.credit_added'));
    }

    /**
     * Debit credit from buyer's account.
     */
    public function debitCredit(Request $request, Buyer $buyer)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', function ($attribute, $value, $fail) use ($buyer) {
                if ($value > $buyer->credit_balance) {
                    $fail(__('backend.buyers.credit.messages.insufficient_balance'));
                }
            }],
            'note' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120'
        ]);

        DB::transaction(function () use ($buyer, $validated, $request) {
            $newBalance = $buyer->credit_balance - $validated['amount'];
            
            $creditHistory = BuyerCreditHistory::create([
                'buyer_id' => $buyer->id,
                'amount' => $validated['amount'],
                'type' => 'deduct',
                'balance_after' => $newBalance,
                'note' => $validated['note'],
                'admin_id' => auth()->id()
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('credit-attachments', 'public');
                
                CreditAttachment::create([
                    'credit_history_id' => $creditHistory->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }

            $buyer->update(['credit_balance' => $newBalance]);
        });

        return back()->with('success', __('backend.buyers.credit.messages.credit_debited'));
    }

    /**
     * Export credit history to CSV.
     */
    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="credit_history.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Buyer', 'Amount', 'Type', 'Balance After', 'Admin', 'Note', 'Date']);

            // Add data
            BuyerCreditHistory::with(['buyer', 'admin'])
                ->orderBy('created_at', 'desc')
                ->chunk(1000, function ($records) use ($file) {
                    foreach ($records as $record) {
                        fputcsv($file, [
                            $record->buyer->name,
                            $record->amount,
                            $record->type,
                            $record->balance_after,
                            $record->admin?->name ?? 'System',
                            $record->note,
                            $record->created_at,
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment(CreditAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $attachment->file_path, 
            $attachment->original_name
        );
    }
}
