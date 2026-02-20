<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nota de Venta</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 6mm 8mm;
            line-height: 1.3;
        }
        .header {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #333;
        }
        .company-block {
            text-align: right;
        }
        .company-name {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }
        .company-details {
            font-size: 9pt;
            margin-bottom: 1px;
            color: #000;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 12px 0 10px 0;
            letter-spacing: 0.5px;
        }
        .sale-info {
            margin-bottom: 8px;
            font-size: 9pt;
        }
        table.sale-info {
            width: 100%;
        }
        table.sale-info td {
            padding: 2px 10px 2px 0;
            vertical-align: top;
        }
        table.sale-info td:first-child {
            padding-left: 0;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 6px 0;
            letter-spacing: 0.3px;
        }
        .client-info div {
            margin-bottom: 2px;
            font-size: 9pt;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9pt;
        }
        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: left;
        }
        table.items th {
            background: #f0f0f0;
            font-weight: bold;
        }
        table.items .num,
        table.items .qty,
        table.items .price,
        table.items .amount {
            text-align: right;
        }
        table.items td.desc {
            max-width: 0;
            word-wrap: break-word;
        }
        .totals-wrap {
            margin-top: 10px;
            text-align: right;
        }
        table.totals {
            margin-left: auto;
            font-size: 9pt;
            border-collapse: collapse;
        }
        table.totals td {
            padding: 3px 0 3px 20px;
        }
        table.totals td:first-child {
            padding-left: 0;
            text-align: right;
        }
        table.totals td:last-child {
            min-width: 90px;
            text-align: right;
        }
        table.totals tr.grand-total td {
            font-weight: bold;
            font-size: 11pt;
            padding-top: 8px;
            border-top: 2px solid #333;
        }
        .footer-motto {
            margin-top: 16px;
            text-align: center;
            font-size: 8pt;
            color: #333;
            max-width: 100%;
        }
        .notes-section {
            margin-top: 8px;
            font-size: 8pt;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                @if($logoBase64)
                    <td width="100" valign="top" style="padding-right: 10px;">
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 52px;" />
                    </td>
                @endif
                <td valign="top" width="*" align="right">
                    <div class="company-block">
                        <div class="company-name">{{ $companyName }}</div>
                        <div class="company-details">RUC: {{ $companyRuc }}</div>
                        @if(!empty($companyAddress))
                            <div class="company-details">{{ $companyAddress }}</div>
                        @endif
                        @if(!empty($sucursal))
                            <div class="company-details">{{ $sucursal }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Nota de Venta</div>

    <table class="sale-info" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td><strong>N°:</strong> {{ $noteNumber }}</td>
            <td><strong>Fecha de Emisión:</strong> {{ $date }}</td>
            <td><strong>Fecha de Vencimiento:</strong> {{ $dueDate }}</td>
            <td><strong>Moneda:</strong> {{ $currency }}</td>
        </tr>
    </table>

    <div class="section-title">Cliente</div>
    <div class="client-info">
        <div><strong>Nombre:</strong> {{ $clientName }}</div>
        <div><strong>DNI/RUC:</strong> {{ $clientRuc }}</div>
        <div><strong>Dirección:</strong> {{ $clientAddress }}</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:40%">Descripción</th>
                <th class="qty" style="width:12%">Cantidad</th>
                <th class="price" style="width:14%">Precio Unit.</th>
                <th class="price" style="width:14%">Descuento</th>
                <th class="amount" style="width:15%">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
            <tr>
                <td class="num">{{ $idx + 1 }}</td>
                <td class="desc">{{ $item['description'] }}</td>
                <td class="qty">{{ $item['quantity'] }}</td>
                <td class="price">{{ $currencySymbol }} {{ $item['unitPrice'] }}</td>
                <td class="price">{{ $currencySymbol }} {{ $item['discount'] }}</td>
                <td class="amount">{{ $currencySymbol }} {{ $item['amount'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrap">
        <table class="totals">
            <tr>
                <td>Subtotal:</td>
                <td>{{ $currencySymbol }} {{ $subtotal }}</td>
            </tr>
            <tr>
                <td>IGV ({{ $taxRate }}%):</td>
                <td>{{ $currencySymbol }} {{ $tax }}</td>
            </tr>
            <tr>
                <td>Descuento Total:</td>
                <td>{{ $currencySymbol }} {{ $discount }}</td>
            </tr>
            <tr class="grand-total">
                <td>Total:</td>
                <td>{{ $currencySymbol }} {{ $total }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($notes))
        <div class="notes-section">
            <strong>Notas:</strong> {{ $notes }}
        </div>
    @endif

    <div class="footer-motto">CPROMED PERU — Centro de curaciones de heridas; cuidado, innovación y recuperación.</div>
</body>
</html>
