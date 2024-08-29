<?php

namespace App\Service;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class TableService
{
    private $connection;
    private $entityManager;

    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }

    public function createTable(string $tableName): int|string
    {
        $sql = "
            CREATE TABLE `$tableName` (
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


         return   $this->connection->executeStatement($sql);


    }

    public function listTables(): array
    {
        return $this->connection->executeQuery("SHOW TABLES FROM pvp_data")->fetchAllAssociative();
    }

    public function writeToTable(string $tableName, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->insert($tableName, $data);
    }
    public function importDataFromJsonOrCsv(string $tableName, UploadedFile $file): void
    {
        $extension = $file->getClientOriginalExtension();

        $data = [];
        if ($extension === 'json') {
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } elseif ($extension === 'csv') {
            $content = file_get_contents($file->getRealPath());
            $rows = str_getcsv($content, "\n");
            $header = str_getcsv(array_shift($rows), ",");
            foreach ($rows as $row) {
                $data[] = array_combine($header, str_getcsv($row, ","));
            }
        }

        foreach ($data as $row) {
            $this->writeToTable($tableName, $row);
        }
    }

    public function getAnlList(string $table_name): array
    {
        $sql = "SELECT * FROM `$table_name`";
        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }


    public function createTableCurrent(string $tableName)
    {
        $sql = "
           CREATE TABLE IF NOT EXISTS $tableName (
            db_id bigint(11) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
            anl_id int(11) NOT NULL,
            stamp timestamp NOT NULL DEFAULT current_timestamp(),
            wr_group int(11) NOT NULL,
            group_ac int(11) NOT NULL,
            wr_num int(11) NOT NULL,
            I varchar(20) NOT NULL,
            I_value int(11) NOT NULL,
            wr_mpp enum('current', 'voltage') NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ";

        return   $this->connection->executeStatement($sql);

    }


    public function createTableCurrentOrVoltage(string $currentTableName, string $type)
    {
        // Construire le nom de la nouvelle table
        $newTableName = $currentTableName . '_' . strtolower(substr($type, 0, 2));

        // Vidanger le contenu de la nouvelle table si elle existe déjà
        $this->entityManager->getConnection()->executeQuery('DROP TABLE IF EXISTS ' . $newTableName);
        // Créer la nouvelle table si elle n'existe pas déjà
        $this->createTableCurrent($newTableName);



        // Transférer les données de la table actuelle vers la nouvelle table
        $data = $this->getAnlList($currentTableName);
        foreach ($data as $row) {
            $jsonColumn = $row['wr_mpp_' . strtolower($type)];
            if ($jsonColumn) {
                $dataArray = json_decode($jsonColumn, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($dataArray)) {
                    foreach ($dataArray as $key => $value) {
                        $sql = "INSERT INTO $newTableName 
                        (anl_id, stamp, wr_group, group_ac, wr_num,  I, I_value, wr_mpp) 
                        VALUES 
                        (:anl_id, :stamp, :wr_group, :group_ac, :wr_num, :I, :I_value, :wr_mpp)";

                        $params = array(
                            'anl_id' => $row['anl_id'],
                            'stamp' => $row['stamp'],
                            'wr_group' => $row['wr_group'],
                            'group_ac' => $row['group_ac'],
                            'wr_num' => $row['wr_num'],
                            'I' => $key,
                            'I_value' => $value,
                            'wr_mpp' => strtolower($type),
                        );

                        $this->entityManager->getConnection()->executeStatement($sql, $params);
                    }
                }
            }
        }
    }



}
