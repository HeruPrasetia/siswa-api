<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
try {
    $koneksi = new PDO("mysql:host=localhost;dbname=master2", "naylatools", "N@yl4naylatools");
    $koneksi->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    print json_encode(["status" => "gagal", "pesan" => "Koneksi Bermasalah: " . $e->getMessage()]);
    die();
}

$act = addslashes($_POST['act']);

switch ($act) {
    case 'data provinsi':
        $data = $koneksi->query("SELECT * FROM `dbmprovinsi` WHERE `Status` = 1")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data kota':
        $ProvinsiID = $_POST['ID'];
        $data = $koneksi->query("SELECT * FROM `dbmkota` WHERE `Status` = 1 AND `ProvinsiID` = '$ProvinsiID'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data kecamatan':
        $KotaID = $_POST['ID'];
        $data = $koneksi->query("SELECT * FROM `dbmkecamatan` WHERE `Status` = 1 AND `KotaID` = '$KotaID'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;
}
