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

    <h2>Statistiques des swaps par heure</h2>

    <!-- Filtres -->
    <div class="filter-group mb-4">
        <select id="periode-select" class="form-select d-inline-block w-auto">
            <option value="today">Aujourd'hui</option>
            <option value="week">Cette semaine</option>
            <option value="month">Ce mois</option>
            <option value="year">Cette année</option>
            <option value="custom">Date spécifique</option>
            <option value="range">Plage de dates</option>
        </select>

        <input type="date" id="custom-date" class="form-control d-inline-block w-auto ms-2" style="display:none;">
        <input type="date" id="start-date" class="form-control d-inline-block w-auto ms-2" style="display:none;">
        <input type="date" id="end-date" class="form-control d-inline-block w-auto ms-2" style="display:none;">

        <button class="btn btn-primary ms-2" id="export-csv">Export CSV</button>
        <button class="btn btn-success ms-1" id="export-excel">Export Excel</button>
        <button class="btn btn-danger ms-1" id="export-pdf">Export PDF</button>
    </div>

    <!-- Tableau -->
    <div class="table-responsive">
        <table id="swap-table" class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Heure</th>
                    <th>Nombre de Swaps</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rempli dynamiquement par JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts d’export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodeSelect = document.getElementById('periode-select');
    const customDate = document.getElementById('custom-date');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const tableBody = document.querySelector('#swap-table tbody');

    function fetchSwaps() {
        let url = '/api/swaps/statistiques-par-heure';
        const periode = periodeSelect.value;

        const params = new URLSearchParams();
        params.append('periode', periode);

        if (periode === 'custom') {
            params.append('date', customDate.value);
        } else if (periode === 'range') {
            params.append('start', startDate.value);
            params.append('end', endDate.value);
        }

        fetch(`${url}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                data.data.forEach(row => {
                    tableBody.innerHTML += `
                        <tr>
                            <td>${row.heure}</td>
                            <td>${row.total}</td>
                        </tr>
                    `;
                });
            });
    }

    periodeSelect.addEventListener('change', () => {
        customDate.style.display = 'none';
        startDate.style.display = 'none';
        endDate.style.display = 'none';

        if (periodeSelect.value === 'custom') {
            customDate.style.display = 'inline-block';
        } else if (periodeSelect.value === 'range') {
            startDate.style.display = 'inline-block';
            endDate.style.display = 'inline-block';
        }

        fetchSwaps();
    });

    [customDate, startDate, endDate].forEach(input => {
        input.addEventListener('change', fetchSwaps);
    });

    // Exports
    document.getElementById('export-csv').addEventListener('click', () => {
        const rows = [['Heure', 'Nombre de Swaps']];
        document.querySelectorAll('#swap-table tbody tr').forEach(tr => {
            const cells = Array.from(tr.children).map(td => td.textContent);
            rows.push(cells);
        });
        const csvContent = rows.map(e => e.join(",")).join("\n");
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "swaps_par_heure.csv";
        link.click();
    });

    document.getElementById('export-excel').addEventListener('click', () => {
        const wb = XLSX.utils.book_new();
        const ws_data = [['Heure', 'Nombre de Swaps']];
        document.querySelectorAll('#swap-table tbody tr').forEach(tr => {
            const cells = Array.from(tr.children).map(td => td.textContent);
            ws_data.push(cells);
        });
        const ws = XLSX.utils.aoa_to_sheet(ws_data);
        XLSX.utils.book_append_sheet(wb, ws, "Swaps");
        XLSX.writeFile(wb, "swaps_par_heure.xlsx");
    });

    document.getElementById('export-pdf').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Statistiques des swaps par heure", 14, 16);
        let y = 30;
        doc.setFontSize(12);
        doc.text("Heure", 20, y);
        doc.text("Nombre", 80, y);
        y += 10;
        document.querySelectorAll('#swap-table tbody tr').forEach(tr => {
            const cells = tr.children;
            doc.text(cells[0].textContent, 20, y);
            doc.text(cells[1].textContent, 80, y);
            y += 10;
        });
        doc.save("swaps_par_heure.pdf");
    });

    fetchSwaps(); // initial
});
</script>
@endsection
