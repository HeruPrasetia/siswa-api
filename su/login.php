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

$Email = addslashes($_POST['Email']);
$Pwd   = $_POST['Password'];
$User = $kon->query("SELECT * FROM `gijutsu_admin2`.`dbm_admin` WHERE `admin_email` = '$Email'  AND `admin_status` = 1")->fetch();
if ($User) {
    if (password_verify($Pwd, $User->admin_password)) {
        $Token = md5(date("Y-m-d H:i:s") . 'admin' . $User->ID);
        $kon->query("INSERT INTO `master2`.`dbslogin` (`Waktu`, `Token`, `UserID`, `UserName`, `PerusahaanID`, `Database`, `Lokasi`, `IP`, `LokasiLogin`, `LastConnect`, `Status`)
                 VALUES (NOW(), '$Token', '$User->ID', '$User->admin_firstname', '0', 'master2', '0', null, null, NOW(), '1');");
        print json_encode(["status" => "sukses", "pesan" => "Login Berhasil", "token" => $Token]);
    } else {
        print json_encode(["status" => "gagal", "pesan" => "Password Salah, silahkan Coba Lagi"]);
    }
} else {
    print json_encode(["status" => "gagal", "pesan" => "User tidak ditemukan"]);
}
