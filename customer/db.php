<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
try {
    $kon = new PDO("mysql:host=localhost;dbname=master2", "naylatools", "N@yl4naylatools");
    $kon->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $kon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $kon->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    $SESITOKEN = isset($_POST['Token']) ? $_POST['Token'] : $_COOKIE['Token'];
    $cekToken = $kon->query("SELECT * FROM `dbslogin` WHERE `Token` = '$SESITOKEN' AND `Status` = 1");
    if ($cekToken->rowCount() > 0) {
        $SQL       = $cekToken->fetch();
        $__userid  = $SQL->UserID;
        $__usahaid = $SQL->PerusahaanID;
        try {
            $koneksi = new PDO("mysql:host=localhost;dbname=$SQL->Database", "naylatools", "N@yl4naylatools");
            $koneksi->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Koneksi Bermasalah: " . $e->getMessage()]);
            die();
        }
    } else {
        print json_encode(["status" => "gagal", "pesan" => "Token tidak di temukan"]);
    }
} catch (PDOException $e) {
    print json_encode(["status" => "gagal", "pesan" => "System Bermasalah: " . $e->getMessage()]);
    die();
}
