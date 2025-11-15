<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'shop_name',
                'value' => 'My Grocery Shop',
                'group_name' => 'general',
                'description' => 'Name of the grocery shop',
            ],
            [
                'key' => 'shop_address',
                'value' => '123 Main Street',
                'group_name' => 'general',
                'description' => 'Physical address of the shop',
            ],
            [
                'key' => 'shop_phone',
                'value' => '0123456789',
                'group_name' => 'general',
                'description' => 'Contact phone number',
            ],
            [
                'key' => 'shop_email',
                'value' => 'info@grocery.local',
                'group_name' => 'general',
                'description' => 'Contact email address',
            ],
            [
                'key' => 'shop_logo',
                'value' => '',
                'group_name' => 'general',
                'description' => 'Shop logo file path',
            ],

            // Inventory Settings
            [
                'key' => 'low_stock_threshold',
                'value' => '10',
                'group_name' => 'inventory',
                'description' => 'Minimum stock level before low stock alert',
            ],
            [
                'key' => 'expiry_alert_days',
                'value' => '30',
                'group_name' => 'inventory',
                'description' => 'Days before expiry to show alert',
            ],

            // Accounting Settings
            [
                'key' => 'currency_symbol',
                'value' => 'Rs.',
                'group_name' => 'accounting',
                'description' => 'Currency symbol to display',
            ],
            [
                'key' => 'date_format',
                'value' => 'd-m-Y',
                'group_name' => 'accounting',
                'description' => 'Date format for displaying dates',
            ],

            // Receipt Settings
            [
                'key' => 'receipt_header',
                'value' => 'Thank you for shopping with us!',
                'group_name' => 'receipt',
                'description' => 'Header text for receipts',
            ],
            [
                'key' => 'receipt_footer',
                'value' => 'Please come again!',
                'group_name' => 'receipt',
                'description' => 'Footer text for receipts',
            ],

            // POS Settings
            [
                'key' => 'pos_tax_rate',
                'value' => '0',
                'group_name' => 'pos',
                'description' => 'Tax rate percentage for POS transactions',
            ],
            [
                'key' => 'pos_allow_discount',
                'value' => '1',
                'group_name' => 'pos',
                'description' => 'Allow discounts in POS (1 = yes, 0 = no)',
            ],

            // Printer Settings
            [
                'key' => 'printer_type',
                'value' => 'file',
                'group_name' => 'printer',
                'description' => 'Printer connection type (file or network)',
            ],
            [
                'key' => 'printer_path',
                'value' => '/dev/usb/lp0',
                'group_name' => 'printer',
                'description' => 'Printer device path or IP address',
            ],
            [
                'key' => 'auto_print_receipt',
                'value' => 'true',
                'group_name' => 'printer',
                'description' => 'Automatically print receipt after sale',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
