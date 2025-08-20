@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<style>
    .main-content { padding: 16px; }
    .notif-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:16px; }
    .notif-title { font-size:22px; font-weight:700; color:#333; }
    .chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:9999px; border:1px solid #e5e7eb; background:#fff; text-decoration:none; color:#111827; font-size:13px; }
    .chip:hover { background:#f9fafb; }
    .chip-primary { border-color:#c7d2fe; background:#eef2ff; }

    .filter-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:12px; }
    .filter-row { display:flex; flex-wrap:wrap; gap:10px; align-items:end; }
    .filter-row .group { display:flex; flex-direction:column; gap:6px; }
    .filter-row label { font-size:12px; color:#6b7280; }
    .filter-row select, .filter-row input[type="date"] {
        padding:8px 10px; border:1px solid #d1d5db; border-radius:8px; min-width:180px;
    }
    .btn { padding:9px 14px; border-radius:10px; border:1px solid #d1d5db; background:#fff; cursor:pointer; }
    .btn-primary { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .btn-light { background:#f9fafb; }

    .notif-grid { display:grid; grid-template-columns:repeat(1,minmax(0,1fr)); gap:16px; margin-top:16px; }
    @media (min-width: 768px) { .notif-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (min-width:1200px) { .notif-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }

    .notif-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 4px 12px rgba(0,0,0,.05); padding:16px; display:flex; gap:12px; position:relative; }
    .notif-icon { width:44px; height:44px; border-radius:12px; background:#f2f6ff; display:flex; align-items:center; justify-content:center; font-size:20px; flex:0 0 auto; }
    .notif-body { flex:1 1 auto; }
    .notif-item-title { margin:0 0 6px; font-weight:700; color:#111827; font-size:15px; }
    .notif-desc { margin:0; color:#4b5563; line-height:1.5; white-space:pre-line; }

    /* Date/heure très visible */
    .notif-time {
        position:absolute; top:12px; right:12px;
        font-weight:700; font-size:12px; color:#1f2937;
        background:#e0e7ff; border:1px solid #c7d2fe;
        padding:6px 10px; border-radius:9999px;
    }
    .pagination-wrap { margin-top:20px; }
</style>

<div class="main-content">
    <div class="notif-toolbar">
        <h1 class="notif-title">Mes Notifications</h1>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a class="chip chip-primary" href="{{ route('notifications.today') }}">🔔 Aujourd'hui</a>
            <a class="chip" href="{{ route('notifications.index', ['scope'=>'week']) }}">Semaine</a>
            <a class="chip" href="{{ route('notifications.index', ['scope'=>'month']) }}">Mois</a>
            <a class="chip" href="{{ route('notifications.index', ['scope'=>'year']) }}">Année</a>
            <a class="chip" href="{{ route('notifications.index') }}">Réinitialiser</a>
        </div>
    </div>

    <div class="filter-card" id="filter-card">
        <form method="GET" action="{{ route('notifications.index') }}">
            <div class="filter-row">
                <div class="group">
                    <label for="scope">Période</label>
                    <select id="scope" name="scope">
                        <option value="all"   {{ $scope==='all'?'selected':'' }}>Toutes</option>
                        <option value="today" {{ $scope==='today'?'selected':'' }}>Aujourd'hui</option>
                        <option value="week"  {{ $scope==='week'?'selected':'' }}>Cette semaine</option>
                        <option value="month" {{ $scope==='month'?'selected':'' }}>Ce mois</option>
                        <option value="year"  {{ $scope==='year'?'selected':'' }}>Cette année</option>
                        <option value="date"  {{ $scope==='date'?'selected':'' }}>À une date</option>
                        <option value="range" {{ $scope==='range'?'selected':'' }}>Entre deux dates</option>
                    </select>
                </div>

                <div class="group" id="one-date" style="display:none;">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="{{ $date }}">
                </div>

                <div class="group" id="range-start" style="display:none;">
                    <label for="start">Début</label>
                    <input type="date" id="start" name="start" value="{{ $start }}">
                </div>

                <div class="group" id="range-end" style="display:none;">
                    <label for="end">Fin</label>
                    <input type="date" id="end" name="end" value="{{ $end }}">
                </div>

                <div class="group">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
                <div class="group">
                    <a href="{{ route('notifications.index') }}" class="btn btn-light">Réinitialiser</a>
                </div>
            </div>
        </form>
        <div style="margin-top:10px; color:#6b7280; font-size:13px;">
            Filtre actif : <strong>{{ $filterLabel }}</strong>
        </div>
    </div>

    @if($notifications->count() === 0)
        <div class="notif-card" style="align-items:center; margin-top:16px;">
            <div class="notif-icon">🔔</div>
            <div class="notif-body">
                <h3 class="notif-item-title">Aucune notification</h3>
                <p class="notif-desc">Vous êtes à jour. Essayez de changer la période ci-dessus.</p>
            </div>
        </div>
    @else
        <div class="notif-grid">
            @foreach ($notifications as $n)
                @php
                    $local = \Carbon\Carbon::parse($n->created_at, 'UTC')->setTimezone($appTz);
                @endphp
                <div class="notif-card">
                    <div class="notif-time">{{ $local->format('d/m/Y H:i') }}</div>
                    <div class="notif-icon">🔔</div>
                    <div class="notif-body">
                        <h3 class="notif-item-title">{{ $n->title }}</h3>
                        <p class="notif-desc">{{ $n->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrap">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<script>
    (function () {
        const scope = document.getElementById('scope');
        const oneDate = document.getElementById('one-date');
        const rangeStart = document.getElementById('range-start');
        const rangeEnd = document.getElementById('range-end');

        function toggleDateFields() {
            const v = scope.value;
            oneDate.style.display   = (v === 'date')  ? 'flex' : 'none';
            rangeStart.style.display = (v === 'range') ? 'flex' : 'none';
            rangeEnd.style.display   = (v === 'range') ? 'flex' : 'none';
        }
        scope.addEventListener('change', toggleDateFields);
        toggleDateFields(); // init
    })();
</script>
@endsection
