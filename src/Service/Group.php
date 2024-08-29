<?php

namespace App\Service;

class Group
{

    //gleich uhrzeit
    private function processAndGroupData(array $rows): array
    {
        $groupedData = []; // Initialise un tableau pour les données regroupées
        foreach ($rows as $row) {

            // Sépare la date de l'heure dans le timestamp
            // $date = substr($row['stamp'], 0, 10);


            // Décode les données JSON pour les courants et les tensions
            $currentData = array_values(json_decode($row['wr_mpp_current'], true) ?: []);
            $voltageData = array_values(json_decode($row['wr_mpp_voltage'], true) ?: []);

            // Traite les données de courant
            if(count($currentData)>0 && count($voltageData )=== 0){
                foreach ($currentData as $key => $value) {
                    $channel = $key;
                    $groupedData[$row['stamp']][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => $value, 'U_value' => null
                    ];
                }
            }elseif (count($currentData)=== 0 && count($voltageData )> 0){
                foreach ($voltageData as $key => $value) {
                    $channel = $key;
                    $groupedData[$row['stamp']][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => null, 'U_value' => $value
                    ];
                }
            }elseif (count($currentData)> 0 && count($voltageData )> 0){
                foreach ($voltageData as $key => $value) {
                    $channel = $key;
                    $groupedData[$row['stamp']][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => $value, 'U_value' => $currentData[$key]
                    ];
                }
            }


        }
        return $groupedData; // Retourne les données regroupées
    }


    // $date = substr($row['stamp'], 0, 10);
    //  $dateHour = substr($row['stamp'], 0, 13);

    // Extract the date and time from the timestamp
    // $dateTime = new DateTime($row['stamp']);
    // Round down the time to the nearest half-hour
    //   $minute = (int)$dateTime->format('i');
//            if ($minute >= 30) {
//                $dateTime->setTime((int)$dateTime->format('H'), 30);
//            } else {
//                $dateTime->setTime((int)$dateTime->format('H'), 0);
//            }
//
//            $groupKey = $dateTime->format('Y-m-d H:i:s'); // Use the modified timestamp as the group key

    private function processAndGroupData_per_day(array $rows): array
    {
        $groupedData = []; // Initialise un tableau pour les données regroupées
        foreach ($rows as $row) {

            // Sépare la date de l'heure dans le timestamp
               $date = substr($row['stamp'], 0, 10);

            // Extraction de l'heure du timestamp
            // $hour = substr($row['stamp'], 11, 2);

            // Décode les données JSON pour les courants et les tensions
            $currentData = array_values(json_decode($row['wr_mpp_current'], true) ?: []);
            $voltageData = array_values(json_decode($row['wr_mpp_voltage'], true) ?: []);

            // Traite les données de courant
            if(count($currentData)>0 && count($voltageData )=== 0){
                foreach ($currentData as $key => $value) {
                    $channel = $key;
                    $groupedData[$date][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => $value, 'U_value' => null
                    ];
                }
            }elseif (count($currentData)=== 0 && count($voltageData )> 0){
                foreach ($voltageData as $key => $value) {
                    $channel = $key;
                    $groupedData[$date][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => null, 'U_value' => $value
                    ];
                }
            }elseif (count($currentData)> 0 && count($voltageData )> 0){
                foreach ($voltageData as $key => $value) {
                    $channel = $key;
                    $groupedData[$date][] = [
                        'anl_id' => $row['anl_id'], 'stamp' => $row['stamp'],
                        'wr_group' => $row['wr_group'], 'group_ac' => $row['group_ac'], 'wr_num' => $row['wr_num'],
                        'channel' => $channel, 'I_value' => $value, 'U_value' => $currentData[$key]
                    ];
                }
            }


        }

        return $groupedData; // Retourne les données regroupées
    }

}


