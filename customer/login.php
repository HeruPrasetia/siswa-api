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
$Domain = $_POST['Domain'];
$Domains  = $kon->query("SELECT * FROM `dbmdomains` WHERE `Domain` = '$Domain' AND `Status` = 1 AND `AppType` = 'Customer'")->fetch();
if (!is_bool($Domains)) {
    if ($Domains->ExpiredDate >= date("Y-m-d")) {
        $DB = $kon->query("SELECT * FROM `dbmdatabase` WHERE `Perusahaan` = '$Domains->Perusahaan'")->fetch();
        if (!is_bool($DB)) {
            $User = $kon->query("SELECT * FROM `$DB->Database`.`dbmcard` WHERE (`Email` = '$Email' OR `Telp` = '$Email')")->fetch();
            if ($User) {
                if ($User->Status == 3) {
                    print json_encode(["status" => "gagal", "pesan" => "Akun belum dikonfirmasi oleh admin"]);
                } else if ($User->Status == 0) {
                    print json_encode(["status" => "gagal", "pesan" => "Akun di nonaktifkan"]);
                } else {
                    if (password_verify($Pwd, $User->Pwd)) {
                        $Token = md5(date("Y-m-d H:i:s") . $Domains->Perusahaan . $User->ID);
                        $kon->query("INSERT INTO `master2`.`dbslogin` (`Waktu`, `Token`, `UserID`, `UserName`, `PerusahaanID`, `Database`, `Lokasi`, `IP`, `LokasiLogin`, `LastConnect`, `Status`)
                                 VALUES (NOW(), '$Token', '$User->ID', '$User->Nama', '$Domains->Perusahaan', '$DB->Database', '0', null, null, NOW(), '1');");
                        print json_encode(["status" => "sukses", "pesan" => "Login Berhasil", "token" => $Token]);
                    } else {
                        print json_encode(["status" => "gagal", "pesan" => "Password Salah, silahkan Coba Lagi"]);
                    }
                }
            } else {
                print json_encode(["status" => "gagal", "pesan" => "User tidak ditemukan"]);
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
