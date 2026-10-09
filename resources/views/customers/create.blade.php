<!DOCTYPE html>
<html>
<head>
    <title>Tambah Customer</title>
</head>
<body>

<h1>Tambah Customer</h1>

<form action="{{ route('customers.store') }}" method="POST">

    @csrf

    <p>Nama</p>
    <input type="text" name="name">

    <br><br>

    <p>No HP</p>
    <input type="text" name="phone">

    <br><br>

    <p>Alamat</p>
    <textarea name="address"></textarea>

    <br><br>

    <p>Paket Internet</p>
    <input type="text" name="package">

    <br><br>

    <button type="submit">
        Simpan Customer
    </button>

</form>

</body>
</html>
