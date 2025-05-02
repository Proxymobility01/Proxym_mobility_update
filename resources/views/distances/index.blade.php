@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Daily Distances for {{ $date }}</h2>

        <form method="GET" class="mb-3">
            <label for="date">Select date:</label>
            <input type="date" name="date" id="date" value="{{ $date }}">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>

        @if($distances->isEmpty())
            <p>No data available for this date.</p>
        @else
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Phone</th>
                    <th>Distance (KM)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($distances as $distance)
                    <tr>
                        <td>{{ $distance->user->prenom }} {{ $distance->user->nom }}</td>
                        <td>{{ $distance->user->phone }}</td>
                        <td>{{ number_format($distance->total_distance_km, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
