<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
try {
    $kon = new PDO("mysql:host=localhost;dbname=dbapis", "naylatools", "N@yl4naylatools");
    $kon->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $kon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $kon->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    print json_encode(["status" => "gagal", "pesan" => "Koneksi Bermasalah: " . $e->getMessage()]);
    die();
}

$Email = addslashes($_POST['Email']);
$Pwd   = $_POST['Password'];

$User = $kon->query("SELECT * FROM `dbmuser` WHERE `Email` = '$Email'")->fetch();
if (!is_bool($User)) {
    if ($User->Status == 1) {
        if (password_verify($Pwd, $User->Pwd)) {
            $service = $kon->query("SELECT * FROM `dbsakses` WHERE `UserID` = '$User->ID' LIMIT 1")->fetch();
            if (!is_bool($service)) {
                $token = md5(date("Y-m-d H:i:s") . $User->ID);
                $kon->query("INSERT INTO `dbapis`.`dbmtoken` (`Token`, `ServiceID`, `UserID`, `RegisterDate`, `ExpiredDate`, `Status`)
                             VALUES ('$token', '$service->ServiceID', '$User->ID', NOW(), NOW(), '1');");
                print json_encode(["status" => "sukses", "pesan" => "Login Berhasil", "token" => $token]);
            } else {
                print json_encode(["status" => "gagal", "pesan" => "Maaf anda tidak memiliki akses ke menu admin"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Password Salah, silahkan Coba Lagi"]);
        }
    } else {
        print json_encode(["status" => "gagal", "pesan" => "User tidak aktif"]);
    }
} else {
    print json_encode(["status" => "gagal", "pesan" => "User Tidak ditemukan"]);
}
