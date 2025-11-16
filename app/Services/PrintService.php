<?php

namespace App\Services;

use App\Models\Sale;

class PrintService
{
    protected $printer;

    public function __construct()
    {
        // Note: The escpos-php library needs to be installed via composer
        // For now, this is a placeholder that logs the receipt
        $this->printer = null;
    }

    public function printReceipt(Sale $sale)
    {
        try {
            $sale->load(['items.product', 'customer', 'cashier', 'payments']);

            // For Phase 1, we'll log the receipt instead of actual printing
            // Install mike42/escpos-php later for actual thermal printing
            $receiptContent = $this->generateReceiptContent($sale);

            logger()->info('Receipt Generated', [
                'invoice' => $sale->invoice_number,
                'total' => $sale->total_amount,
                'content' => $receiptContent
            ]);

            return true;
        } catch (\Exception $e) {
            logger()->error('Print failed: ' . $e->getMessage());
            return false;
        }
    }

    private function generateReceiptContent(Sale $sale)
    {
        $content = [];
        $content[] = "========================================";
        $content[] = setting('shop_name', 'Grocery Shop');
        $content[] = setting('shop_address', '');
        $content[] = "Tel: " . setting('shop_phone', '');
        $content[] = "========================================";
        $content[] = "Invoice: " . $sale->invoice_number;
        $content[] = "Date: " . $sale->sale_date->format('Y-m-d H:i:s');
        $content[] = "Cashier: " . $sale->cashier->name;

        if ($sale->customer) {
            $content[] = "Customer: " . $sale->customer->name;
            if ($sale->customer->phone) {
                $content[] = "Phone: " . $sale->customer->phone;
            }
        }

        $content[] = "========================================";
        $content[] = sprintf("%-20s %8s %10s", "Item", "Qty", "Price");
        $content[] = "----------------------------------------";

        foreach ($sale->items as $item) {
            $name = $item->product->name;
            if ($item->is_box_sale) {
                $name .= " [BOX]";
            }

            $content[] = sprintf("%-20s", substr($name, 0, 20));
            $content[] = sprintf("%8s x Rs.%-10s = Rs.%s",
                number_format($item->quantity, 0),
                number_format($item->unit_price, 2),
                number_format($item->total_price, 2)
            );

            if ($item->discount_amount > 0) {
                $content[] = "  Discount: -Rs." . number_format($item->discount_amount, 2);
            }
        }

        $content[] = "========================================";
        $content[] = sprintf("%-20s Rs.%s", "Subtotal:", number_format($sale->subtotal, 2));

        if ($sale->discount_amount > 0) {
            $content[] = sprintf("%-20s -Rs.%s", "Discount:", number_format($sale->discount_amount, 2));
        }

        $content[] = sprintf("%-20s Rs.%s", "TOTAL:", number_format($sale->total_amount, 2));
        $content[] = "========================================";

        $payment = $sale->payments->first();
        $content[] = "Payment Mode: " . strtoupper($payment->payment_mode);
        $content[] = "Amount Paid: Rs." . number_format($payment->amount, 2);

        $content[] = "========================================";
        $content[] = setting('receipt_footer', 'Thank you for shopping with us!');
        $content[] = "Please visit again";
        $content[] = "========================================";

        return implode("\n", $content);
    }

    public function testPrint()
    {
        logger()->info('Test Print Successful');
        return true;
    }
}
