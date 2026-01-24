<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fechamentos Mensais</title>
    <style>
        @page {
            padding: 15mm 15mm 15mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding: 15px;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            background-color: #fff;
        }

        .container {
            width: 100%;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table thead {
            background-color: #f5f5f5;
            font-weight: bold;
            border-bottom: 2px solid #333;
        }

        table th {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 11pt;
        }

        table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #e8e8e8;
            font-weight: bold;
            border-top: 2px solid #333;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }

        .value {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(file_exists(public_path('images/icons/icon-logo.png')))
            <img src="{{ public_path('images/icons/icon-logo.png') }}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">
            @endif
            <h1>FECHAMENTOS {{ $year_month }}</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">CNPJ</th>
                    <th style="width: 50%;">Nome do Cliente</th>
                    <th style="width: 30%;" class="text-right">Valor Líquido</th>
                </tr>
            </thead>
            <tbody>
                @forelse($closures as $closure)
                    <tr>
                        <td>{{ $closure['cnpj'] }}</td>
                        <td>{{ $closure['name'] }}</td>
                        <td class="value">R$ {{ number_format($closure['total_net'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">
                            Nenhum fechamento encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Relatório gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
