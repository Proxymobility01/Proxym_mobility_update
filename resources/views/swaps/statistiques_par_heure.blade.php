@extends('layouts.app')

@section('content')
<div class="main-content">

<!-- Onglets de navigation -->
<div class="nav-tabs mb-4 d-flex flex-wrap gap-2">
    <div class="nav-tab {{ Request::is('batteries/soe') ? 'active' : '' }}"
         onclick="window.location.href='{{ route('batterie.soe') }}'">
        🔋 SOE des Batteries
    </div>
    <div class="nav-tab {{ Request::is('etat-batteries') ? 'active' : '' }}"
         onclick="window.location.href='{{ route('etat-batteries.index') }}'">
        ⏱️ État Batteries (5min)
    </div>
    <div class="nav-tab {{ Request::is('swaps/statistiques-par-heure') ? 'active' : '' }}"
         onclick="window.location.href='{{ route('swaps.par.heure') }}'">
        📊 Swaps par Heure
    </div>
</div>

<h2>Statistiques des swaps par agence et par heure</h2>

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
    <table id="swap-table" class="table table-bordered table-striped">
        <thead class="table-light">
            <tr id="header-row">
                <th>Agence</th>
                <!-- Colonnes heures ajoutées dynamiquement -->
            </tr>
        </thead>
        <tbody>
            <!-- Rempli dynamiquement par JS -->
        </tbody>
    </table>
</div>
</div>

<!-- Librairies d’export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodeSelect = document.getElementById('periode-select');
    const customDate = document.getElementById('custom-date');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const tableHead = document.getElementById('header-row');
    const tableBody = document.querySelector('#swap-table tbody');
    let heures = [];
    let agencesList = [];

    function fetchSwaps() {
        const periode = periodeSelect.value;
        const params = new URLSearchParams({ periode });

        if (periode === 'custom') {
            params.append('date', customDate.value);
        } else if (periode === 'range') {
            params.append('start', startDate.value);
            params.append('end', endDate.value);
        }

        fetch(`/api/swaps/statistiques-par-heure?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                agencesList = data.agences;
                const raw = data.data;
                heures = []; // reset

                // Structuration
                const structured = {};
                raw.forEach(row => {
                    const heure = row.heure;
                    if (!heures.includes(heure)) heures.push(heure);
                    agencesList.forEach(ag => {
                        if (!structured[ag.id]) {
                            structured[ag.id] = { nom: ag.nom, heures: {}, total: 0 };
                        }
                        structured[ag.id].heures[heure] = row[ag.id] || 0;
                        structured[ag.id].total += row[ag.id] || 0;
                    });
                });

                renderTable(structured);
            });
    }

    function renderTable(data) {
        const heuresUniq = [...new Set(heures)];
        tableHead.innerHTML = `<th>Agence</th>` + heuresUniq.map(h => `<th>${h}</th>`).join('') + `<th>Total</th>`;
        tableBody.innerHTML = '';

        for (const details of Object.values(data)) {
            let row = `<tr><td>${details.nom}</td>`;
            heuresUniq.forEach(h => {
                row += `<td>${details.heures[h] || 0}</td>`;
            });
            row += `<td><strong>${details.total}</strong></td></tr>`;
            tableBody.innerHTML += row;
        }
    }

    // Affichage des dates selon filtre
    periodeSelect.addEventListener('change', () => {
        customDate.style.display = startDate.style.display = endDate.style.display = 'none';
        if (periodeSelect.value === 'custom') customDate.style.display = 'inline-block';
        else if (periodeSelect.value === 'range') {
            startDate.style.display = 'inline-block';
            endDate.style.display = 'inline-block';
        }
        fetchSwaps();
    });

    [customDate, startDate, endDate].forEach(el => el.addEventListener('change', fetchSwaps));
    fetchSwaps();

    // Export CSV
    document.getElementById('export-csv').addEventListener('click', () => {
        const rows = [];
        const heuresUniq = [...new Set(heures)];
        const header = ['Agence', ...heuresUniq, 'Total'];
        rows.push(header);

        document.querySelectorAll('#swap-table tbody tr').forEach(tr => {
            const cells = Array.from(tr.children).map(td => td.textContent.trim());
            rows.push(cells);
        });

        const csv = rows.map(e => e.join(",")).join("\n");
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "swaps_par_agence.csv";
        link.click();
    });

    // Export Excel
    document.getElementById('export-excel').addEventListener('click', () => {
        const heuresUniq = [...new Set(heures)];
        const ws_data = [['Agence', ...heuresUniq, 'Total']];

        document.querySelectorAll('#swap-table tbody tr').forEach(tr => {
            const row = Array.from(tr.children).map(td => td.textContent.trim());
            ws_data.push(row);
        });

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(ws_data);
        XLSX.utils.book_append_sheet(wb, ws, "Swaps");
        XLSX.writeFile(wb, "swaps_par_agence.xlsx");
    });

    // Export PDF
    document.getElementById('export-pdf').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: "landscape" });
        doc.setFontSize(12);
        doc.text("Statistiques des swaps par agence et par heure", 14, 16);

        const heuresUniq = [...new Set(heures)];
        const headers = ['Agence', ...heuresUniq, 'Total'];

        const rows = [];
        document.querySelectorAll('#swap-table tbody tr').forEach(tr => {
            const row = Array.from(tr.children).map(td => td.textContent.trim());
            rows.push(row);
        });

        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 20,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [40, 40, 40] }
        });

        doc.save("swaps_par_agence.pdf");
    });
});
</script>
@endsection
