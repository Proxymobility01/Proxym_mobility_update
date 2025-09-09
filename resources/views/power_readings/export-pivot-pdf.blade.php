<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Suivi Élec — Pivot</title>
    <style>
        body{ font-family: DejaVu Sans, sans-serif; font-size:11px; }
        table{ width:100%; border-collapse:collapse; }
        th,td{ border:1px solid #333; padding:4px; }
        thead th{ background:#f5f5a5; }
    </style>
</head>
<body>
{{-- Reuse the Excel layout for PDF rendering --}}
@include('power_readings.export-pivot-excel', [
    'sheetLabel' => $sheetLabel ?? ($mode ?? 'Pivot'),
    'agences'    => $agences,
    'rows'       => $rows,
    'dateMin'    => $dateMin ?? null,
    'dateMax'    => $dateMax ?? null,
])
</body>
</html>
