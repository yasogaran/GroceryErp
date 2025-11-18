<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class POSController extends Controller
{
    /**
     * Show print preview for a sale receipt
     */
    public function printReceipt($saleId)
    {
        $sale = Sale::with([
            'items.product',
            'customer',
            'cashier',
            'payments.bankAccount',
            'shift'
        ])->findOrFail($saleId);

        return view('pos.print-receipt', compact('sale'));
    }
}
