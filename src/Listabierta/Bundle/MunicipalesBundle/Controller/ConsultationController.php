<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ConsultationController extends Controller
{
    public function step1Action(Request $request)
    {
        return $this->render('MunicipalesBundle:Consultation:step1.html.twig');
    }
}
