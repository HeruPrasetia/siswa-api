<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT * FROM `gijutsu_admin2`.`dbmreqfitur`")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'detail':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `gijutsu_admin2`.`dbmreqfiturdetail` WHERE `DocID` = '$ID'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data pelanggan':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT * FROM `dbmperusahaan` WHERE `Status` = 1 AND (`Nama` LIKE '%$q%' OR Email LIKE '%$q%')")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
