<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CandidacyController extends Controller
{
    public function indexAction(Request $request)
    {
        $this->step1Action();
    }
    
    public function step1Action(Request $request)
    {
    	return $this->render('MunicipalesBundle:Candidacy:step1.html.twig');
    }
}
