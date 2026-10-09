<h1>Tambah Customer</h1>

<form action="/customers" method="POST">

    @csrf

    <input type="text" name="name" placeholder="Nama Customer">

    <br><br>

    <input type="text" name="phone" placeholder="Nomor HP">

    <br><br>

    <textarea name="address" placeholder="Alamat"></textarea>

    <br><br>

    <input type="text" name="package" placeholder="Paket Internet">

    <br><br>

    <button type="submit">Simpan</button>

</form>
