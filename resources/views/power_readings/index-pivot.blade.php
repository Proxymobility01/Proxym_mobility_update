@extends('layouts.app')

@section('content')
    <div class="main-content" style="padding: 25px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
        <!-- Header Section -->
        <div style="background: linear-gradient(135deg, #B6D431 0%, #9BBE2A 100%); border-radius: 15px; padding: 30px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(182, 212, 49, 0.3); color: white;">
            <h2 style="font-size: 2.2rem; font-weight: 700; margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                <i class="fas fa-chart-line"></i>
                Suivi des Consommations — Pivot
            </h2>
            <p style="font-size: 1.1rem; margin: 8px 0 0 0; opacity: 0.9;">Tableau de bord des consommations énergétiques</p>
        </div>

        <!-- Filters Section -->
        <div style="background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin-bottom: 25px; overflow: hidden;">
            <div style="background: #2C3E50; color: white; padding: 20px 30px;">
                <h5 style="margin: 0; font-weight: 600;"><i class="fas fa-sliders-h"></i> Filtres & Paramètres</h5>
            </div>
            <div style="padding: 30px;">
                <form method="get">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; align-items: end;">
                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #2C3E50; margin-bottom: 12px; font-size: 1rem;">
                                <i class="fas fa-calendar-alt" style="color: #B6D431; width: 18px;"></i>
                                Date de début
                            </label>
                            <input type="date" name="date_min" value="{{ $dateMin ?? '' }}"
                                   style="width: 100%; border: 2px solid #e9ecef; border-radius: 12px; padding: 16px 20px; font-size: 1rem; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: linear-gradient(145deg, #ffffff, #f8f9fa); box-shadow: 0 4px 12px rgba(0,0,0,0.05);"
                                   onmouseover="this.style.borderColor='#D4E661'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.1)'"
                                   onmouseout="this.style.borderColor='#e9ecef'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'"
                                   onfocus="this.style.borderColor='#B6D431'; this.style.boxShadow='0 0 0 0.2rem rgba(182, 212, 49, 0.25), 0 8px 25px rgba(182, 212, 49, 0.15)'; this.style.transform='translateY(-2px)'; this.style.background='white'"
                                   onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'; this.style.background='linear-gradient(145deg, #ffffff, #f8f9fa)'">
                        </div>

                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #2C3E50; margin-bottom: 12px; font-size: 1rem;">
                                <i class="fas fa-calendar-check" style="color: #B6D431; width: 18px;"></i>
                                Date de fin
                            </label>
                            <input type="date" name="date_max" value="{{ $dateMax ?? '' }}"
                                   style="width: 100%; border: 2px solid #e9ecef; border-radius: 12px; padding: 16px 20px; font-size: 1rem; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: linear-gradient(145deg, #ffffff, #f8f9fa); box-shadow: 0 4px 12px rgba(0,0,0,0.05);"
                                   onmouseover="this.style.borderColor='#D4E661'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.1)'"
                                   onmouseout="this.style.borderColor='#e9ecef'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'"
                                   onfocus="this.style.borderColor='#B6D431'; this.style.boxShadow='0 0 0 0.2rem rgba(182, 212, 49, 0.25), 0 8px 25px rgba(182, 212, 49, 0.15)'; this.style.transform='translateY(-2px)'; this.style.background='white'"
                                   onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'; this.style.background='linear-gradient(145deg, #ffffff, #f8f9fa)'">
                        </div>

                        <div>
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #2C3E50; margin-bottom: 12px; font-size: 1rem;">
                                <i class="fas fa-cogs" style="color: #B6D431; width: 18px;"></i>
                                Mode d'agrégation
                            </label>
                            <select name="mode"
                                    style="width: 100%; border: 2px solid #e9ecef; border-radius: 12px; padding: 16px 20px; font-size: 1rem; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: linear-gradient(145deg, #ffffff, #f8f9fa); box-shadow: 0 4px 12px rgba(0,0,0,0.05); appearance: none; background-image: linear-gradient(45deg, #B6D431 50%, transparent 50%), linear-gradient(135deg, transparent 50%, #B6D431 50%); background-position: right 20px center, right 15px center; background-size: 5px 5px, 5px 5px; background-repeat: no-repeat; padding-right: 45px;"
                                    onmouseover="this.style.borderColor='#D4E661'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.1)'"
                                    onmouseout="this.style.borderColor='#e9ecef'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'"
                                    onfocus="this.style.borderColor='#B6D431'; this.style.boxShadow='0 0 0 0.2rem rgba(182, 212, 49, 0.25), 0 8px 25px rgba(182, 212, 49, 0.15)'; this.style.transform='translateY(-2px)'; this.style.background='white'"
                                    onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'; this.style.transform='translateY(0)'">
                                @php $m = $mode ?? 'latest'; @endphp
                                <option value="latest" @selected($m==='latest')>Dernier du jour</option>
                                <option value="first"  @selected($m==='first')>Premier du jour</option>
                                <option value="sum"    @selected($m==='sum')>Somme du jour</option>
                                <option value="avg"    @selected($m==='avg')>Moyenne du jour</option>
                                <option value="max"    @selected($m==='max')>Max du jour</option>
                                <option value="all"    @selected($m==='all')>Toutes les lectures</option>
                            </select>
                        </div>

                        <div>
                            <label style="opacity: 0;">&nbsp;</label>
                            <button type="submit"
                                    style="background: linear-gradient(135deg, #B6D431 0%, #9BBE2A 100%); color: white; border: none; border-radius: 12px; padding: 16px 32px; font-weight: 600; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 6px 20px rgba(182, 212, 49, 0.3), 0 2px 10px rgba(0,0,0,0.1); font-size: 1rem; min-width: 200px; position: relative; overflow: hidden; width: 100%;"
                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 30px rgba(182, 212, 49, 0.4), 0 4px 20px rgba(0,0,0,0.15)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(182, 212, 49, 0.3), 0 2px 10px rgba(0,0,0,0.1)'"
                                    onmousedown="this.style.transform='translateY(-1px)'"
                                    onmouseup="this.style.transform='translateY(-3px)'">
                                <i class="fas fa-search"></i>
                                Appliquer les filtres
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Buttons -->
        <div style="margin-bottom: 30px; width: 100%;">
            <div style="display: flex; gap: 25px; justify-content: flex-end; flex-wrap: wrap; align-items: center;">
                <button type="button" onclick="window.location.href='{{ route('power_readings.export.excel', request()->all()) }}'"
                        style="display: flex; align-items: center; gap: 0; padding: 0; border: none; border-radius: 18px; font-weight: 600; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 30px rgba(0,0,0,0.15), 0 6px 15px rgba(0,0,0,0.1); overflow: hidden; position: relative; cursor: pointer; min-width: 220px; height: 75px; font-family: inherit; outline: none; background: linear-gradient(135deg, #1e7e34 0%, #28a745 50%, #20c997 100%); color: white;"
                        onmouseover="this.style.transform='translateY(-6px) scale(1.03)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.2), 0 10px 25px rgba(0,0,0,0.15)'; this.style.background='linear-gradient(135deg, #20c997 0%, #28a745 50%, #1e7e34 100%)'"
                        onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.15), 0 6px 15px rgba(0,0,0,0.1)'; this.style.background='linear-gradient(135deg, #1e7e34 0%, #28a745 50%, #20c997 100%)'"
                        onmousedown="this.style.transform='translateY(-3px) scale(1.02)'"
                        onmouseup="this.style.transform='translateY(-6px) scale(1.03)'">
                    <div style="background: rgba(255,255,255,0.2); padding: 0; display: flex; align-items: center; justify-content: center; width: 75px; height: 75px; border-radius: 18px 0 0 18px; backdrop-filter: blur(10px);">
                        <i class="fas fa-file-excel" style="font-size: 2rem; color: rgba(255,255,255,0.95); text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 0 20px; text-align: left;">
                        <span style="font-size: 1.2rem; font-weight: 700; margin-bottom: 3px; letter-spacing: 0.8px; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Excel</span>
                        <span style="font-size: 0.9rem; opacity: 0.9; font-weight: 400; color: rgba(255,255,255,0.9); text-shadow: 0 1px 2px rgba(0,0,0,0.2);">Télécharger en .xlsx</span>
                    </div>
                    <div style="padding: 0 20px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: all 0.4s ease;">
                        <i class="fas fa-download" style="font-size: 1.3rem; color: rgba(255,255,255,0.9); text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></i>
                    </div>
                </button>

                <button type="button" onclick="window.location.href='{{ route('power_readings.export.pdf', request()->all()) }}'"
                        style="display: flex; align-items: center; gap: 0; padding: 0; border: none; border-radius: 18px; font-weight: 600; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 30px rgba(0,0,0,0.15), 0 6px 15px rgba(0,0,0,0.1); overflow: hidden; position: relative; cursor: pointer; min-width: 220px; height: 75px; font-family: inherit; outline: none; background: linear-gradient(135deg, #dc3545 0%, #e74c3c 50%, #fd7e14 100%); color: white;"
                        onmouseover="this.style.transform='translateY(-6px) scale(1.03)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.2), 0 10px 25px rgba(0,0,0,0.15)'; this.style.background='linear-gradient(135deg, #fd7e14 0%, #e74c3c 50%, #dc3545 100%)'"
                        onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.15), 0 6px 15px rgba(0,0,0,0.1)'; this.style.background='linear-gradient(135deg, #dc3545 0%, #e74c3c 50%, #fd7e14 100%)'"
                        onmousedown="this.style.transform='translateY(-3px) scale(1.02)'"
                        onmouseup="this.style.transform='translateY(-6px) scale(1.03)'">
                    <div style="background: rgba(255,255,255,0.2); padding: 0; display: flex; align-items: center; justify-content: center; width: 75px; height: 75px; border-radius: 18px 0 0 18px; backdrop-filter: blur(10px);">
                        <i class="fas fa-file-pdf" style="font-size: 2rem; color: rgba(255,255,255,0.95); text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 0 20px; text-align: left;">
                        <span style="font-size: 1.2rem; font-weight: 700; margin-bottom: 3px; letter-spacing: 0.8px; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">PDF</span>
                        <span style="font-size: 0.9rem; opacity: 0.9; font-weight: 400; color: rgba(255,255,255,0.9); text-shadow: 0 1px 2px rgba(0,0,0,0.2);">Télécharger en .pdf</span>
                    </div>
                    <div style="padding: 0 20px; display: flex; align-items: center; justify-content: center; opacity: 0.8; transition: all 0.4s ease;">
                        <i class="fas fa-download" style="font-size: 1.3rem; color: rgba(255,255,255,0.9); text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></i>
                    </div>
                </button>
            </div>
        </div>

        <!-- Data Table Section -->
        <div style="background: white; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); overflow: hidden;">
            <div style="background: #2C3E50; color: white; padding: 20px 30px;">
                <h5 style="margin: 0; font-weight: 600;"><i class="fas fa-table"></i> Données de Consommation</h5>
            </div>
            <div style="padding: 0; overflow: auto; max-height: 600px; border: 1px solid #dee2e6;">
                <table id="consumptionTable" class="display table table-striped table-hover" style="width: 100%; min-width: 1200px; margin: 0;">
                    <thead>
                    <tr>
                        <th style="background: #2C3E50; color: white; font-weight: 600; text-align: center; padding: 15px 10px; border: none; position: sticky; top: 0; z-index: 11; min-width: 150px; left: 0;">
                            <i class="fas fa-calendar"></i>
                            {{ (isset($mode) && $mode==='all') ? 'Jour & Heure' : 'Jour' }}
                        </th>
                        @foreach($agences as $agence)
                            <th style="background: #B6D431; color: white; font-weight: 600; text-align: center; padding: 15px 10px; border: none; position: sticky; top: 0; z-index: 10; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px;" colspan="4">
                                <i class="fas fa-building"></i>
                                {{ $agence->nom_agence }}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        <th style="background: #2C3E50; position: sticky; left: 0; z-index: 11; top: 59px;"></th>
                        @foreach($agences as $agence)
                            <th style="background: #9BBE2A; color: white; font-weight: 600; text-align: center; padding: 12px 8px; font-size: 0.9rem; position: sticky; top: 59px; z-index: 10;"><i class="fas fa-bolt"></i> C1</th>
                            <th style="background: #9BBE2A; color: white; font-weight: 600; text-align: center; padding: 12px 8px; font-size: 0.9rem; position: sticky; top: 59px; z-index: 10;"><i class="fas fa-bolt"></i> C2</th>
                            <th style="background: #9BBE2A; color: white; font-weight: 600; text-align: center; padding: 12px 8px; font-size: 0.9rem; position: sticky; top: 59px; z-index: 10;"><i class="fas fa-bolt"></i> C3</th>
                            <th style="background: #9BBE2A; color: white; font-weight: 600; text-align: center; padding: 12px 8px; font-size: 0.9rem; position: sticky; top: 59px; z-index: 10;"><i class="fas fa-bolt"></i> C4</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach(($rows ?? []) as $row)
                        <tr>
                            <td style="background: #f8f9fa; font-weight: 600; position: sticky; left: 0; z-index: 5; border-right: 3px solid #B6D431; padding: 12px 15px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span style="color: #2C3E50; font-size: 1rem;">
                                            {{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}
                                        </span>
                                    @if(!empty($row['time']))
                                        <span style="color: #666; font-size: 0.85rem; font-weight: 400;">{{ $row['time'] }}</span>
                                    @endif
                                </div>
                            </td>
                            @foreach($agences as $agence)
                                @php $cell = $row['cells'][$agence->id] ?? null; @endphp
                                <td style="text-align: center; padding: 12px 8px; min-width: 80px;">
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 500; font-size: 0.9rem; min-width: 45px; {{ $cell['kw1'] ? 'background: linear-gradient(135deg, #D4E661 0%, #B6D431 100%); color: #2C3E50;' : 'background: #e9ecef; color: #6c757d;' }}">
                                            {{ $cell['kw1'] ?? '-' }}
                                        </span>
                                </td>
                                <td style="text-align: center; padding: 12px 8px; min-width: 80px;">
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 500; font-size: 0.9rem; min-width: 45px; {{ $cell['kw2'] ? 'background: linear-gradient(135deg, #D4E661 0%, #B6D431 100%); color: #2C3E50;' : 'background: #e9ecef; color: #6c757d;' }}">
                                            {{ $cell['kw2'] ?? '-' }}
                                        </span>
                                </td>
                                <td style="text-align: center; padding: 12px 8px; min-width: 80px;">
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 500; font-size: 0.9rem; min-width: 45px; {{ $cell['kw3'] ? 'background: linear-gradient(135deg, #D4E661 0%, #B6D431 100%); color: #2C3E50;' : 'background: #e9ecef; color: #6c757d;' }}">
                                            {{ $cell['kw3'] ?? '-' }}
                                        </span>
                                </td>
                                <td style="text-align: center; padding: 12px 8px; min-width: 80px;">
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 500; font-size: 0.9rem; min-width: 45px; {{ $cell['kw4'] ? 'background: linear-gradient(135deg, #D4E661 0%, #B6D431 100%); color: #2C3E50;' : 'background: #e9ecef; color: #6c757d;' }}">
                                            {{ $cell['kw4'] ?? '-' }}
                                        </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <style>
        /* DataTables overrides */
        #consumptionTable_wrapper .dataTables_length,
        #consumptionTable_wrapper .dataTables_filter {
            margin: 20px;
        }

        #consumptionTable_wrapper .dataTables_filter input,
        #consumptionTable_wrapper .dataTables_length select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            margin-left: 10px;
        }

        #consumptionTable_wrapper .dataTables_filter input:focus {
            border-color: #B6D431;
            box-shadow: 0 0 0 0.2rem rgba(182, 212, 49, 0.25);
            outline: none;
        }

        #consumptionTable_wrapper .dataTables_paginate .paginate_button {
            padding: 8px 16px;
            margin: 0 2px;
            border-radius: 8px;
            border: none;
            background: #f8f9fa;
            color: #2C3E50;
        }

        #consumptionTable_wrapper .dataTables_paginate .paginate_button:hover {
            background: #B6D431 !important;
            color: white !important;
        }

        #consumptionTable_wrapper .dataTables_paginate .paginate_button.current {
            background: #B6D431 !important;
            color: white !important;
        }

        #consumptionTable_wrapper .dataTables_info {
            margin: 20px;
            color: #2C3E50;
            font-weight: 500;
        }

        .dataTables_processing {
            background: rgba(255, 255, 255, 0.95) !important;
            border: none !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
            color: #2C3E50 !important;
            font-weight: 600 !important;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#consumptionTable').DataTable({
                // Core settings
                processing: true,
                serverSide: false,

                // Pagination
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10 entrées', '25 entrées', '50 entrées', '100 entrées', 'Toutes les entrées']
                ],

                // Scrolling - disabled to use container scrolling
                scrollX: false,
                scrollY: false,

                // Search
                searching: true,

                // Ordering
                ordering: true,
                order: [[0, 'desc']], // Sort by date descending

                // Language
                language: {
                    "processing": '<div style="display: flex; align-items: center; justify-content: center;"><div class="spinner-border text-primary" style="margin-right: 15px;" role="status"></div>Chargement des données...</div>',
                    "lengthMenu": "Afficher _MENU_ par page",
                    "zeroRecords": "Aucune donnée trouvée - essayez de modifier vos filtres",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Aucune entrée disponible",
                    "infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
                    "search": "Rechercher:",
                    "paginate": {
                        "first": "Premier",
                        "last": "Dernier",
                        "next": "Suivant",
                        "previous": "Précédent"
                    },
                    "emptyTable": "Aucune donnée disponible dans le tableau",
                    "loadingRecords": "Chargement en cours..."
                },

                // Column configuration
                columnDefs: [
                    {
                        targets: 0,
                        width: '150px'
                    },
                    {
                        targets: '_all',
                        className: 'text-center'
                    }
                ],

                // DOM layout
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',

                // Callbacks
                initComplete: function(settings, json) {
                    // Style the search input
                    $('#consumptionTable_filter input').attr('placeholder', 'Rechercher dans le tableau...');

                    console.log('✅ Table de consommation initialisée avec succès');
                }
            });
        });
    </script>
@endpush
