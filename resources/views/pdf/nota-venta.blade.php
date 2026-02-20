<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nota de Venta</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            color: #000;
            margin: 0;
            padding: 18mm;
            line-height: 1.35;
        }
        .header {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #333;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .company-details {
            font-size: 10pt;
            margin-bottom: 2px;
            color: #000;
        }
        .doc-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 18px 0 16px 0;
            letter-spacing: 1px;
        }
        .sale-info {
            margin-bottom: 14px;
            font-size: 10pt;
        }
        .sale-info div {
            margin-bottom: 3px;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 14px 0 8px 0;
            letter-spacing: 0.5px;
        }
        .client-info div {
            margin-bottom: 3px;
            font-size: 10pt;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10pt;
        }
        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 6px 8px;
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
            margin-top: 14px;
            text-align: right;
        }
        table.totals {
            margin-left: auto;
            font-size: 10pt;
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
            font-size: 12pt;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .footer-motto {
            margin-top: 28px;
            text-align: center;
            font-size: 10pt;
            color: #333;
        }
        .notes-section {
            margin-top: 12px;
            font-size: 9pt;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                @if($logoBase64)
                    <td width="120" valign="top" style="padding-right: 15px;">
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 65px;" />
                    </td>
                @endif
                <td valign="top">
                    <div class="company-name">{{ $companyName }}</div>
                    <div class="company-details">RUC: {{ $companyRuc }}</div>
                    <div class="company-details">{{ $companyAddress }}</div>
                    @if(!empty($sucursal))
                        <div class="company-details">{{ $sucursal }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Nota de Venta</div>

    <div class="sale-info">
        <div><strong>N°:</strong> {{ $noteNumber }}</div>
        <div><strong>Fecha de Emisión:</strong> {{ $date }}</div>
        <div><strong>Fecha de Vencimiento:</strong> {{ $dueDate }}</div>
        <div><strong>Moneda:</strong> {{ $currency }}</div>
    </div>

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

    <div class="footer-motto">Juntos Cuidamos Tu Salud...</div>
</body>
</html>
