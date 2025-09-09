@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Suivi Électrique — Power Readings</h1>

        <div class="mb-3">
            <a href="{{ route('power_readings.export.excel') }}" class="btn btn-success">📊 Export Excel</a>
            <a href="{{ route('power_readings.export.pdf') }}" class="btn btn-danger">📄 Export PDF</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Agence</th>
                    <th>Compteur 1</th>
                    <th>Compteur 2</th>
                    <th>Compteur 3</th>
                    <th>Compteur 4</th>
                    <th>Batteries chargées</th>
                    <th>Batteries low</th>
                </tr>
                </thead>
                <tbody>
                @foreach($readings as $r)
                    <tr>
                        <td>{{ $r->created_at }}</td>
                        <td>{{ $r->agence->nom_agence ?? 'Agence #'.$r->agence_id }}</td>
                        <td>{{ $r->kw1 }}</td>
                        <td>{{ $r->kw2 }}</td>
                        <td>{{ $r->kw3 }}</td>
                        <td>{{ $r->kw4 }}</td>
                        <td>{{ $r->charged_batteries }}</td>
                        <td>{{ $r->low_batteries }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $readings->links() }}
    </div>
@endsection
