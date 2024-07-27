<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data menu':
        print json_encode(["status" => "sukses", "data" => $service]);
        break;

    case 'edit menu':
        $Konten = $_POST['Konten'];
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbapis`.`dbmservice` SET `Konten` = '$Konten' WHERE `ID` = '$__serviceid'");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Menu"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'home konten':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $j    = $koneksi->query("SELECT `ID` FROM `dbskonten` WHERE `Menu` = 'Home' AND `Konten` LIKE '%$q%' ")->rowCount();
        $data = $koneksi->query("SELECT * FROM `dbskonten` WHERE `Menu` = 'Home' AND `Konten` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();

        print json_encode(['status' => "sukses", "data" => $data, "profile" => $service, "j" => $j]);
        break;

    case 'data konten':
        $Menu = $_POST['Menu'];
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbskonten` WHERE `Menu` = '$Menu' AND `Konten` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT * FROM `dbskonten` WHERE `Menu` = '$Menu' AND `Konten` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "service" => $service, "j" => $j, "m" => $m]);
        break;


    case 'data galeri':
        $Menu = $_POST['Menu'];
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbskonten` WHERE `Menu` = '$Menu' AND `Konten` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT `ID`, `Judul`, `Posisi`, `NoUrut`, `Status` FROM `dbskonten` WHERE `Menu` = '$Menu' AND `Konten` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'detail galeri':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbskonten` WHERE `ID` = '$ID'")->fetch();
        print json_encode(["status" => "sukses", "data" => $data]);
        break;

    case 'tambah konten':
        $ID         = addslashes($_POST['ID']);
        $Judul      = addslashes($_POST['Judul']);
        $Konten     = $_POST['Konten'];
        $NoUrut     = addslashes($_POST['NoUrut']);
        $Status     = isset($_POST['Status']) ? 1 : 0;
        $Menu       = addslashes($_POST['Menu']);
        $Posisi     = addslashes($_POST['Posisi']);
        $KontenType = addslashes($_POST['KontenType']);

        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbskonten` (`Menu`, `Posisi`, `Judul`, `KontenType`, `Konten`, `NoUrut`, `Status`)
                            VALUES ('$Menu', '$Posisi', '$Judul', '$KontenType', '$Konten', '$NoUrut', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Data", "ID" => $ID]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit konten':
        $ID     = addslashes($_POST['ID']);
        $Judul  = addslashes($_POST['Judul']);
        $Konten = $_POST['Konten'];
        $NoUrut = addslashes($_POST['NoUrut']);
        $Status = isset($_POST['Status']) ? 1 : 0;

        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbskonten` SET `Status` = '$Status', `Judul` = '$Judul', `Konten` = '$Konten', `NoUrut` = '$NoUrut' WHERE `ID` = '$ID'");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Data", "ID" => $ID]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'detail banner atas':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query(("SELECT * FROM `dbskonten` WHERE `ID` = '$ID'"))->fetch();
        $Konten = json_decode($data->Konten);
        $filePath = $Konten->background;
        if (strlen($filePath) < 50) {
            $imageData = file_get_contents("../$filePath");
            $base64Image = base64_encode($imageData);
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $dataUri = 'data:image/' . $fileExtension . ';base64,' . $base64Image;
            $Konten = $data->Konten;
            $Konten = str_replace($filePath, $dataUri, $Konten);
        }
        $data->Konten = $Konten;
        print json_encode(["status" => "sukses", "data" => $data]);
        break;

    case 'edit banner atas':
        $ID         = $_POST['ID'];
        $Judul      = addslashes($_POST['Judul']);
        $keterangan = addslashes($_POST['keterangan']);
        $background = $_POST['background'];
        $data = $koneksi->query("SELECT * FROM `dbskonten` WHERE ID = '$ID'")->fetch();
        if (!is_bool($data)) {
            $Konten = json_decode($data->Konten);
            $Konten->keterangan = $keterangan;
            try {
                $koneksi->beginTransaction();
                $folderPath = "../file/$__serviceid/";
                if (!file_exists($folderPath))  mkdir($folderPath, 0777, true);
                $pos  = strpos($background, ';');
                $ext  = explode('/', substr($background, 0, $pos))[1];
                $b64  = explode(',', $background)[1];
                $bin  = base64_decode($b64);
                $im   = imageCreateFromString($bin);
                $fileName = "banneratasbg" . $ID . "." . $ext;
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
                $background = "file/$__serviceid/$fileName";
                $Konten->background = $background;
                $Konten = json_encode($Konten);
                $koneksi->exec("UPDATE `dbskonten` SET `Judul` = '$Judul', `Konten` = '$Konten' WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Data", "ID" => $ID]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Konten tidak ditemukan"]);
        }
        break;

    case 'edit form banner atas':
        $ID = addslashes($_POST['ID']);
        $keteranganForm = addslashes($_POST['keteranganForm']);
        $judulForm = addslashes($_POST['judulForm']);
        $data = $koneksi->query("SELECT * FROM `dbskonten` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($data)) {
            $Konten = json_decode($data->Konten);
            $Konten->isForm = isset($_POST['isForm']) ? 1 : 0;
            $Konten->keteranganForm = $keteranganForm;
            $Konten->judulForm = $judulForm;
            try {
                $koneksi->beginTransaction();
                $Konten = json_encode($Konten);
                $koneksi->exec("UPDATE `dbskonten` SET `Konten` = '$Konten' WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Berhasil Edit Data", "ID" => $ID]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Konten tidak ditemukan"]);
        }
        break;

    case 'hapus konten':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbskonten` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($data)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbskonten` WHERE `ID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Berhasil Hapus Data", "ID" => $ID]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "data tidak ditemukan"]);
        }
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
