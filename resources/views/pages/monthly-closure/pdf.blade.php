<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fechamento Mensal</title>
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
            font-size: 15pt;
            color: #333;
            background-color: #fff;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 15px 0;
        }

        .container {
            width: 100%;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2px;
            color: #333;
        }

        .header p {
            font-size: 10pt;
            color: #999;
        }


        .section-title-info {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 8px;
            background-color: rgba(234, 106, 18, 0.1);
            border-left: 3px solid rgb(234, 106, 18);
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 8px;
        }


        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .col {
            display: table-cell;
            width: 33.33%;
            padding: 8px;
            vertical-align: top;
        }

        .col-label {
            font-weight: bold;
            font-size: 9pt;
            color: #999;
            margin-bottom: 4px;
        }

        .col-value {
            font-size: 11pt;
            font-weight: bold;
            color: #333;
        }

        .highlight-row {
            background-color: #fefbf5ff;
        }

        .highlight-row .col-value {
            color: #2e7d32;
            font-size: 12pt;
        }

        .col-2 {
            display: table-cell;
            width: 50%;
            padding: 8px;
            vertical-align: top;
        }

        .info-box {
            margin-bottom: 6px;
        }

        .info-label {
            font-size: 8pt;
            color: #999;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 10pt;
            font-weight: bold;
            color: #333;
        }

        .shipments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .shipments-table thead {
            background-color: rgb(234, 106, 18);
            color: white;
        }

        .shipments-table th {
            padding: 6px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid rgb(234, 106, 18);
        }

        .shipments-table td {
            padding: 6px;
            font-size: 9pt;
        }

        .shipments-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
        }

        .divider {
            height: 1px;
            background-color: #ddd;
            margin: 15px 0;
        }

        .closure-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .closure-info div {
            display: table-row;
        }

        .closure-info .label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f5f5f5;
            font-size: 10pt;
        }

        .closure-info .value {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            width: 70%;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- CABEÇALHO -->
        <div class="header">
            @if(file_exists(public_path('images/icons/icon-logo.png')))
            <img src="{{ public_path('images/icons/icon-logo.png') }}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">
            @endif
            <h1>FECHAMENTO MENSAL</h1>
        </div>

        <!-- INFORMAÇÕES DO FECHAMENTO -->
        <div class="section-title-info">Informações do Fechamento</div>
        <div class="closure-info">
            <div>
                <span class="label">Período</span>
                <span class="value">{{ $month }}/{{ $year }}</span>
            </div>
            <div>
                <span class="label">Cliente</span>
                <span class="value">{{ $client_name }}</span>
            </div>
        </div>

        <div class="section-title-info">Detalhes do Fechamento</div>
        <!-- ETIQUETAS SIMPLES, KIT E SUPER KIT -->
        <div class="row">
            <div class="col">
                <div class="col-label">Etiquetas Simples</div>
                <div class="col-value">{{ $total_simple_labels }}</div>
            </div>
            <div class="col">
                <div class="col-label">Etiquetas Kit</div>
                <div class="col-value">{{ $total_kit_labels }}</div>
            </div>
            <div class="col">
                <div class="col-label">Etiquetas Super Kit</div>
                <div class="col-value">{{ $total_superkit_labels }}</div>
            </div>
        </div>

        <!-- PREÇOS UNITÁRIOS -->
        <div class="row">
            <div class="col">
                <div class="col-label">Unitário Simples</div>
                <div class="col-value">R$ {{ number_format($unit_price_simple, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Unitário Kit</div>
                <div class="col-value">R$ {{ number_format($unit_price_kit, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Unitário Super Kit</div>
                <div class="col-value">-</div>
            </div>
        </div>


        <!-- VALORES TOTAIS -->
        <div class="row">
            <div class="col">
                <div class="col-label">Valor Simples</div>
                <div class="col-value">R$ {{ number_format($total_simple_net, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Valor Kit</div>
                <div class="col-value">R$ {{ number_format($total_kit_net, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Valor Super Kit</div>
                <div class="col-value">R$ {{ number_format($total_superkit_value, 2, ',', '.') }}</div>
            </div>
        </div>

        <hr>

        <!-- RESUMO FINANCEIRO -->
        <div class="row highlight-row">
            <div class="col">
                <div class="col-label">Valor Bruto</div>
                <div class="col-value">R$ {{ number_format($total_gross, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Desconto</div>
                <div class="col-value" style="color: #d32f2f;">R$ {{ number_format($total_discount, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Valor Líquido</div>
                <div class="col-value">R$ {{ number_format($total_net, 2, ',', '.') }}</div>
            </div>
        </div>

        <hr>
        <!-- QR CODE PIX -->
        @if($total_net > 0 && !empty($qr_code_url ?? null))
        <div style="margin-top: 20px; margin-bottom: 20px;">
            <div style="display: table; width: 100%;">
                <!-- Coluna Esquerda - Texto -->
                <div style="display: table-cell; width: 55%; vertical-align: middle; padding: 20px;">
                    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 15px; text-align: center;">
                        Realize o pagamento diretamente pelo QRCODE
                    </div>
                    
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 10pt; color: #666; margin-bottom: 10px;">Ou via chave PIX (CNPJ)</div>
                        <div style="font-size: 16pt; font-weight: bold; color: #1e3a8a; margin-bottom: 10px;">
                            62.001.080/0001-57
                        </div>
                        <div style="font-size: 11pt; color: #666;">
                            Valor: <span style="font-weight: bold; color: #2e7d32; font-size: 13pt;">R$ {{ number_format($total_net, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Coluna Direita - QR Code -->
                <div style="display: table-cell; width: 45%; vertical-align: middle; text-align: center; padding: 20px;">
                    <img src="{{ $qr_code_url }}" alt="QR Code PIX" style="width: 180px; height: 180px; border: 1px solid #ddd; padding: 5px; background-color: white;">
                </div>
            </div>
        </div>
        @endif

        <hr>

        <div class="row">
            <div class="col" style="width: 100%;">
                <div class="col-label">Faixa de Preço</div>
                <div class="col-value">{{ $price_range }}</div>
            </div>
            <div class="col">
                <div class="col-label">Desconto referente às etiquetas simples</div>
                <div class="col-value">R$ {{ number_format($total_discount_simple, 2, ',', '.') }}</div>
            </div>
            <div class="col">
                <div class="col-label">Desconto referente às etiquetas kit</div>
                <div class="col-value">R$ {{ number_format($total_discount_kit, 2, ',', '.') }}</div>
            </div>
        </div>

        <hr>

        <!-- REMESSAS INCLUÍDAS -->
        @if($shipments && count($shipments) > 0)
        <div class="section-title">Remessas Incluídas:</div>
        <table class="shipments-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>DATA</th>
                    <th class="text-right">QTD</th>
                    <th class="text-right">VALOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipments as $shipment)
                <tr>
                    <td>{{ $shipment['shipment_code'] }}</td>
                    <td>{{ $shipment['creation_date'] }}</td>
                    <td class="text-right">{{ $shipment['total_items'] ?? 0 }}</td>
                    <td class="text-right">R$ {{ number_format($shipment['value'] ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- RODAPÉ -->
        <div class="footer">
            <p>Documento gerado automaticamente em {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
