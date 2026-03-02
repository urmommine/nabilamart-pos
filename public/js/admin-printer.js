// Helper for notifications in Admin (Filament-style)
function sendAdminNotification(title, message, type = 'success') {
    if (typeof FilamentNotification !== 'undefined') {
        let notification = new FilamentNotification()
            .title(title);

        if (message) {
            notification.body(message);
        }

        // Apply status (danger, success, warning, info)
        if (type === 'error') {
            notification.danger();
        } else if (type === 'success') {
            notification.success();
        } else if (type === 'warning') {
            notification.warning();
        } else {
            notification.info();
        }

        notification.send();
    } else {
        // Fallback to POS-style notification if POS listener exists on this page
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type, message: title + (message ? ': ' + message : '') }
        }));
        console.log(`[Admin Printer] ${type.toUpperCase()}: ${title} - ${message}`);
    }
}

document.addEventListener('livewire:init', () => {
    // Listen for Connect Event
    Livewire.on('connect-printer', (data) => {
        const type = data.type || data || 'bluetooth';
        connectPrinter(type);
    });

    // Listen for Print Event
    Livewire.on('print-invoice', async (data) => {
        let receipt = data.data || data; // handle potential wrapper

        if (!window.btPrinter) {
            sendAdminNotification('Printer Belum Terhubung', 'Klik tombol Connect Printer terlebih dahulu.', 'error');
            return;
        }

        let print = window.btPrinter;

        try {
            const fmt = (v) => new Intl.NumberFormat('id-ID').format(v);

            // Header
            await print.writeText(receipt.storeName, { align: "center", bold: true, size: "double" });
            if (receipt.storeAddress) await print.writeText(receipt.storeAddress, { align: "center" });
            if (receipt.storePhone) await print.writeText(receipt.storePhone, { align: "center" });

            await print.writeDashLine();
            await print.writeText("No  : " + receipt.invoice, { align: "left" });
            await print.writeText("Tgl : " + receipt.date, { align: "left" });
            await print.writeText("Kasir: " + receipt.cashier, { align: "left" });
            if (receipt.customer) await print.writeText("Plgn : " + receipt.customer, { align: "left" });
            await print.writeDashLine();

            // Items
            if (receipt.items && receipt.items.length > 0) {
                for (let item of receipt.items) {
                    await print.writeText(item.name, { align: "left", bold: true });

                    if (item.discount_info && item.original_price && item.original_price > item.price) {
                        // Discounted: show original price + discount info
                        let discLine = "  " + item.qty + " x " + fmt(item.original_price) + "  (" + item.discount_info + ")";
                        await print.writeText(discLine, { align: "left" });
                        // Original total (before discount)
                        let origTotal = item.qty * item.original_price;
                        await print.writeTextWith2Column("", fmt(origTotal), { underline: true, bold: true });
                        // Discounted total (underlined)
                        await print.writeTextWith2Column("", fmt(item.total));
                    } else {
                        // No discount: standard format
                        let line_left = "  " + item.qty + " x " + fmt(item.price);
                        await print.writeTextWith2Column(line_left, fmt(item.total));
                    }
                }
            }
            await print.writeDashLine();

            // Totals
            await print.writeTextWith2Column("Subtotal", "Rp " + fmt(receipt.subtotal));
            if (receipt.discount > 0) {
                await print.writeTextWith2Column("Diskon", "-Rp " + fmt(receipt.discount));
            }
            if (receipt.tax > 0) {
                await print.writeTextWith2Column("Pajak", "Rp " + fmt(receipt.tax));
            }

            await print.writeDashLine();
            await print.writeTextWith2Column("TOTAL", "Rp " + fmt(receipt.total), { bold: true });
            await print.writeDashLine();

            // Payment
            const methodLabel = { cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' };
            const payLabel = methodLabel[receipt.payment_method] || receipt.payment_method;
            await print.writeTextWith2Column("Bayar (" + payLabel + ")", "Rp " + fmt(receipt.amount_paid));
            if (receipt.change > 0) {
                await print.writeTextWith2Column("Kembali", "Rp " + fmt(receipt.change));
            }

            // Footer
            await print.writeLineBreak();
            await print.writeText(receipt.footer, { align: "center" });
            await print.writeLineBreak({ count: 3 });

            sendAdminNotification('Berhasil Mencetak', '', 'success');

        } catch (e) {
            console.error(e);
            sendAdminNotification('Gagal Mencetak', e.message, 'error');
        }
    });
});

function connectPrinter(type = 'bluetooth') {
    console.log("Connect button clicked, type:", type);

    // Map backend type to PrintHub type
    let phType = type;
    if (type === 'usb_web' || type === 'usb') phType = 'usb';
    if (type === 'bluetooth') phType = 'bluetooth';

    if (typeof PrintHub === 'undefined') {
        sendAdminNotification('Library Error', 'PrintHub library not loaded.', 'error');
        return;
    }

    try {
        // Always re-init so we get a fresh attempt for the chosen type
        // Use global PrintHub class exposed in app.js
        printerInstance = new PrintHub({
            paperSize: "58",
            printerType: phType
        });
        console.log("PrintHub instance " + phType + " created");
    } catch (e) {
        console.error("Error creating printer instance:", e);
        sendAdminNotification('Init Error', e.message, 'error');
        return;
    }

    printerInstance.connectToPrint({
        onReady: (print) => {
            window.btPrinter = print;
            sendAdminNotification('Printer Terhubung', 'Siap mencetak via ' + (phType === 'usb' ? 'USB' : 'Bluetooth') + '.', 'success');
        },
        onFailed: (message) => {
            let errorBody = message;

            // Check for common USB claimInterface error
            if (phType === 'usb' && message.includes('claimInterface')) {
                errorBody = 'Gagal claim interface. HARAP TUTUP TAB POS TERLEBIH DAHULU (Perangkat USB hanya bisa diklaim oleh satu tab). Jika masih gagal, pastikan driver WinUSB aktif.';
            }

            sendAdminNotification('Koneksi Gagal', errorBody, 'error');
            console.error("Connection Failed:", message);
        }
    });
}
