<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Customer</title>
</head>
<body>

    <h1>Daftar Customer</h1>

    <a href="{{ route('customers.create') }}">
        Tambah Customer
    </a>

    <br><br>

    @if(session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    @if($customers->count() > 0)

        <table border="1" cellpadding="10">

            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>No HP</th>
                <th>Alamat</th>
                <th>Paket</th>
            </tr>

            @foreach($customers as $customer)

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->package }}</td>
                </tr>

            @endforeach

        </table>

    @else

        <p>Belum ada customer.</p>

    @endif

</body>
</html>
