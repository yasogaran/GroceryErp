<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounts Report</title>
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

        .type-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }

        .type-asset { background: #d4edda; color: #155724; }
        .type-liability { background: #f8d7da; color: #721c24; }
        .type-equity { background: #d1ecf1; color: #0c5460; }
        .type-income { background: #e7d4f5; color: #6f42c1; }
        .type-expense { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Accounts Report - {{ ucfirst($reportType) }}</h1>
        <p>Generated on {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Period:</strong> {{ $filters['startDate'] }} to {{ $filters['endDate'] }}</p>
        <p><strong>Account Type:</strong> {{ $filters['accountType'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th class="right">Opening</th>
                <th class="right">Debits</th>
                <th class="right">Credits</th>
                <th class="right">Net Movement</th>
                <th class="right">Closing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->code }}</td>
                    <td>{{ $account->name }}</td>
                    <td>
                        <span class="type-badge type-{{ $account->account_type }}">
                            {{ ucfirst($account->account_type) }}
                        </span>
                    </td>
                    <td class="right">{{ number_format($account->opening_balance, 2) }}</td>
                    <td class="right">{{ number_format($account->period_debits, 2) }}</td>
                    <td class="right">{{ number_format($account->period_credits, 2) }}</td>
                    <td class="right">{{ number_format($account->net_movement, 2) }}</td>
                    <td class="right">{{ number_format($account->closing_balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right">Totals:</td>
                <td class="right">{{ number_format($accounts->sum('period_debits'), 2) }}</td>
                <td class="right">{{ number_format($accounts->sum('period_credits'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
