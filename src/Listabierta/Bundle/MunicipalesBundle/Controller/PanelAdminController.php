<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PanelAdminController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->render('MunicipalesBundle:PanelAdmin:index.html.twig');
    }
}
