<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
try {
    $kon = new PDO("mysql:host=localhost;dbname=master2", "naylatools", "N@yl4naylatools");
    $kon->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $kon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $kon->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    print json_encode(["status" => "gagal", "pesan" => "Koneksi Bermasalah: " . $e->getMessage()]);
    die();
}

$Nama         = addslashes($_POST['Nama']);
$Email        = addslashes($_POST['Email']);
$Telp         = addslashes($_POST['Telp']);
$Alamat       = addslashes($_POST['Alamat']);
$ProvinsiID   = addslashes($_POST['ProvinsiID']);
$KotaID       = addslashes($_POST['KotaID']);
$KecamatanID  = addslashes($_POST['KecamatanID']);
$Password     = $_POST['Password'];
$RePassword   = $_POST['RePassword'];
if ($Password == $RePassword) {
    $Domain     = $_POST['Domain'];
    $Domains    = $kon->query("SELECT * FROM `dbmdomains` WHERE `Domain` = '$Domain' AND `Status` = 1 AND `AppType` = 'Customer'")->fetch();
    if (!is_bool($Domains)) {
        if ($Domains->ExpiredDate >= date("Y-m-d")) {
            $DB = $kon->query("SELECT * FROM `dbmdatabase` WHERE `Perusahaan` = '$Domains->Perusahaan'")->fetch();
            if (!is_bool($DB)) {
                $User = $kon->query("SELECT * FROM `$DB->Database`.`dbmcard` WHERE (`Email` = '$Email' OR `Telp` = '$Telp')")->fetch();
                if (!$User) {
                    $option = ['cost' => 5];
                    $pwd    = password_hash("$Password", PASSWORD_DEFAULT, $option);
                    $kon->query("INSERT INTO `$DB->Database`.`dbmcard` (`Jenis`, `Nama`, `Telp`, `Email`, `Alamat`, `Provinsi`, `NamaProvinsi`, `Kota`, `NamaKota`, `Kec`, `NamaKec`, `Pwd`, `Lokasi`, `TimeCreated`, `Status`)
                                 VALUES ('pelanggan', '$Nama', '$Telp', '$Email', '$Alamat', '$ProvinsiID', 'Jawa Timur', '$KotaID', 'Sidoarjo', '$KecamatanID', 'Sukodono', '$pwd', '0', NOW(), '3');");
                    print json_encode(["status" => "sukses", "pesan" => "Pendaftaran berhasil"]);
                } else {
                    print json_encode(["status" => "gagal", "pesan" => "Email atau Telp Sudah digunakan"]);
                }
            } else {
                print json_encode(["status" => "gagal", "pesan" => "Database tidak ditemukan"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Domain sudah expired"]);
        }
    } else {
        print json_encode(["status" => "gagal", "pesan" => "Domain $Domain tidak terdaftar"]);
    }
} else {
    print json_encode(["status" => "gagal", "pesan" => "Password tidak sama"]);
}
