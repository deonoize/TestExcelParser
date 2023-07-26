<div>
    @foreach($rows as $row)
        <p>{{ $row['id'] }}: {{ $row['name'] }} {{ $row['date'] }}</p>
    @endforeach
</div>
