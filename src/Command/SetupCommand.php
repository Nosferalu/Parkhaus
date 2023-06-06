<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SetupCommand extends Command
{

    private Connection $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:create-tables')
            ->setDescription('Create Tables.');
    }


    protected function execute(InputInterface $input,OutputInterface $output): int
    {

        $parkplatzQuery = 'CREATE TABLE Parkplaetze (
        parkplatz_id INT PRIMARY KEY,
        bezeichnung VARCHAR(50) NOT NULL,
        kapazitaet INT NOT NULL
    );';

        $fahrzeugQuery = 'CREATE TABLE Fahrzeuge (
        kennzeichen VARCHAR(20) PRIMARY KEY,
        einfahrtsdatum DATETIME,
        ausfahrtsdatum DATETIME,
        parkplatz_id INT,
        FOREIGN KEY (parkplatz_id) REFERENCES Parkplaetze(parkplatz_id)
    );';

        $parkerQuery = 'CREATE TABLE Parker (
        parker_id VARCHAR(100) PRIMARY KEY,
        vorname VARCHAR(50) NOT NULL,
        nachname VARCHAR(50) NOT NULL,
        fahrzeug_id VARCHAR(20),
        email VARCHAR(50) NOT NULL,
        password VARCHAR(50) NOT NULL,
        dauerparker BOOLEAN NOT NULL DEFAULT false,
    FOREIGN KEY (fahrzeug_id) REFERENCES Fahrzeuge(kennzeichen)
);';


        $this->connection->executeQuery($parkplatzQuery);
        $this->connection->executeQuery($fahrzeugQuery);
        $this->connection->executeQuery($parkerQuery);

        return Command::SUCCESS;


    }


}