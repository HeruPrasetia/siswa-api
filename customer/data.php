<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data profile':
        $data = $koneksi->query("SELECT * FROM `dbsprofile`")->fetch();
        print json_encode(["status" => "sukses", "profile" => $data]);
        break;

    case 'data arap':
        $s    = $_POST['order'];
        $b    = $_POST['by'];
        $l    = 100;
        $p    = $_POST['page'];
        $m    = ($p > 1) ? ($p * $l) - $l : 0;
        $j    = $koneksi->query("SELECT `ID` FROM `dbmhutangpiutang` WHERE `CardID` = '$__userid' ")->rowCount();
        $data = $koneksi->query("SELECT * FROM `dbmhutangpiutang` WHERE `CardID` = '$__userid' ORDER BY $s $b LIMIT $m, $l")->fetchAll();

        print json_encode(['status' => "sukses", "data" => $data, "j" => $j]);
        break;

    case 'detail arap':
        $DocNumber = addslashes($_POST['DocNumber']);
        $data = $koneksi->query("SELECT * FROM `dbtpayment` WHERE `ReffDocNumber` = '$DocNumber'")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data]);
        break;

    case "data penagihan":
        $data = $koneksi->query("SELECT a.* FROM `dbtaraplist` a INNER JOIN `dbmhutangpiutang` b ON a.`DocNumber` = b.`DocNumber` WHERE b.`CardID` = '$__userid'")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data]);
        break;

    case "data sales":
        $D1 = $_POST['D1'];
        $D2 = $_POST['D2'];
        $data = $koneksi->query("SELECT * FROM `dbtitemtrans` WHERE `CardID` = '$__userid' AND `DocType` = 'SALES' AND `Status` = 1 AND `DocDate` BETWEEN '$D1' AND '$D2'")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data]);
        break;


    case "detail sales":
        $DocNumber = addslashes(($_POST['DocNumber']));
        $data = $koneksi->query("SELECT * FROM `dbtitemtransdetail` WHERE `DocNumber` = '$DocNumber'")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data]);
        break;

    case 'data trans project':
        $data = $koneksi->query("SELECT * FROM `dbtitemtrans` WHERE `DocType` = 'PROJK' AND `CardID` = '$__userid'")->fetchAll();
        if ($data) {
            print json_encode(["status" => "sukses", "data" => $data]);
        } else {
            print json_encode(["status" => "gagal", "pesan" => "data tidak ditemukan"]);
        }
        break;

    case 'data kunjungan':
        $data = $koneksi->query("SELECT a.*, b.`Tanggal`, b.`Notes`, c.`Nama`
                                 FROM `dbmjadwalkunjungandetail`  a
                                 INNER JOIN `dbmjadwalkunjungan` b ON a.`DocID` = b.`ID`
                                 LEFT JOIN `dbmkaryawan` c ON b.`KaryawanID` = c.`UserID`
                                 WHERE `CardID` = '$__userid'")->fetchAll();
        print json_encode(["status" => "sukses", "data" => $data]);
        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
