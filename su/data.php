<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data profile':
        $data = $koneksi->query("SELECT * FROM `dbsprofile`")->fetch();
        print json_encode(["status" => "sukses", "profile" => $data]);
        break;

    case 'data menu':
        $data = $koneksi->query("SELECT a.* FROM `master2`.`dbsmenu` a 
                                INNER JOIN `gijutsu_admin2`.`dbsakses` b ON a.`Path` = b.`Akses`
                                WHERE a.`App` = 'admin' AND b.`AdminID` = '$__userid' AND a.`Status` = 1;")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data perusahaan':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT * FROM `dbmperusahaan` WHERE `Nama` LIKE '%$q%'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'detail perusahaan':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmperusahaan` WHERE `ID` = '$ID'")->fetch();
        $karyawan = $koneksi->query("SELECT `ID`, `Nama`, `Email` FROM `dbmuser` WHERE `Perusahaan` = '$ID' AND `Status` = 1")->fetchAll();
        $domain = $koneksi->query("SELECT * FROM `dbmdomains` WHERE `Perusahaan` = '$ID'")->fetch();
        $database = $koneksi->query("SELECT *, '' AS `Ukuran` FROM `dbmdatabase` WHERE `Perusahaan` = '$ID'")->fetch();
        if ($database) {
            $ukuran = $koneksi->query("SELECT COALESCE(ROUND(SUM(data_length + index_length) / 1024 / 1024, 2), 0) AS 'Ukuran'
                                    FROM information_schema.tables
                                    WHERE table_schema = '$database->Database'")->fetch();

            $database->Ukuran = $ukuran->Ukuran;
        }
        $folderPath = "../../pos/assets/berkas/$__usahaid";
        $files = glob($folderPath . '*');
        $fileCount = count($files);
        $folderSizeBytes = getDirectorySize($folderPath);
        $folderSizeKB = round($folderSizeBytes / 1024, 2);
        $folder = ["jumlah" => $fileCount, "ukuran" => $folderSizeBytes];
        print json_encode(['status' => "sukses", "data" => $data, "karyawan" => $karyawan, "database" => $database, "domain" => $domain, "folder" => $folder]);
        break;

    case 'detail system':
        $TokenUser = $_POST['TokenUser'];
        $data = $koneksi->query("SELECT a.*, b.`Nama` AS `Perusahaan`, c.`Nama`, c.`Telp`, c.`Email` 
                                 FROM `dbslogin` a
                                 LEFT JOIN `dbmperusahaan` b ON a.`PerusahaanID` = b.`ID`
                                 LEFT JOIN `dbmuser` c ON a.`UserID` = c.`ID`
                                 WHERE a.`Token` = '$TokenUser'")->fetch();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data log':
        $data = $koneksi->query("SELECT a.*, COALESCE(timestampdiff(MINUTE, TIME(a.`LastConnect`), TIME(NOW())),0) AS Connect, b.`Nama` 
                                 FROM `dbslogin` a
                                 LEFT JOIN `dbmperusahaan` b ON a.`PerusahaanID` = b.`ID`
                                 WHERE a.`Status` = 1 AND DATE(a.`LastConnect`) = CURDATE() HAVING Connect <= 1")->fetchAll();

        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'files':
        $directory = isset($_POST['dir']) ? $_POST['dir'] : '../../pos/assets/berkas/';
        $baseUrl = 'http://yourdomain.com/path/to/directory';
        $files = [];

        $dir = new DirectoryIterator($directory);

        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $extension = $fileinfo->isFile() ? pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) : 'directory';
                $files[] = [
                    'Nama' => $fileinfo->getFilename(),
                    'Type' => $extension,
                    'Size' => $fileinfo->isFile() ? $fileinfo->getSize() : 0,
                    'Date' => date("Y-m-d H:i:s", $fileinfo->getCTime()),
                    'Path' => $fileinfo->isDir() ? $directory . '/' . $fileinfo->getFilename() : '',
                    'Link' => $fileinfo->isFile() ? $baseUrl . '/' . $fileinfo->getFilename() : ''
                ];
            }
        }

        $perusahaan = $koneksi->query("SELECT `ID`, `Nama` FROM `dbmperusahaan`")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $files, "perusahaan" => $perusahaan]);
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
