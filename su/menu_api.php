<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT *, IF(Status = 1, 'Aktif', 'Tidak Aktif') AS `SSTS`, IF(`IsGroup` = 1, 'Group', 'Menu') AS `Jenis` FROM `dbsmenu`")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'detail':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbsmenu` WHERE `ID` = '$ID'")->fetch();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}