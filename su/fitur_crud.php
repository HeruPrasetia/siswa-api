<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'tambah':
        if (isset($_POST['Fitur'])) {
            $PerusahaanID = addslashes($_POST['PerusahaanID']);
            $NamaPerusahaan = addslashes($_POST['NamaPerusahaan']);
            $Tanggal = addslashes($_POST['Tanggal']);
            $Notes = addslashes($_POST['Notes']);
            $Status = addslashes($_POST['Status']);
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("INSERT INTO `gijutsu_admin2`.`dbmreqfitur` (`PerusahaanID`, `NamaPerusahaan`, `Tanggal`, `Notes`, `TimeCreated`, `Status`)
                            VALUES ('$PerusahaanID', '$NamaPerusahaan', '$Tanggal', '$Notes', NOW(), '$Status');");
                $DocID = $koneksi->lastInsertId();
                $Fitur = $_POST['Fitur'];
                $Detail = $_POST['Detail'];
                $Biaya = $_POST['Biaya'];
                $IsPaid = $_POST['IsPaid'];
                foreach ($Fitur as $i => $fitur) {
                    $koneksi->exec("INSERT INTO `gijutsu_admin2`.`dbmreqfiturdetail` (`DocID`, `JatuhTempo`, `Fitur`, `Detail`, `UserID`, `Biaya`, `IsPaid`, `Status`)
                                    VALUES ('$DocID', '$Tanggal', '$Fitur[$i]', '$Detail[$i]', '0', '$Biaya[$i]', '$IsPaid[$i]', 'Pending');");
                }
                print json_encode(["status" => "sukses", "pesan" => "Tambah Kategori berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "silahkan tambahkan fitur"]);
        }
        break;

    case 'hapus':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `gijutsu_admin2`.`dbmreqfiturdetail` WHERE `DocID` = '$ID' AND `Status` <> 'Pending'")->rowCount();
        if ($data == 0) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `gijutsu_admin2`.`dbmreqfiturdetail` WHERE `DocID` = '$ID'");
                $koneksi->exec("DELETE FROM `gijutsu_admin2`.`dbmreqfitur` WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Hapus Fitur berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Fitur sudah diproses"]);
        }
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
