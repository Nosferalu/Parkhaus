<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ParkhausController extends AbstractController
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @Route("/", name="app_homepage")
     * @IsGranted("ROLE_USER")
     */
    public function homepageShow(): Response
    {
        // Execute the SQL query to sum the kapazität where kapazität is not 0
        $query = '
            SELECT SUM(kapazitaet) AS totalKapazitaet
            FROM Parkplaetze
            WHERE kapazitaet > 0
        ';
        $result = $this->connection->executeQuery($query)->fetch();

        $totalKapazitaet = $result['totalKapazitaet'];

        return $this->render('pages/home.html.twig', [
            'totalKapazitaet' => $totalKapazitaet,
        ]);
    }

    /**
     * @Route("/check-in", name="check_in_route", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function checkIn(): Response
    {
        $userId = $this->getUser()->getUserIdentifier();

        $hasParkplatz = $this->connection->executeQuery('
        SELECT parkplatz_id
        FROM Fahrzeuge
        WHERE kennzeichen IN (
            SELECT fahrzeug_id
            FROM Parker
            WHERE email = ?
        )
        AND parkplatz_id IS NOT NULL
    ', [$userId])->fetchOne();


        if ($hasParkplatz > 0) {
            return $this->redirectToRoute('app_homepage');
        }



        // Check if there are any available Parkplaetze
        $availableParkplaetze = $this->connection->executeQuery('
        SELECT parkplatz_id, bezeichnung, kapazitaet
        FROM Parkplaetze
        WHERE kapazitaet > 0
    ')->fetchAll();

        if (count($availableParkplaetze) > 0) {
            // Check if the user is a Dauerparker
            $isDauerparker = $this->connection->executeQuery('
            SELECT dauerparker
            FROM Parker
            WHERE email = ?
        ', [$userId])->fetchOne();

            $parkplatzId = null;

            // If the user is a Dauerparker, assign a Dauerparkplatz if available
            if ($isDauerparker) {
                foreach ($availableParkplaetze as $parkplatz) {
                    if ($parkplatz['parkplatz_id'] >= 1 && $parkplatz['parkplatz_id'] <= 40) {
                        $parkplatzId = $parkplatz['parkplatz_id'];
                        break;
                    }
                }
            }

            // If a Dauerparkplatz is not available or the user is not a Dauerparker, assign a Regulärer Parkplatz
            if ($parkplatzId === null) {
                foreach ($availableParkplaetze as $parkplatz) {
                    if ($parkplatz['parkplatz_id'] > 40) {
                        $parkplatzId = $parkplatz['parkplatz_id'];
                        break;
                    }
                }
            }


            // If parkplatzId is not assigned, proceed with the check-in
            if ($parkplatzId !== null) {
                // Get the current timestamp as the Einfahrtszeit
                $einfahrtszeit = date('Y-m-d H:i:s');

                // Register the Fahrzeug to the assigned Parkplatz
                $this->connection->executeQuery('
                UPDATE Fahrzeuge
                SET parkplatz_id = ?, einfahrtsdatum = ?
                WHERE parkplatz_id IS NULL
                AND kennzeichen IN (
                    SELECT fahrzeug_id
                    FROM Parker
                    WHERE email = ?
                )
                LIMIT 1
            ', [$parkplatzId, $einfahrtszeit, $userId]);

                // Decrease the kapazitaet of the assigned Parkplatz by 1
                $this->connection->executeQuery('
                UPDATE Parkplaetze
                SET kapazitaet = kapazitaet - 1
                WHERE parkplatz_id = ?
            ', [$parkplatzId]);

                // Redirect to the homepage or any other desired page
                return $this->redirectToRoute('app_homepage');
            }
        }

        return $this->redirectToRoute('app_homepage');
    }



    /**
     * @Route("/check-out", name="check_out_route", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function checkOut(): Response
    {
        $userId = $this->getUser()->getUserIdentifier();

        // Check if the Fahrzeug of the Parker has a Parkplatz_id
        $parkplatzId = $this->connection->executeQuery('
        SELECT f.parkplatz_id
        FROM Fahrzeuge f
        INNER JOIN Parker p ON f.kennzeichen = p.fahrzeug_id
        WHERE p.email = ?
        AND f.parkplatz_id IS NOT NULL
    ', [$userId])->fetchOne();


        if ($parkplatzId > 0) {
            // Set AusfahrtDatum to the current datetime
            $ausfahrtsDatum = date('Y-m-d H:i:s');

            // Update Fahrzeuge table to set AusfahrtDatum
            $this->connection->executeQuery('
            UPDATE Fahrzeuge
            SET ausfahrtsdatum = ?
            WHERE kennzeichen = (
                SELECT fahrzeug_id
                FROM Parker
                WHERE email = ?
            )
        ', [$ausfahrtsDatum, $userId]);


            // Calculate the price based on einfahrtsdatum and ausfahrtsdatum
            $einfahrtsdatum = $this->connection->executeQuery('
            SELECT einfahrtsdatum
            FROM Fahrzeuge
            WHERE kennzeichen = (
                SELECT fahrzeug_id
                FROM Parker
                WHERE email = ?
            )
        ', [$userId])->fetchOne();

            $entryTime = strtotime($einfahrtsdatum);
            $exitTime = strtotime($ausfahrtsDatum);

            // Calculate the duration in hours
            $duration = ($exitTime - $entryTime) / (60 * 60);

            // Calculate the price per hour (e.g., €3)
            $pricePerHour = 3;

            // Calculate the total price
            $price = ceil($duration) * $pricePerHour;

            // Redirect to the payment page with the calculated price
            return $this->render('pages/payment.html.twig', [
                'price' => $price,
            ]);
        }

        // If the Fahrzeug does not have a Parkplatz_id, do not proceed

        // Redirect to the homepage or any other desired page
        return $this->redirectToRoute('app_homepage');
    }

    /**
     * @Route("/payment", name="payment_route", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function payment(): Response
    {
        $userId = $this->getUser()->getUserIdentifier();

        $parkplatzId = $this->connection->executeQuery('
        SELECT f.parkplatz_id
        FROM Fahrzeuge f
        INNER JOIN Parker p ON f.kennzeichen = p.fahrzeug_id
        WHERE p.email = ?
        AND f.parkplatz_id IS NOT NULL
    ', [$userId])->fetchOne();

        // Update Parkplaetze table to increase Kapazitaet by 1 for the corresponding Parkplatz
        $this->connection->executeQuery('
            UPDATE Parkplaetze
            SET kapazitaet = kapazitaet + 1
            WHERE parkplatz_id = ?
        ', [$parkplatzId]);

        // Set Einfahrtsdatum, Ausfahrtsdatum, and Parkplatz_id to null
        $this->connection->executeQuery('
        UPDATE Fahrzeuge
        SET einfahrtsdatum = null, ausfahrtsdatum = null, parkplatz_id = null
        WHERE kennzeichen = (
            SELECT fahrzeug_id
            FROM Parker
            WHERE email = ?
        )
    ', [$userId]);

        // Redirect to the homepage or any other desired page
        return $this->redirectToRoute('app_homepage');
    }
}
