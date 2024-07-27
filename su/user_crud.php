<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'logout sesi':
        $ID = addslashes($_POST['ID']);
        $koneksi->exec("UPDATE `dbslogin` SET `Status` = 0 WHERE `ID` = '$ID'");
        print json_encode(['status' => "sukses", "pesan" => 'Berhasil logout Sesi user']);
        break;

    case 'logout user':
        $ID = addslashes($_POST['ID']);
        $koneksi->exec("UPDATE `dbslogin` SET `Status` = 0 WHERE `UserID` = '$ID'");
        print json_encode(['status' => "sukses", "pesan" => 'Berhasil logout user']);
        break;

    case 'hapus user':
        $ID = addslashes($_POST['ID']);
        $user = $koneksi->query("SELECT * FROM dbmuser WHERE ID = '$ID'")->fetch();
        if ($user) {
            $Database = $koneksi->query("SELECT * FROM dbmdatabase WHERE Perusahaan = '$user->Perusahaan'")->fetch();
            if ($Database) {
                $cekDB = $koneksi->query("SHOW DATABASES LIKE '$Database->Database';")->rowCount();
                if ($cekDB > 0) {
                    $koneksi->exec("DELETE FROM `$Database->Database`.`dbmkaryawan` WHERE `UserID` = '$ID'");
                    $koneksi->exec("DELETE FROM `$Database->Database`.`dbsakses` WHERE `UserID` = '$ID'");
                    $koneksi->exec("DELETE FROM `dbmuser` WHERE `ID` = '$ID'");
                    $koneksi->exec("DELETE FROM `dbslogin` WHERE `UserID` = '$ID'");
                    print json_encode(['status' => "sukses", "pesan" => "Berhasil hapus user"]);
                } else {
                    $koneksi->exec("DELETE FROM `dbmuser` WHERE `ID` = '$ID'");
                    $koneksi->exec("DELETE FROM `dbslogin` WHERE `UserID` = '$ID'");
                    print json_encode(['status' => "sukses", "pesan" => "Databse tidak ditemukan"]);
                }
            } else {
                $koneksi->exec("DELETE FROM `dbmuser` WHERE `ID` = '$ID'");
                $koneksi->exec("DELETE FROM `dbslogin` WHERE `UserID` = '$ID'");
                print json_encode(['status' => "sukses", "pesan" => "Database user tidak di temukan"]);
            }
        } else {
            print json_encode(['status' => "gagal", "pesan" => "User tidak ditemukan"]);
        }
        break;

    case 'hak akses':
        $ID = addslashes($_POST['ID']);
        $Akses = isset($_POST['Akses']) ? $_POST['Akses'] : [];
        $Tambah = isset($_POST['Tambah']) ? $_POST['Tambah'] : [];
        $Edit = isset($_POST['Edit']) ? $_POST['Edit'] : [];
        $Hapus = isset($_POST['Hapus']) ? $_POST['Hapus'] : [];
        $koneksi->exec("DELETE FROM `gijutsu_admin2`.`dbsakses` WHERE `AdminID` = '$ID'");
        $sql = "INSERT INTO `gijutsu_admin2`.`dbsakses` (`AdminID`, `Akses`, `Tambah`, `Edit`, `Hapus`, `Limit`) VALUES ";
        foreach ($Akses as $i => $akses) {
            $sql .= "('$ID', '$akses', '0', '0', '0', '100'), ";
        }
        $sql = rtrim($sql, ", ");
        $sql .= ";
                
                ";
        foreach ($Tambah as $i => $akses) {
            $sql .= "UPDATE `gijutsu_admin2`.`dbsakses` SET `Tambah` = 1 WHERE `AdminID` = '$ID' AND `Akses` = '$akses'; ";
        }

        foreach ($Edit as $i => $akses) {
            $sql .= "UPDATE `gijutsu_admin2`.`dbsakses` SET `Edit` = 1 WHERE `AdminID` = '$ID' AND `Akses` = '$akses'; ";
        }

        foreach ($Hapus as $i => $akses) {
            $sql .= "UPDATE `gijutsu_admin2`.`dbsakses` SET `Hapus` = 1 WHERE `AdminID` = '$ID' AND `Akses` = '$akses'; ";
        }

        $koneksi->exec($sql);
        print json_encode(['status' => "sukses", "pesan" => "Berhasil merubah hak akses"]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
