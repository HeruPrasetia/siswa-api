<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT a.*, b.`Nama` AS `Perusahaan`, IF(a.`IsOwner` = 1, 'Owner', 'User') AS `SSTS` FROM `dbmuser` a 
                                 LEFT JOIN `dbmperusahaan` b ON a.`Perusahaan` = b.`ID`
                                 WHERE a.`Nama` LIKE '%$q%' OR a.`Email` LIKE '%$q%'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data jenis usaha':
        $data = $koneksi->query("SELECT * FROM `dbmusaha` WHERE `Status` = 1")->fetchAll();
        $db = $koneksi->query("SELECT a.*, b.`Nama` FROM `dbmdatabase` a LEFT JOIN `dbmperusahaan` b ON a.`Perusahaan` = b.`ID` WHERE a.`Status` = 1")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "db" => $db]);
        break;

    case 'data menu perusahaan':
        $Database = addslashes($_POST['Database']);
        $data = $koneksi->query("SELECT * FROM `$Database`.`dbsmenu` WHERE `Status` = 1")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'detail':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT *, IF(`Status` =1, 'Aktif', 'Tidak Aktif') AS `SSTS` FROM `dbslogin` WHERE `UserID` = '$ID'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data menu':
        $ID = addslashes($_POST['ID']);
        $Database = $koneksi->query("SELECT * FROM `dbmdatabase` WHERE `Perusahaan` = '$ID'")->fetch();
        if ($Database) {
            $data = $koneksi->query("SELECT *, IF(`IsGroup` =1, 'Group', 'Menu') AS `Type` FROM `$Database->Database`.`dbsmenu`")->fetchAll();
            print json_encode(['status' => "sukses", "data" => $data]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "database tidak ditemukan"]);
        }
        break;

    case 'data log':
        $ID = addslashes($_POST['ID']);
        $Database = $koneksi->query("SELECT * FROM `dbmdatabase` WHERE `Perusahaan` = '$ID'")->fetch();
        if ($Database) {
            $data = $koneksi->query("SELECT * FROM `$Database->Database`.`dbtjsontrans` WHERE `Status` = 'gagal' ORDER BY `ID` DESC")->fetchAll();
            print json_encode(['status' => "sukses", "data" => $data]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "database tidak ditemukan"]);
        }
        break;

    case 'data menus':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT * FROM `dbsmenu` WHERE `App` = 'POS' AND `Status` = 1 AND Nama LIKE '%$q%'")->fetchall();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'backup db':
        $Database = $_POST['Database'];
        $backup_file = "../files/" . $Database . date("Y-m-d") . '.sql';
        $command = "mysqldump --host=localhost --user=naylatools --password=N@yl4naylatools $Database > $backup_file";
        exec($command, $output, $return_var);
        if ($return_var === 0) {
            $file_sql = $backup_file;
            $file_output = "$backup_file.gz";
            $handle_sql = fopen($file_sql, 'r');
            $handle_output = gzopen($file_output, 'w9');

            while (!feof($handle_sql)) {
                gzwrite($handle_output, fread($handle_sql, 8192));
            }

            fclose($handle_sql);
            gzclose($handle_output);
            unlink($file_sql);
            print json_encode(['status' => "sukses", "pesan" => "https://api.gijutsusoftware.com/files/$file_output"]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Gagal Backup database $return_var"]);
        }
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
