<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SMSInboundController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$query = $request->query;
		//$msisdn = $query->msisdn;

		$phone = 1234;
		
		$message = \Swift_Message::newInstance()
		->setSubject('Telefono movil ' . $phone . ' verificado correctamenet')
		->setFrom('verificaciones@municipales2015.listabierta.org')
		->setTo($this->container->getParameter('admin_email'))
		->setBody(
				$this->renderView(
						'MunicipalesBundle:Mail:sms_inbound_verification.html.twig',
						array('phone' => $phone)
				)
		)
		;
		$this->get('mailer')->send($message);
		
		return new Response('OK', 200);
	}
	
	public function callbackAction(Request $request = NULL)
	{
		$query = $request->query;
		//$msisdn = $query->msisdn;

		return new Response('OK', 200);
	}
}
