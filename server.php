<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Origin: *");
include_once 'Database.php';

$database = new Database();
$db = $database->getConnection();

$valid_tables = ['detail_pesanan', 'karyawan', 'pelanggan', 'pesanan', 'produk', 'user'];


function getData($db, $table) {
    global $valid_tables;
    if (!in_array($table, $valid_tables)) {
        return json_encode(['message' => 'Invalid table specified']);
    }
    $query = "SELECT * FROM " . $table;
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($data);
}

function insertData($db, $table, $data) {
    global $valid_tables;

    if (!in_array($table, $valid_tables)) {
        error_log("Invalid table specified: $table");
        return json_encode(['message' => 'Invalid table specified']);
    }

    if (empty($data) || !is_array($data)) {
        error_log("Invalid data provided: " . print_r($data, true));
        return json_encode(['message' => 'Invalid data provided']);
    }

    try {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        error_log("Query: $query");
        error_log("Data: " . print_r($data, true));

        $stmt = $db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            return json_encode(['message' => 'Data successfully added']);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("PDO Error: " . $errorInfo[2]);
            return json_encode(['message' => 'Failed to add data', 'error' => $errorInfo[2]]);
        }
    } catch (PDOException $e) {
        error_log("PDO Exception: " . $e->getMessage());
        return json_encode(['message' => 'Database error', 'error' => $e->getMessage()]);
    }
}

function updateData($db, $table, $id, $data) {
    global $valid_tables;
    if (!in_array($table, $valid_tables)) {
        return json_encode(['message' => 'Invalid table specified']);
    }

    $setClause = "";
    foreach ($data as $key => $value) {
        $setClause .= $key . " = :" . $key . ", ";
    }
    $setClause = rtrim($setClause, ", ");

    $query = "UPDATE " . $table . " SET " . $setClause . " WHERE id = :id";
    $stmt = $db->prepare($query);

    foreach ($data as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':id', $id);

    if ($stmt->execute()) {
        return json_encode(['message' => 'Data successfully updated']);
    } else {
        return json_encode(['message' => 'Failed to update data']);
    }
}

