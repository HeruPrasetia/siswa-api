<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data':
        $q = addslashes($_POST['q']);
        $koneksi->exec("DROP TEMPORARY TABLE IF EXISTS tbLogin;
                        CREATE TEMPORARY TABLE tbLogin
                        SELECT PerusahaanID, LastConnect 
                        FROM dbslogin 
                        WHERE (PerusahaanID, LastConnect) IN (
                            SELECT PerusahaanID, MAX(LastConnect)
                            FROM dbslogin
                            GROUP BY PerusahaanID
                        )
                        ORDER BY LastConnect DESC;");
        $data = $koneksi->query("SELECT a.*, IF(a.`Status` = 1, 'Aktif', 'Tidak Aktif') AS `SSTS`,
                                 DATEDIFF(a.`Sampai`, CURDATE()) AS `Sisa`,
                                 b.`LastConnect`,
                                 if(DATEDIFF(CURDATE(), DATE(b.`LastConnect`)) > 5 AND `DBSize` < 5, 'Stuk', 'Aktif') AS Stuk
                                 FROM `dbmperusahaan` a 
                                 LEFT JOIN `tbLogin` b ON a.`ID` = b.`PerusahaanID`
                                 WHERE a.`Nama` LIKE '%$q%' ORDER BY `Tanggal` DESC")->fetchAll();
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

    case 'detail neraca':
        $ID = $_POST['ID'];
        $data = $koneksi->query("SELECT `Neraca` FROM `master2`.`dbmlogneraca` WHERE ID = '$ID'")->fetch();
        print json_encode(['status' => "sukses", "data" => $data->Neraca]);
        break;

    case 'detail':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmperusahaan` WHERE `ID` = '$ID'")->fetch();
        $koneksi->exec("DROP TEMPORARY TABLE IF EXISTS `tbLogin`;
                        CREATE TEMPORARY TABLE `tbLogin`
                        SELECT `UserID`, `LastConnect` FROM `dbslogin` WHERE `PerusahaanID` = '$ID' GROUP BY UserID ORDER BY `LastConnect` DESC;");
        $karyawan = $koneksi->query("SELECT a.`Nama`, a.`Email`, a.`Telp`, b.`LastConnect` 
                                    FROM `dbmuser` a LEFT JOIN `tbLogin` b ON a.`ID` = b.`UserID`
                                    WHERE a.`Perusahaan` = '$ID' AND `Status` = 1;")->fetchAll();
        $domain = $koneksi->query("SELECT * FROM `dbmdomains` WHERE `Perusahaan` = '$ID'")->fetch();
        $neraca = $koneksi->query("SELECT `ID`, `Tanggal`, `TimeCreated`, `Aset`, `Liabilitas`, `Balance` FROM `master2`.`dbmlogneraca` WHERE `Perusahaan` = '$ID'")->fetchAll();
        $database = $koneksi->query("SELECT *, '' AS `Ukuran` FROM `dbmdatabase` WHERE `Perusahaan` = '$ID'")->fetch();
        if ($database) {
            $ukuran = $koneksi->query("SELECT COALESCE(ROUND(SUM(data_length + index_length) / 1024 / 1024, 2), 0) AS 'Ukuran'
                                        FROM information_schema.tables
                                        WHERE table_schema = '$database->Database'")->fetch();

            $database->Ukuran = $ukuran->Ukuran;
        }
        $folderPath = "../../pos/assets/berkas/$ID/";
        $files = glob($folderPath . '*');
        $fileCount = count($files);
        $folderSizeBytes = getDirectorySize($folderPath);
        $folderSizeKB = round($folderSizeBytes / 1024 / 1024, 2);
        $folder = ["jumlah" => $fileCount, "ukuran" => $folderSizeKB];
        print json_encode(['status' => "sukses", "data" => $data, "karyawan" => $karyawan, "database" => $database, "domain" => $domain, "folder" => $folder, "neraca" => $neraca]);
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
