<table>
    <thead>
    <tr>
        <th colspan="{{ 1 + (count($agences) * 4) }}" style="font-weight:bold;font-size:14px;">
            {{ $sheetLabel ?? 'Montant Recharge' }} — ΔkWh (Fin − Matin)
            ({{ \Illuminate\Support\Carbon::parse($dateMin ?? now())->format('d/m/Y') }}
            → {{ \Illuminate\Support\Carbon::parse($dateMax ?? now())->format('d/m/Y') }})
        </th>
    </tr>
    <tr>
        <th rowspan="2">Jour</th>
        @foreach($agences as $a)
            <th colspan="4">{{ $a->nom_agence }}</th>
        @endforeach
    </tr>
    <tr>
        @foreach($agences as $a)
            <th>Compteur 1</th>
            <th>Compteur 2</th>
            <th>Compteur 3</th>
            <th>Compteur 4</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach(($rows ?? []) as $r)
        <tr>
            <td>{{ \Illuminate\Support\Carbon::parse($r['date'])->format('d/m/Y') }}</td>
            @foreach($agences as $a)
                @php $c = $r['cells'][$a->id] ?? null; @endphp
                <td>{{ number_format(($c['kw1'] ?? 0), 3, '.', '') }}</td>
                <td>{{ number_format(($c['kw2'] ?? 0), 3, '.', '') }}</td>
                <td>{{ number_format(($c['kw3'] ?? 0), 3, '.', '') }}</td>
                <td>{{ number_format(($c['kw4'] ?? 0), 3, '.', '') }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
