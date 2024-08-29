<?php

namespace App\Service;

use PDO;
use PDOException;

class G4NService
{
    private PDO $pdo;
    private PDO $pdo_cv;


    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $host = 'localhost';
        $dbname = 'pvp_data';
        $user = 'root';
        $password = '';

        $charset = 'utf8mb4';
//        $host = '178.254.20.178';
//        $dbname = 'pvp_data';
//        $user = 'admin';
//        $password = 'influxdb2023';
//        $charset = 'utf8mb4';
//
//        $host = '128.204.133.210';
//        $dbname = 'pvp_data';
//        $user = 'pvpluy_2';
//        $password = '8f4yMfFFRyqkrT-w6Ak2';
//        $charset = 'utf8mb4';

        $dbname_string = 'pvp_division';

        // Création d'une connexion PDO
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $dsn_cv = "mysql:host=$host;dbname=$dbname_string;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
            $this->pdo_cv = new PDO($dsn_cv, $user, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    private function dropTable(string $tableName): void

    {
        $current='db_string_current_'.$tableName;
        $voltage='db_string_voltage_'.$tableName;
        $this->pdo_cv->exec("DROP TABLE IF EXISTS `$current`");
        $this->pdo_cv->exec("DROP TABLE IF EXISTS `$voltage`");

    }

    private function createTable(string $tableName): void
    {
        $this->pdo_cv->exec($this->createTableCurrentSQL('db_string_current_'.$tableName ));
        $this->pdo_cv->exec($this->createTableVoltageSQL('db_string_voltage_'.$tableName ));
    }

    public function processTables(string $x, string $y): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Récupération des noms de tables correspondants
        $tables = $this->fetchTableNames('db__pv_dcist%');

        // créer les tables db__pv_dcist_current et db__pv_dcist_voltage


        foreach ($tables as $table) {

               $this->dropTable($table);
               $this->createTable( $table);

            // Extraction et insertion des données
            $this->extractAndInsertData($table, $x, $y);
        }
    }

    private function createTableCurrentSQL(string $tableName): string
    {
        return "
            CREATE TABLE $tableName (
                db_id bigint(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                anl_id int(11) NOT NULL,
                stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                wr_group int(11) NOT NULL,
                group_ac int(11) NOT NULL,
                wr_num int(11) NOT NULL,
                I varchar(20) NOT NULL,
                I_value varchar(20) NOT NULL,
                wr_mpp enum('current') NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    private function createTableVoltageSQL(string $tableName): string
    {
        return "
            CREATE TABLE $tableName (
                db_id bigint(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                anl_id int(11) NOT NULL,
                stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                wr_group int(11) NOT NULL,
                group_ac int(11) NOT NULL,
                wr_num int(11) NOT NULL,
                I varchar(20) NOT NULL,
                I_value varchar(20) NOT NULL,
                wr_mpp enum('voltage') NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    private function fetchTableNames(string $likePattern): array
    {

       // $query = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '$likePattern' AND table_name NOT LIKE '%_current' AND table_name NOT LIKE '%_voltage';";
        $query = "SHOW TABLES LIKE '$likePattern'";
        return $this->pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);


    }

    private function extractAndInsertData(string $tableName, string $x, string $y): void
    {
        $stmt = $this->pdo->prepare("SELECT anl_id, stamp, wr_group, group_ac, wr_num, wr_mpp_current, wr_mpp_voltage FROM `$tableName` WHERE (stamp >= ? AND stamp <= ?) AND (JSON_LENGTH(wr_mpp_current) > 0 OR JSON_LENGTH(wr_mpp_voltage) > 0)");
        $stmt->execute([$x, $y]);
        $data = $stmt->fetchAll();

        foreach ($data as $row) {
            $this->insertJSONData($row, 'db_string_current_'.$tableName, 'wr_mpp_current', 'current');
            $this->insertJSONData($row, 'db_string_voltage_'.$tableName, 'wr_mpp_voltage', 'voltage');
        }
    }

    private function insertJSONData(array $row, string $targetTable, string $jsonColumn, string $wr_mppValue): void
    {
        $json = json_decode($row[$jsonColumn], true, 512, JSON_THROW_ON_ERROR);
        if ($json) {
            foreach ($json as $I => $I_value) {

                $stmt = $this->pdo_cv->prepare("INSERT INTO `$targetTable` (anl_id, stamp, wr_group, group_ac, wr_num, I, I_value, wr_mpp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");


                $stmt->execute([
                    $row['anl_id'],
                    $row['stamp'],
                    $row['wr_group'],
                    $row['group_ac'],
                    $row['wr_num'],
                    $I,
                    $I_value,
                    $wr_mppValue
                ]);
            }
        }
    }
}
