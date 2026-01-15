<div class="receipt">
    @section('title', $order->invoice_number)

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
                <div class="item-detail">
                    <span>{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                </div>
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

    <!-- No visible actions needed for auto-print/iframe scenario, but kept for standalone debug -->
    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak</button>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Optional: If you want to use Livewire events
        });

        // Auto print immediately when loaded
        window.onload = function () {
            setTimeout(function () {
                window.print();
                // Check if we are inside an iframe
                if (window.self !== window.top) {
                    // We are in an iframe, maybe message parent to done? 
                    // Not strictly necessary as the print dialog is modal.
                } else {
                    // Standalone window
                    // window.close(); // Optional: close if opened as popup
                }
            }, 500);
        };
    </script>
</div>