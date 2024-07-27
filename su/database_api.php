<?php
include "db.php";
$act = addslashes($_POST['act']);

switch ($act) {
    case 'data':
        $q = addslashes($_POST['q']);
        $data = $koneksi->query("SELECT a.*, b.`Nama`, IF(a.`Status` = 1, 'Aktif', 'Tidak Aktif') AS `SSTS`, b.`DBSize` FROM `dbmdatabase` a 
                                 INNER JOIN `dbmperusahaan` b ON a.`Perusahaan` = b.`ID` WHERE a.`Database` LIKE '%$q%'")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data database':
        $koneksi->exec("DROP TEMPORARY TABLE IF EXISTS tbDatabase;
                        CREATE TEMPORARY TABLE tbDatabase
                        SELECT table_schema AS 'Nama', 0 AS 'Ukuran'
                        FROM information_schema.tables
                        GROUP BY table_schema;");
        $data = $koneksi->query("SELECT a.*, b.`Nama`, '' AS `Link`, c.`Ukuran` FROM `dbmdatabase` a 
                                 INNER JOIN `dbmperusahaan` b ON a.`Perusahaan` = b.`ID` 
                                 INNER JOIN `tbDatabase` c ON a.`Database` = c.`Nama`")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'data database backup':
        $koneksi->exec("DROP TEMPORARY TABLE IF EXISTS tbDatabase;
  
                        CREATE TEMPORARY TABLE tbDatabase AS
                        SELECT a.table_schema AS Nama, 
                                COALESCE(ROUND(SUM(a.data_length + a.index_length) / 1024 / 1024, 2), 0) AS Ukuran, 
                                b.Perusahaan
                        FROM information_schema.tables a
                        INNER JOIN dbmdatabase b ON a.table_schema = b.Database
                        GROUP BY a.table_schema;

                        UPDATE dbmperusahaan a 
                        INNER JOIN tbDatabase b ON a.ID = b.Perusahaan 
                        SET a.DBSize = b.Ukuran;
                        
                        DROP TEMPORARY TABLE IF EXISTS tbDatabase;
                        CREATE TEMPORARY TABLE tbDatabase
                        SELECT table_schema AS 'Nama' FROM information_schema.tables GROUP BY table_schema;");
        $data = $koneksi->query("SELECT a.`Database`, b.`Nama`, '' AS `Link` FROM `dbmdatabase` a 
                                 INNER JOIN `dbmperusahaan` b ON a.`Perusahaan` = b.`ID` 
                                 INNER JOIN `tbDatabase` c ON a.`Database` = c.`Nama` 
                                 WHERE b.`Status` = 1
                                 UNION ALL
                                 SELECT 'pos', 'System', ''
                                 UNION ALL
                                 SELECT 'master2', 'System', ''")->fetchAll();
        print json_encode(['status' => "sukses", "data" => $data]);
        break;

    case 'backup db':
        $Database = $_POST['Database'];
        $backup_file = "../files/" . $Database . date("Y-m-d") . '.sql';
        $command = "mysqldump --host=localhost --user=naylatools --password=N@yl4naylatools $Database > $backup_file";
        exec($command, $output, $return_var);
        ######### UNTUK SISMPAN NERACA ##############
        if ($Database != "master2" || $Database != "pos") {
            $Period = "";
            $Lokasi = 0;
            $sql = "";
            if ($Period == "") {
                $cekPeriod = $koneksi->query("SELECT * FROM `$Database`.`dbmperiod`")->rowCount();
                if ($cekPeriod > 0) {
                    $QPeriod = $koneksi->query("SELECT * FROM `$Database`.`dbmperiod` WHERE `Status` = 1 AND `Lokasi` = '$Lokasi'")->fetch();
                    if ($QPeriod->IsAwal == 0) {
                        $akunLaba = $koneksi->query("SELECT a.`Lakukan`, b.`Description` AS `NamaAkun` FROM `$Database`.`dbssetting` a LEFT JOIN `$Database`.`dbmakun` b ON a.`Lakukan` = b.`Code` WHERE a.`Untuk` = 'AkunLaba'")->fetch();

                        $ssql = "#UPDATE 
                        SET @StartDate      = '$QPeriod->StartDate' COLLATE utf8mb4_general_ci;
                        SET @EndDate        = '$QPeriod->EndDate' COLLATE utf8mb4_general_ci;
                        SET @PeriodID       = '$QPeriod->ID' COLLATE utf8mb4_general_ci;
                        SET @Lokasi         = '$Lokasi' COLLATE utf8mb4_general_ci;
                        SET @JenisUsaha     = (SELECT JenisUsaha FROM `$Database`.dbsprofile LIMIT 1);
                        SET @KodeAkunHutang = (SELECT `Lakukan` AS `AkunID` FROM `$Database`.`dbssetting` WHERE `Untuk` = 'AkunHutangUsaha');
                        SET @NamaAkunHutang = (SELECT `Description` FROM `$Database`.`dbmakun` WHERE `Code` = @KodeAkunHutang);
                        SET @KodeAkunLabaDitahan = (SELECT `Lakukan` AS `AkunID` FROM `$Database`.`dbssetting` WHERE `Untuk` = 'AkunLabaDitahan');
                        SET @NamaAkunLabaDitahan = (SELECT `Description` FROM `$Database`.`dbmakun` WHERE `Code` = @KodeAkunLabaDitahan);
                        SET @KodeAkunPersediaan = (SELECT `Lakukan` AS `AkunID` FROM `$Database`.`dbssetting` WHERE `Untuk` = 'AkunPersediaan');
                        SET @NamaAkunPersediaan = (SELECT `Description` FROM `$Database`.`dbmakun` WHERE `Code` = @KodeAkunPersediaan);
                        SET @PeriodBefore = (SELECT ID FROM `$Database`.`dbmperiod` WHERE `Status` = 0 AND Lokasi = @Lokasi ORDER BY ID DESC LIMIT 1);
                        
                        SET @Penjualan = (SELECT ROUND(SUM(a.Qty * a.Price), 2) AS Total FROM `$Database`.`dbtitemtransrecept` a INNER JOIN `$Database`.`dbtitemtrans` b ON a.DocNumber = b.`DocNumber` WHERE b.DocType = 'SALES' AND b.Lokasi = @Lokasi AND a.DocDate BETWEEN @StartDate AND @EndDate);

                        SET @DiscPenjualan = (SELECT COALESCE(SUM(DAmount), 0) FROM `$Database`.`dbtjournal` WHERE DocType = 'SALES' AND TransType = 'Diskon Penjualan' AND Lokasi = @Lokasi AND DocDate BETWEEN @StartDate AND @EndDate);
                        SET @ReturPenjualan = (SELECT COALESCE(SUM(GrandTotal), 0) FROM `$Database`.`dbtitemtrans` WHERE ReturType = 'Uang' AND DocType = 'SRTRN' AND `Processed` = 1 AND Lokasi = @Lokasi AND DocDate BETWEEN @StartDate AND @EndDate);
                        SET @TotalPenjualan = @Penjualan - (@DiscPenjualan + @ReturPenjualan);

                        SET @PersediaanAwal = (SELECT COALESCE(`PersediaanAwal`, 0) FROM `$Database`.`dbmperiod` WHERE `ID` = @PeriodID AND `Lokasi` = @Lokasi);
                        
                        SET @AkunPembelian = (SELECT `Description` FROM `$Database`.`dbssetting` a LEFT JOIN `$Database`.`dbmakun` b ON a.`Lakukan` = b.`Code` WHERE Untuk = 'AkunPembelian');
                        SET @Pembelian = (SELECT COALESCE(SUM(a.`Qty` * a.`Price`), 0) FROM `$Database`.`dbtitemtransrecept` a 
                                          INNER JOIN `$Database`.`dbtitemtrans` b ON a.`DocNumber` = b.DocNumber WHERE b.DocType = 'PURCH' AND b.Lokasi = @Lokasi AND a.`DocDate` BETWEEN @StartDate AND @EndDate);

                        SET @AkunDiscPembelian = (SELECT `Description` FROM `$Database`.`dbssetting` a LEFT JOIN `$Database`.`dbmakun` b ON a.`Lakukan` = b.`Code` WHERE `Untuk` = 'AkunDiskonPembelian' );
                        SET @DiscPembelian = (SELECT COALESCE(SUM(CAmount), 0) FROM `$Database`.`dbtjournal` WHERE DocType = 'PURCH' AND TransType = 'Diskon Pembelian' AND Lokasi = @Lokasi AND DocDate BETWEEN @StartDate AND @EndDate);

                        SET @AkunReturPembelian = (SELECT `Description` FROM `$Database`.`dbssetting` a LEFT JOIN `$Database`.`dbmakun` b ON a.`Lakukan` = b.`Code` WHERE `Untuk` = 'AkunReturPembelian');
                        SET @ReturPembelian = (SELECT COALESCE(SUM(`GrandTotal`), 0) FROM `$Database`.`dbtitemtrans` WHERE `DocType` = 'PRTRN' AND `Processed` = 1 AND Lokasi = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate);
                        SET @TotalPembelian = @Pembelian - (@DiscPembelian + @ReturPembelian);
                        SET @PersediaanSiapDiJual = @PersediaanAwal + @TotalPembelian;
                        SET @BiayaProduksi = (SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` WHERE `TransType` = 'Biaya Produksi' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate);
                        SET @GajiProduksi = (SELECT COALESCE(SUM(`Amount`), 0) FROM `$Database`.`dbtgaji` WHERE `Lokasi` = @Lokasi AND TransType = 'Gaji Produksi' AND `Tanggal` BETWEEN @StartDate AND @EndDate);
                        SET @Overhead = @BiayaProduksi + @GajiProduksi;
                        SET @PersediaanAkhir = (SELECT @PersediaanSiapDiJual - COALESCE(SUM(a.Qty * a.Price) - COALESCE(@Overhead, 0), 0) FROM `$Database`.`dbmitemlog` a 
                                                INNER JOIN `$Database`.`dbtitemtrans` b ON a.DocNumber = b.DocNumber 
                                                WHERE b.`Lokasi` = @Lokasi AND b.DocType = 'SALES' AND a.DocDate BETWEEN @StartDate AND @EndDate);
                        
                        SET @AkunHpp = (SELECT `Description` FROM `$Database`.`dbssetting` a LEFT JOIN `$Database`.`dbmakun` b ON a.`Lakukan` = b.`Code` WHERE `Untuk` = 'AkunBebanPokokPenjualan');
                        SET @HPP = (@PersediaanAwal + @TotalPembelian - @PersediaanAkhir + @Overhead);
                        SET @PendapatanKotor = IF(@TotalPenjualan > 0 OR @TotalPembelian > 0, @TotalPenjualan - @HPP, 0);

                        DROP TEMPORARY TABLE IF EXISTS `tbBeban`;
                        CREATE TEMPORARY TABLE `tbBeban`
                        SELECT COALESCE(SUM(`Amount`), 0) AS `Total` FROM `$Database`.`dbtgaji` 
                        WHERE `Lokasi` = @Lokasi AND `TransType` <> 'Gaji Produksi' AND `Tanggal` BETWEEN @StartDate AND @EndDate
                        UNION ALL
                        SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` WHERE `TransType` = 'Beban Usaha' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate
                        UNION ALL
                        SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` 
                        WHERE `TransType` = 'Beban Administrasi' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate
                        UNION ALL
                        SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` 
                        WHERE `TransType` = 'Beban Iklan' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate
                        UNION ALL
                        SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` 
                        WHERE `TransType` = 'Beban Lain-lain' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate
                        UNION ALL
                        SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` 
                        WHERE `TransType` = 'Penyusutan Aset' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate
                        UNION ALL
                        SELECT COALESCE(SUM(`DAmount`), 0) FROM `$Database`.`dbtjournal` 
                        WHERE `TransType` = 'Biaya Training' AND `DAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate;

                        SET @Beban = (SELECT COALESCE(SUM(`Total`), 0) FROM `tbBeban`);
                        SET @PendapatanBersih = @PendapatanKotor - @Beban;

                        SET @PendapatanLain = (SELECT COALESCE(SUM(`CAmount`), 0) FROM `$Database`.`dbtjournal` WHERE `TransType` IN ('Pendapatan Di Luar Usaha', 'Pendapatan Lain-lain') AND `CAmount` > 0 AND `Lokasi` = @Lokasi AND `DocDate` BETWEEN @StartDate AND @EndDate);
                        SET @OverPaid = (SELECT SUM(`OverPaid`) FROM `$Database`.`dbtitemtrans` WHERE `DocType` = 'SALES' AND `Lokasi` = @Lokasi AND Processed = 1 AND IsDelivery = 1 AND `DocDate` BETWEEN @StartDate AND @EndDate);
                        SET @Laba = @PendapatanBersih + @PendapatanLain + @OverPaid;

                        SET @LabaAwal = (SELECT COALESCE(SUM(a.`Amount`), 0) FROM `$Database`.`dbmneraca` a LEFT JOIN `$Database`.`dbmperiod` b ON a.`PeriodID` = b.`ID` WHERE a.`Tipe` = 'Laba Awal' AND a.`Lokasi` = @Lokasi AND b.`IsAwal` = 1 AND b.`Lokasi` = @Lokasi);
                        
                        SET @LabaBerjalan = (SELECT COALESCE(SUM(`Amount`), 0) + COALESCE(@LabaAwal, 0) FROM `$Database`.`dbmneraca` WHERE `GroupType` = 'Laba Berjalan' AND `Lokasi` = @Lokasi AND PeriodID <> @PeriodID);
                        SET @Laba2 = (@Laba + COALESCE(@LabaBerjalan, 0));

                        SET @AkunLaba = (SELECT `Description` FROM `$Database`.`dbmakun` a INNER JOIN `$Database`.`dbssetting` b ON a.`Code` = b.`Lakukan` WHERE b.`Untuk` = 'AkunLaba');
                        SET @CodeLaba = (SELECT `Lakukan` FROM `$Database`.`dbssetting` WHERE `Untuk` = 'AkunLaba');

                        DROP TEMPORARY TABLE IF EXISTS tbLabaRugi;
                        CREATE TEMPORARY TABLE tbLabaRugi 
                        SELECT 'Liabilitas' AS `Posisi`, 'Laba Ditahan' AS GroupType, ROUND(COALESCE(@LabaBerjalan, 0), 4) AS Total, @KodeAkunLabaDitahan AS PayCode, @NamaAkunLabaDitahan AS PayAkun
                        UNION ALL
                        SELECT 'Liabilitas' AS `Posisi`, 'Laba Berjalan' AS GroupType, ROUND(COALESCE(@Laba, 0), 4) AS Total, @CodeLaba AS PayCode, @AkunLaba AS PayAkun;";
                        // print $ssql;
                        $koneksi->exec($ssql);
                        $sql = "SELECT 'Aset' AS `Posisi`, 'Aset Lancar' AS `GroupType`, `Amount` AS `Total`, `Code` AS `AkunID`, `Nama` AS `AkunName` FROM `$Database`.`dbmakunpembayaran` WHERE `Status` = 1 AND `Lokasi` = @Lokasi
                        UNION ALL
                        SELECT 'Aset' AS `Posisi`, 'Piutang Usaha' AS `GroupType`, ROUND(SUM(`Balance`), 4) AS Total, `AkunID` AS `PayCode`, `NamaAkun` AS `PayAkun` FROM `$Database`.`dbmhutangpiutang` 
                        WHERE `Status` = 'jalan' AND `Balance` > 0 AND `DocType` = 'piutang' AND `Lokasi` = @Lokasi GROUP BY `AkunID`
                        UNION ALL
                        SELECT 'Aset' AS `Posisi`, 'Aset Lancar' AS `GroupType`, ROUND(COALESCE(@PersediaanAkhir, 0), 4) AS `Total`, @KodeAkunPersediaan, @NamaAkunPersediaan
                        UNION ALL
                        SELECT 'Aset' AS `Posisi`, 'Aset Tetap', ROUND(SUM(`Amount`), 4) AS `Total`, `AkunID`, `NamaAkun` FROM `$Database`.`dbmaset` WHERE `DocType` = 'Aset' AND `Lokasi` = @Lokasi GROUP BY `AkunID`
                        UNION ALL
                        SELECT 'Aset' AS `Posisi`, 'Aset Lain-lain', ROUND(SUM(`Balance`), 4), `PayCode`, `PayName` FROM `$Database`.`dbtpayment` WHERE (`Processed` = 0 OR `Balance` > 0) AND `DocType` = 'DOWNP' AND `Lokasi` = @Lokasi GROUP BY `AkunID`
                        UNION ALL
                        SELECT 'Aset' AS `Posisi`, 'Penyusutan' AS `GroupType`, ROUND(SUM(a.`Amount`), 4), a.`AkunID`, a.`NamaAkun` FROM `$Database`.`dbmasetdetail` a LEFT JOIN `$Database`.`dbmaset` b ON b.`ID` = a.`DocID` 
                        WHERE a.`DocType` = 'Penyusutan' AND b.`DocType` = 'Aset'  AND b.`Lokasi` = @Lokasi GROUP BY a.`AkunID`
                        UNION ALL
                        SELECT 'Liabilitas' AS `Posisi`, 'Hutang Usaha' AS `GroupType`, ROUND(SUM(`Balance`), 4) AS `Total`, `AkunID` AS `PayCode`, `NamaAkun` AS `PayAkun` FROM `$Database`.`dbmhutangpiutang` 
                        WHERE `Status` = 'jalan' AND `Balance` > 0 AND `DocType` = 'hutang' AND `Lokasi` = @Lokasi  GROUP BY `AkunID`
                        UNION ALL
                        SELECT 'Liabilitas' AS `Posisi`, 'Hutang Pph Badan' AS `GroupType`, ROUND(SUM(`Ppn`)), `AkunID`, `NamaAkun` 
                        FROM `$Database`.`dbtitemtranstax` WHERE `DocType` = 'SALES' AND `Status` = 0 AND `DocDate` BETWEEN @StartDate AND @EndDate GROUP BY `AkunID`
                        UNION ALL
                        SELECT 'Liabilitas' AS `Posisi`, 'Modal', ROUND(SUM(`Amount`), 4), `AkunID`, `NamaAkun` FROM `$Database`.`dbmaset` WHERE `DocType` = 'Modal' AND `Status` = 'Aktif' AND `Lokasi` = @Lokasi GROUP BY `AkunID`
                        UNION ALL
                        SELECT 'Liabilitas' AS `Posisi`, 'Prive', ROUND(SUM(`Amount`), 4), `AkunID`, `NamaAkun` FROM `$Database`.`dbmaset` WHERE `DocType` = 'PRIVE' AND `Lokasi` = @Lokasi GROUP BY `AkunID`
                        UNION ALL
                        SELECT `Posisi`, `GroupType`, COALESCE(`Total`, 0), `PayCode`, `PayAkun` FROM `tbLabaRugi`;";
                    } else {
                        $sql = "SELECT *, `Amount` AS `Total` FROM `$Database`.`dbmneraca` WHERE `PeriodID` IS NULL AND `Lokasi` = @Lokasi";
                    }
                } else {
                    $QPeriod = $koneksi->query("SELECT * FROM `$Database`.`dbmperiod` WHERE `ID` = '$Period'")->fetch();
                    $sql = "SELECT *, `Amount` AS `Total` FROM `$Database`.`dbmneraca` WHERE `PeriodID`  = '$Period'";
                    // print $sql;
                }
                $Aset = 0;
                $Liabilitas = 0;
                if (!is_bool($QPeriod)) {
                    $data = $koneksi->query($sql)->fetchAll();
                    foreach ($data as $i => $dd) {
                        if ($dd->Posisi == "Aset") {
                            if ($dd->GroupType != "Penyusutan") {
                                $Aset += $dd->Total;
                            } else {
                                $Aset -= $dd->Total;
                            }
                        } else if ($dd->Posisi == "Liabilitas") {
                            if ($dd->GroupType != "Prive") {
                                $Liabilitas += $dd->Total;
                            } else {
                                $Liabilitas -= $dd->Total;
                            }
                        }
                    }
                    $data = json_encode($data);
                } else {
                    $data = json_encode([]);
                }
                $Balance = $Aset - $Liabilitas;
                $koneksi->exec("INSERT INTO `master2`.`dbmlogneraca` ( `Perusahaan`, `NamaDB`, `Tanggal`, `Neraca`, `Aset`, `Liabilitas`, `Balance`, `TimeCreated`)
                                VALUES( (SELECT `Perusahaan` FROM `master2`.`dbmdatabase` WHERE `Database` = '$Database'), '$Database', CURDATE(), '$data', '$Aset', '$Liabilitas', '$Balance', NOW());");
                $koneksi = null;
            }
        }
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

    case 'backup all':
        $folderPath = '../files/';
        $nama = 'backupdb-' . date('Y-m-d') . '.tar.gz';
        $compressedFileName = "../files/backup/$nama";
        $phar = new PharData($compressedFileName);
        $files = glob($folderPath . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $phar->addFile($file);
            }
        }
        unset($phar);
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        print json_encode(['status' => "sukses", "pesan" => "https://api.gijutsusoftware.com/files/backup/$nama"]);
        break;

    case 'schema db':
        $Database = $_POST['Database'];
        $sql = $_POST['SQL'];
        $koneksi->exec("USE `$Database`;
                        
                        $sql");
        print json_encode(['status' => "sukses", "pesan" => "Selesai"]);
        break;

    case 'edit status':
        $ID = addslashes($_POST['ID']);
        $Status = $_POST['Status'];
        $koneksi->exec("UPDATE `dbmdatabase` SET `Status` = '$Status' WHERE `ID` = '$ID'");
        print json_encode(['status' => "sukses", "pesan" => "Berhasil merubah status"]);
        break;

    case 'reset db':
        $Database = $_POST['Database'];
        $JenisReset = $_POST['JenisReset'];
        $cekDB = $koneksi->query("SHOW DATABASES LIKE '$Database';")->rowCount();
        if ($cekDB != 0) {
            if ($JenisReset == "Semua") {
                $koneksi->exec("#Reset Semuanya
                                #TRANSAKSI
                                TRUNCATE TABLE `$Database`.`dbtitemtrans`;
                                TRUNCATE TABLE `$Database`.`dbtitemtranstax`;
                                TRUNCATE TABLE `$Database`.`dbtitemtranscost`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransdetail`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransvoucher`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransmaterial`;
                                TRUNCATE TABLE `$Database`.`dbtitemtranscard`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransfile`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransunit`;
                                TRUNCATE TABLE `$Database`.`dbtjournaltmp`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransrecept`;

                                #Transaksi pembayaran
                                TRUNCATE TABLE `$Database`.`dbtkastransfer`;
                                TRUNCATE TABLE `$Database`.`dbtpayment`;
                                TRUNCATE TABLE `$Database`.`dbsrecno`;

                                #MASTER AKUN
                                TRUNCATE TABLE `$Database`.`dbmakun`;
                                TRUNCATE TABLE `$Database`.`dbmakunpembayaran`;
                                TRUNCATE TABLE `$Database`.`dbmaset`;
                                TRUNCATE TABLE `$Database`.`dbmhutangpiutang`;
                                TRUNCATE TABLE `$Database`.`dbmperiod`;
                                TRUNCATE TABLE `$Database`.`dbmperiodakun`;
                                TRUNCATE TABLE `$Database`.`dbmperiodakunpembayaran`;
                                TRUNCATE TABLE `$Database`.`dbmperiodgaji`;
                                TRUNCATE TABLE `$Database`.`dbmperiodstok`;
                                TRUNCATE TABLE `$Database`.`dbmneraca`;
                                TRUNCATE TABLE `$Database`.`dbtjournal`;

                                #Trans Karyawan
                                TRUNCATE TABLE `$Database`.`dbtapproval`;
                                TRUNCATE TABLE `$Database`.`dbtaraplist`;
                                TRUNCATE TABLE `$Database`.`dbtiklan`;
                                TRUNCATE TABLE `$Database`.`dbtizin`;
                                TRUNCATE TABLE `$Database`.`dbtkaryawanlog`;
                                TRUNCATE TABLE `$Database`.`dbtkasbon`;

                                #Produksi
                                TRUNCATE TABLE `$Database`.`dbtdelivery`;
                                TRUNCATE TABLE `$Database`.`dbtdeliverydetail`;
                                TRUNCATE TABLE `$Database`.`dbtproduksi`;
                                TRUNCATE TABLE `$Database`.`dbtproduksidetail`;

                                #Mater item
                                TRUNCATE TABLE `$Database`.`dbmitem`;
                                TRUNCATE TABLE `$Database`.`dbmitemmaterial`;
                                TRUNCATE TABLE `$Database`.`dbmitemdetail`;
                                TRUNCATE TABLE `$Database`.`dbmkategori`;
                                TRUNCATE TABLE `$Database`.`dbmitemimg`;
                                TRUNCATE TABLE `$Database`.`dbmsatuan`;
                                TRUNCATE TABLE `$Database`.`dbmmarketplaceprice`;
                                TRUNCATE TABLE `$Database`.`dbmitemvoucher`;
                                TRUNCATE TABLE `$Database`.`dbmvoucher`;
                                TRUNCATE TABLE `$Database`.`dbmitemlog`;
                                TRUNCATE TABLE `$Database`.`dbmitemstok`; 
                                TRUNCATE TABLE `$Database`.`dbmitemsum`;
                                TRUNCATE TABLE `$Database`.`dbmitemunit`;
                                TRUNCATE TABLE `$Database`.`dbmpricelist`;
                                TRUNCATE TABLE `$Database`.`dbmpricelistdetail`;

                                #TASK
                                TRUNCATE TABLE `$Database`.`dbmtask`;
                                TRUNCATE TABLE `$Database`.`dbmtaskchecklist`;
                                TRUNCATE TABLE `$Database`.`dbmtaskjudul`;
                                TRUNCATE TABLE `$Database`.`dbmtaskkomentar`;
                                TRUNCATE TABLE `$Database`.`dbmtaskshare`;

                                #HRD
                                TRUNCATE TABLE `$Database`.`dbtabsensi`;
                                TRUNCATE TABLE `$Database`.`dbtgaji`;
                                TRUNCATE TABLE `$Database`.`dbtgajidetail`;
                                TRUNCATE TABLE `$Database`.`dbmgajikaryawan`;
                                TRUNCATE TABLE `$Database`.`dbmkomponengaji`;

                                #SYSTEM
                                UPDATE `$Database`.`dbsprofile` SET `AkunID` = NULL, `UsahaID` = NULL;
                                UPDATE `$Database`.`dbmlokasi` SET `IsDone` = 0, `IsDoneNeraca` = 0, `IsDoneInputNeraca` = 0;
                                TRUNCATE TABLE `$Database`.`dbtjsontrans`;
                                TRUNCATE TABLE `$Database`.`log`;

                                #SALES
                                TRUNCATE TABLE `$Database`.`dbmjadwalkunjungan`;
                                TRUNCATE TABLE `$Database`.`dbmjadwalkunjungandetail`;
                                TRUNCATE TABLE `$Database`.`dbmsalescall`;

                                #TRAINING
                                TRUNCATE TABLE `$Database`.`dbmtraining`;
                                TRUNCATE TABLE `$Database`.`dbmtrainingbudget`;
                                TRUNCATE TABLE `$Database`.`dbmtrainingdetail`;");
            } else {
                $koneksi->exec("#Reset Transaksi
                                #TRANSAKSI
                                TRUNCATE TABLE `$Database`.`dbtitemtrans`;
                                TRUNCATE TABLE `$Database`.`dbtitemtranstax`;
                                TRUNCATE TABLE `$Database`.`dbtitemtranscost`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransdetail`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransvoucher`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransmaterial`;
                                TRUNCATE TABLE `$Database`.`dbtitemtranscard`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransfile`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransunit`;
                                TRUNCATE TABLE `$Database`.`dbtjournaltmp`;
                                TRUNCATE TABLE `$Database`.`dbtitemtransrecept`;

                                #Transaksi pembayaran
                                TRUNCATE TABLE `$Database`.`dbtkastransfer`;
                                TRUNCATE TABLE `$Database`.`dbtpayment`;
                                TRUNCATE TABLE `$Database`.`dbsrecno`;

                                #MASTER AKUN
                                UPDATE `$Database`.`dbmakun` SET `Amount` = 0;
                                UPDATE `$Database`.`dbmakunpembayaran` SET `Amount` = 0;
                                TRUNCATE TABLE `$Database`.`dbmaset`;
                                TRUNCATE TABLE `$Database`.`dbmhutangpiutang`;
                                TRUNCATE TABLE `$Database`.`dbmperiod`;
                                TRUNCATE TABLE `$Database`.`dbmperiodakun`;
                                TRUNCATE TABLE `$Database`.`dbmperiodakunpembayaran`;
                                TRUNCATE TABLE `$Database`.`dbmperiodgaji`;
                                TRUNCATE TABLE `$Database`.`dbmperiodstok`;
                                TRUNCATE TABLE `$Database`.`dbmneraca`;

                                #Trans Karyawan
                                UPDATE `$Database`.`dbtkasbon` SET `Balance` = 0;

                                #Produksi
                                TRUNCATE TABLE `$Database`.`dbtdelivery`;
                                TRUNCATE TABLE `$Database`.`dbtdeliverydetail`;
                                TRUNCATE TABLE `$Database`.`dbtjournal`;

                                #Mater item
                                TRUNCATE TABLE `$Database`.`dbmitem`;
                                TRUNCATE TABLE `$Database`.`dbmitemmaterial`;
                                TRUNCATE TABLE `$Database`.`dbmitemdetail`;
                                TRUNCATE TABLE `$Database`.`dbmkategori`;
                                TRUNCATE TABLE `$Database`.`dbmitemimg`;
                                TRUNCATE TABLE `$Database`.`dbmsatuan`;
                                TRUNCATE TABLE `$Database`.`dbmmarketplaceprice`;
                                TRUNCATE TABLE `$Database`.`dbmitemvoucher`;
                                TRUNCATE TABLE `$Database`.`dbmvoucher`;
                                TRUNCATE TABLE `$Database`.`dbmitemlog`;
                                TRUNCATE TABLE `$Database`.`dbmitemstok`; 
                                TRUNCATE TABLE `$Database`.`dbmitemsum`;
                                TRUNCATE TABLE `$Database`.`dbmitemunit`;
                                TRUNCATE TABLE `$Database`.`dbmpricelist`;
                                TRUNCATE TABLE `$Database`.`dbmpricelistdetail`;

                                #SYSTEM
                                UPDATE `$Database`.`dbsprofile` SET `AkunID` = NULL, `UsahaID` = NULL;
                                UPDATE `$Database`.`dbmlokasi` SET `IsDone` = 0, `IsDoneNeraca` = 0, `IsDoneInputNeraca` = 0;
                                TRUNCATE TABLE `$Database`.`dbtjsontrans`;
                                TRUNCATE TABLE `$Database`.`log`;

                                #TRAINING
                                TRUNCATE TABLE `$Database`.`dbmtraining`;
                                TRUNCATE TABLE `$Database`.`dbmtrainingbudget`;
                                TRUNCATE TABLE `$Database`.`dbmtrainingdetail`;");
            }
            print json_encode(['status' => "sukses", "pesan" => "Berhasil reset database"]);
        } else {
            print json_encode(['status' => "gagal", "pesan" => "Database $Database Tidak ditemukan"]);
        }
        break;

    case 'restore':
        try {
            $host = 'localhost';
            $username = 'naylatools';
            $password = 'N@yl4naylatools';
            $database = $_POST['Database'];

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Protokol harus POST");
            if (!isset($_FILES['Files']) || $_FILES['Files']['error'] !== UPLOAD_ERR_OK) throw new Exception("Silahkan pilih file");

            // Mendapatkan informasi file
            $fileTmpPath = $_FILES['Files']['tmp_name'];
            $fileName = $_FILES['Files']['name'];
            $fileSize = $_FILES['Files']['size'];
            $fileType = $_FILES['Files']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $uploadFileDir = '../files/';
            $dest_path = $uploadFileDir . $fileName;

            if ($fileExtension != "zip" && $fileExtension != "sql") throw new Exception("File harus zip atau sql");
            if (!move_uploaded_file($fileTmpPath, $dest_path)) throw new Exception("Gagal upload file");

            if ($fileExtension == "zip") {
                $zip = new ZipArchive;
                if ($zip->open($dest_path) === TRUE) {
                    $zip->extractTo($uploadFileDir);
                    $zip->close();
                    // Asumsikan hanya ada satu file SQL di dalam ZIP
                    $extracted_files = scandir($uploadFileDir);
                    foreach ($extracted_files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                            $dest_path = $uploadFileDir . $file;
                            break;
                        }
                    }
                } else {
                    throw new Exception("Gagal mengekstrak file ZIP");
                }
            }

            // Membuat koneksi ke database
            $conn = new mysqli($host, $username, $password, $database);

            // Cek koneksi
            if ($conn->connect_error) {
                throw new Exception("Koneksi gagal: " . $conn->connect_error);
            }

            // Membaca file .sql
            $sql = file_get_contents($dest_path);

            if ($sql === false) {
                throw new Exception("Error reading the SQL file.");
            }

            // Membagi file menjadi beberapa query
            $queries = explode(";", $sql);
            $totalQueries = count($queries);
            $currentQuery = 0;

            // Eksekusi setiap query
            foreach ($queries as $query) {
                $trimmed_query = trim($query);
                if (!empty($trimmed_query)) {
                    if ($conn->query($trimmed_query) === false) {
                        throw new Exception("Error executing query: " . $conn->error);
                    }
                    $currentQuery++;
                    $percentComplete = ($currentQuery / $totalQueries) * 100;
                    echo json_encode(["status" => "progress", 'progress' => $percentComplete]);
                    ob_flush();
                    flush();
                }
            }

            $conn->close();
            echo json_encode(["status" => "sukses", "pesan" => "Proses reset berhasil"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "gagal", "pesan" => "Proses gagal: " . $e->getMessage()]);
        }

        break;

    default:
        print json_encode(['status' => "gagal", "pesan" => "perintah tidak di temukan $act"]);
        break;
}
