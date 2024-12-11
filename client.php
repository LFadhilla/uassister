<?php
$serverUrl = "http://192.168.218.91:8081/www/toko-roti/server/server.php";

// Fungsi untuk mengambil data (GET request)
function getData($table) {
    global $serverUrl;
    $url = $serverUrl . "?table=" . $table;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Mendapatkan tabel yang dipilih
$table = isset($_GET['table']) ? $_GET['table'] : 'produk';
$data = getData($table);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Roti</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #fff4e6;
            color: #5d4037;
        }
        h1 {
            color: #d84315;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
        }
        th {
            background-color: #ffcc80;
            color: #5d4037;
        }
        .action-buttons button {
            margin-right: 5px;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .edit-button {
            background-color: #8bc34a;
            color: white;
        }
        .edit-button:hover {
            background-color: #689f38;
        }
        .delete-button {
            background-color: #f44336;
            color: white;
        }
        .delete-button:hover {
            background-color: #d32f2f;
        }
        .form-container {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            color: #d84315;
        }
        .form-container input, .form-container button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container button {
            background-color: #d84315;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #bf360c;
        }
    </style>
</head>
<body>
    <h1>Toko Roti</h1>

    <!-- Dropdown untuk memilih tabel -->
    <form method="GET" action="">
        <label for="table">Pilih Tabel:</label>
        <select name="table" id="table" onchange="this.form.submit()">
            <option value="produk" <?php echo $table === 'produk' ? 'selected' : ''; ?>>Produk</option>
            <option value="pelanggan" <?php echo $table === 'pelanggan' ? 'selected' : ''; ?>>Pelanggan</option>
            <option value="pesanan" <?php echo $table === 'pesanan' ? 'selected' : ''; ?>>Pesanan</option>
            <option value="detail_pesanan" <?php echo $table === 'detail_pesanan' ? 'selected' : ''; ?>>Detail Pesanan</option>
        </select>
    </form>

    <!-- Tabel Data -->
    <h2>Data <?php echo ucfirst($table); ?></h2>
    <table>
        <thead>
            <tr>
                <?php if ($data && isset($data[0])): ?>
                    <?php foreach (array_keys($data[0]) as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                    <th>Aksi</th>
                <?php else: ?>
                    <th>Data Tidak Tersedia</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($data): ?>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $key => $value): ?>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                        <td class="action-buttons">
                            <button class="edit-button" onclick="openEditForm(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                            <button class="delete-button" onclick="deleteData('<?php echo $table; ?>', '<?php echo $row['id']; ?>')">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="100%">Tidak ada data untuk tabel ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Form Tambah Data -->
    <div class="form-container" id="addForm">
        <h2>Tambah Data</h2>
        <form method="POST" onsubmit="return handleAddData(event)">
            <input type="hidden" name="table" value="<?php echo $table; ?>">
            <?php if ($data && isset($data[0])): ?>
                <?php foreach (array_keys($data[0]) as $column): ?>
                    <?php if ($column !== 'id'): ?> <!-- Jangan tampilkan kolom ID -->
                        <label for="<?php echo $column; ?>"><?php echo ucfirst($column); ?>:</label>
                        <input type="text" id="add<?php echo $column; ?>" name="<?php echo $column; ?>" required>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <button type="submit">Tambah</button>
        </form>
    </div>

    <!-- Form Edit Data -->
    <div class="form-container" id="editForm" style="display:none;">
        <h2>Edit Data</h2>
        <form method="POST" onsubmit="return handleEditData(event)">
            <input type="hidden" name="table" value="<?php echo $table; ?>">
            <input type="hidden" id="editId" name="id">
            <?php if ($data && isset($data[0])): ?>
                <?php foreach (array_keys($data[0]) as $column): ?>
                    <?php if ($column !== 'id'): ?> <!-- Jangan tampilkan kolom ID -->
                        <label for="edit<?php echo $column; ?>"><?php echo ucfirst($column); ?>:</label>
                        <input type="text" id="edit<?php echo $column; ?>" name="<?php echo $column; ?>" required>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <button type="submit">Simpan</button>
            <button type="button" onclick="closeEditForm()">Batal</button>
        </form>
    </div>

    <script>
        function openEditForm(row) {
            const form = document.getElementById('editForm');
            form.style.display = 'block';
            document.getElementById('editId').value = row.id;
            Object.keys(row).forEach(key => {
                const input = document.getElementById('edit' + key);
                if (input) input.value = row[key];
            });
        }

        function closeEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }

        function deleteData(table, id) {
            if (confirm('Yakin ingin menghapus data ini?')) {
                fetch('<?php echo $serverUrl; ?>', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ table: table, id: id })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function handleAddData(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            fetch('<?php echo $serverUrl; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => console.error('Error:', error));
        }

        function handleEditData(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            fetch('<?php echo $serverUrl; ?>', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>