<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Journal Entries Report</title>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8pt;
        }

        table thead {
            background-color: #333;
            color: white;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
        }

        table th.right {
            text-align: right;
        }

        table tbody tr {
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table td {
            padding: 6px;
        }

        table td.right {
            text-align: right;
        }

        table tfoot {
            background: #f5f5f5;
            font-weight: bold;
        }

        .status {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .status.posted {
            background: #d4edda;
            color: #155724;
        }

        .status.draft {
            background: #e2e8f0;
            color: #4a5568;
        }

        .status.reversed {
            background: #f8d7da;
            color: #721c24;
        }

        .entry-lines {
            font-size: 7pt;
            padding-left: 10px;
            margin-top: 5px;
        }

        .entry-lines div {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Journal Entries Report - {{ ucfirst($reportType) }}</h1>
        <p>Generated on {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Period:</strong> {{ $filters['startDate'] }} to {{ $filters['endDate'] }}</p>
        <p><strong>Status:</strong> {{ $filters['status'] }} | <strong>Account:</strong> {{ $filters['account'] }}</p>
    </div>

    @if($reportType === 'summary')
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="right">Total Debit</th>
                    <th class="right">Total Credit</th>
                    <th>Status</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td>{{ $entry->date->format('Y-m-d') }}</td>
                        <td>{{ $entry->reference }}</td>
                        <td>
                            {{ $entry->description }}
                            <div class="entry-lines">
                                @foreach($entry->lines as $line)
                                    <div>{{ $line->account?->account_code }} - {{ $line->account?->account_name }}:
                                        @if($line->debit > 0)
                                            Dr {{ number_format($line->debit, 2) }}
                                        @else
                                            Cr {{ number_format($line->credit, 2) }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="right">{{ number_format($entry->lines->sum('debit'), 2) }}</td>
                        <td class="right">{{ number_format($entry->lines->sum('credit'), 2) }}</td>
                        <td>
                            <span class="status {{ $entry->status }}">{{ ucfirst($entry->status) }}</span>
                        </td>
                        <td>{{ $entry->createdBy?->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right">Totals:</td>
                    <td class="right">{{ number_format($entries->sum(fn($e) => $e->lines->sum('debit')), 2) }}</td>
                    <td class="right">{{ number_format($entries->sum(fn($e) => $e->lines->sum('credit')), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th class="right">Debit</th>
                    <th class="right">Credit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    @foreach($entry->lines as $line)
                        <tr>
                            <td>{{ $entry->date->format('Y-m-d') }}</td>
                            <td>{{ $entry->reference }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ $line->account?->account_code ?? 'N/A' }}</td>
                            <td>{{ $line->account?->account_name ?? 'N/A' }}</td>
                            <td class="right">{{ number_format($line->debit, 2) }}</td>
                            <td class="right">{{ number_format($line->credit, 2) }}</td>
                            <td>
                                <span class="status {{ $entry->status }}">{{ ucfirst($entry->status) }}</span>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="right">Totals:</td>
                    <td class="right">{{ number_format($stats['total_debits'], 2) }}</td>
                    <td class="right">{{ number_format($stats['total_credits'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>
</html>
