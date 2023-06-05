<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FillDatabaseCommand extends Command
{

    private Connection $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:fill-database')
            ->setDescription('Create Tables.');
    }

    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        // Anzahl der Parkplätze insgesamt
        $gesamtParkplaetze = 180;

        // Anzahl der Dauerparkplätze
        $dauerParkplaetze = 40;


        for ($i = 1; $i <= $dauerParkplaetze; $i++) {
            $parkplatzBezeichnung = 'Dauerparkplatz ' . $i;
            $kapazitaet = 1;

            $sql = "INSERT INTO Parkplaetze (parkplatz_id, bezeichnung, kapazitaet)
            VALUES ($i, '$parkplatzBezeichnung', $kapazitaet)";

            // Ausführen des SQL-Statements
            $this->connection->executeQuery($sql);
        }

        for ($i = $dauerParkplaetze + 1; $i <= $gesamtParkplaetze; $i++) {
            $parkplatzBezeichnung = 'Regulärer Parkplatz ' . ($i - $dauerParkplaetze);
            $kapazitaet = 1;

            $sql = "INSERT INTO Parkplaetze (parkplatz_id, bezeichnung, kapazitaet)
            VALUES ($i, '$parkplatzBezeichnung', $kapazitaet)";

            // Ausführen des SQL-Statements
            $this->connection->executeQuery($sql);
        }





        return Command::SUCCESS;


    }


}