<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;

class SMSInboundController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$query = $request->query;
		//$msisdn = $query->msisdn;

		$phone = '555443322';
		$email = $this->container->getParameter('admin_email');
		
		// Look the phone and mail to verify
		$entity_manager = $this->getDoctrine()->getManager();
		$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
		 
		$phone_verified = $phone_verified_repository->findOneBy(array('phone' => $phone, 'email' => $email));
		
		if(!empty($phone_verified) && $phone_verified->getTimestamp() == 0)
		{
			$phone_verified->setTimestamp(time());
			
			$entity_manager->persist($phone_verified);
			$entity_manager->flush();
			
			$message = \Swift_Message::newInstance()
			->setSubject('Telefono movil ' . $phone . ' verificado correctamente' . implode(',', $query->all()) . implode(',', $request->all()) )
			->setFrom('verificaciones@municipales2015.listabierta.org', 'Verificaciones')
			->setTo($email)
			->setBody(
					$this->renderView(
							'MunicipalesBundle:Mail:sms_inbound_verification.html.twig',
							array('phone' => $phone)
					)
			)
			;
			$this->get('mailer')->send($message);
		}
		else 
		{
			echo 'No phone found or already verified';
		}
		
		return new Response('OK', 200);
	}
	
	public function callbackAction(Request $request = NULL)
	{
		$query = $request->query;
		//$msisdn = $query->msisdn;

		return new Response('OK', 200);
	}
}
