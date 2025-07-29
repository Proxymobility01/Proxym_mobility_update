@extends('layouts.app')

@section('content')
<div class="main-content">
    <!-- Onglets de navigation -->
<div class="nav-tabs mb-4 d-flex flex-wrap gap-2">
    <div class="nav-tab {{ Request::is('batteries/soe') ? 'active' : '' }}"
         data-tab="soe"
         data-url="{{ route('batterie.soe') }}"
         onclick="window.location.href=this.dataset.url">
        🔋 SOE des Batteries
    </div>

    <div class="nav-tab {{ Request::is('etat-batteries') ? 'active' : '' }}"
         data-tab="etat"
         data-url="{{ route('etat-batteries.index') }}"
         onclick="window.location.href=this.dataset.url">
        ⏱️ État Batteries (5min)
    </div>

    <div class="nav-tab {{ Request::is('swaps/statistiques-par-heure') ? 'active' : '' }}"
         data-tab="swaps"
         data-url="{{ route('swaps.par.heure') }}"
         onclick="window.location.href=this.dataset.url">
        📊 Swaps par Heure
    </div>
</div>

    <h2 class="mb-4">⏱️ État des batteries avec association (toutes les 5 minutes)</h2>

    <div class="mb-3 d-flex gap-2">
        <button onclick="exportTableToCSV('etat_batteries_table')" class="btn btn-outline-primary">📄 Export CSV</button>
        <button onclick="exportTableToExcel('etat_batteries_table')" class="btn btn-outline-success">📊 Export Excel</button>
        <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Imprimer</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="etat_batteries_table">
            <thead class="table-dark">
                <tr>
                    <th>MAC ID</th>
                    <th>SOC (%)</th>
                    <th>Utilisateur associé</th>
                    <th>Horodatage</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($etats as $etat)
                    <tr>
                        <td>{{ $etat->mac_id }}</td>
                        <td class="{{ $etat->soc !== null && $etat->soc < 30 ? 'text-danger fw-bold' : '' }}">
                            {{ $etat->soc ?? 'N/A' }}
                        </td>
                        <td>
                            @if ($etat->user)
                                {{ $etat->user->nom }} {{ $etat->user->prenom }} ({{ $etat->user->phone }})
                            @else
                                <span class="text-muted">Non associé</span>
                            @endif
                        </td>
                        <td>{{ $etat->captured_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $etats->links() }}
</div>

{{-- Scripts JS pour les exports --}}
@verbatim
<script>
function exportTableToCSV(tableId) {
    let table = document.getElementById(tableId);
    let rows = table.querySelectorAll("tr");
    let csv = [];

    for (let row of rows) {
        let cells = row.querySelectorAll("th, td");
        let rowData = [];
        for (let cell of cells) {
            rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
        }
        csv.push(rowData.join(","));
    }

    let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
    let link = document.createElement("a");
    link.setAttribute("href", csvContent);
    link.setAttribute("download", "etat_batteries.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportTableToExcel(tableId) {
    let table = document.getElementById(tableId).outerHTML;

    let template = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<!--[if gte mso 9]>
<xml>
    <x:ExcelWorkbook>
        <x:ExcelWorksheets>
            <x:ExcelWorksheet>
                <x:Name>Feuille1</x:Name>
                <x:WorksheetOptions>
                    <x:DisplayGridlines/>
                </x:WorksheetOptions>
            </x:ExcelWorksheet>
        </x:ExcelWorksheets>
    </x:ExcelWorkbook>
</xml>
<![endif]-->
</head>
<body>
    ${table}
</body>
</html>`;

    let base64 = s => window.btoa(unescape(encodeURIComponent(s)));
    let uri = 'data:application/vnd.ms-excel;base64,';
    let link = document.createElement('a');
    link.href = uri + base64(template);
    link.download = 'etat_batteries.xls';
    link.click();
}
</script>
@endverbatim
@endsection
