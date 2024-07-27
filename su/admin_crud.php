<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'tambah':
        $admin_firstname = addslashes($_POST['admin_firstname']);
        $admin_lastname = addslashes($_POST['admin_lastname']);
        $admin_username = addslashes($_POST['admin_username']);
        $admin_email = addslashes($_POST['admin_email']);
        $admin_password = $_POST['admin_password'];
        $admin_status = isset($_POST['admin_status']) ? 1 : 0;
        $option = ['cost' => 5];
        $pwd    = password_hash("$admin_password", PASSWORD_DEFAULT, $option);
        $cek      = $koneksi->query("SELECT * FROM `gijutsu_admin2`.`dbm_admin` WHERE `admin_email` = '$admin_email'")->rowCount();
        if ($cek == 0) {
            $koneksi->exec("INSERT INTO `gijutsu_admin2`.`dbm_admin` (`admin_firstname`, `admin_lastname`, `admin_username`, `admin_email`, `admin_password`, `admin_status`)
                                VALUES ('$admin_firstname', '$admin_lastname', '$admin_username', '$admin_email', '$pwd', '$admin_status');");
            print json_encode(['status' => "sukses", "pesan" => "Berhasil Tambah admin"]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Email $admin_email Sudah terdaftar"]);
        }
        break;

    case 'edit':
        $ID = addslashes($_POST['ID']);
        $admin_firstname = $_POST['admin_firstname'];
        $admin_lastname = $_POST['admin_lastname'];
        $admin_username = $_POST['admin_username'];
        $admin_email = $_POST['admin_email'];
        $admin_status = isset($_POST['admin_status']) ? 1 : 0;
        $koneksi->exec("UPDATE `gijutsu_admin2`.`dbm_admin`
                        SET `admin_firstname` = '$admin_firstname',
                            `admin_lastname` = '$admin_lastname',
                            `admin_username` = '$admin_username',
                            `admin_email` = '$admin_email',
                            `admin_status` = '$admin_status'
                        WHERE `ID` = '$ID';");
        print json_encode(['status' => "sukses", "pesan" => 'Berhasil edit admin']);
        break;

    case 'hapus':
        $ID = addslashes($_POST['ID']);
        $koneksi->exec("DELETE FROM `gijutsu_admin2`.`dbm_admin` WHERE `ID` = '$ID'");
        print json_encode(['status' => "sukses", "pesan" => "Berhasil hapus admin"]);
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
