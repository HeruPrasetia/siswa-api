<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'tambah':
        $UsahaID     = $_POST['UsahaID'];
        $Nama        = $_POST['Nama'];
        $Alamat      = $_POST['Alamat'];
        $Domain      = "https://apps.gijutsusoftware.com";
        $DomainKasir = "https://apps.gijutsusoftware.com";
        $Telp        = $_POST['Telp'];
        $Email       = $_POST['Email'];
        $Type        = "POS";
        $Aktif       = $_POST['Aktif'];
        $Sampai      = $_POST['Sampai'];
        $Tanggal     = DATE("Y-m-d");
        $PaymentType = $_POST['PaymentType'];
        $Reff        = $__usahaid;
        $MaxEmployee = $_POST['MaxEmployee'];
        $MaxLokasi   = $_POST['MaxLokasi'];
        $JenisUsaha  = addslashes($_POST['JenisUsaha']);
        $Status      = 1;
        $Files       = $_FILES['File'];
        if (!empty($Files)) {
            if (isset($_POST['MenuID'])) {
                $MenuID = $_POST['MenuID'];
                $NamaMenu = $_POST['NamaMenu'];
                $Menu = $_POST['Menu'];
                $cek = $koneksi->query("SELECT * FROM `dbmperusahaan` WHERE `Email` = '$Email'")->rowCount();
                if ($cek == 0) {
                    $cek = $koneksi->query("SELECT * FROM `dbmuser` WHERE `Email` = '$Email'")->rowCount();
                    if ($cek == 0) {
                        $koneksi->exec("INSERT INTO `dbmperusahaan` (`UsahaID`, `Nama`, `Alamat`, `JenisUsaha`, `Domain`, `DomainKasir`, `Telp`, `Email`, `Type`, `Aktif`, `Sampai`, `Tanggal`, `PaymentType`, `Reff`, `MaxEmployee`, `MaxLokasi`,  `Status`)
                                        VALUES ('$UsahaID', '$Nama', '$Alamat', '$JenisUsaha', '$Domain', '$DomainKasir', '$Telp', '$Email', '$Type', '$Aktif', '$Sampai', '$Tanggal', '$PaymentType', '$Reff', '$MaxEmployee', '$MaxLokasi', '$Status');");
                        $Perusahaan = $koneksi->lastInsertId();
                        $option   = ['cost' => 5];
                        $Password = password_hash("default1234", PASSWORD_DEFAULT, $option);
                        $koneksi->exec("INSERT INTO `dbmuser` (`Perusahaan`, `Nama`, `NamaBelakang`, `UserID`, `Email`, `Telp`, `Alamat`, `Password`, `Status`, `IsOwner`, `Type`)
                                        VALUES ('$Perusahaan', '$Nama', '', '1', '$Email', '$Telp', '$Alamat', '$Password', '1', '1', 'pos');");
                        $Database = "pos_$Perusahaan";
                        $koneksi->exec("INSERT INTO `dbmdatabase` (`Perusahaan`, `Host`, `User`, `Password`, `Database`, `Type`, `Tanggal`, `Status`)
                                        VALUES ('$Perusahaan', 'localhost', 'naylatools', 'N@yl4naylatools', '$Database', 'POS', '$Tanggal', '$Status');");
                        $cekDB = $koneksi->query("SHOW DATABASES LIKE '$Database';")->rowCount();
                        if ($cekDB == 0) {
                            $koneksi->exec("CREATE DATABASE `$Database`");
                            $Qpos = $koneksi->query("SHOW TABLES FROM `pos`");
                            while ($pos = $Qpos->fetch()) {
                                $tabel = $pos->Tables_in_pos;
                                $koneksi->exec("CREATE TABLE IF NOT EXISTS `$Database`.`$tabel` LIKE `pos`.`$tabel`;");
                            }

                            $sqlMenu = "DROP TEMPORARY TABLE IF EXISTS `tbMenu`;
                                        CREATE TEMPORARY TABLE `tbMenu`
                                        ";
                            foreach ($MenuID as $i => $IDMenu) {
                                $sqlMenu .= "SELECT `IdMenu`, '$NamaMenu[$i]' AS `Nama`, `Keterangan`, `File`, `Path`, `Icon`, `TableField`, `TableFieldDefault`, `TableLimit`, `Posisi`, `IsGroup`, `IsOrder`, `App`, `Status`  FROM `$Menu`.`dbsmenu` WHERE `ID` = '$IDMenu' AND `App` = 'POS'
                                            UNION ALL ";
                            }
                            $sqlMenu = rtrim($sqlMenu, "UNION ALL ");

                            $sqlMenu .= ";
                                        INSERT INTO `$Database`.`dbsmenu` (`IdMenu`, `Nama`, `Keterangan`, `File`, `Path`, `Icon`, `TableField`, `TableFieldDefault`, `TableLimit`, `Posisi`, `IsGroup`, `IsOrder`, `App`, `Status`)
                                        SELECT `IdMenu`, `Nama`, `Keterangan`, `File`, `Path`, `Icon`, `TableField`, `TableFieldDefault`, `TableLimit`, `Posisi`, `IsGroup`, `IsOrder`, `App`, `Status` FROM `tbMenu`;";
                            $koneksi->exec("#INSERT BULK UNTUK MASTER IZIN DAN CUTI DAN JABATAN
                                            INSERT INTO `$Database`.`dbmizin` (`Nama`, `Keterangan`, `Status`)
                                            SELECT `Nama`, `Keterangan`, 1 FROM `pos`.`dbmizin` WHERE `Status` = 1;
                                            
                                            INSERT INTO `$Database`.`dbmcuti` (`Nama`, `Lama`, `Keterangan`, `Status`)
                                            SELECT `Nama`, `Lama`, `Keterangan`, 1 FROM pos.`dbmcuti` WHERE `Status` = 1;
                                            
                                            INSERT INTO `$Database`.`dbmjabatan` (`Nama`, `Level`, `Keterangan`, `ApproveIzin`, `ApproveCuti`, `ApproveJamKerja`, `Status`)
                                            SELECT `Nama`, `Level`, `Keterangan`, `ApproveIzin`, `ApproveCuti`, `ApproveJamKerja`, `Status` FROM `pos`.`dbmjabatan` WHERE `Status` = 1;
                                            
                                            INSERT INTO `$Database`.`dbmjamkerja` (`Nama`, `JamMasuk`, `JamPulang`, `Lokasi`, `Status`)
                                            SELECT `Nama`, `JamMasuk`, `JamPulang`, `Lokasi`, `Status` FROM `pos`.`dbmjamkerja` WHERE `Status` = 1;

                                            INSERT INTO `$Database`.`dbmkomponengaji` (`Nama`, `Amount`, `Notes`, `FN`, `Setting`, `isAbsen`, `Status`)
                                            SELECT `Nama`, `Amount`, `Notes`, `FN`, `Setting`, `isAbsen`, `Status` FROM  `pos`.`dbmkomponengaji` WHERE Status = 1;

                                            INSERT INTO `$Database`.`dbmmarketplace` (`Jenis`, `Nama`, `Keterangan`, `Link`, `Pajak`, `Amount`, `Code`, `AkunID`, `NamaAkun`, `Status`)
                                            SELECT `Jenis`, `Nama`, `Keterangan`, `Link`, `Pajak`, `Amount`, `Code`, `AkunID`, `NamaAkun`, `Status` FROM `pos`.`dbmmarketplace` WHERE `Status` = 1;

                                            INSERT INTO `$Database`.`dbmsatuan` (`Nama`, `Status`) SELECT `Nama`, `Status` FROM `pos`.`dbmsatuan` WHERE `Status` = 1;

                                            INSERT INTO `$Database`.`dbsprinting` (`Name`, `DocType`, `IsPrinting`)
                                            SELECT `Name`, `DocType`, `IsPrinting` FROM `pos`.`dbsprinting`;

                                            INSERT INTO `$Database`.`dbssetting` (`GroupType`, `Untuk`, `Lakukan`, `Notes`)
                                            SELECT `GroupType`, `Untuk`, `Lakukan`, `Notes` FROM `dbmtemplatesetting`;

                                            #INSERT MENU
                                            $sqlMenu
                                            
                                            #RESET HAK AKSES
                                            TRUNCATE TABLE `$Database`.dbsakses;
                                            INSERT INTO `$Database`.`dbsakses` (`LokasiID`, `UserID`, `Akses`, `Tambah`,`Edit`,`Hapus`,`IsFavorit`, `TableField`, `TableLimit`) 
                                            SELECT '0', (SELECT `ID` FROM `dbmuser` WHERE `Perusahaan` = '$Perusahaan' AND `IsOwner` = 1), `Path`, '1', '1', '1', '0', `TableFieldDefault`, `TableLimit` FROM dbsmenu WHERE `Status` = 1 AND `IsGroup` = 0;

                                            #MENAMBAHN PELANGGAN DAN SUPLIER
                                            INSERT INTO `$Database`.`dbmcard` (`Jenis`, `Nama`, `Telp`, `Email`, `Alamat`, `WEB`, `Provinsi`, `NamaProvinsi`, `Kota`, `NamaKota`, `Kec`, `NamaKec`, `KodePos`, `Pwd`, `TanggalLahir`, `IsDefault`, `IsMember`, `MemberCode`, `Point`, `IsCabang`, `Lokasi`, `TimeCreated`, `Status`)
                                            VALUES 
                                            ('pelanggan', 'Pelanggan', '00000', 'pelanggan@gmail.com', '$Alamat', '', '11', 'Jawa Timur', '31', 'NamaKota', 'Sidoarjo', '456', 'Sukodono', '', CURDATE(), '1', '0', NULL, '0', NULL, '0', NOW(), '1'),  
                                            ('suplier', 'Suplier', '111111', 'suplier@gmail.com', '$Alamat', '', '11', 'Jawa Timur', '31', 'NamaKota', 'Sidoarjo', '456', 'Sukodono', '', CURDATE(), '1', '0', NULL, '0', NULL, '0', NOW(), '1');  
                                            
                                            UPDATE `$Database`.`dbmcard` SET ID = 0 WHERE Nama = 'Pelanggan';
                                            UPDATE `$Database`.`dbmcard` SET ID = 1 WHERE Nama = 'Suplier';

                                            INSERT INTO `$Database`.`dbmlokasi` (`Nama`, `Alamat`, `Telp`, `Email`, `Keterangan`, `Domain`,`IsPusat`, `IsDone`, `IsDoneNeraca`, `Status`)
                                            SELECT 'Posat', `Alamat`, `Telp`, `Email`, 'Pusat', '/', '1', '0', '0', '1' FROM `dbmperusahaan` WHERE `ID` = '$Perusahaan';
                                            
                                            UPDATE `$Database`.`dbmlokasi` SET `ID` = 0 WHERE `ID` = 1;

                                            INSERT INTO `$Database`.`dbmkaryawan` (`JoinDate`, `NIK`, `UserID`, `Type`, `Nama`, `Email`, `Telp`, `Alamat`, `Password`, `Status`)
                                            SELECT CURDATE(), CONCAT(DATE_FORMAT(CURDATE(), '%Y'),'0001'), `ID`, 'Owner', `Nama`, `Email`, `Telp`, `Alamat`, `Password`, 'Tetap' FROM dbmuser WHERE ID = (SELECT ID FROM dbmuser WHERE Perusahaan = '$Perusahaan' AND IsOwner = 1);
                                            
                                            INSERT INTO `$Database`.`dbsprofile` (`Nama`, `Alamat`, `Telp`, `Domain`, `DomainKasir`, `AkunID`, `UsahaID`, `Status`)
                                            VALUES ('$Nama', '$Alamat', '$Telp', '$Domain', '$DomainKasir', '1', '1', '1');");
                        }
                        mkdir("../../pos/assets/berkas/$Perusahaan", 0777);
                        if (isset($_FILES["File"])) {
                            $target_dir        = "file/transfer/";
                            $cekDir            = "../file/transfer/";
                            $ok_ext            = array('jpg', 'png', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xlx', 'xlsx');
                            $original_filename = $Files['name'];
                            $tmp               = $Files['tmp_name'];
                            $filename          = explode(".", $Files["name"]);
                            $file_name         = $Files['name'];
                            $file_name_no_ext  = $filename[0];
                            $file_extension    = strtolower($filename[count($filename) - 1]);
                            $file_type         = $Files['type'];
                            $file_size         = $Files['size'];
                            $fileNewName       = "$Perusahaan-$Tanggal-baru.$file_extension";
                            $link              = $target_dir . $fileNewName;
                            if (in_array($file_extension, $ok_ext)) {
                                if (in_array($file_extension, $ok_ext)) {
                                    $Harga = $_POST['Harga'];
                                    $koneksi->exec("INSERT INTO `gijutsu_admin2`.`dbtpayment` (`Jenis`, `PerusahaanID`, `NamaPerusahaan`, `Tanggal`, `Sampai`, `Harga`, `Oleh`, `Files`, `TimeCreated`)
                                                    VALUES ('Baru', '$Perusahaan', '$Nama', '$Tanggal', '$Sampai', '$Harga', '$__userid', '$link', NOW());");
                                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                                }
                            }
                        }
                        print json_encode(['status' => "sukses", "pesan" => "Berhasil Tambah Perusahaan"]);
                    } else {
                        print json_encode(['status' => "gagal", "pesan" => "Email $Email Sudah terdaftar"]);
                    }
                } else {
                    print json_encode(['status' => "gagal", "pesan" => "Email $Email Sudah terdaftar"]);
                }
            } else {
                print json_encode(['status' => "gagal", "pesan" => "Silahkan pilih menu"]);
            }
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Silahkan pilih bukti transfer"]);
        }
        break;

    case 'edit':
        $ID = addslashes($_POST['ID']);
        $UsahaID     = $_POST['UsahaID'];
        $Nama        = $_POST['Nama'];
        $Alamat      = $_POST['Alamat'];
        $Domain      = "https://apps.gijutsusoftware.com";
        $DomainKasir = "https://apps.gijutsusoftware.com";
        $Telp        = $_POST['Telp'];
        $Email       = $_POST['Email'];
        $JenisUsaha  = addslashes($_POST['JenisUsaha']);
        $Type        = "POS";
        $Reff        = $__usahaid;
        $Status      = isset($_POST['Status']) ? 1 : 0;
        $koneksi->exec("UPDATE `dbmperusahaan`
                        SET `UsahaID` = '$UsahaID',
                            `Nama` = '$Nama',
                            `Alamat` = '$Alamat',
                            `Telp` = '$Telp',
                            `Reff` = '$Reff',
                            `JenisUsaha` = '$JenisUsaha',
                            `Status` = '$Status'
                        WHERE `ID` = '$ID';");
        print json_encode(['status' => "sukses", "pesan" => 'Berhasil edit perusahaan']);
        break;

    case 'tambah menu':
        $ID = addslashes($_POST['ID']);
        $Perusahaan = addslashes($_POST['Perusahaan']);
        $Menu = $koneksi->query("SELECT * FROM dbsmenu WHERE ID = '$ID'")->fetch();
        if ($Menu) {
            $Database = $koneksi->query("SELECT * FROM `dbmdatabase` WHERE `Perusahaan` = '$Perusahaan'")->fetch();
            if ($Database) {
                $cek = $koneksi->query("SELECT * FROM `$Database->Database`.`dbsmenu` WHERE Path = '$Menu->Path'")->rowCount();
                if ($cek == 0) {
                    $koneksi->exec("INSERT INTO `$Database->Database`.`dbsmenu` (`IdMenu`, `Nama`, `Keterangan`, `File`, `Path`, `Icon`, `TableField`, `TableFieldDefault`, `TableLimit`, `Posisi`, `IsGroup`, `IsOrder`, `App`, `Status`)
                                    SELECT `IdMenu`, `Nama`, `Keterangan`, `File`, `Path`, `Icon`, `TableField`, `TableFieldDefault`, `TableLimit`, `Posisi`, `IsGroup`, `IsOrder`, `App`, `Status` FROM dbsmenu WHERE ID = '$ID'");
                    $data = $koneksi->query("SELECT * FROM `$Database->Database`.dbsmenu WHERE Status = 1")->fetchAll();
                    print json_encode(['status' => "sukses", "pesan" => "Berhasil menambahkan menu $Menu->Nama", "data" => $data]);
                } else {
                    print json_encode(['status' => "gagal", "pesan" => "Menu $Menu->Nama Sudah ada"]);
                }
            } else {
                print json_encode(['status' => "gagal", "pesan" => "Database tidak ditemukan"]);
            }
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Menu tidak ditemukan"]);
        }
        break;

    case 'hapus menu':
        $ID = addslashes($_POST['ID']);
        $Perusahaan = addslashes($_POST['Perusahaan']);
        $Database = $koneksi->query("SELECT * FROM `dbmdatabase` WHERE `Perusahaan` = '$Perusahaan'")->fetch();
        if ($Database) {
            $koneksi->exec("DELETE FROM `$Database->Database`.`dbsmenu` WHERE `ID` = '$ID'");
            $data = $koneksi->query("SELECT * FROM `$Database->Database`.`dbsmenu` WHERE `Status` = 1")->fetchAll();
            print json_encode(['status' => "sukses", "pesan" => "Berhasil hapus menu", "data" => $data]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Database tidak ditemukan"]);
        }
        break;

    case 'tambah bimbingan':
        $Perusahaan = addslashes($_POST['Perusahaan']);
        $Status     = addslashes($_POST['Status']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Tanggal    = $_POST['Tanggal'];
        $koneksi->exec("UPDATE `dbmperusahaan` SET `LastTraining` = '$Tanggal', `TrainingResult` = '$Status' WHERE `ID` = '$Perusahaan'");
        $koneksi->exec("INSERT INTO `master2`.`dbtbimbingan` ( `Perusahaan`, `Tanggal`, `Keteranngan`, `Status`, `TimeCreated`)
                        VALUES ( '$Perusahaan', '$Tanggal', '$Keterangan', '$Status', NOW());");
        print json_encode(['status' => "sukses", "pesan" => "Bimbingan berhasil dibuat"]);
        break;

    case 'proses':
        $Jenis = $_POST['Jenis'];
        $ID = addslashes($_POST['ID']);
        $Data = $koneksi->query("SELECT * FROM `dbmperusahaan` WHERE `ID` = '$ID'")->fetch();
        if ($Jenis == "Perpanjang") {
            $Tanggal  = $_POST['Tanggal'];
            $Sampai = $_POST['Sampai'];
            $PaymentType = $_POST['PaymentType'];
            $Harga = $_POST['Harga'];
            $Files = $_FILES['File'];
            if (!empty($Files)) {
                $target_dir        = "file/transfer/";
                $cekDir            = "../file/transfer/";
                $ok_ext            = array('jpg', 'png', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xlx', 'xlsx');
                $original_filename = $Files['name'];
                $tmp               = $Files['tmp_name'];
                $filename          = explode(".", $Files["name"]);
                $file_name         = $Files['name'];
                $file_name_no_ext  = $filename[0];
                $file_extension    = strtolower($filename[count($filename) - 1]);
                $file_type         = $Files['type'];
                $file_size         = $Files['size'];
                $fileNewName       = "$ID-$Tanggal-perpanjang.$file_extension";
                $link              = $target_dir . $fileNewName;
                if (in_array($file_extension, $ok_ext)) {
                    $koneksi->exec("UPDATE `dbmperusahaan` SET `Sampai` = '$Sampai', `Aktif` = '$Tanggal', `PaymentType` = '$PaymentType', `Status` = 1 WHERE `ID` = '$ID'");
                    $koneksi->exec("INSERT INTO `gijutsu_admin2`.`dbtpayment` (`Jenis`, `PerusahaanID`, `NamaPerusahaan`, `Tanggal`, `Sampai`, `Harga`, `Oleh`, `Files`, `TimeCreated`)
                                    VALUES ('$Jenis', '$ID', '$$Data->Nama', '$Tanggal', '$Sampai', '$Harga', '$__userid', '$link', NOW());");
                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                    print json_encode(['status' => "sukses", "pesan" => "Proses $Jenis Berhasil"]);
                } else {
                    print json_encode(['status' => "sukses", "pesan" => "Jenis file tidak sesuai aturan"]);
                }
            } else {
                print json_encode(['status' => "gagal", "pesan" => "Silahkan pilih bukti pembayaran"]);
            }
        } else {
            $Harga = $_POST['Harga'];
            $Files = $_FILES['File'];
            $MaxEmployee = $_POST['MaxEmployee'];
            $MaxLokasi = $_POST['MaxLokasi'];
            if (!empty($Files)) {
                $target_dir        = "file/transfer/";
                $cekDir            = "../file/transfer/";
                $ok_ext            = array('jpg', 'png', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xlx', 'xlsx');
                $original_filename = $Files['name'];
                $tmp               = $Files['tmp_name'];
                $filename          = explode(".", $Files["name"]);
                $file_name         = $Files['name'];
                $file_name_no_ext  = $filename[0];
                $file_extension    = strtolower($filename[count($filename) - 1]);
                $file_type         = $Files['type'];
                $file_size         = $Files['size'];
                $Tanggal           = date("Y-m-d");
                $fileNewName       = "$ID-$Tanggal-perpanjang.$file_extension";
                $link              = $target_dir . $fileNewName;
                if (in_array($file_extension, $ok_ext)) {
                    $koneksi->exec("UPDATE `dbmperusahaan` SET `MaxEmployee` = '$MaxEmployee', `MaxLokasi` = '$MaxLokasi' WHERE `ID` = '$ID'");
                    $koneksi->exec("INSERT INTO `gijutsu_admin2`.`dbtpayment` (`Jenis`, `PerusahaanID`, `NamaPerusahaan`, `Tanggal`, `Sampai`, `Harga`, `Oleh`, `Files`, `TimeCreated`)
                                            VALUES ('$Jenis', '$ID', '$Data->Nama', CURDATE(), CURDATE(), '$Harga', '$__userid', '$link', NOW());");
                    move_uploaded_file($tmp, $cekDir . $fileNewName);
                    print json_encode(['status' => "sukses", "pesan" => "Proses $Jenis Berhasil"]);
                } else {
                    print json_encode(['status' => "sukses", "pesan" => "Jenis file tidak sesuai aturan"]);
                }
            } else {
                print json_encode(['status' => "gagal", "pesan" => "Silahkan pilih bukti pembayaran"]);
            }
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
