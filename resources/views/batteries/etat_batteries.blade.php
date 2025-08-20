@extends('layouts.app')

@section('content')
<div class="main-content mt-4">

    <h2 class="mb-3">📊 État des batteries (SOC toutes les 5 minutes)</h2>

    <!-- Filtres -->
    <div class="filter-group mb-4">
        <form method="GET">
            <select name="periode" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                <option value="today" {{ $periode === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                <option value="week" {{ $periode === 'week' ? 'selected' : '' }}>Cette semaine</option>
                <option value="month" {{ $periode === 'month' ? 'selected' : '' }}>Ce mois</option>
                <option value="year" {{ $periode === 'year' ? 'selected' : '' }}>Cette année</option>
                <option value="custom" {{ $periode === 'custom' ? 'selected' : '' }}>Date spécifique</option>
                <option value="range" {{ $periode === 'range' ? 'selected' : '' }}>Plage de dates</option>
            </select>

            @if ($periode === 'custom')
                <input type="date" name="date" class="form-control d-inline-block w-auto ms-2" onchange="this.form.submit()" value="{{ request('date') }}">
            @endif

            @if ($periode === 'range')
                <input type="date" name="start" class="form-control d-inline-block w-auto ms-2" value="{{ request('start') }}">
                <input type="date" name="end" class="form-control d-inline-block w-auto ms-2" value="{{ request('end') }}">
                <button type="submit" class="btn btn-primary ms-2">Appliquer</button>
            @endif
        </form>
    </div>

    <!-- Boutons d'export -->
    <div class="mb-3 d-flex gap-2">
        <button onclick="exportToExcel()" class="btn btn-success">📤 Export Excel</button>
        <button onclick="exportToCSV()" class="btn btn-primary">📄 Export CSV</button>
        <button onclick="exportToPDF()" class="btn btn-danger">🧾 Export PDF</button>
    </div>

    <!-- Tableau -->
    <div class="table-responsive" style="max-height: 70vh; overflow: auto">
        <table id="socTable" class="table table-bordered table-sm">
            <thead class="thead-dark sticky-top bg-light">
                <tr>
                    <th>MAC ID</th>
                    @foreach ($timeSlots as $slot)
                        <th>{{ \Carbon\Carbon::parse($slot)->format('H:i') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $macId => $row)
                    <tr>
                        <td><strong>{{ $macId }}</strong></td>
                        @foreach ($timeSlots as $slot)
                            <td>{{ $row[$slot] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- SheetJS pour Excel et CSV -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- jsPDF pour PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    // Export Excel
    function exportToExcel() {
        const table = document.getElementById("socTable");
        const ws = XLSX.utils.table_to_sheet(table);

        // Forcer format texte pour la colonne MAC ID
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let R = range.s.r + 1; R <= range.e.r; ++R) {
            const cellAddress = XLSX.utils.encode_cell({ r: R, c: 0 });
            if (ws[cellAddress]) {
                ws[cellAddress].z = '@';
                ws[cellAddress].t = 's';
            }
        }

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "SOC");
        XLSX.writeFile(wb, `etat_batteries_soc_{{ $start }}_to_{{ $end }}.xlsx`);
    }

    // Export CSV
    function exportToCSV() {
        const table = document.getElementById("socTable");
        const ws = XLSX.utils.table_to_sheet(table);
        const csv = XLSX.utils.sheet_to_csv(ws);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `etat_batteries_soc_{{ $start }}_to_{{ $end }}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Export PDF
    async function exportToPDF() {
        const table = document.getElementById("socTable");
        const clone = table.cloneNode(true);
        clone.style.width = "fit-content";
        const container = document.createElement("div");
        container.style.padding = "20px";
        container.appendChild(clone);
        document.body.appendChild(container);

        await html2canvas(container, {
            scale: 2,
            useCORS: true
        }).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jspdf.jsPDF('l', 'mm', 'a4');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save(`etat_batteries_soc_{{ $start }}_to_{{ $end }}.pdf`);
        });

        document.body.removeChild(container);
    }
</script>
@endsection
