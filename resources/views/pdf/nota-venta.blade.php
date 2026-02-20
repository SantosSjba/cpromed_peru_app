<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nota de Venta</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #000;
            margin: 0;
            padding: 6mm 3mm;
            line-height: 1.5;
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
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1px;
            letter-spacing: 0.2px;
        }
        .company-details {
            font-size: 7pt;
            margin-bottom: 0;
            color: #000;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 10 0 20px 0;
            padding: 0;
            letter-spacing: 0.3px;
        }
        .sale-info {
            width: 100%;
            margin: 0 0 10px 0;
            padding: 0;
            font-size: 7pt;
        }
        table.sale-info-table {
            width: 100% !important;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }
        table.sale-info-table td {
            vertical-align: top;
            padding: 2px 0 4px 0;
            width: 25%;
            margin: 0;
        }
        .sale-info-label {
            font-weight: bold;
            margin: 0;
        }
        .sale-info-value {
            padding-left: 25px;
            margin: 0;
        }
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 6px 0;
            padding: 0;
            letter-spacing: 0.2px;
        }
        .client-info {
            width: 100%;
            margin: 0 0 10px 0;
            padding: 0;
        }
        .client-info table {
            width: 100% !important;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7pt;
            margin: 0;
            padding: 0;
        }
        .client-info td {
            vertical-align: top;
            padding: 2px 0 4px 0;
            width: 33.33%;
            margin: 0;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 10px 0;
            font-size: 7pt;
        }
        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 3px 4px;
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
            margin: 0 0 10px 0;
            text-align: right;
        }
        table.totals {
            margin-left: auto;
            font-size: 7pt;
            border-collapse: collapse;
        }
        table.totals td {
            padding: 2px 0 2px 14px;
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
            font-size: 9pt;
            padding-top: 6px;
            border-top: 2px solid #333;
        }
        .footer-motto {
            margin: 12px 0 0 0;
            padding: 0;
            text-align: center;
            font-size: 6pt;
            color: #333;
        }
        .notes-section {
            margin: 0 0 8px 0;
            padding: 0;
            font-size: 7pt;
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
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 42px;" />
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

    <div class="sale-info">
        <table class="sale-info-table" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
            <colgroup>
                <col style="width: 25%;" />
                <col style="width: 25%;" />
                <col style="width: 25%;" />
                <col style="width: 25%;" />
            </colgroup>
            <tr>
                <td>
                    <div class="sale-info-label">N°:</div>
                    <div class="sale-info-value">{{ $noteNumber }}</div>
                </td>
                <td>
                    <div class="sale-info-label">Fecha de Emisión:</div>
                    <div class="sale-info-value">{{ $date }}</div>
                </td>
                <td>
                    <div class="sale-info-label">Fecha de Vencimiento:</div>
                    <div class="sale-info-value">{{ $dueDate }}</div>
                </td>
                <td>
                    <div class="sale-info-label">Moneda:</div>
                    <div class="sale-info-value">{{ $currency }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Cliente</div>
    <div class="client-info">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
            <colgroup>
                <col style="width: 33.33%;" />
                <col style="width: 33.33%;" />
                <col style="width: 33.34%;" />
            </colgroup>
            <tr>
                <td>
                    <div class="sale-info-label">Nombre:</div>
                    <div class="sale-info-value">{{ $clientName }}</div>
                </td>
                <td>
                    <div class="sale-info-label">DNI/RUC:</div>
                    <div class="sale-info-value">{{ $clientRuc }}</div>
                </td>
                <td>
                    <div class="sale-info-label">Dirección:</div>
                    <div class="sale-info-value">{{ $clientAddress }}</div>
                </td>
            </tr>
        </table>
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

    <div class="footer-motto">CPROMED PERU — CENTRO DE INMUNOTERAPIA & MEDICINA ALTERNATIVA</div>
</body>
</html>
