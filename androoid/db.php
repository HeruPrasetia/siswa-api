<?php
$SESITOKEN = isset($_POST['Token']) ? $_POST['Token'] : $_COOKIE['token'];
$cekToken = $kon->query("SELECT * FROM `Token` WHERE `Token` = '$SESITOKEN' AND `Status` = 1 AND `Apis` = 'androoid'");
if ($cekToken->rowCount() > 0) {
    $SQL        = $cekToken->fetch();
    $__expired  = $SQL->TimeExpired;
    try {
        $koneksi = new PDO("mysql:host=localhost;dbname=android", "naylatools", "N@yl4naylatools");
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
