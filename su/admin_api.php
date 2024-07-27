<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT *, IF(`admin_status` = 1, 'Aktif', 'Tidak Aktif') AS `SSTS` FROM `gijutsu_admin2`.`dbm_admin` 
                                 WHERE `admin_firstname` LIKE '%$q%' 
                                 ORDER BY `admin_firstname` ASC")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data akses':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT a.ID, a.Nama, a.Path, 
                                IF(b.Akses IS NULL, 0, 1) AS Akses,
                                COALESCE(b.Tambah, 0) AS Tambah,
                                COALESCE(b.Edit, 0) AS Edit,
                                COALESCE(b.Hapus, 0) AS Hapus 
                                FROM master2.dbsmenu a
                                LEFT JOIN gijutsu_admin2.dbsakses b ON a.Path = b.`Akses` AND b.AdminID = '$ID'
                                WHERE a.App = 'admin' AND a.Status = 1")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
