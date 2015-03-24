<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;

class SMSInboundController extends Controller
{
	const KEYWORD_INBOUND = 'ACEPTO';
	
	public function indexAction(Request $request = NULL)
	{
		$logger = $this->get('logger');
		$query = $request->query;
		
		$msisdn = $query->get('msisdn', NULL); // Customer mobile number
		$to = $query->get('to', NULL); // SMS inbound number
		$message_id = $query->get('messageId', NULL);
		$text = $query->get('text', NULL);
		$type = $query->get('type', NULL);
		$keyword = $query->get('keyword', NULL);
		$message_timestamp = $query->get('message-timestamp', NULL);

		// Remove prefix (34) (spain)
		$phone = strlen($msisdn) > 9 && substr($msisdn, 0, 2) == '34' ? substr($msisdn, 2, strlen($msisdn)) : $msisdn;

		$logger->info('SMS Index: ' . $msisdn . ' ' . $to . ' ' . $message_id . ' ' . $text . ' ' . $type . ' ' . $keyword . ' ' . $message_timestamp);
		
		
		// Look the phone and mail to verify
		$entity_manager = $this->getDoctrine()->getManager();
		$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
		 
		$phones_verified = $phone_verified_repository->findBy(array('phone' => $phone));
		
		$result = '';
		// Treat the special case for the same phone and several emails
		foreach($phones_verified as $phone_verified)
		{
			if(!empty($phone_verified) && $phone_verified->getTimestamp() == 0)
			{
				//if($keyword == self::KEYWORD_INBOUND)
				//{
					$email = $phone_verified->getEmail();
					$phone_verified->setTimestamp(time());
					
					$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
					$entity_manager->persist($phone_verified);
					$entity_manager->flush();
					
					// . implode(',', $query->all())
					$message = \Swift_Message::newInstance()
					->setSubject('Tu teléfono movil ' . $phone . ' ha sido verificado correctamente')
					->setFrom('verificaciones@' . $host, 'Verificaciones')
					->setTo($email)
					->setBody(
							$this->renderView(
									'MunicipalesBundle:Mail:sms_inbound_verification.html.twig',
									array('phone' => $phone)
							), 'text/html'
					);
					
					$this->get('mailer')->send($message);
					
					// Check if mode is admin for sent a welcome email confirmation
					if($phone_verified->getMode() == PhoneVerified::MODE_ADMIN)
					{
						// Search admin record from email and phone
						
						// Sent email with admin register data to his own mail with name, password and admin link
						/*
						$message = \Swift_Message::newInstance()
						->setSubject('Tu cuenta administrador ha sido verificada correctamente')
						->setFrom('verificaciones@municipales2015.listabierta.org', 'Verificaciones')
						->setTo($email)
						->setBody(
								$this->renderView(
										'MunicipalesBundle:Mail:sms_inbound_verification.html.twig',
										array('phone' => $phone)
								), 'text/html'
						);
							
						$this->get('mailer')->send($message);
						*/
					}
					
					$result .= 'Verified ' . $phone . ' with mail ' . $email . '<br />';
				//}
				//else 
				//{
				//	echo 'Keyword sms is not valid';
				//}
			}
			else 
			{
				echo 'No phone found or already verified';
			}
		}
		
		return new Response('OK' . $result, 200);
	}
	
	public function callbackAction(Request $request = NULL)
	{
		$logger = $this->get('logger');
		$query = $request->query;
		
		$msisdn = $query->get('msisdn', NULL); // Customer mobile number
		$to = $query->get('to', NULL); // SMS inbound number
		$message_id = $query->get('messageId', NULL);
		$text = $query->get('text', NULL);
		$type = $query->get('type', NULL);
		$keyword = $query->get('keyword', NULL);
		$message_timestamp = $query->get('message-timestamp', NULL);

		// Remove prefix (34) (spain)
		$phone = strlen($msisdn) > 9 && substr($msisdn, 0, 2) == '34' ? substr($msisdn, 2, strlen($msisdn)) : $msisdn;

		
		$logger->info('SMS Callback: ' . $msisdn . ' ' . $to . ' ' . $message_id . ' ' . $text . ' ' . $type . ' ' . $keyword . ' ' . $message_timestamp);
		
		// Look the phone and mail to verify
		$entity_manager = $this->getDoctrine()->getManager();
		$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
		 
		$phones_verified = $phone_verified_repository->findBy(array('phone' => $phone));
		
		$result = '';
		// Treat the special case for the same phone and several emails
		foreach($phones_verified as $phone_verified)
		{
			if(!empty($phone_verified) && $phone_verified->getTimestamp() == 0)
			{
				//if($keyword == self::KEYWORD_INBOUND)
				//{
					$email = $phone_verified->getEmail();
					$phone_verified->setTimestamp(time());
					
					$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
					
					$entity_manager->persist($phone_verified);
					$entity_manager->flush();
					
					// . implode(',', $query->all())
					$message = \Swift_Message::newInstance()
					->setSubject('Tu teléfono movil ' . $phone . ' ha sido verificado correctamente')
					->setFrom('verificaciones@' . $host, 'Verificaciones')
					->setTo($email)
					->setBody(
							$this->renderView(
									'MunicipalesBundle:Mail:sms_inbound_verification.html.twig',
									array('phone' => $phone)
							), 'text/html'
					)
					;
					$this->get('mailer')->send($message);
					
					$result .= 'Verified ' . $phone . ' with mail ' . $email . '<br />';
				//}
				//else 
				//{
				//	echo 'Keyword sms is not valid';
				//}
			}
			else 
			{
				echo 'No phone found or already verified';
			}
		}
		
		return new Response('OK' . $result, 200);
	}
}
