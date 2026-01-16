<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StoreSetting;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\BluetoothPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class ReceiptPrinter
{
    protected ?Printer $printer = null;

    /**
     * Get printer connector based on settings
     */
    protected function getConnector()
    {
        $printerType = StoreSetting::get(StoreSetting::PRINTER_TYPE, 'usb');
        $printerName = StoreSetting::get(StoreSetting::PRINTER_NAME, '');
        $printerIp = StoreSetting::get(StoreSetting::PRINTER_IP, '');

        switch ($printerType) {
            case 'network':
                if (empty($printerIp)) {
                    throw new \Exception('IP Printer tidak dikonfigurasi');
                }
                return new NetworkPrintConnector($printerIp, 9100);
            
            case 'usb':
            default:
                if (empty($printerName)) {
                    // Try to use default printer on Windows
                    $printerName = 'POS-58';
                }
                
                // Windows printer
                if (PHP_OS_FAMILY === 'Windows') {
                    return new WindowsPrintConnector($printerName);
                }
                
                // Linux/Mac - use file connector
                return new FilePrintConnector('/dev/usb/lp0');
        }
    }

    /**
     * Print receipt for an order
     */
    public function printReceipt(Order $order): void
    {
        $connector = $this->getConnector();
        $this->printer = new Printer($connector);

        try {
            $this->printHeader();
            $this->printOrderInfo($order);
            $this->printItems($order);
            $this->printTotals($order);
            $this->printPayment($order);
            $this->printFooter();

            $this->printer->cut();
            $this->printer->pulse();
        } finally {
            $this->printer->close();
        }
    }

    /**
     * Print store header
     */
    protected function printHeader(): void
    {
        $storeName = StoreSetting::get(StoreSetting::STORE_NAME, 'POS STORE');
        $storeAddress = StoreSetting::get(StoreSetting::STORE_ADDRESS, '');
        $storePhone = StoreSetting::get(StoreSetting::STORE_PHONE, '');

        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->setEmphasis(true);
        $this->printer->setTextSize(2, 2);
        $this->printer->text($storeName . "\n");
        $this->printer->setTextSize(1, 1);
        $this->printer->setEmphasis(false);
        
        if ($storeAddress) {
            $this->printer->text($storeAddress . "\n");
        }
        if ($storePhone) {
            $this->printer->text("Telp: " . $storePhone . "\n");
        }
        
        $this->printer->text(str_repeat("=", 32) . "\n");
    }

    /**
     * Print order info
     */
    protected function printOrderInfo(Order $order): void
    {
        $this->printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->printer->text("No    : " . $order->invoice_number . "\n");
        $this->printer->text("Kasir : " . $order->user->name . "\n");
        $this->printer->text("Waktu : " . $order->created_at->format('d/m/Y H:i:s') . "\n");
        $this->printer->text(str_repeat("-", 32) . "\n");
    }

    /**
     * Print order items
     */
    protected function printItems(Order $order): void
    {
        foreach ($order->items as $item) {
            // Product name
            $this->printer->text($this->truncate($item->product_name, 32) . "\n");
            
            // Quantity x Price = Total
            $qty = $item->quantity;
            $price = number_format((float) $item->unit_price, 0, ',', '.');
            $total = number_format((float) $item->total_price, 0, ',', '.');
            
            $line = "  {$qty} x {$price}";
            $this->printer->text($this->formatLine($line, $total, 32) . "\n");
        }
        
        $this->printer->text(str_repeat("-", 32) . "\n");
    }

    /**
     * Print totals
     */
    protected function printTotals(Order $order): void
    {
        // Subtotal
        $this->printer->text($this->formatLine(
            "Subtotal",
            number_format((float) $order->subtotal, 0, ',', '.'),
            32
        ) . "\n");

        // Discount
        if ($order->discount > 0) {
            $this->printer->text($this->formatLine(
                "Diskon",
                "-" . number_format((float) $order->discount, 0, ',', '.'),
                32
            ) . "\n");
        }

        // Tax
        if ($order->tax > 0) {
            $this->printer->text($this->formatLine(
                "Pajak",
                number_format((float) $order->tax, 0, ',', '.'),
                32
            ) . "\n");
        }

        $this->printer->text(str_repeat("-", 32) . "\n");

        // Total
        $this->printer->setEmphasis(true);
        $this->printer->text($this->formatLine(
            "TOTAL",
            "Rp" . number_format((float) $order->total_amount, 0, ',', '.'),
            32
        ) . "\n");
        $this->printer->setEmphasis(false);
    }

    /**
     * Print payment info
     */
    protected function printPayment(Order $order): void
    {
        $paymentMethod = match($order->payment_method) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            default => $order->payment_method
        };

        $this->printer->text($this->formatLine(
            "Bayar ({$paymentMethod})",
            "Rp" . number_format((float) $order->amount_paid, 0, ',', '.'),
            32
        ) . "\n");

        if ($order->change > 0) {
            $this->printer->text($this->formatLine(
                "Kembali",
                "Rp" . number_format((float) $order->change, 0, ',', '.'),
                32
            ) . "\n");
        }

        $this->printer->text(str_repeat("=", 32) . "\n");
    }

    /**
     * Print footer
     */
    protected function printFooter(): void
    {
        $footer = StoreSetting::get(StoreSetting::RECEIPT_FOOTER, 'Terima Kasih Sudah Berbelanja!');
        
        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->text("\n" . $footer . "\n");
        $this->printer->text("\n\n");
    }

    /**
     * Format a line with left and right text
     */
    protected function formatLine(string $left, string $right, int $width): string
    {
        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);
        $spaces = max(1, $width - $leftLen - $rightLen);
        
        return $left . str_repeat(" ", $spaces) . $right;
    }

    /**
     * Truncate text to fit width
     */
    protected function truncate(string $text, int $maxLength): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        return mb_substr($text, 0, $maxLength - 3) . '...';
    }
}
