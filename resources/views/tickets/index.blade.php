<h1>Daftar Customer</h1>

<a href="/customers/create">Tambah Customer</a>

<hr>

@foreach($customers as $customer)

<p>
{{ $customer->name }}
-
{{ $customer->phone }}
</p>

@endforeach
