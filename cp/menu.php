<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'menu':
        $data = $koneksi->query("SELECT * FROM `dbapis`.`dbsakses` WHERE `UserID` = '$__userid' AND `ServiceID` = '$__serviceid'")->fetch();
        if (!is_bool($data)) {
            print json_encode(['status' => "sukses", "data" => $data]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Anda tidak memiliki akses"]);
        }
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
