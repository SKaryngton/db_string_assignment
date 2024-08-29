<?php

namespace App\Service;

use App\Entity\Anlage;
use App\Entity\AnlageStringAssignment;
use Doctrine\Persistence\ManagerRegistry;
use Shuchkin\SimpleXLSX;

class ExcelImporter
{

    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function import(string $filePath, string $connectionName, Anlage $anlage)
    {
        $xlsx = SimpleXLSX::parse($filePath);
        if (!$xlsx) {
            return false;
        }

        $entityManager = $this->registry->getManager($connectionName);

        foreach ($xlsx->rows() as $row) {
            $assignment = new AnlageStringAssignment();

            // Ici, configurez l'entité AnlageStringAssignment avec les données de $row
            // Assurez-vous que l'ordre et le nombre des colonnes dans le fichier Excel
            // correspondent à l'ordre des propriétés dans l'entité
            //$assignment->setStationNr($row[0]);
            $assignment->setInverterNr($row[1]);
            $assignment->setStringNr($row[2]);
            $assignment->setChannelNr($row[3]);
            $assignment->setStringActive($row[4]);
            $assignment->setChannelCat($row[5]);
            $assignment->setPosition($row[6]);
            $assignment->setTilt($row[7]);
            $assignment->setAzimut($row[8]);
            $assignment->setPanelType($row[9]);
            $assignment->setInverterType($row[10]);
            // Assurez-vous que l'entité Anlage est correctement configurée pour cet exemple
            $assignment->setAnlId($anlage);

            $entityManager->persist($assignment);
        }

        $entityManager->flush();
        return true;
    }
}
