<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParkhausController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepageShow(): Response
    {
        return $this->render('pages/home.html.twig');
    }
}