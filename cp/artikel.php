<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data kategori':
        $s    = isset($_POST['order']) ? $_POST['order'] : "Nama";
        $b    = isset($_POST['by']) ? $_POST['by'] : "ASC";
        $l    = 100;
        $p    = isset($_POST['page']) ? $_POST['page'] : 0;
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = isset($_POST['q']) ? addslashes($_POST['q']) : "";
        $t    = $koneksi->query("SELECT `ID` FROM `dbmkategori` WHERE `Status` = 1 AND `Nama` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT * FROM `dbmkategori` WHERE `Status` = 1 AND `Nama` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'tambah kategori':
        $Nama   = addslashes($_POST['Nama']);
        $Status = isset($_POST['Status']) ? 1 : 0;
        $cek    = $koneksi->query("SELECT * FROM `dbmkategori` WHERE `Nama` = '$Nama'")->rowCount();
        if ($cek == 0) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("INSERT INTO `dbmkategori` (`Nama`, `Status`) VALUES ('$Nama', '$Status');");
                print json_encode(["status" => "sukses", "pesan" => "Tambah Kategori berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Kategori $Nama Sudah terdaftar"]);
        }
        break;

    case 'edit kategori':
        $ID     = addslashes($_POST['ID']);
        $Nama   = addslashes($_POST['Nama']);
        $Status = isset($_POST['Status']) ? 1 : 0;
        $Data   = $koneksi->query("SELECT * FROM `dbmkategori` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Data)) {
            $cek   = $koneksi->query("SELECT * FROM `dbmkategori` WHERE `Nama` = '$Nama' AND `ID` <> '$ID'")->rowCount();
            if ($cek == 0) {
                try {
                    $koneksi->beginTransaction();
                    $koneksi->exec("UPDATE `dbmkategori` SET `Nama` = '$Nama', `Status` = '$Status' WHERE `ID` = '$ID'");
                    print json_encode(["status" => "sukses", "pesan" => "Edit Kategori berhasil"]);
                    $koneksi->commit();
                } catch (PDOException $e) {
                    print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
                }
            } else {
                print json_encode(["status" => "gagal", "pesan" => "Kategori $Nama Sudah terdaftar"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Admin tidak ditemukan"]);
        }
        break;

    case 'hapus kategori':
        $ID    = addslashes($_POST['ID']);
        $cek   = $koneksi->query("SELECT * FROM `dbmkategori` WHERE `ID` = $ID")->rowCount();
        if ($cek > 0) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbmkategori` WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Hapus admin berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "User tidak ditemukan"]);
        }
        break;




    case 'data artikel':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $fil  = $_POST['KategoriID'] == "" ? "" : " a.`KategoriID` = '" . $_POST['KategoriID'] . "' AND ";
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT a.`ID` FROM `dbmartikel` a WHERE $fil a.`Judul` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT a.`ID`, a.`Judul`, a.`TimeCreated`, a.`Tanggal`, a.`Status`, a.`Artikel`, a.`Pembuat`, b.`Nama` AS `Kategori` 
                                 FROM `dbmartikel` a
                                 LEFT JOIN `dbmkategori` b ON a.`KategoriID` = b.`ID` 
                                 WHERE $fil a.`Judul` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'detail artikel':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmartikel` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($data)) {
            $filePath = $data->Banner;
            if (strlen($filePath) < 50) {
                $imageData = file_get_contents("../$filePath");
                $base64Image = base64_encode($imageData);
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                $dataUri = 'data:image/' . $fileExtension . ';base64,' . $base64Image;
                $data->Banner = $dataUri;
            }
            print json_encode(['status' => "sukses", "data" => $data]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Data tidak ditemukan"]);
        }
        break;

    case 'tambah artikel':
        $Judul      = addslashes($_POST['Judul']);
        $KategoriID = addslashes($_POST['KategoriID']);
        $Banner     = addslashes($_POST['Banner']);
        $Artikel    = addslashes($_POST['Artikel']);
        $Tanggal    = addslashes($_POST['Tanggal']);
        $Status     = addslashes($_POST['Status']);
        $Status     = $_POST['Status'];;
        $User       = $koneksi->query("SELECT a.`Nama` FROM `dbapis`.`dbmuser` a INNER JOIN `dbapis`.`dbmtoken` b ON a.`ID` = b.`UserID` WHERE `Token` = '$SESITOKEN'")->fetch();
        $Pembuat    = $User->Nama;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmartikel` (`Judul`, `KategoriID`, `Banner`, `Artikel`, `Pembuat`, `Tanggal`, `TimeCreated`, `Status`)
                            VALUES ('$Judul', '$KategoriID', '', '$Artikel', '$Pembuat', '$Tanggal', NOW(), '$Status');");
            $ID = $koneksi->lastInsertId();
            $folderPath = "../file/$__serviceid/";
            if (!file_exists($folderPath))  mkdir($folderPath, 0777, true);
            $pos  = strpos($Banner, ';');
            $ext  = explode('/', substr($Banner, 0, $pos))[1];
            $b64  = explode(',', $Banner)[1];
            $bin  = base64_decode($b64);
            $im   = imageCreateFromString($bin);
            $fileName = "banner" . $ID . "." . $ext;
            $img_file = $folderPath . $fileName;

            if ($ext == 'png') {
                imagesavealpha($im, true);
                imagepng($im, $img_file);
            } elseif ($ext == 'jpeg' || $ext == 'jpg') {
                imagejpeg($im, $img_file, 100);  // Sesuaikan kualitas jika diperlukan
            } elseif ($ext == 'gif') {
                imagegif($im, $img_file);
            }
            imagedestroy($im);
            $banner = "file/$__serviceid/$fileName";
            $koneksi->exec("UPDATE `dbmartikel` SET `Banner` = '$banner' WHERE `ID` = '$ID'");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Artikel berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit artikel':
        $ID          = addslashes($_POST['ID']);
        $Judul       = addslashes($_POST['Judul']);
        $KategoriID  = addslashes($_POST['KategoriID']);
        $Banner      = addslashes($_POST['Banner']);
        $Artikel     = addslashes($_POST['Artikel']);
        $Tanggal     = addslashes($_POST['Tanggal']);
        $Status      = $_POST['Status'];
        $User        = $koneksi->query("SELECT a.`Nama` FROM `dbapis`.`dbmuser` a INNER JOIN `dbapis`.`dbmtoken` b ON a.`ID` = b.`UserID` WHERE `Token` = '$SESITOKEN'")->fetch();
        $Pembuat     = $User->Nama;
        try {
            $koneksi->beginTransaction();
            $folderPath = "../file/$__serviceid/";
            if (!file_exists($folderPath))  mkdir($folderPath, 0777, true);
            $pos  = strpos($Banner, ';');
            $ext  = explode('/', substr($Banner, 0, $pos))[1];
            $b64  = explode(',', $Banner)[1];
            $bin  = base64_decode($b64);
            $im   = imageCreateFromString($bin);
            $fileName = "banner" . $ID . "." . $ext;
            $img_file = $folderPath . $fileName;

            if ($ext == 'png') {
                imagesavealpha($im, true);
                imagepng($im, $img_file);
            } elseif ($ext == 'jpeg' || $ext == 'jpg') {
                imagejpeg($im, $img_file, 100);  // Sesuaikan kualitas jika diperlukan
            } elseif ($ext == 'gif') {
                imagegif($im, $img_file);
            }
            imagedestroy($im);
            $banner = "file/$__serviceid/$fileName";
            $koneksi->exec("UPDATE `dbmartikel`
                            SET `Judul` = '$Judul',
                                `KategoriID` = '$KategoriID',
                                `Banner` = '$banner',
                                `Artikel` = '$Artikel',
                                `Pembuat` = '$Pembuat',
                                `Tanggal` = '$Tanggal',
                                `Status` = '$Status'
                            WHERE `ID` = '$ID'");
            print json_encode(["status" => "sukses", "pesan" => "Edit Artikel berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus artikel':
        $ID = addslashes($_POST['ID']);
        $Data = $koneksi->query("SELECT * FROM `dbmartikel` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Data)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbmartikel` WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "Hapus Artikel berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data Artikel tidak ditemukan"]);
        }
        break;




    case 'data komentar':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $fil  = $_POST['ArtikelID'] == "" ? "" : " a.ArtikelID = '" . $_POST['ArtikelID'] . "' AND ";
        $t    = $koneksi->query("SELECT a.`ID` FROM `dbmkomentar` a WHERE $fil a.`Nama` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT a.*, b.`Judul` FROM `dbmkomentar` a 
                                 LEFT JOIN `dbmartikel` b ON a.`ArtikelID` = b.`ID` 
                                 WHERE $fil a.`Nama` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'proses komentar':
        $ID      = addslashes($_POST['ID']);
        $Status  = $_POST['Status'];
        $data    = $koneksi->query("SELECT `ID` FROM `dbmkomentar` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($data)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("UPDATE `dbmkomentar` SET `Status` = '$Status' WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Berhasil Proses Komentar"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Komentar tidak ditemukan"]);
        }
        break;

    case 'balas komentar':
        $ArtikelID = addslashes($_POST['ArtikelID']);
        $ReplayID  = addslashes($_POST['ReplayID']);
        $Komentar  = addslashes($_POST['Komentar']);
        $User      = $koneksi->query("SELECT a.`Nama` FROM `dbapis`.`dbmuser` a INNER JOIN `dbapis`.`dbmtoken` b ON a.`ID` = b.`UserID` WHERE `Token` = '$SESITOKEN'")->fetch();
        $Nama      = $User->Nama;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmkomentar` (`ArtikelID`, `KomentarType`, `ReplayID`, `Komentar`, `Nama`, `Email`, `TimeCreated`, `Status`)
                            VALUES ('$ArtikelID', 'Artikel', '$ReplayID', '$Komentar', '$Nama', '$service->Email', NOW(), '1');");
            print json_encode(["status" => "sukses", "pesan" => "Edit Artikel berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus komentar':
        $ID     = addslashes($_POST['ID']);
        $data   = $koneksi->query("SELECT `ID` FROM `dbmkomentar` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($data)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbmkomentar` WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Berhasil Hapus Komentar"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Komentar tidak ditemukan"]);
        }
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}

function isBase64($string)
{
    $decodedString = base64_decode($string, true);
    return ($decodedString !== false) && (base64_encode($decodedString) === $string);
}