function deleteData($db, $table, $id) {
    global $valid_tables;
    if (!in_array($table, $valid_tables)) {
        return json_encode(['message' => 'Invalid table specified']);
    }

    $id_columns = [
        'detail_pesanan' => 'id_detail',
        'karyawan' => 'id_karyawan',
        'pelanggan' => 'id_pelanggan',
        'pesanan' => 'id_pesanan',
        'produk' => 'id_produk',
        'user' => 'id_user'
    ];

    $id_column = isset($id_columns[$table]) ? $id_columns[$table] : 'id';

    try {
        $query = "DELETE FROM $table WHERE $id_column = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return json_encode(['message' => 'Data successfully deleted']);
        } else {
            $errorInfo = $stmt->errorInfo();
            return json_encode(['message' => 'Failed to delete data', 'error' => $errorInfo[2]]);
        }
    } catch (PDOException $e) {
        return json_encode(['message' => 'Database error', 'error' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['table'])) {
        echo getData($db, $_GET['table']);
    } else {
        echo json_encode(['message' => 'No table specified']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['table']) && in_array($inputData['table'], $valid_tables)) {
        $table = $inputData['table'];
        $data = [];
        
        switch ($table) {
            case 'detail_pesanan':
                if (isset($inputData['id_pesanan'], $inputData['id_produk'], $inputData['jumlah'], $inputData['harga_per_item'], $inputData['subtotal'])) {
                    $data = [
                        'id_pesanan' => $inputData['id_pesanan'],
                        'id_produk' => $inputData['id_produk'],
                        'jumlah' => $inputData['jumlah'],
                        'harga_per_item' => $inputData['harga_per_item'],
                        'subtotal' => $inputData['subtotal']
                    ];
                }
                break;
            case 'karyawan':
                if (isset($inputData['nama_karyawan'], $inputData['posisi'], $inputData['alamat'], $inputData['telepon'], $inputData['gaji'])) {
                    $data = [
                        'nama_karyawan' => $inputData['nama_karyawan'],
                        'posisi' => $inputData['posisi'],
                        'alamat' => $inputData['alamat'],
                        'telepon' => $inputData['telepon'],
                        'gaji' => $inputData['gaji']
                    ];
                }
                break;
            case 'pelanggan':
                if (isset($inputData['nama_pelanggan'], $inputData['alamat'], $inputData['telepon'], $inputData['email'], $inputData['tanggal_daftar'])) {
                    $data = [
                        'nama_pelanggan' => $inputData['nama_pelanggan'],
                        'alamat' => $inputData['alamat'],
                        'telepon' => $inputData['telepon'],
                        'email' => $inputData['email'],
                        'tanggal_daftar' => $inputData['tanggal_daftar']
                    ];
                }
                break;
            case 'pesanan':
                if (isset($inputData['id_pelanggan'], $inputData['tanggal_pesanan'], $inputData['status_pesanan'], $inputData['total_harga'])) {
                    $data = [
                        'id_pelanggan' => $inputData['id_pelanggan'],
                        'tanggal_pesanan' => $inputData['tanggal_pesanan'],
                        'status_pesanan' => $inputData['status_pesanan'],
                        'total_harga' => $inputData['total_harga']
                    ];
                }
                break;
                case 'produk':
                    if (isset($inputData['nama_produk'], $inputData['harga'], $inputData['stok'], $inputData['deskripsi'])) {
                        $data = [
                            'nama_produk' => $inputData['nama_produk'],
                            'harga' => $inputData['harga'],
                            'stok' => $inputData['stok'],
                            'deskripsi' => $inputData['deskripsi']
                        ];
                    } else {
                        error_log("Missing required data for 'produk': " . print_r($inputData, true));
                        echo json_encode(['message' => 'Missing required data for produk']);
                        exit;
                    }
                    break;
            case 'user':
                if (isset($inputData['username'], $inputData['password'], $inputData['role'], $inputData['id_karyawan'])) {
                    $data = [
                        'username' => $inputData['username'],
                        'password' => $inputData['password'],
                        'role' => $inputData['role'],
                        'id_karyawan' => $inputData['id_karyawan']
                    ];
                }
                break;
            default:
                echo json_encode(['message' => 'Invalid table']);
                break;
        }
        echo insertData($db, $table, $data);
    } else {
        echo json_encode(['message' => 'Invalid table or missing data']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    if (isset($inputData['table'], $inputData['id'])) {
        $table = $inputData['table'];
        $id = $inputData['id'];

        if (!in_array($table, $valid_tables)) {
            echo json_encode(['message' => 'Invalid table specified']);
            exit;
        }

        $id_column = '';
        switch ($table) {
            case 'detail_pesanan':
                $id_column = 'id_detail';
                break;
            case 'karyawan':
                $id_column = 'id_karyawan';
                break;
            case 'pelanggan':
                $id_column = 'id_pelanggan';
                break;
            case 'pesanan':
                $id_column = 'id_pesanan';
                break;
            case 'produk':
                $id_column = 'id_produk';
                break;
            case 'user':
                $id_column = 'id_user';
                break;
        }

        $data = $inputData;
        unset($data['table'], $data['id']); 

        $setClause = '';
        foreach ($data as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ", ");
        
        $query = "UPDATE $table SET $setClause WHERE $id_column = :id";
        $stmt = $db->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Data successfully updated']);
        } else {
            echo json_encode(['message' => 'Failed to update data']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    if (isset($inputData['table'], $inputData['id'])) {
        $table = $inputData['table'];
        $id = $inputData['id'];
        echo deleteData($db, $table, $id);
    } else {
        echo json_encode(['message' => 'Invalid table or id']);
    }
}
?>
