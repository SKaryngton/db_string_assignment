<?php

namespace App\Command;



use App\Service\G4NService_2;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PvpDataCommand extends Command
{
    protected static $defaultName = 'app:pvp';



    public function __construct(private readonly G4NService_2 $tableService)
    {

        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $month = 7;//month
        $year = 2024; // Define the year
        $suffix ='CX104'; //db suffix name

        // Using DateTime to set start and end dates
        $dateX = new DateTime("$year-$month-01 00:00:00");
        $dateY = (clone $dateX)->modify('last day of this month')->setTime(23, 59, 59);

        // Transfer data for the specified date range
        $this->tableService->transferData($suffix,$dateX->format('Y-m-d H:i:s'), $dateY->format('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }

}
// execute in the console with
//  php bin/console app:pvp
