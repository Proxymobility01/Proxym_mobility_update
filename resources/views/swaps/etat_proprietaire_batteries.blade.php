@extends('layouts.app')

@section('content')
<div class="main-content">
    <h2 class="mb-4">📋 Tableau de propriétaire des batteries (30 min) – {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h2>

    <!-- 🔍 Formulaire de filtre par date -->
    <div class="mb-4">
        <form method="GET" action="{{ route('swaps.etat_batteries_proprietaire') }}" class="form-inline">
            <label for="date" class="me-2 fw-bold">Choisir une date :</label>
            <input type="date" name="date" id="date" value="{{ $date }}" class="form-control me-2" max="{{ now()->toDateString() }}">
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    </div>

    <!-- 📤 Boutons d'export -->
    <div class="mb-3 d-flex gap-2">
        <button onclick="exportToExcel()" class="btn btn-success">📤 Export Excel</button>
        <button onclick="exportToCSV()" class="btn btn-primary">📄 Export CSV</button>
        <button onclick="exportToPDF()" class="btn btn-danger">🧾 Export PDF</button>
    </div>

    <!-- 📊 Tableau -->
    <div class="table-responsive">
        <table id="ownershipTable" class="table table-bordered table-sm" style="min-width: max-content;">
            <thead class="table-dark sticky-top">
                <tr>
                    <th>MAC ID</th>
                    @foreach ($slots as $slot)
                        <th>{{ $slot }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $mac => $values)
                    <tr>
                        <td class="fw-bold">{{ $mac }}</td>
                        @foreach ($slots as $slot)
                            <td>{{ $values[$slot] }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 📦 Exportation JS -->
<!-- SheetJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- jsPDF + html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    // ✅ Export Excel
    function exportToExcel() {
        const table = document.getElementById("ownershipTable");
        const ws = XLSX.utils.table_to_sheet(table);
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let R = range.s.r + 1; R <= range.e.r; ++R) {
            const cellAddress = XLSX.utils.encode_cell({ r: R, c: 0 });
            if (ws[cellAddress]) {
                ws[cellAddress].z = '@';
                ws[cellAddress].t = 's';
            }
        }
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Proprietaire");
        XLSX.writeFile(wb, `etat_proprietaire_batterie_{{ $date }}.xlsx`);
    }

    // ✅ Export CSV
    function exportToCSV() {
        const table = document.getElementById("ownershipTable");
        const ws = XLSX.utils.table_to_sheet(table);
        const csv = XLSX.utils.sheet_to_csv(ws);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `etat_proprietaire_batterie_{{ $date }}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // ✅ Export PDF
    async function exportToPDF() {
        const table = document.getElementById("ownershipTable");
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
            pdf.save(`etat_proprietaire_batterie_{{ $date }}.pdf`);
        });

        document.body.removeChild(container);
    }
</script>
@endsection
