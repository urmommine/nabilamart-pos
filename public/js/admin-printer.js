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
            // Header
            await print.writeText(receipt.storeName, { align: "center", bold: true, size: "double" });
            if (receipt.storeAddress) await print.writeText(receipt.storeAddress, { align: "center" });
            if (receipt.storePhone) await print.writeText(receipt.storePhone, { align: "center" });

            await print.writeLineBreak();
            await print.writeText("No: " + receipt.invoice, { align: "left" });
            await print.writeText("Tgl: " + receipt.date, { align: "left" });
            await print.writeText("Kasir: " + receipt.cashier, { align: "left" });
            await print.writeText("Pelanggan: " + receipt.customer, { align: "left" });
            await print.writeDashLine();

            // Items
            if (receipt.items && receipt.items.length > 0) {
                for (let item of receipt.items) {
                    await print.writeText(item.name, { align: "left" });

                    if (item.discount_info) {
                        await print.writeText("  (Disc: " + item.discount_info + ")", { align: "left" });
                    }

                    // Format: 2x @10.000   20.000
                    let line2_left = item.qty + "x @" + new Intl.NumberFormat('id-ID').format(item.price);
                    let line2_right = new Intl.NumberFormat('id-ID').format(item.total);
                    await print.writeTextWith2Column(line2_left, line2_right);
                }
            }
            await print.writeDashLine();

            // Totals
            await print.writeTextWith2Column("Subtotal", new Intl.NumberFormat('id-ID').format(receipt.subtotal));
            if (receipt.discount > 0) {
                await print.writeTextWith2Column("Diskon", "-" + new Intl.NumberFormat('id-ID').format(receipt.discount));
            }
            if (receipt.tax > 0) {
                await print.writeTextWith2Column("Pajak", new Intl.NumberFormat('id-ID').format(receipt.tax));
            }

            // Total Large
            await print.writeLineBreak();
            await print.writeText("TOTAL", { align: "center", bold: true });
            await print.writeText("Rp " + new Intl.NumberFormat('id-ID').format(receipt.total), { align: "center", bold: true, size: "double" });
            await print.writeLineBreak();

            await print.writeTextWith2Column("Tunai", new Intl.NumberFormat('id-ID').format(receipt.amount_paid));
            await print.writeTextWith2Column("Kembali", new Intl.NumberFormat('id-ID').format(receipt.change));

            // Footer
            await print.writeDashLine();
            await print.writeText(receipt.footer, { align: "center" });
            await print.writeLineBreak(3); // Feed

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
        printerInstance = new PrintHub.init({
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
