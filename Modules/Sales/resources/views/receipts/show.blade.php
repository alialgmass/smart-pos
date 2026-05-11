<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: bold; }
        .header p { font-size: 11px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .items { width: 100%; border-collapse: collapse; }
        .items th { text-align: left; font-size: 11px; border-bottom: 1px solid #000; padding: 3px 0; }
        .items td { padding: 3px 0; font-size: 11px; }
        .items .qty { text-align: center; }
        .items .price { text-align: right; }
        .items .total { text-align: right; }
        .totals { margin-top: 6px; }
        .totals .row { display: flex; justify-content: space-between; padding: 2px 0; }
        .totals .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 4px; margin-top: 4px; }
        .payment-info { margin-top: 8px; font-size: 11px; }
        .footer { text-align: center; margin-top: 12px; font-size: 10px; }
        .barcode { text-align: center; margin-top: 8px; }
        .barcode svg, .barcode img { max-width: 100%; height: auto; }
        @media print {
            body { width: 100%; margin: 0; padding: 5px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>{{ __('Tax Invoice') }}</p>
        <p><strong>{{ $sale->invoice_number }}</strong></p>
        <p>{{ $sale->created_at->format('d/m/Y h:i A') }}</p>
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 50%;">{{ __('Item') }}</th>
                <th class="qty" style="width: 15%;">{{ __('Qty') }}</th>
                <th class="price" style="width: 20%;">{{ __('Price') }}</th>
                <th class="total" style="width: 15%;">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td class="qty">{{ $item->qty }}</td>
                <td class="price">{{ number_format($item->price, 2) }}</td>
                <td class="total">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals">
        <div class="row">
            <span>{{ __('Subtotal') }}</span>
            <span>{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        @if ($sale->discount_amount > 0)
        <div class="row">
            <span>{{ __('Discount') }}</span>
            <span>-{{ number_format($sale->discount_amount, 2) }}</span>
        </div>
        @endif
        @if ($sale->tax_amount > 0)
        <div class="row">
            <span>{{ __('Tax') }}</span>
            <span>{{ number_format($sale->tax_amount, 2) }}</span>
        </div>
        @endif
        <div class="row grand-total">
            <span>{{ __('Total') }}</span>
            <span>{{ number_format($sale->total, 2) }}</span>
        </div>
        <div class="row">
            <span>{{ __('Paid') }}</span>
            <span>{{ number_format($sale->paid_amount, 2) }}</span>
        </div>
        @if ($sale->change_amount > 0)
        <div class="row">
            <span>{{ __('Change') }}</span>
            <span>{{ number_format($sale->change_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="payment-info">
        <p>{{ __('Payment Method') }}: {{ $sale->payment_method->name ?? $sale->payment_method }}</p>
        <p>{{ __('Status') }}: {{ $sale->status->name ?? $sale->status }}</p>
    </div>

    <div class="footer">
        <p>{{ __('Thank you for your purchase!') }}</p>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
