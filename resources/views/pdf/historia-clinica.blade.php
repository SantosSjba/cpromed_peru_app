<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historia Clínica - {{ $paciente->nombre_completo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #222;
            margin: 0;
            padding: 8mm 10mm;
            line-height: 1.45;
        }
        /* Encabezado: logo + texto en color del logo */
        .header {
            margin-bottom: 10mm;
            padding-bottom: 6mm;
            border-bottom: 2px solid #1565c0;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 50px;
            vertical-align: middle;
        }
        .header-logo img {
            max-height: 42px;
            max-width: 50px;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 6px;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1565c0;
            margin: 0 0 1px 0;
            letter-spacing: 0.3px;
        }
        .header-subtitle {
            font-size: 8pt;
            color: #1565c0;
            margin: 0;
            line-height: 1.35;
        }
        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            color: #222;
            margin: 6mm 0 6mm 0;
            padding: 0;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1565c0;
            margin: 5mm 0 3mm 0;
            padding-bottom: 1mm;
            border-bottom: 1px solid #90caf9;
        }
        .field-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            margin: 3mm 0 0.5mm 0;
        }
        .field-value {
            margin: 0 0 2mm 0;
            font-size: 9pt;
        }
        .grid-2 {
            margin: 0 0 2mm 0;
        }
        .grid-2 table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .grid-2 td {
            padding: 1mm 4mm 1mm 0;
            vertical-align: top;
            width: 50%;
        }
        .block-text {
            margin: 2mm 0;
            padding: 2mm 0;
            white-space: pre-wrap;
            font-size: 8pt;
        }
        table.consultas {
            width: 100%;
            border-collapse: collapse;
            margin: 3mm 0;
            font-size: 8pt;
        }
        table.consultas th,
        table.consultas td {
            border: 1px solid #333;
            padding: 2mm 3mm;
            text-align: left;
            vertical-align: top;
        }
        table.consultas th {
            background: #e3f2fd;
            font-weight: bold;
            color: #1565c0;
        }
        .consulta-detail {
            margin: 4mm 0;
            padding: 3mm;
            border: 1px solid #ccc;
            background: #fafafa;
            font-size: 8pt;
        }
        .consulta-detail-title {
            font-weight: bold;
            color: #1565c0;
            margin-bottom: 1mm;
        }
        .footer-note {
            margin-top: 6mm;
            font-size: 7pt;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- Encabezado: logo + CPROMEDSALUD --}}
    <div class="header">
        <div class="header-logo">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo" />
            @endif
        </div>
        <div class="header-text">
            <p class="header-title">CPROMEDSALUD</p>
            <p class="header-subtitle">Centro de Inmunoterapia &<br/>Medicina Regenerativa</p>
        </div>
    </div>

    <h1 class="doc-title">HISTORIA CLÍNICA</h1>

    {{-- Datos del paciente --}}
    <div class="section-title">Datos del paciente</div>
    <table class="grid-2" style="width:100%; border-collapse:collapse; font-size:8pt;">
        <tr>
            <td style="width:50%; padding:1mm 4mm 1mm 0;"><span class="field-label">Apellidos y nombres</span><br/><span class="field-value">{{ $paciente->apellidos }} {{ $paciente->nombres }}</span></td>
            <td style="width:50%; padding:1mm 0;"><span class="field-label">DNI</span><br/><span class="field-value">{{ $paciente->dni ?? '—' }}</span></td>
        </tr>
        <tr>
            <td style="padding:1mm 4mm 1mm 0;"><span class="field-label">Fecha de nacimiento</span><br/><span class="field-value">{{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : '—' }}</span></td>
            <td style="padding:1mm 0;"><span class="field-label">Edad</span><br/><span class="field-value">{{ $paciente->edad_calculada !== null ? $paciente->edad_calculada . ' años' : '—' }}</span></td>
        </tr>
        <tr>
            <td style="padding:1mm 4mm 1mm 0;"><span class="field-label">Género</span><br/><span class="field-value">{{ $paciente->genero === 'M' ? 'Masculino' : ($paciente->genero === 'F' ? 'Femenino' : $paciente->genero ?? '—') }}</span></td>
            <td style="padding:1mm 0;"><span class="field-label">Grupo sanguíneo</span><br/><span class="field-value">{{ $paciente->grupo_sanguineo ?? '—' }}</span></td>
        </tr>
        <tr>
            <td style="padding:1mm 4mm 1mm 0;"><span class="field-label">Dirección</span><br/><span class="field-value">{{ $paciente->direccion ?? '—' }}</span></td>
            <td style="padding:1mm 0;"><span class="field-label">Celular</span><br/><span class="field-value">{{ $paciente->celular ?? '—' }}</span></td>
        </tr>
        <tr>
            <td style="padding:1mm 4mm 1mm 0;"><span class="field-label">Email</span><br/><span class="field-value">{{ $paciente->email ?? '—' }}</span></td>
            <td style="padding:1mm 0;"><span class="field-label">Ocupación</span><br/><span class="field-value">{{ $paciente->ocupacion ?? '—' }}</span></td>
        </tr>
    </table>

    @if($ficha = $paciente->historiaClinicaFicha)
        {{-- Antecedentes --}}
        <div class="section-title">Antecedentes</div>
        <div class="field-label">Antecedentes médicos</div>
        <div class="block-text">{{ $ficha->antecedentes_medicos ?: '—' }}</div>
        <div class="field-label">Antecedentes personales</div>
        <div class="block-text">{{ $ficha->antecedentes_personales ?: '—' }}</div>
        <div class="field-label">Antecedentes familiares</div>
        <div class="block-text">{{ $ficha->antecedentes_familiares ?: '—' }}</div>
        <div class="field-label">Enfermedades previas</div>
        <div class="block-text">{{ $ficha->enfermedades_previas ?: '—' }}</div>
        <div class="field-label">Cirugías</div>
        <div class="block-text">{{ $ficha->cirugias_si_no ? 'Sí' : 'No' }}{{ $ficha->cirugias_detalle ? ' — ' . $ficha->cirugias_detalle : '' }}</div>
        <div class="field-label">Alergias</div>
        <div class="block-text">{{ $ficha->alergias ?: '—' }}</div>
        <div class="field-label">Medicamentos actuales</div>
        <div class="block-text">{{ $ficha->medicamentos_actuales ?: '—' }}</div>
    @endif

    {{-- Consultas --}}
    <div class="section-title">Consultas</div>
    @if($paciente->historiaClinicaConsultas && $paciente->historiaClinicaConsultas->count() > 0)
        @foreach($paciente->historiaClinicaConsultas->sortBy('fecha_consulta') as $index => $c)
            <div class="consulta-detail">
                <div class="consulta-detail-title">Consulta {{ $index + 1 }} — {{ $c->fecha_consulta->format('d/m/Y H:i') }}</div>
                <div class="field-label">Motivo de consulta</div>
                <div class="block-text">{{ $c->motivo_consulta ?: '—' }}</div>
                <div class="field-label">Enfermedad actual</div>
                <div class="block-text">{{ $c->enfermedad_actual ?: '—' }}</div>
                <div class="field-label">Dx (Diagnóstico)</div>
                <div class="block-text">{{ $c->dx ?: '—' }}</div>
                <div class="field-label">Tx (Tratamiento)</div>
                <div class="block-text">{{ $c->tx ?: '—' }}</div>
                <div class="field-label">Plan Dx.</div>
                <div class="block-text">{{ $c->plan_dx ?: '—' }}</div>
                <div class="field-label">Recomendaciones</div>
                <div class="block-text">{{ $c->recomendaciones ?: '—' }}</div>
            </div>
        @endforeach
    @else
        <div class="block-text">No hay consultas registradas.</div>
    @endif

    <p class="footer-note">Documento generado el {{ now()->format('d/m/Y H:i') }} — CPROMEDSALUD - Centro de Inmunoterapia & Medicina Regenerativa. No incluye exámenes complementarios.</p>
</body>
</html>
