<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #2b2b2b; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #b89a5e; padding-bottom: 8px; margin-bottom: 12px; }
        .header .store { font-size: 15px; font-weight: bold; }
        .muted { color: #6b6b6b; }
        .doc-title { text-align: right; font-size: 12px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        .data th { background: #f1f0eb; border: 1px solid #d9d6cc; padding: 5px 6px; text-align: left; font-size: 9px; }
        .data td { border: 1px solid #e4e1d8; padding: 5px 6px; }
        .text-right { text-align: right; }
        .totals { margin-top: 12px; width: 320px; float: right; }
        .totals td { padding: 4px 6px; border-bottom: 1px solid #e4e1d8; }
        .totals .label { color: #6b6b6b; }
        .totals .value { text-align: right; font-weight: bold; }
        .clear { clear: both; }
        .footer { margin-top: 16px; font-size: 9px; color: #6b6b6b; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="store">{{ $storeName }}</div>
                    <div class="muted">{{ $report['title'] }}</div>
                    @if ($report['dated'])
                        <div class="muted">Del {{ \Illuminate\Support\Carbon::parse($report['from'])->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($report['to'])->format('d/m/Y') }}</div>
                    @endif
                </td>
                <td class="doc-title">
                    <div>Generado {{ $generatedAt->format('d/m/Y H:i') }}</div>
                    <div class="muted" style="font-weight: normal;">{{ count($report['rows']) }} registros</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                @foreach ($report['columns'] as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($report['columns']) }}" class="muted" style="text-align: center; padding: 14px;">Sin datos en el período seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        @foreach ($report['totals'] as $total)
            <tr>
                <td class="label">{{ $total['label'] }}</td>
                <td class="value">{{ $total['value'] }}</td>
            </tr>
        @endforeach
    </table>
    <div class="clear"></div>

    <div class="footer">{{ $report['description'] }}</div>
</body>
</html>
