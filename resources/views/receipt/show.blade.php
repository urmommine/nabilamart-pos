<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $order->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            background: #f5f5f5;
            padding: 20px;
        }

        .receipt {
            width: 280px;
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .store-info {
            font-size: 11px;
            color: #666;
        }

        .order-info {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .order-info p {
            display: flex;
            justify-content: space-between;
        }

        .items {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .item {
            margin-bottom: 8px;
        }

        .item-name {
            font-weight: bold;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            padding-left: 10px;
            color: #666;
        }

        .totals {
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total-row.grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }

        .payment {
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            padding-top: 10px;
            border-top: 1px dashed #333;
        }

        .footer p {
            margin-bottom: 5px;
        }

        .actions {
            margin-top: 20px;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-print {
            background: #10B981;
            color: white;
        }

        .btn-close {
            background: #EF4444;
            color: white;
        }

        /* Define page size for thermal printer (58mm width) */
        @page {
            size: 58mm auto;
            margin: 0;
        }

        @media print {

            html,
            body {
                width: 100%;
                background: white;
                padding: 0;
                margin: 0;
                display: flex;
                justify-content: center;
            }

            .receipt {
                box-shadow: none;
                width: 58mm;
                max-width: 58mm;
                padding: 2mm;
                margin: 0 auto;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="header">
            <div class="store-name">{{ $storeSettings['name'] }}</div>
            @if($storeSettings['address'])
                <div class="store-info">{{ $storeSettings['address'] }}</div>
            @endif
            @if($storeSettings['phone'])
                <div class="store-info">Telp: {{ $storeSettings['phone'] }}</div>
            @endif
        </div>

        <div class="order-info">
            <p><span>No. Invoice:</span> <span>{{ $order->invoice_number }}</span></p>
            <p><span>Kasir:</span> <span>{{ $order->user->name }}</span></p>
            <p><span>Tanggal:</span> <span>{{ $order->created_at->format('d/m/Y H:i') }}</span></p>
        </div>

        <div class="items">
            @foreach($order->items as $item)
                <div class="item">
                    <div class="item-name">{{ $item->product_name }}</div>
                    @php
                        $originalPrice = $item->original_price ?? $item->unit_price;
                        $hasDiscount = $item->discount_info && (float) $originalPrice > (float) $item->unit_price;
                    @endphp
                    @if($hasDiscount)
                        <div class="item-detail" style="font-size: 10px; color: #999;">
                            <span>
                                {{ $item->quantity }} x
                                <span style="text-decoration: line-through;">Rp
                                    {{ number_format($originalPrice, 0, ',', '.') }}</span>
                                <span style="color: #e74c3c; font-style: italic;">({{ $item->discount_info }})</span>
                            </span>
                            <span style="text-decoration: line-through;">Rp
                                {{ number_format($originalPrice * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                        <div class="item-detail">
                            <span>{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="item-detail">
                            <span>{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->discount > 0)
                <div class="total-row">
                    <span>Diskon</span>
                    <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->tax > 0)
                <div class="total-row">
                    <span>Pajak</span>
                    <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="payment">
            <div class="total-row">
                <span>Bayar ({{ ucfirst($order->payment_method) }})</span>
                <span>Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
            </div>
            @if($order->change > 0)
                <div class="total-row">
                    <span>Kembali</span>
                    <span>Rp {{ number_format($order->change, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>{{ $storeSettings['footer'] }}</p>
            <p style="font-size: 10px; color: #999;">{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak</button>
        <button class="btn btn-close" onclick="window.close()">✕ Tutup</button>
    </div>

    <script>
        // Auto print on load
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>