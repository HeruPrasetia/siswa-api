<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'setting profile':
        $data = $koneksi->query("SELECT * FROM `dbapis`.`dbmservice` WHERE  `ID` = '$__serviceid'")->fetch();
        if (!is_bool($data)) {
            if (strlen($data->Logo) < 50) {
                $imageData = file_get_contents("../$data->Logo");
                $base64Image = base64_encode($imageData);
                $fileExtension = pathinfo($data->Logo, PATHINFO_EXTENSION);
                $dataUri = 'data:image/' . $fileExtension . ';base64,' . $base64Image;
                $data->Logo = $dataUri;
            }
            if (strlen($data->LogoPanjang) < 50) {
                $imageData = file_get_contents("../$data->LogoPanjang");
                $base64Image = base64_encode($imageData);
                $fileExtension = pathinfo($data->LogoPanjang, PATHINFO_EXTENSION);
                $dataUri = 'data:image/' . $fileExtension . ';base64,' . $base64Image;
                $data->LogoPanjang = $dataUri;
            }
            print json_encode(['status' => "sukses", "data" => $data]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Anda tidak memiliki akses"]);
        }
        break;

    case 'edit profile':
        $ID             = addslashes($_POST['ID']);
        $Nama           = addslashes($_POST['Nama']);
        $Keterangan     = addslashes($_POST['Keterangan']);
        $LogoPanjang    = addslashes($_POST['LogoPanjang']);
        $Logo           = addslashes($_POST['Logo']);
        $ColorDefault   = addslashes($_POST['ColorDefault']);
        $ColorSecondary = addslashes($_POST['ColorSecondary']);
        $Email          = addslashes($_POST['Email']);
        $Telp           = addslashes($_POST['Telp']);
        $Alamat         = addslashes($_POST['Alamat']);
        $Status         = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $folderPath = "../file/$__serviceid/";
            if (!file_exists($folderPath))  mkdir($folderPath, 0777, true);
            if ($LogoPanjang != "") {
                $pos  = strpos($LogoPanjang, ';');
                $ext  = explode('/', substr($LogoPanjang, 0, $pos))[1];
                $b64  = explode(',', $LogoPanjang)[1];
                $bin  = base64_decode($b64);
                $im   = imageCreateFromString($bin);
                $fileName = "logopanjang.$ext";
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
                $LogoPanjang = "file/$__serviceid/$fileName";
            }

            if ($Logo != "") {
                $pos  = strpos($Logo, ';');
                $ext  = explode('/', substr($Logo, 0, $pos))[1];
                $b64  = explode(',', $Logo)[1];
                $bin  = base64_decode($b64);
                $im   = imageCreateFromString($bin);
                $fileName = "logopanjang.$ext";
                $img_file = $folderPath . $fileName;

                if ($ext == 'png') {
                    imagesavealpha($im, true);
                    imagepng($im, $img_file);
                } elseif ($ext == 'jpeg' || $ext == 'jpg') {
                    imagejpeg($im, $img_file, 100);  // Sesuaikan kualitas jika diperlukan
                } elseif ($ext == 'gif') {
                    imagegif($im, $img_file);
                }
                $Logo = "file/$__serviceid/$fileName";
            }

            $koneksi->exec("UPDATE `dbapis`.`dbmservice`
                            SET `Nama` = '$Nama',
                                `Keterangan` = '$Keterangan',
                                `LogoPanjang` = '$LogoPanjang',
                                `Logo` = '$Logo',
                                `ColorDefault` = '$ColorDefault',
                                `ColorSecondary` = '$ColorSecondary',
                                `Telp` = '$Telp',
                                `Email` = '$Email',
                                `Alamat` = '$Alamat',
                                `Status` = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Berhasil edit profile"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }

        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
