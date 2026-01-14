<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - @yield('title', 'POS')</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            html, body {
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
    {{ $slot }}
</body>
</html>
