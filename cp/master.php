<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data admin':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmadmin` WHERE `Status` = 1 AND `Nama` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT * FROM `dbmadmin` WHERE `Status` = 1 AND `Nama` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'tambah admin':
        $Nama  = addslashes($_POST['Nama']);
        $Email = addslashes($_POST['Email']);
        $cek   = $koneksi->query("SELECT * FROM `dbapis`.`dbmuser` WHERE `Email` = '$Email'")->rowCount();
        if ($cek == 0) {
            $Telp     = addslashes($_POST['Telp']);
            $Pwd      = addslashes($_POST['Pwd']);
            $option   = ['cost' => 5];
            $password = password_hash("$Pwd", PASSWORD_DEFAULT, $option);
            $Status   = isset($_POST['Status']) ? 1 : 0;
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("INSERT INTO `dbapis`.`dbmuser` (`PerusahaanID`, `Nama`, `Email`, `Pwd`, `Status`)
                                VALUES ('$__usahaid', '$Nama', '$Email', '$password', '$Status');");
                $UserID = $koneksi->lastInsertId();
                $koneksi->exec("INSERT INTO `dbmadmin` (`UserID`, `Nama`, `Email`, `Pwd`, `Telp`, `Status`)
                                VALUES ('$UserID', '$Nama', '$Email', '$password', '$Telp', '$Status');");
                print json_encode(["status" => "sukses", "pesan" => "Tambah admin berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Email $Email Sudah terdaftar"]);
        }
        break;

    case 'edit admin':
        $ID    = addslashes($_POST['ID']);
        $Nama  = addslashes($_POST['Nama']);
        $Email = addslashes($_POST['Email']);
        $Admin = $koneksi->query("SELECT * FROM `dbmadmin` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Admin)) {
            $cek   = $koneksi->query("SELECT * FROM `dbapis`.`dbmuser` WHERE `Email` = '$Email' AND `ID` <> '$Admin->UserID'")->rowCount();
            if ($cek == 0) {
                $Telp   = addslashes($_POST['Telp']);
                $Status = isset($_POST['Status']) ? 1 : 0;
                try {
                    $koneksi->beginTransaction();
                    $koneksi->exec("UPDATE `dbapis`.`dbmuser` SET `Nama` = '$Nama', `Email` = '$Email', `Telp` = '$Telp' WHERE `ID` = '$Admin->UserID'");
                    $koneksi->exec("UPDATE `dbmadmin` SET `Nama` = '$Nama', `Email` = '$Email', `Telp` = '$Telp' WHERE `ID` = '$ID'");
                    print json_encode(["status" => "sukses", "pesan" => "Edit admin berhasil"]);
                    $koneksi->commit();
                } catch (PDOException $e) {
                    print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
                }
            } else {
                print json_encode(["status" => "gagal", "pesan" => "Email $Email Sudah terdaftar"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Admin tidak ditemukan"]);
        }
        break;

    case 'data hak akses':
        $ID = addslashes($_POST['ID']);
        $Menu = $koneksi->query("SELECT Menu FROM `dbapis`.`dbmservice` WHERE `ID` = '$__serviceid'")->fetch();
        $Akses = $koneksi->query("SELECT Menu FROM `dbapis`.`dbsakses` WHERE `UserID` = '$ID' AND `ServiceID` = '$__serviceid'")->fetch();
        print json_encode(["status" => "sukses", "menu" => $Menu->Menu, "akses" => $Akses->Menu]);
        break;

    case 'edit hak akses':
        $UserID = $_POST['UserID'];
        $Menu = $_POST['Menu'];
        try {
            $koneksi->beginTransaction();
            $Akses = $koneksi->query("SELECT Menu FROM `dbapis`.`dbsakses` WHERE `UserID` = '$UserID' AND `ServiceID` = '$__serviceid'")->fetch();
            if (!is_bool($Akses)) {
                $koneksi->exec("UPDATE `dbapis`.`dbsakses` SET `Menu` = '$Menu' WHERE `UserID` = '$UserID' AND `ServiceID` = '$__serviceid'");
            } else {
                $koneksi->exec("INSERT INTO `dbapis`.`dbsakses` (`UserID`, `ServiceID`, `Menu`, `Status`)
                                    VALUES ('$UserID', '$__serviceid', '$Menu', '1');");
            }
            print json_encode(["status" => "sukses", "pesan" => "Edit Hak Akses Berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus admin':
        $ID    = addslashes($_POST['ID']);
        $cek   = $koneksi->query("SELECT * FROM `dbapis`.`dbmuser` WHERE `ID` = $ID")->rowCount();
        if ($cek > 0) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbapis`.`dbmuser` WHERE `ID` = $ID");
                $koneksi->exec("DELETE FROM `dbmadmin` WHERE `UserID` = '$ID'");
                print json_encode(["status" => "sukses", "pesan" => "Hapus admin berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "User tidak ditemukan"]);
        }
        break;




    case 'data guru':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmguru` WHERE `NamaDepan` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT *, concat(NamaDepan, ' ', NamaBelakang) AS `Nama` FROM `dbmguru` WHERE `NamaDepan` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'tambah guru':
        $NIP          = addslashes($_POST['NIP']);
        $NIK          = addslashes($_POST['NIK']);
        $NamaDepan    = addslashes($_POST['NamaDepan']);
        $NamaBelakang = addslashes($_POST['NamaBelakang']);
        $TanggalLahir = addslashes($_POST['TanggalLahir']);
        $TempatLahir  = addslashes($_POST['TempatLahir']);
        $Alamat       = addslashes($_POST['Alamat']);
        $Telp         = addslashes($_POST['Telp']);
        $Email        = addslashes($_POST['Email']);
        $Pendidikan   = addslashes($_POST['Pendidikan']);
        $StatusKepegawaian = addslashes($_POST['StatusKepegawaian']);
        $Foto         = $_POST['Foto'];
        $Status       = isset($_POST['Status']) ? "Aktif" : "Keluar";
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmguru` (`NIP`, `NIK`, `NamaDepan`, `NamaBelakang`, `TanggalLahir`, `TempatLahir`, `Alamat`, `Telp`, `Email`, `Pendidikan`, `StatusKepegawaian`, `Foto`, `Status`)
                            VALUES ('$NIP', '$NIK', '$NamaDepan', '$NamaBelakang', '$TanggalLahir', '$TempatLahir', '$Alamat', '$Telp', '$Email', '$Pendidikan', '$StatusKepegawaian', '$Foto', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Guru berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit guru':
        $ID           = addslashes($_POST['ID']);
        $NIP          = addslashes($_POST['NIP']);
        $NIK          = addslashes($_POST['NIK']);
        $NamaDepan    = addslashes($_POST['NamaDepan']);
        $NamaBelakang = addslashes($_POST['NamaBelakang']);
        $TanggalLahir = addslashes($_POST['TanggalLahir']);
        $TempatLahir  = addslashes($_POST['TempatLahir']);
        $Alamat       = addslashes($_POST['Alamat']);
        $Telp         = addslashes($_POST['Telp']);
        $Email        = addslashes($_POST['Email']);
        $Pendidikan   = addslashes($_POST['Pendidikan']);
        $StatusKepegawaian = addslashes($_POST['StatusKepegawaian']);
        $Foto         = $_POST['Foto'];
        $Status       = isset($_POST['Status']) ? "Aktif" : "Keluar";
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmguru`
                            SET `NIP` = '$NIP',
                                `NIK` = '$NIK',
                                `NamaDepan` = '$NamaDepan',
                                `NamaBelakang` = '$NamaBelakang',
                                `TanggalLahir` = '$TanggalLahir',
                                `TempatLahir` = '$TempatLahir',
                                `Alamat` = '$Alamat',
                                `Telp` = '$Telp',
                                `Email` = '$Email',
                                `Pendidikan` = '$Pendidikan',
                                `StatusKepegawaian` = '$StatusKepegawaian',
                                `Foto` = '$Foto',
                                `Status` = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Edit Guru berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus guru':
        $ID = addslashes($_POST['ID']);
        $Guru = $koneksi->query("SELECT * FROM `dbmguru` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Guru)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbmguru` WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "Edit Guru berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data guru tidak ditemukan"]);
        }
        break;




    case 'data wali murid':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmwalimurid` WHERE `NamaDepan` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT *, concat(NamaDepan, ' ', NamaBelakang) AS `Nama` FROM `dbmwalimurid` WHERE `NamaDepan` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'tambah wali murid':
        $NIK          = addslashes($_POST['NIK']);
        $NamaDepan    = addslashes($_POST['NamaDepan']);
        $NamaBelakang = addslashes($_POST['NamaBelakang']);
        $TanggalLahir = addslashes($_POST['TanggalLahir']);
        $TempatLahir  = addslashes($_POST['TempatLahir']);
        $Hubungan     = addslashes($_POST['Hubungan']);
        $JenisKelamin = addslashes($_POST['JenisKelamin']);
        $Telp         = addslashes($_POST['Telp']);
        $Email        = addslashes($_POST['Email']);
        $Alamat       = addslashes($_POST['Alamat']);
        $Pendidikan   = addslashes($_POST['Pendidikan']);
        $Pekerjaan    = addslashes($_POST['Pekerjaan']);
        $Status       = isset($_POST['Status']) ? 1 : 0;

        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmwalimurid` (`NIK`, `NamaDepan`, `NamaBelakang`, `TanggalLahir`, `TempatLahir`, `Hubungan`, `JenisKelamin`, `Telp`, `Email`, `Alamat`, `Pendidikan`, `Pekerjaan`, `Status`)
                            VALUES ('$NIK', '$NamaDepan', '$NamaBelakang', '$TanggalLahir', '$TempatLahir', '$Hubungan', '$JenisKelamin', '$Telp', '$Email', '$Alamat', '$Pendidikan', '$Pekerjaan', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Wali Murid berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit wali murid':
        $ID           = addslashes($_POST['ID']);
        $NIK          = addslashes($_POST['NIK']);
        $NamaDepan    = addslashes($_POST['NamaDepan']);
        $NamaBelakang = addslashes($_POST['NamaBelakang']);
        $TanggalLahir = addslashes($_POST['TanggalLahir']);
        $TempatLahir  = addslashes($_POST['TempatLahir']);
        $Hubungan     = addslashes($_POST['Hubungan']);
        $JenisKelamin = addslashes($_POST['JenisKelamin']);
        $Telp         = addslashes($_POST['Telp']);
        $Email        = addslashes($_POST['Email']);
        $Alamat       = addslashes($_POST['Alamat']);
        $Pendidikan   = addslashes($_POST['Pendidikan']);
        $Pekerjaan    = addslashes($_POST['Pekerjaan']);
        $Status       = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmwalimurid`
                            SET `NIK`           = '$NIK',
                                `NamaDepan`     = '$NamaDepan',
                                `NamaBelakang`  = '$NamaBelakang',
                                `TanggalLahir`  = '$TanggalLahir',
                                `TempatLahir`   = '$TempatLahir',
                                `Hubungan`      = '$Hubungan',
                                `JenisKelamin`  = '$JenisKelamin',
                                `Telp`          = '$Telp',
                                `Email`         = '$Email',
                                `Alamat`        = '$Alamat',
                                `Pendidikan`    = '$Pendidikan',
                                `Pekerjaan`     = '$Pekerjaan',
                                `Status`        = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Edit Wali Murid berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus wali murid':
        $ID = addslashes($_POST['ID']);
        $Guru = $koneksi->query("SELECT * FROM `dbmwalimurid` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Guru)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbmwalimurid` WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "Edit Guru berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data guru tidak ditemukan"]);
        }
        break;




    case 'data murid':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmmurid` WHERE `NamaDepan` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT *, concat(NamaDepan, ' ', NamaBelakang) AS `Nama` FROM `dbmmurid` WHERE `NamaDepan` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'detail murid':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmmurid` WHERE `ID` = '$ID'")->fetch();
        $wali = $koneksi->query("SELECT * FROM `dbmwalimurid` WHERE `Status` = 1")->fetchAll();
        $kelas = $koneksi->query("SELECT * FROM `dbmkelas` WHERE `Status` = 1")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data, "wali" => $wali, "kelas" => $kelas]);
        break;

    case 'tambah murid':
        $NIS          = addslashes($_POST['NIS']);
        $NamaDepan    = addslashes($_POST['NamaDepan']);
        $NamaBelakang = addslashes($_POST['NamaBelakang']);
        $Telp         = addslashes($_POST['Telp']);
        $Email        = addslashes($_POST['Email']);
        $TanggalLahir = addslashes($_POST['TanggalLahir']);
        $TempatLahir  = addslashes($_POST['TempatLahir']);
        $JenisKelamin = addslashes($_POST['JenisKelamin']);
        $Alamat       = addslashes($_POST['Alamat']);
        $Foto         = addslashes($_POST['Foto']);
        $TanggalMasuk = addslashes($_POST['TanggalMasuk']);
        $TanggalLulus = addslashes($_POST['TanggalLulus']);
        $KelasID      = addslashes($_POST['KelasID']);
        $WaliID       = addslashes($_POST['WaliID']);
        $Status       = addslashes($_POST['Status']);
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmmurid` (`NIS`, `NamaDepan`, `NamaBelakang`, `Telp`, `Email`, `TanggalLahir`, `TempatLahir`, `JenisKelamin`, `Alamat`, `Foto`, `TanggalMasuk`, `TanggalLulus`, `KelasID`, `WaliID`, `Status`)
                            VALUES ('$NIS', '$NamaDepan', '$NamaBelakang', '$Telp', '$Email', '$TanggalLahir', '$TempatLahir', '$JenisKelamin', '$Alamat', '$Foto', '$TanggalMasuk', '$TanggalLulus', '$KelasID', '$WaliID', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Murid berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit murid':
        $ID           = addslashes($_POST['ID']);
        $NIS          = addslashes($_POST['NIS']);
        $NamaDepan    = addslashes($_POST['NamaDepan']);
        $NamaBelakang = addslashes($_POST['NamaBelakang']);
        $Telp         = addslashes($_POST['Telp']);
        $Email        = addslashes($_POST['Email']);
        $TanggalLahir = addslashes($_POST['TanggalLahir']);
        $TempatLahir  = addslashes($_POST['TempatLahir']);
        $JenisKelamin = addslashes($_POST['JenisKelamin']);
        $Alamat       = addslashes($_POST['Alamat']);
        $Foto         = addslashes($_POST['Foto']);
        $TanggalMasuk = addslashes($_POST['TanggalMasuk']);
        $TanggalLulus = addslashes($_POST['TanggalLulus']);
        $KelasID      = addslashes($_POST['KelasID']);
        $WaliID       = addslashes($_POST['WaliID']);
        $Status       = addslashes($_POST['Status']);
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmmurid`
                            SET `NIS` = '$NIS',
                                `NamaDepan` = '$NamaDepan',
                                `NamaBelakang` = '$NamaBelakang',
                                `Telp` = '$Telp',
                                `Email` = '$Email',
                                `TanggalLahir` = '$TanggalLahir',
                                `TempatLahir` = '$TempatLahir',
                                `JenisKelamin` = '$JenisKelamin',
                                `Alamat` = '$Alamat',
                                `Foto` = '$Foto',
                                `TanggalMasuk` = '$TanggalMasuk',
                                `TanggalLulus` = '$TanggalLulus',
                                `KelasID` = '$KelasID',
                                `WaliID` = '$WaliID',
                                `Status` = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Edit Murid berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus murid':
        $ID = addslashes($_POST['ID']);
        $Guru = $koneksi->query("SELECT * FROM `dbmmurid` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Guru)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("DELETE FROM `dbmmurid` WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "hapus murid berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data guru tidak ditemukan"]);
        }
        break;



    case 'data kelas':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmkelas` WHERE `Status` = 1 AND `Nama` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT a.*, CONCAT(b.NamaDepan, ' ', b.NamaBelakang) AS `WaliKelas` FROM `dbmkelas` a LEFT JOIN dbmguru b ON a.`GuruID` = b.`ID` WHERE a.`Status` = 1 AND a.`Nama` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'detail kelas':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmkelas` WHERE `ID` = '$ID'")->fetch();
        $guru = $koneksi->query("SELECT CONCAT(NamaDepan, ' ', NamaBelakang ) AS Nama, ID FROM `dbmguru` WHERE `Status` = 1")->fetchAll();
        $jurusan = $koneksi->query("SELECT * FROM `dbmjurusan` WHERE `Status` = 1")->fetchAll();
        $detail = $koneksi->query("SELECT * FROM `dbmkelasdetail` WHERE `KelasID` = '$ID'")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data, "guru" => $guru, "jurusan" => $jurusan, "detail" => $detail]);
        break;

    case 'tambah kelas':
        $Nama       = addslashes($_POST['Nama']);
        $Kode       = addslashes($_POST['Kode']);
        $Level      = addslashes($_POST['Level']);
        $GuruID     = addslashes($_POST['GuruID']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Jurusan    = addslashes($_POST['Jurusan']);
        $Status     = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmkelas` (`Nama`, `Kode`, `Level`, `GuruID`, `Keterangan`, `Jurusan`, `Status`)
                            VALUES ('$Nama', '$Kode', '$Level', '$GuruID', '$Keterangan', '$Jurusan', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Kelas berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit kelas':
        $ID         = addslashes($_POST['ID']);
        $Kode       = addslashes($_POST['Kode']);
        $Nama       = addslashes($_POST['Nama']);
        $Level      = addslashes($_POST['Level']);
        $GuruID     = addslashes($_POST['GuruID']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Jurusan    = addslashes($_POST['Jurusan']);
        $Status     = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmkelas`
                            SET `Nama`      = '$Nama',
                                `Kode`      = '$Kode',
                                `Level`     = '$Level',
                                `GuruID`    = '$GuruID',
                                `Keterangan`= '$Keterangan',
                                `Jurusan`   = '$Jurusan',
                                `Status`    = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Edit kelas berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus kelas':
        $ID = addslashes($_POST['ID']);
        $Guru = $koneksi->query("SELECT * FROM `dbmkelas` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Guru)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("UPDATE`dbmkelas` SET `Status` = 0 WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "Hapus Kelas berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data kelas tidak ditemukan"]);
        }
        break;





    case 'data jurusan':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmjurusan` WHERE `Status` = 1 AND `Nama` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT a.*, CONCAT(b.`NamaDepan`, ' ', b.`NamaBelakang`) AS `KepalaJurusan` FROM `dbmjurusan` a LEFT JOIN `dbmguru` b ON a.`GuruID` = b.`ID`  
                                 WHERE a.`Nama` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'tambah jurusan':
        $Kode       = addslashes($_POST['Kode']);
        $GuruID     = addslashes($_POST['GuruID']);
        $Nama       = addslashes($_POST['Nama']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Status     = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmjurusan` (`Kode`, `GuruID`, `Nama`, `Keterangan`, `Status`)
                            VALUES ('$Kode', '$GuruID', '$Nama', '$Keterangan', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Jurusan berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'detail jurusan':
        $ID = addslashes($_POST['ID']);
        $data = $koneksi->query("SELECT * FROM `dbmjurusan` WHERE `ID` = '$ID'")->fetch();
        $guru = $koneksi->query("SELECT CONCAT(NamaDepan, ' ', NamaBelakang ) AS Nama, ID FROM `dbmguru` WHERE `Status` = 1")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data, "guru" => $guru]);
        break;

    case 'edit jurusan':
        $ID         = addslashes($_POST['ID']);
        $Kode       = addslashes($_POST['Kode']);
        $GuruID     = addslashes($_POST['GuruID']);
        $Nama       = addslashes($_POST['Nama']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Status     = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmjurusan`
                            SET `Nama`       = '$Nama',
                                `Kode`       = '$Kode',
                                `GuruID`     = '$GuruID',
                                `Keterangan` = '$Keterangan',
                                `Status`     = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Edit Jurusan berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus jurusan':
        $ID = addslashes($_POST['ID']);
        $Guru = $koneksi->query("SELECT * FROM `dbmjurusan` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Guru)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("UPDATE`dbmjurusan` SET `Status` = 0 WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "Hapus jurusan berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data jurusan tidak ditemukan"]);
        }
        break;




    case 'data mata pelajaran':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $q    = addslashes($_POST['q']);
        $t    = $koneksi->query("SELECT `ID` FROM `dbmpelajaran` WHERE `Status` <> 3 AND `Nama` LIKE '%$q%' ")->rowCount();
        $j    = ceil($t / $l);
        $data = $koneksi->query("SELECT * FROM `dbmpelajaran` WHERE `Status` <> 3 AND `Nama` LIKE '%$q%' ORDER BY $s $b LIMIT $m, $l")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data, "j" => $j, "m" => $m]);
        break;

    case 'tambah mata pelajaran':
        $Kode       = addslashes($_POST['Kode']);
        $Nama       = addslashes($_POST['Nama']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Sks        = addslashes($_POST['Sks']);
        $IsWajib    = isset($_POST['IsWajib']) ? 1 : 0;
        $Status     = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("INSERT INTO `dbmpelajaran` (`Kode`, `Nama`, `Keterangan`, `Sks`, `IsWajib`, `Status`)
                            VALUES ('$Kode', '$Nama', '$Keterangan', '$Sks', '$IsWajib', '$Status');");
            print json_encode(["status" => "sukses", "pesan" => "Tambah Mata Pelajaran berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'edit mata pelajaran':
        $ID         = addslashes($_POST['ID']);
        $Kode       = addslashes($_POST['Kode']);
        $Nama       = addslashes($_POST['Nama']);
        $Keterangan = addslashes($_POST['Keterangan']);
        $Sks        = addslashes($_POST['Sks']);
        $IsWajib    = isset($_POST['IsWajib']) ? 1 : 0;
        $Status     = isset($_POST['Status']) ? 1 : 0;
        try {
            $koneksi->beginTransaction();
            $koneksi->exec("UPDATE `dbmpelajaran`
                            SET `Nama`       = '$Nama',
                                `Kode`       = '$Kode',
                                `Keterangan` = '$Keterangan',
                                `Sks`        = '$Sks',
                                `IsWajib`    = '$IsWajib',
                                `Status`     = '$Status'
                            WHERE `ID` = '$ID';");
            print json_encode(["status" => "sukses", "pesan" => "Edit Mata Pelajaran berhasil"]);
            $koneksi->commit();
        } catch (PDOException $e) {
            print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
        }
        break;

    case 'hapus mata pelajaran':
        $ID = addslashes($_POST['ID']);
        $Guru = $koneksi->query("SELECT * FROM `dbmpelajaran` WHERE `ID` = '$ID'")->fetch();
        if (!is_bool($Guru)) {
            try {
                $koneksi->beginTransaction();
                $koneksi->exec("UPDATE `dbmpelajaran` SET `Status` = 3 WHERE `ID` = '$ID' ");
                print json_encode(["status" => "sukses", "pesan" => "Hapus jurusan berhasil"]);
                $koneksi->commit();
            } catch (PDOException $e) {
                print json_encode(["status" => "gagal", "pesan" => "Proses Gagal $e"]);
            }
        } else {
            print json_encode(["status" => "gagal", "pesan" => "Data jurusan tidak ditemukan"]);
        }
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
