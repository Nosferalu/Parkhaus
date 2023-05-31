<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;



class ParkhausController extends AbstractController
{

    #[Route('/home', name: 'home')]
    public function list()
    {

        return $this->render('pages/home.html.twig');
    }

}