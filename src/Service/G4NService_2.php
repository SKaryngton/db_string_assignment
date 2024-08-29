<?php

namespace App\Service;

use DateTime;
use PDO;
use PDOException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class G4NService_2
{
    private PDO $sourceDb;
    private PDO $targetDb;

    // Constructor to initialize the service.
    public function __construct()
    {
        $this->connect(); // Establishes connection to the databases.
    }

    /* établit une connexion à deux bases de données : une source ($sourceDb) et une cible ($targetDb).*/
    // Private method to establish database connections.
    private function connect(): void
    {
        // Database connection parameters
        $host = '128.204.133.210'; // Database host
        $dbname = 'pvp_data'; // Name of the source database
        $user = 'pvpluy_2'; // Database username
        $password = '8f4yMfFFRyqkrT-w6Ak2'; // Database password
        $charset = 'utf8mb4'; // Character set for the database
        $dbname_string = 'pvp_division'; // Name of the target database

        // Data Source Names for the databases
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $dsn_cv = "mysql:host=$host;dbname=$dbname_string;charset=$charset";

        // Options for PDO connection
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Establishing the PDO connections
        try {
            $this->sourceDb = new PDO($dsn, $user, $password, $options);
            $this->targetDb = new PDO($dsn_cv, $user, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /* getTableNames() récupère les noms des tables de la base de données source qui correspondent à un certain motif.
    Les noms de tables sont filtrés en fonction de certains critères, comme l'absence de certaines chaînes dans le nom de la table et la vérification si la table n'est pas vide.*/
    // SQL query to retrieve table names from the source database.
    private function getTableNames(string $suffix): array
    {
        $tableNames = [];

        // Modify the query to include the suffix passed to the function
        $pattern = "db__pv_dcist_{$suffix}%";
        $query = "SHOW TABLES FROM pvp_data LIKE '$pattern'";
        $potentialTables = $this->sourceDb->query($query)->fetchAll(PDO::FETCH_COLUMN);

        // Iterate through the tables to check if they are non-empty and meet other criteria
        foreach ($potentialTables as $tableName) {
            if (!str_contains($tableName, 'G4NET_') && !str_contains($tableName, '_copy')) {
                // Check if the table is non-empty
                $rowCount = $this->sourceDb->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
                if ($rowCount > 0) {
                    $tableNames[] = $tableName;
                }
            }
        }

        return $tableNames;
    }

    /*getNewTableName()  génère un nouveau nom de table pour la base de données cible en remplaçant le préfixe du nom de table de la base de données source.*/
    // Generate a new table name by replacing the prefix.
    private function getNewTableName(string $oldTableName): string
    {
        $suffix = str_replace('db__pv_dcist_', '', $oldTableName);
        return 'db__string_pv_' . $suffix;
    }

    /* createNewTable()  crée une nouvelle table dans la base de données cible avec une structure de colonnes spécifique.*/
    // Create a new table in the target database.
    private function createNewTable(string $tableName): void
    {
        $createSql = "CREATE TABLE IF NOT EXISTS `$tableName` (
            `db_id` bigint(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `anl_id` int(11) NOT NULL,
            `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `wr_group` int(11) NOT NULL,
            `group_ac` int(11) NOT NULL,
            `wr_num` int(11) NOT NULL,
            `channel` varchar(20) NOT NULL,
            `I_value` varchar(20) DEFAULT NULL,
            `U_value` varchar(20) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->targetDb->exec($createSql);
    }

   /*fetchDataBetweenDates() récupère des données de la base de données source entre deux dates spécifiées pour une table donnée.*/
    // Fetch data between specified dates from the source database.
    private function fetchDataBetweenDates(string $table, string $startDate, string $endDate): array
    {
        $query = $this->sourceDb->prepare("SELECT anl_id, stamp, wr_group, group_ac, wr_num, wr_mpp_current, wr_mpp_voltage FROM `$table` WHERE `stamp` BETWEEN ? AND ?");
        $query->execute([$startDate, $endDate]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

   /* processAndGroupData()  traite les données récupérées, notamment en convertissant des données JSON en tableaux,
   en regroupant les données en fonction de la colonne "stamp" et en les préparant pour l'insertion dans la base de données cible.*/
    private function processAndGroupData(array $rows): array
    {
        $groupedData = []; // // Initialize array to hold grouped data
        foreach ($rows as $row) {



            // processing current and voltage data from rows
            $currentData = array_values(json_decode($row['wr_mpp_current'], true) ?: []);
            $voltageData = array_values(json_decode($row['wr_mpp_voltage'], true) ?: []);


            // Handling various combinations of current and voltage data
            if(count($currentData)>0 && count($voltageData )=== 0){
                // Code for processing current data only
                foreach ($currentData as $key => $value) {
                    $channel = $key + 1 ;
                    $groupedData[$row['stamp']][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => $value, 'U_value' => null
                    ];
                }
            }elseif (count($currentData)=== 0 && count($voltageData )> 0){
                foreach ($voltageData as $key => $value) {
                    $channel = $key + 1 ;
                    $groupedData[$row['stamp']][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => null, 'U_value' => $value
                    ];
                }
            }elseif (count($currentData)> 0 && count($voltageData )> 0){
                // Code for processing both current and voltage data
                foreach ($voltageData as $key => $value) {
                    $channel = $key + 1 ;
                    $groupedData[$row['stamp']][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => $value, 'U_value' => $currentData[$key]
                    ];
                }
            }

        }

        return $groupedData;
    }

    /*executeInsert() effectue une opération d'insertion en masse pour un lot de données dans la base de données cible.*/
    // Execute the insert operation for a batch of data.
    private function executeInsert(string $tableName, array $data): void
    {
        // Code for building and executing the batch insert query
        $placeholders = [];
        foreach ($data as $row) {
            $placeholders[] = "(" . rtrim(str_repeat('?,', count($row)), ',') . ")";
        }

        $values = array_merge([], ...$data);
        $sql = "INSERT INTO `$tableName` ( `anl_id`, `stamp`, `wr_group`, `group_ac`, `wr_num`, `channel`, `I_value`, `U_value`) VALUES " . implode(',', $placeholders);
        $stmt = $this->targetDb->prepare($sql);
        $stmt->execute($values);
    }

    /*insertGroupedData() insère les données regroupées dans la base de données cible en plusieurs lots pour éviter de dépasser la limite de placeholders.*/
    // Insert grouped data into the target database.
    private function insertGroupedData(string $tableName, array $groupedData): void
    {
        // Insert data into the target table in batches to avoid exceeding the placeholder limit.
        $batchSize = 6500; // Adjust this number based on your needs 65535
        $allData = [];

        foreach ($groupedData as $stamp => $data) {
            foreach ($data as $row) {
                $allData[] = array_values($row);
                if (count($allData) >= $batchSize) {
                    $this->executeInsert($tableName, $allData);
                    $allData = []; // Reset the array after insertion
                }
            }
        }

        // Insert any remaining data
        if (!empty($allData)) {
            $this->executeInsert($tableName, $allData);
        }
    }



    /* transferData()  coordonne l'ensemble du processus de transfert de données.
    Elle récupère les noms des tables de la base de données source, génère de nouveaux noms de tables pour la base de données cible,
    récupère les données de chaque table entre les dates spécifiées,
    crée les nouvelles tables dans la base de données cible, traite et groupe les données,
    puis les insère en lots dans les tables cibles correspondantes.*/
    // Retrieve table names, process data, and perform transfer
    // Code for transferring data from source to target database
    public function transferData(string $startDate, string $endDate): void
    {
        // Set unlimited time limit and memory for the script
        set_time_limit(0);
        ini_set('memory_limit', '-1');


        // Retrieve the names of the tables from the source database that match the specified pattern.
        $tables = $this->getTableNames('CX181');


        foreach ($tables as $table) {
            // Generate the new table name for the target database
            $newTableName = $this->getNewTableName($table);

            // Retrieve data from the source table between the specified dates.
            $rows = $this->fetchDataBetweenDates($table, $startDate, $endDate);


            if(count($rows)>0){
                // Create (or recreate) the table in the target database.
                $this->createNewTable($newTableName);

                // Process the data and group it by 'stamp'.
                $groupedData = $this->processAndGroupData($rows);

                // Insert the grouped data into the target table
                $this->insertGroupedData($newTableName, $groupedData);
            }


        }
    }

}
