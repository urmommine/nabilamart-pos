// Global Printer Instance
let printerInstance = null;
window.btPrinter = null;

document.addEventListener('livewire:init', () => {
    // Listen for Connect Event
    Livewire.on('connect-printer', () => {
        connectPrinter();
    });

    // Listen for Print Event
    Livewire.on('print-invoice', async (data) => {
        let receipt = data.data || data; // handle potential wrapper

        if (!window.btPrinter) {
            new FilamentNotification()
                .title('Printer Belum Terhubung')
                .body('Klik tombol Connect Printer terlebih dahulu.')
                .danger()
                .send();
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

            new FilamentNotification()
                .title('Berhasil Mencetak')
                .success()
                .send();

        } catch (e) {
            console.error(e);
            new FilamentNotification()
                .title('Gagal Mencetak')
                .body(e.message)
                .danger()
                .send();
        }
    });
});

function connectPrinter() {
    console.log("Connect button clicked");
    if (typeof PrintHub === 'undefined') {
        new FilamentNotification()
            .title('Library Error')
            .body('PrintHub library not loaded.')
            .danger()
            .send();
        return;
    }

    if (!printerInstance) {
        try {
            // PrintHub Init
            printerInstance = new PrintHub.init({
                paperSize: "58",
                printerType: "bluetooth"
            });
            console.log("PrintHub instance created");
        } catch (e) {
            console.error("Error creating printer instance:", e);
            new FilamentNotification()
                .title('Init Error')
                .body(e.message)
                .danger()
                .send();
            return;
        }
    }

    printerInstance.connectToPrint({
        onReady: (print) => {
            window.btPrinter = print;
            new FilamentNotification()
                .title('Printer Terhubung')
                .body('Siap mencetak via Bluetooth.')
                .success()
                .send();
        },
        onFailed: (message) => {
            new FilamentNotification()
                .title('Koneksi Gagal')
                .body(message)
                .danger()
                .send();
        }
    });
}
