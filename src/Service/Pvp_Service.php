<?php

namespace App\Service;

use PDO;
use PDOException;

class Pvp_Service
{
    private PDO $pdo_local;
    private PDO $pdo_pvp;


    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $host_local = '178.254.20.178';
        $dbname_local = 'pvp_data';
        $user_local = 'admin';
        $password_local = 'influxdb2023';


        $host_pvp = '128.204.133.210';
        $dbname_pvp = 'pvp_data';
        $user_pvp = 'pvpluy_2';
        $password_pvp = '8f4yMfFFRyqkrT-w6Ak2';

        $charset = 'utf8mb4';
        // Création d'une connexion PDO
        $dsn_local = "mysql:host=$host_local;dbname=$dbname_local;charset=$charset";
       // $dsn_pvp = "mysql:host=$host_pvp;dbname=$dbname_pvp;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo_local = new PDO($dsn_local, $user_local, $password_local, $options);
        //    $this->pdo_pvp = new PDO($dsn_pvp, $user_pvp, $password_pvp, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }





    public function copy(string $tableName, string $x, string $y): void
    {
        $stmt = $this->pdo_pvp->prepare("SELECT anl_id, stamp, wr_group, group_ac, wr_num, wr_mpp_current, wr_mpp_voltage FROM `$tableName` WHERE (stamp >= ? AND stamp <= ?) AND (JSON_LENGTH(wr_mpp_current) > 0 OR JSON_LENGTH(wr_mpp_voltage) > 0)");
        $stmt->execute([$x, $y]);
        $data = $stmt->fetchAll();
        $this->createTable($tableName);
        foreach ($data as $row) {
            $stmt = $this->pdo_local->prepare("INSERT INTO `$tableName` (anl_id, stamp, wr_group, group_ac, wr_num, wr_mpp_current, wr_mpp_voltage) VALUES (?, ?, ?, ?, ?, ?, ?)");


            $stmt->execute([
                $row['anl_id'],
                $row['stamp'],
                $row['wr_group'],
                $row['group_ac'],
                $row['wr_num'],
                $row['wr_mpp_current'],
                $row['wr_mpp_voltage'],

            ]);
        }
    }

    private function createTable(string $tableName): void
    {
        $this->pdo_local->exec($this->createTableSQL($tableName));

    }

    private function createTableSQL(string $tableName): string
    {
        return"
            CREATE TABLE  IF NOT EXISTS `$tableName` (
                `db_id` bigint(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `anl_id` int(11) NOT NULL,
                `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `wr_group` int(11) NOT NULL,
                `group_ac` int(11) NOT NULL,
                `wr_num` int(11) NOT NULL,
                `wr_idc` varchar(20) DEFAULT NULL,
                `wr_udc` varchar(20) DEFAULT NULL,
                `wr_pdc` varchar(20) DEFAULT NULL,
                `wr_temp` varchar(20) DEFAULT NULL,
                `wr_mpp_current` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`wr_mpp_current`)),
                `wr_mpp_voltage` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`wr_mpp_voltage`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ";

    }


}
