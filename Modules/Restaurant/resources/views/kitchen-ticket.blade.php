<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->order_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 72mm;
            margin: 0 auto;
            padding: 8px 4px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
        }
        .items th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }
        .items td {
            padding: 4px 0;
            vertical-align: top;
        }
        .items .qty {
            text-align: center;
            width: 40px;
        }
        .items .name {
            padding-left: 8px;
        }
        .notes {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #000;
        }
        .notes p {
            margin: 2px 0;
            font-style: italic;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kitchen Ticket</h1>
        <p><strong>Order:</strong> {{ $order->order_number }}</p>
        <p><strong>Table:</strong> {{ $order->table->name ?? 'N/A' }}</p>
        <p><strong>Time:</strong> {{ $order->created_at->format('H:i') }}</p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="qty">Qty</th>
                <th class="name">Item</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td class="qty">{{ $item->qty }}</td>
                    <td class="name">
                        {{ $item->name }}
                        @if ($item->notes)
                            <br><em>{{ $item->notes }}</em>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($order->notes)
        <div class="notes">
            <p><strong>Order Notes:</strong> {{ $order->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Thank you!</p>
        <p class="no-print" style="margin-top:8px;">
            <button onclick="window.print()">Print</button>
            <button onclick="window.close()">Close</button>
        </p>
    </div>
</body>
</html>
