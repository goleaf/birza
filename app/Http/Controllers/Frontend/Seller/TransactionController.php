<?php

namespace App\Http\Controllers\Frontend\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the seller's transactions
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        
        $transactions = $seller->transactions()
            ->with(['order'])
            ->when($request->get('type'), function($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->get('date_from'), function($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->get('date_to'), function($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate(15);

        $filters = [
            'type' => $request->get('type'),
            'dateFrom' => $request->get('date_from'),
            'dateTo' => $request->get('date_to')
        ];

        $stats = [
            'total_deductions' => $seller->transactions()->where('type', 'deduction')->sum('amount'),
            'total_refunds' => $seller->transactions()->where('type', 'refund')->sum('amount'),
            'current_balance' => $seller->balance
        ];

        return view('frontend.seller.transactions.index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'stats' => $stats
        ]);
    }
}
