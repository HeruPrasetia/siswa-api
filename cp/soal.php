<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data bank soal':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $j    = $koneksi->query("SELECT `ID` FROM `dbmbanksoal` WHERE `Judul` LIKE '%$q%' ")->rowCount();
        $data = $koneksi->query("SELECT a.*, b.`Nama` AS `MataPelajaran`, CONCAT(c.`NamaDepan`, ' ', c.`NamaBelakang`) AS `Guru` 
                                 FROM `dbmbanksoal` a
                                 LEFT JOIN `dbmpelajaran` b ON a.`MapelID` = b.`ID`
                                 LEFT JOIN `dbmguru` c ON a.`GuruID` = b.`ID` 
                                 WHERE a.`Judul` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();

        print json_encode(['status' => "sukses", "data" => $data, "j" => $j]);
        break;

    case 'detail bank soal':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmbanksoal` WHERE `ID`  = '$ID'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'tambah bank soal':
        $MapelID    = addslashes($_POST['MapelID']);
        $GuruID     = addslashes($_POST['GuruID']);
        $Judul      = addslashes($_POST['Judul']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Waktu      = addslashes($_POST['Waktu']);
        $JumlahView = addslashes($_POST['JumlahView']);
        $Status     = addslashes($_POST['Status']);
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmbanksoal` (`MapelID`, `GuruID`, `Judul`, `Keterangan`, `Waktu`, `JumlahView`, `Status`)
                            VALUES ('$MapelID', '$GuruID', '$Judul', '$Keterangan', '$Waktu', '$JumlahView', '$Status'); ");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil Menambah Bank Soal"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit bank soal':
        $ID         = addslashes($_POST['ID']);
        $MapelID    = addslashes($_POST['MapelID']);
        $GuruID     = addslashes($_POST['GuruID']);
        $Judul      = addslashes($_POST['Judul']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Waktu      = addslashes($_POST['Waktu']);
        $JumlahView = addslashes($_POST['JumlahView']);
        $Status     = addslashes($_POST['Status']);
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmbanksoal`
                            SET `MapelID` = '$MapelID',
                                `GuruID` = '$GuruID',
                                `Judul` = '$Judul',
                                `Keterangan` = '$Keterangan',
                                `Waktu` = '$Waktu',
                                `JumlahView` = '$JumlahView',
                                `Status` = '$Status'
                            WHERE `ID` = '$ID';
          ");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Bank Soal"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus bank soal':
        $ID = addslashes($_POST['ID']);
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("DELETE FROM `dbmbanksoal` WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Bank Soal"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
