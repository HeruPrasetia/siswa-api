<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'tambah':
        $Nama = addslashes($_POST['Nama']);
        $IdMenu = addslashes($_POST['IdMenu']);
        $Path = addslashes($_POST['Path']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Icon = addslashes($_POST['Icon']);
        $Posisi = addslashes($_POST['Posisi']);
        $IsOrder = addslashes($_POST['IsOrder']);
        $Status = isset($_POST['Status']) ? 1 : 0;
        $IsGroup = isset($_POST['IsGroup']) ? 1 : 0;
        $App = isset($_POST['App']) ? $_POST['App'] : "POS";
        $koneksi->exec("INSERT INTO `dbsmenu` ( `IdMenu`, `Nama`, `Keterangan`, `File`, `Path`, `Icon`, `TableField`, `TableFieldDefault`, `TableLimit`, `Posisi`, `IsGroup`, `IsOrder`, `App`, `Status`)
                        VALUES ('$IdMenu', '$Nama', '$Keterangan', '$Path', '$Path', '$Icon', '{}', '{}', '100', '$Posisi', '$IsGroup', '$IsOrder', '$App', '$Status');");
        print json_encode(['status' => "sukses", "pesan" => "Berhasil Tambah Menu"]);
        break;

    case 'edit':
        $ID = addslashes($_POST['ID']);
        $Nama = addslashes($_POST['Nama']);
        $IdMenu = addslashes($_POST['IdMenu']);
        $Path = addslashes($_POST['Path']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Icon = addslashes($_POST['Icon']);
        $Posisi = addslashes($_POST['Posisi']);
        $IsOrder = addslashes($_POST['IsOrder']);
        $Status = isset($_POST['Status']) ? 1 : 0;
        $IsGroup = isset($_POST['IsGroup']) ? 1 : 0;
        $App = isset($_POST['App']) ? $_POST['App'] : "POS";
        $koneksi->exec("UPDATE `dbsmenu`
                        SET `IdMenu` = '$IdMenu',
                            `Nama` = '$Nama',
                            `Keterangan` = '$Keterangan',
                            `File` = '$Path',
                            `Path` = '$Path',
                            `Icon` = '$Icon',
                            `Posisi` = '$Posisi',
                            `IsGroup` = '$IsGroup',
                            `IsOrder` = '$IsOrder',
                            `Status` = '$Status',
                            `App` = '$App'
                        WHERE `ID` = '$ID';");
        print json_encode(['status' => "sukses", "pesan" => "Berhasil edit Menu"]);
        break;

    case 'hapus':
        $ID = addslashes($_POST['ID']);
        $koneksi->exec("DELETE FROM `dbsmenu` WHERE `ID` = '$ID'");
        print json_encode(['status' => "sukses", "pesan" => "Berhasil hapus Menu"]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}

function getDirectorySize($path)
{
    $totalSize = 0;
    foreach (glob($path . '/*') as $file) {
        if (is_file($file)) {
            $totalSize += filesize($file);
        } elseif (is_dir($file)) {
            $totalSize += getDirectorySize($file);
        }
    }
    return $totalSize;
}
