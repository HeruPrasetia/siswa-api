<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'req project':
        $NamaPerusahaan = isset($_POST['NamaPerusahaan']) ? addslashes($_POST['NamaPerusahaan']) : "";
        $NamaPemohon = isset($_POST['NamaPemohon']) ? addslashes($_POST['NamaPemohon']) : "";
        $NoHP = isset($_POST['NoHP']) ? addslashes($_POST['NoHP']) : "";
        $Jabatan = isset($_POST['Jabatan']) ? addslashes($_POST['Jabatan']) : "";
        $Alamat = isset($_POST['Alamat']) ? addslashes($_POST['Alamat']) : "";
        $NamaDinas = isset($_POST['NamaDinas']) ? addslashes($_POST['NamaDinas']) : "";
        $AlamatDinas = isset($_POST['AlamatDinas']) ? addslashes($_POST['AlamatDinas']) : "";
        $NamaPerwakilan = isset($_POST['NamaPerwakilan']) ? addslashes($_POST['NamaPerwakilan']) : "";
        $KodePaket = isset($_POST['KodePaket']) ? addslashes($_POST['KodePaket']) : "";
        $NamaPaket = isset($_POST['NamaPaket']) ? addslashes($_POST['NamaPaket']) : "";
        $LokasiPaket = isset($_POST['LokasiPaket']) ? addslashes($_POST['LokasiPaket']) : "";
        $VolumePekerjaan = isset($_POST['VolumePekerjaan']) ? addslashes($_POST['VolumePekerjaan']) : "";
        $PanjangJalan = isset($_POST['PanjangJalan']) ? addslashes($_POST['PanjangJalan']) : "";
        $LebarJalan = isset($_POST['LebarJalan']) ? addslashes($_POST['LebarJalan']) : "";
        $NamaPabrikan = isset($_POST['NamaPabrikan']) ? addslashes($_POST['NamaPabrikan']) : "";
        $AlamatPabrikan = isset($_POST['AlamatPabrikan']) ? addslashes($_POST['AlamatPabrikan']) : "";
        $NamaPenanggungJawab = isset($_POST['NamaPenanggungJawab']) ? addslashes($_POST['NamaPenanggungJawab']) : "";
        $NoHPPenanggungJawab = isset($_POST['NoHPPenanggungJawab']) ? addslashes($_POST['NoHPPenanggungJawab']) : "";
        $Notes = "";
        $Details = [
            "NamaPerusahaan" => $NamaPerusahaan,
            "NamaPemohon" => $NamaPemohon,
            "NoHP" => $NoHP,
            "Jabatan" => $Jabatan,
            "Alamat" => $Alamat,
            "NamaDinas" => $NamaDinas,
            "AlamatDinas" => $AlamatDinas,
            "NamaPerwakilan" => $NamaPerwakilan,
            "KodePaket" => $KodePaket,
            "NamaPaket" => $NamaPaket,
            "LokasiPaket" => $LokasiPaket,
            "VolumePekerjaan" => $VolumePekerjaan,
            "PanjangJalan" => $PanjangJalan,
            "LebarJalan" => $LebarJalan,
            "NamaPabrikan" => $NamaPabrikan,
            "AlamatPabrikan" => $AlamatPabrikan,
            "NamaPenanggungJawab" => $NamaPenanggungJawab,
            "NoHPPenanggungJawab" => $NoHPPenanggungJawab,
            "KontrakSPK" => "",
            "RAB" => "",
            "GambarKerja" => "",
            "MC0" => "",
            "SuratDukunganPabrikan" => ""
        ];
        $DocNumber = docnumber("PROJK");
        $koneksi->exec("INSERT INTO `dbtitemtrans` (`DocType`, `DocNumber`, `ReffDocNumber`, `DocDate`, `CardType`, `Notes`, `CardID`, `CardName`, `TransStatus`, `TimeCreated`, `TimeUpdated`,  `Lokasi`, `Status`)
                        VALUES ('PROJK', '$DocNumber', null, CURDATE(), 'pelanggan', '$Notes', '$__userid', (SELECT `Nama` FROM `dbmcard` WHERE `ID` = '$__userid'), 'Belum Diproses', NOW(), NOW(), '0', '1');");
        $cmp        = $__usahaid;
        $target_dir = "assets/berkas/$cmp/";
        $cekDir     = "../../pos/assets/berkas/$cmp/";
        $ok_ext     = array('jpg', 'png', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xlx', 'xlsx');
        if (!file_exists($cekDir) && !is_dir($cekDir)) if (mkdir($cekDir)) chmod($cekDir, 0777);

        if (isset($_FILES['KontrakSPK'])) {
            $file              = $_FILES['KontrakSPK'];
            $original_filename = $file['name'];
            $tmp               = $file['tmp_name'];
            $filename          = explode(".", $file["name"]);
            $file_name         = $file['name'];
            $file_name_no_ext  = $filename[0];
            $file_extension    = strtolower($filename[count($filename) - 1]);
            $file_type         = $file['type'];
            $file_size         = $file['size'];
            $fileNewName       = "$DocNumber" . "KontrakSPK" . "." . $file_extension;
            $link              = $target_dir . $fileNewName;
            if (in_array($file_extension, $ok_ext)) {
                $Details['KontrakSPK'] = $link;
                $koneksi->exec("INSERT INTO `dbtitemtransfile`(`DocNumber`, `File`, `Ukuran`) VALUES('$DocNumber', '$link', '$file_size')");
                move_uploaded_file($tmp, $cekDir . $fileNewName);
            }
        }
        if (isset($_FILES['RAB'])) {
            $file       = $_FILES['RAB'];
            $original_filename = $file['name'];
            $tmp               = $file['tmp_name'];
            $filename          = explode(".", $file["name"]);
            $file_name         = $file['name'];
            $file_name_no_ext  = $filename[0];
            $file_extension    = strtolower($filename[count($filename) - 1]);
            $file_type         = $file['type'];
            $file_size         = $file['size'];
            $fileNewName       = "$DocNumber" . "RAB" . "." . $file_extension;
            $link              = $target_dir . $fileNewName;
            if (in_array($file_extension, $ok_ext)) {
                if (in_array($file_extension, $ok_ext)) {
                    $Details['RAB'] = $link;
                    $koneksi->exec("INSERT INTO `dbtitemtransfile`(`DocNumber`, `File`, `Ukuran`) VALUES('$DocNumber', '$link', '$file_size')");
                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                }
            }
        }

        if (isset($_FILES["GambarKerja"])) {
            $file       = $_FILES['GambarKerja'];
            $original_filename = $file['name'];
            $tmp               = $file['tmp_name'];
            $filename          = explode(".", $file["name"]);
            $file_name         = $file['name'];
            $file_name_no_ext  = $filename[0];
            $file_extension    = strtolower($filename[count($filename) - 1]);
            $file_type         = $file['type'];
            $file_size         = $file['size'];
            $fileNewName       = "$DocNumber" . "GambarKerja" . "." . $file_extension;
            $link              = $target_dir . $fileNewName;
            if (in_array($file_extension, $ok_ext)) {
                if (in_array($file_extension, $ok_ext)) {
                    $Details['GambarKerja'] = $link;
                    $koneksi->exec("INSERT INTO `dbtitemtransfile`(`DocNumber`, `File`, `Ukuran`) VALUES('$DocNumber', '$link', '$file_size')");
                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                }
            }
        }

        if (isset($_FILES["MC0"])) {
            $file       = $_FILES['MC0'];
            $original_filename = $file['name'];
            $tmp               = $file['tmp_name'];
            $filename          = explode(".", $file["name"]);
            $file_name         = $file['name'];
            $file_name_no_ext  = $filename[0];
            $file_extension    = strtolower($filename[count($filename) - 1]);
            $file_type         = $file['type'];
            $file_size         = $file['size'];
            $fileNewName       = "$DocNumber" . "MC0" . "." . $file_extension;
            $link              = $target_dir . $fileNewName;
            if (in_array($file_extension, $ok_ext)) {
                if (in_array($file_extension, $ok_ext)) {
                    $Details['MC0'] = $link;
                    $koneksi->exec("INSERT INTO `dbtitemtransfile`(`DocNumber`, `File`, `Ukuran`) VALUES('$DocNumber', '$link', '$file_size')");
                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                }
            }
        }

        if (isset($_FILES["SuratDukunganPabrikan"])) {
            $file       = $_FILES['SuratDukunganPabrikan'];
            $original_filename = $file['name'];
            $tmp               = $file['tmp_name'];
            $filename          = explode(".", $file["name"]);
            $file_name         = $file['name'];
            $file_name_no_ext  = $filename[0];
            $file_extension    = strtolower($filename[count($filename) - 1]);
            $file_type         = $file['type'];
            $file_size         = $file['size'];
            $fileNewName       = "$DocNumber" . "SuratDukunganPabrikan" . "." . $file_extension;
            $link              = $target_dir . $fileNewName;
            if (in_array($file_extension, $ok_ext)) {
                if (in_array($file_extension, $ok_ext)) {
                    $Details['SuratDukunganPabrikan'] = $link;
                    $koneksi->exec("INSERT INTO `dbtitemtransfile`(`DocNumber`, `File`, `Ukuran`) VALUES('$DocNumber', '$link', '$file_size')");
                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                }
            }
        }
        $Details = json_encode($Details);
        $koneksi->exec("UPDATE `dbtitemtrans` SET `Details` = '$Details' WHERE `DocNumber` = '$DocNumber'");
        print json_encode(["status" => "sukses", "pesan" => "Berhasil req project"]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}


function docnumber($type, $isCustom = false)
{
    include("db.php");
    $MM  = date('m');
    $YY  = substr(date('Y'), 2);
    $__lokasi = 0;
    $DOCNUMBER = "";
    if ($isCustom == false) {
        $cek = $koneksi->query("SELECT count(ID) FROM `dbsrecno` WHERE `DocType` = '$type' AND `MM` = '$MM' AND `YY` = '$YY' AND `Lokasi` = '$__lokasi'")->fetchColumn();
        if ($cek == 0) $koneksi->query("INSERT INTO `dbsrecno` (`DocType`, `YY`, `MM`, `DocNo`, `Lokasi`) VALUES ('$type', '$YY', '$MM', '0', '$__lokasi')");
        $koneksi->query("UPDATE `dbsrecno` SET `DocNo` = `DocNo` + 1 WHERE `DocType` = '$type' AND `MM` = '$MM' AND `YY` = '$YY' AND `Lokasi` = '$__lokasi'");
        $no = $koneksi->query("SELECT * FROM `dbsrecno` WHERE `DocType` = '$type' AND `MM` = '$MM' AND `YY` = '$YY' AND `Lokasi` = '$__lokasi'")->fetch();
        $DocNo = strlen($no->DocNo);
        if ($DocNo == 1) {
            $nomer = "0000$no->DocNo";
        } else if ($DocNo == 2) {
            $nomer = "000$no->DocNo";
        } else if ($DocNo == 3) {
            $nomer = "00$no->DocNo";
        } else if ($DocNo == 4) {
            $nomer = "0$no->DocNo";
        } else if ($DocNo == 5) {
            $nomer = "$no->DocNo";
        }
        $DOCNUMBER = $type . $__lokasi . $YY . $MM . $nomer;
    } else {
        $setting = $koneksi->query("SELECT `Lakukan` FROM `dbssetting` WHERE `Untuk` = '$type'")->fetch();
        $cek = $koneksi->query("SELECT count(ID) FROM `dbsrecno` WHERE `DocType` = '$setting->Lakukan' AND `MM` = '$MM' AND `YY` = '$YY' AND `Lokasi` = '$__lokasi'")->fetchColumn();
        if ($cek == 0) $koneksi->query("INSERT INTO `dbsrecno` (`DocType`, `YY`, `MM`, `DocNo`, `Lokasi`) VALUES ('$setting->Lakukan', '$YY', '$MM', '0', '$__lokasi')");
        $koneksi->query("UPDATE `dbsrecno` SET `DocNo` = `DocNo` + 1 WHERE `DocType` = '$setting->Lakukan' AND `MM` = '$MM' AND `YY` = '$YY' AND `Lokasi` = '$__lokasi' ");
        $no = $koneksi->query("SELECT * FROM `dbsrecno` WHERE `DocType` = '$setting->Lakukan' AND `MM` = '$MM' AND `YY` = '$YY' AND `Lokasi` = '$__lokasi'")->fetch();
        $DocNo = strlen($no->DocNo);
        if ($DocNo == 1) {
            $nomer = "0000$no->DocNo";
        } else if ($DocNo == 2) {
            $nomer = "000$no->DocNo";
        } else if ($DocNo == 3) {
            $nomer = "00$no->DocNo";
        } else if ($DocNo == 4) {
            $nomer = "0$no->DocNo";
        } else if ($DocNo == 5) {
            $nomer = "$no->DocNo";
        }
        $DOCNUMBER = $setting->Lakukan . $__lokasi . $YY . $MM . $nomer;
    }
    return $DOCNUMBER;
}
