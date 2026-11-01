<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pharmacy Bill {{ $bill['invoiceNumber'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h1, h2, p { margin: 0; }
        .header { border-bottom: 2px solid #1f2937; padding-bottom: 14px; }
        .header table, .details, .items, .totals { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .pharmacy-name { font-size: 21px; font-weight: bold; }
        .muted { color: #6b7280; }
        .section { margin-top: 20px; }
        .details td { padding: 3px 0; vertical-align: top; }
        .items { margin-top: 10px; }
        .items th, .items td { border-bottom: 1px solid #d1d5db; padding: 8px 6px; text-align: left; }
        .items th { background: #f3f4f6; }
        .numeric { text-align: right !important; white-space: nowrap; }
        .totals { margin-top: 12px; }
        .totals td { padding: 4px 6px; }
        .grand-total { border-top: 2px solid #1f2937; font-size: 14px; font-weight: bold; }
        .footer { margin-top: 30px; border-top: 1px solid #d1d5db; padding-top: 10px; text-align: center; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <p class="pharmacy-name">{{ $order->pharmacy->name }}</p>
                    @if ($order->pharmacy->address)
                        <p>{{ $order->pharmacy->address->address_line_1 }}</p>
                        @if ($order->pharmacy->address->address_line_2)
                            <p>{{ $order->pharmacy->address->address_line_2 }}</p>
                        @endif
                        <p>
                            {{ collect([
                                $order->pharmacy->address->upazila,
                                $order->pharmacy->address->district,
                                $order->pharmacy->address->division,
                                $order->pharmacy->address->postal_code,
                            ])->filter()->implode(', ') }}
                        </p>
                    @endif
                    <p>
                        @if ($order->pharmacy->phone)
                            Phone: {{ $order->pharmacy->phone }}
                        @endif
                        @if ($order->pharmacy->email)
                            {{ $order->pharmacy->phone ? ' · ' : '' }}Email: {{ $order->pharmacy->email }}
                        @endif
                    </p>
                </td>
                <td style="text-align: right">
                    <h1>PHARMACY BILL</h1>
                    <p>{{ $bill['invoiceNumber'] }}</p>
                    <p>{{ $order->dispensed_at?->format('d M Y, h:i A') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Prescription details</h2>
        <table class="details">
            <tr>
                <td><strong>Patient</strong></td>
                <td>{{ $order->prescription->patient?->name ?? '—' }}</td>
                <td><strong>Prescription</strong></td>
                <td>{{ $order->prescription->prescription_number ?? $order->prescription->uuid }}</td>
            </tr>
            <tr>
                <td><strong>Order</strong></td>
                <td>{{ $order->id }}</td>
                <td><strong>Received</strong></td>
                <td>{{ $order->received_at?->format('d M Y, h:i A') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Dispensed items</h2>
        <table class="items">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Strength</th>
                    <th class="numeric">Qty</th>
                    <th class="numeric">Regular price</th>
                    <th class="numeric">Discount</th>
                    <th class="numeric">Unit price</th>
                    <th class="numeric">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bill['lines'] as $line)
                    <tr>
                        <td>{{ $line['medicine'] }}</td>
                        <td>{{ $line['strength'] ?: '—' }}</td>
                        <td class="numeric">{{ number_format($line['quantity']) }}</td>
                        <td class="numeric">{{ $bill['currencySymbol'] }}{{ $line['regularPrice'] }}</td>
                        <td class="numeric">{{ $line['discountPercentage'] }}%</td>
                        <td class="numeric">{{ $bill['currencySymbol'] }}{{ $line['discountedPrice'] }}</td>
                        <td class="numeric">{{ $bill['currencySymbol'] }}{{ $line['lineTotal'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="totals">
            <tr>
                <td class="numeric">Subtotal ({{ $bill['currencyCode'] }})</td>
                <td class="numeric">{{ $bill['currencySymbol'] }}{{ $bill['subtotal'] }}</td>
            </tr>
            <tr>
                <td class="numeric">Discount</td>
                <td class="numeric">−{{ $bill['currencySymbol'] }}{{ $bill['discountTotal'] }}</td>
            </tr>
            <tr class="grand-total">
                <td class="numeric">Total due</td>
                <td class="numeric">{{ $bill['currencySymbol'] }}{{ $bill['total'] }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">Thank you for choosing {{ $order->pharmacy->name }}.</div>
</body>
</html>
