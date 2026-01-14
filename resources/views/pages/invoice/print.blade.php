<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .company-info h1 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 5px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        .client-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .client-info, .invoice-info {
            width: 48%;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .client-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        /* Prescription Section */
        .prescription-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .prescription-title {
            font-size: 14px;
            font-weight: bold;
            color: #17a2b8;
            margin-bottom: 10px;
        }
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
        }
        .prescription-table th,
        .prescription-table td {
            padding: 5px 10px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .prescription-table th {
            background: #e9ecef;
            font-size: 11px;
        }
        .eye-label {
            font-weight: bold;
            color: #dc3545;
        }
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #007bff;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 300px;
        }
        .totals-table td {
            padding: 8px 15px;
        }
        .totals-table .total-row {
            font-size: 16px;
            font-weight: bold;
            background: #007bff;
            color: #fff;
        }
        .totals-table .paid-row {
            color: #28a745;
        }
        .totals-table .remaining-row {
            color: #dc3545;
            font-weight: bold;
        }
        /* Payment History */
        .payment-history {
            margin-bottom: 30px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-table th,
        .payment-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        .payment-table th {
            background: #28a745;
            color: #fff;
        }
        /* Notes */
        .notes-section {
            padding: 15px;
            background: #fff3cd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        /* Footer */
        .invoice-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 11px;
        }
        /* Print Styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .invoice-container {
                padding: 0;
            }
        }
        /* Print Button */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            background: #0056b3;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid { background: #d4edda; color: #155724; }
        .status-partial { background: #fff3cd; color: #856404; }
        .status-unpaid { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Print Invoice</button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>Optics POS</h1>
                <p>Your Optical Solutions</p>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p class="invoice-number">#{{ $invoice->invoice_number }}</p>
                <p>Date: {{ $invoice->invoiced_at?->format('M d, Y') }}</p>
                <p>Due: {{ $invoice->due_at?->format('M d, Y') ?? 'N/A' }}</p>
                <p>
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Client & Invoice Info -->
        <div class="client-section">
            <div class="client-info">
                <p class="section-title">Bill To:</p>
                <p class="client-name">{{ $invoice->client?->name ?? 'N/A' }}</p>
                @if($invoice->client?->phone)
                    <p>Phone: {{ is_array($invoice->client->phone) ? implode(', ', array_filter($invoice->client->phone)) : $invoice->client->phone }}</p>
                @endif
                @if($invoice->client?->address)
                    <p>{{ $invoice->client->address }}</p>
                @endif
            </div>
            <div class="invoice-info">
                <p class="section-title">Invoice Info:</p>
                <p>Created By: {{ $invoice->user?->name ?? 'N/A' }}</p>
                <p>Created At: {{ $invoice->created_at?->format('M d, Y H:i') }}</p>
            </div>
        </div>

        <!-- Prescription -->
        @if($invoice->paper)
            <div class="prescription-section">
                <p class="prescription-title">Prescription (الروشتة)</p>
                <table class="prescription-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>SPH</th>
                            <th>CYL</th>
                            <th>AXIS</th>
                            <th>ADD</th>
                            <th>IPD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="eye-label">Right (OD)</td>
                            <td>{{ $invoice->paper->R_sph ?: '-' }}</td>
                            <td>{{ $invoice->paper->R_cyl ?: '-' }}</td>
                            <td>{{ $invoice->paper->R_axis ?: '-' }}</td>
                            <td rowspan="2">{{ $invoice->paper->addtion ?: '-' }}</td>
                            <td rowspan="2">{{ $invoice->paper->ipd ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="eye-label">Left (OS)</td>
                            <td>{{ $invoice->paper->L_sph ?: '-' }}</td>
                            <td>{{ $invoice->paper->L_cyl ?: '-' }}</td>
                            <td>{{ $invoice->paper->L_axis ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Items Table -->
        @if($invoice->items->count() > 0 || $invoice->lenses->count() > 0)
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Description</th>
                        <th style="width: 15%" class="text-center">Qty</th>
                        <th style="width: 15%" class="text-right">Price</th>
                        <th style="width: 20%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNum = 0; @endphp
                    @foreach($invoice->items as $item)
                        @php $rowNum++; @endphp
                        <tr>
                            <td>{{ $rowNum }}</td>
                            <td>{{ $item->name ?? $item->product?->name ?? 'Product' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->price, 2) }}</td>
                            <td class="text-right">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($invoice->lenses as $lens)
                        @php $rowNum++; @endphp
                        <tr>
                            <td>{{ $rowNum }}</td>
                            <td>🔍 {{ $lens->name ?? 'Lens' }}</td>
                            <td class="text-center">{{ $lens->quantity }}</td>
                            <td class="text-right">{{ number_format($lens->price, 2) }}</td>
                            <td class="text-right">{{ number_format($lens->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">{{ number_format($invoice->amount, 2) }}</td>
                </tr>
                <tr class="paid-row">
                    <td>Paid:</td>
                    <td class="text-right">{{ number_format($invoice->paid, 2) }}</td>
                </tr>
                <tr class="remaining-row">
                    <td>Remaining:</td>
                    <td class="text-right">{{ number_format($invoice->remaining, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total:</td>
                    <td class="text-right">{{ number_format($invoice->amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment History -->
        @if($invoice->transactions->count() > 0)
            <div class="payment-history">
                <p class="section-title">Payment History</p>
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->paid_at?->format('M d, Y') }}</td>
                                <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
            <div class="notes-section">
                <p class="section-title">Notes</p>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <p>Thank you for your business!</p>
            <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</body>
</html>




