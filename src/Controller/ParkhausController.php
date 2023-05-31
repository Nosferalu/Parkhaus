<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParkhausController extends AbstractController
{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
    }

    #[Route('/', name: 'app_homepage')]
    public function homepageShow(): Response
    {
        $query = 'SELECT * FROM test';

        $results = $this->connection->executeQuery($query)->fetchAll();

        return $this->render('pages/home.html.twig');
    }
}