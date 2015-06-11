<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\Consultation\ConsultationStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Consultation\ConsultationStep2Type;

use Symfony\Component\Form\FormError;
use Listabierta\Bundle\MunicipalesBundle\Entity\CensusUserRepository;
use Listabierta\Bundle\MunicipalesBundle\Entity\Consultation;

use Listabierta\Bundle\MunicipalesBundle\Lib\tractis\SymfonyTractisApi;

class ConsultationController extends Controller
{
	private function checkCloseTime()
	{
		$close_time = new \Datetime('2015-06-11', new \DateTimeZone('Europe/Madrid'));
		$close_time->setTime(8, 0, 0); // 08:00 AM
		 
		$now = new \Datetime('NOW', new \DateTimeZone('Europe/Madrid'));
		
		if($now->getTimestamp() - $close_time->getTimestamp() > 0)
		{
			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
					'error' => 'Lo sentimos, el plazo de votación ha finalizado. <br /> <br />
    							Hora actual: ' . date('H:i:s d-m-Y' , $now->getTimestamp()) . '<br />
    							Fecha de cierre: ' . date('H:i:s d-m-Y' , $close_time->getTimestamp()) . '<br />',
			));
		}
		
		return NULL;
	}
	
	private function checkAdminOpenTime()
	{
		$close_time = new \Datetime('2015-06-11', new \DateTimeZone('Europe/Madrid'));
		$close_time->setTime(22, 30, 0); // 08:00 AM
			
		$now = new \Datetime('NOW', new \DateTimeZone('Europe/Madrid'));
	
		if($now->getTimestamp() - $close_time->getTimestamp() < 0)
		{
			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
					'error' => 'Aun no pueden ser consultados los resultados de la votación. <br /> <br />
    							Hora actual: ' . date('H:i:s d-m-Y' , $now->getTimestamp()) . '<br />
    							Fecha de apertura: ' . date('H:i:s d-m-Y' , $close_time->getTimestamp()) . '<br />',
			));
		}
	
		return NULL;
	}
	
    public function step1Action(Request $request, $token = NULL)
    {
    	$is_closed = $this->checkCloseTime();
    	
    	if(!empty($is_closed)) 
    	{
    		return $is_closed;
    	}
    	
    	$entity_manager = $this->getDoctrine()->getManager();
    	$session = $this->getRequest()->getSession();
    	 
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío en paso 1',
    		));
    		
    		// @todo send email with tokens
   			// @todo Check vote date
    	}
    	else
    	{
    		$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUser');
    		$census_user = $census_user_repository->findOneByToken($token);
    		
    		if(empty($census_user))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error: El token ya ha sido consumido o no es válido',
    			));
    		}
    		
    		$translator = $this->get('translator');
    		$translations = array();
    		
    		$translations['consultation.step1.first_question_option_A'] = $translator->trans('consultation.step1.first_question_option_A');
    		$translations['consultation.step1.first_question_option_B'] = $translator->trans('consultation.step1.first_question_option_B');
    		
    		$translations['consultation.step1.second_question_option_A'] = $translator->trans('consultation.step1.second_question_option_A');
    		$translations['consultation.step1.second_question_option_B'] = $translator->trans('consultation.step1.second_question_option_B');
    		 
    		$form = $this->createForm(new ConsultationStep1Type($translations), NULL, array(
    				'action' => $this->generateUrl('consultation_step1', array('token' => $token) ),
    				'method' => 'POST',
    			)
    		);
    		
    		$form->handleRequest($request);
    		
    		$ok = TRUE;
    		if ($form->isValid())
    		{
    			$session->clear();
    			
    			$first_question = $form['first_question']->getData();
    		
    			if(empty($first_question))
    			{
    				$form->addError(new FormError('Debes elegir una respuesta en la primera pregunta para continuar'));
    				$ok = FALSE;
    			}
    			else 
    			{
    				$session->set('first_question', $first_question);
    			}
    			
    			if($first_question == 'b')
    			{
    				if(empty($first_question))
    				{
    					$form->addError(new FormError('Debes elegir una respuesta en la segunda pregunta para continuar'));
    					$ok = FALSE;
    				}
    				else
    				{
	    				$second_question = $form['second_question']->getData();
	    				$session->set('second_question', $second_question);
    				}
    			}
    		
    			if($ok)
    			{
    				return $this->step2Action($request, $token);
    			}
    		}
    		
    		return $this->render('MunicipalesBundle:Consultation:step1.html.twig', array(
    				'token' => $token,
    				'form'  => $form->createView(),
    			)
    		);
    	}
    }
    
    public function step2Action(Request $request, $token = NULL)
    {
    	$is_closed = $this->checkCloseTime();
    	 
    	if(!empty($is_closed))
    	{
    		return $is_closed;
    	}
    	
    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$session = $this->getRequest()->getSession();
    	
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío en paso 2.',
    		));
    	}
    	else
    	{
    		$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUser');
    		$census_user = $census_user_repository->findOneByToken($token);
    		
    		if(empty($census_user))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error: El token ya ha sido consumido o no es válido',
    			));
    		}
    		
    		$first_question = $session->get('first_question');

    		if(empty($first_question))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error: Sesión expirada. Debes elegir una respuesta en la primera pregunta para continuar',
    			));
    		}

    		$translator = $this->get('translator');
    		$first_question_text = $translator->trans('consultation.step1.first_question_option_' . strtoupper($first_question));
    		
    		$output_msg = 'Elegiste opción ' . $first_question_text;
    		if($first_question == 'b')
    		{
    			$second_question = $session->get('second_question');
    			
    			if(empty($second_question))
    			{
    				return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    						'error' => 'Error: Sesión expirada. Debes elegir una respuesta en la segunda pregunta para continuar',
    				));
    			}
    			else
    			{
    				$second_question_text = $translator->trans('consultation.step1.second_question_option_' . strtoupper($second_question));
    				
    				$output_msg .= ' entonces ' . $second_question_text;
    			}
    		}
    		
    		$form = $this->createForm(new ConsultationStep2Type(), NULL, array(
    				'action' => $this->generateUrl('consultation_step3', array('token' => $token) ),
    				'method' => 'POST',
    			)
    		);
    		
    		return $this->render('MunicipalesBundle:Consultation:step2.html.twig', array(
	    			'token' => $token,
	    			'form'  => $form->createView(),
    				'output_msg' => $output_msg,
	    		)
	    	);
	    	
    	}
    }
    
    public function step3Action(Request $request, $token = NULL)
    {
    	$is_closed = $this->checkCloseTime();
    	 
    	if(!empty($is_closed))
    	{
    		return $is_closed;
    	}
    	
    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$session = $this->getRequest()->getSession();
    	 
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío en paso 3',
    		));
    	}
    	else
    	{
    		$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUser');
    		$census_user = $census_user_repository->findOneByToken($token);
    		
    		if(empty($census_user))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error: El token ya ha sido consumido o no es válido',
    			));
    		}
    		
    		$data = array();
    		
    		$first_question = $session->get('first_question');
    		
    		if(empty($first_question))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error: Sesión expirada. Debes elegir una respuesta en la primera pregunta para continuar',
    			));
    		}
    		
    		$data['first'] = $first_question;
    		
    		if($first_question == 'b')
    		{
    			$second_question = $session->get('second_question');
    			 
    			if(empty($second_question))
    			{
    				return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    						'error' => 'Error: Sesión expirada. Debes elegir una respuesta en la segunda pregunta para continuar',
    				));
    			}
    			else
    			{
    				$data['second'] = $second_question;
    			}
    		}
 
    		$census_user_id    = $census_user->getId();
    		$census_user_email = $census_user->getEmail();
    		$census_user_name  = $census_user->getName();
    		
    		$data_info = array();
    		
    		$data_info['census_user_id'] = $census_user_id;
    		$data_info['data'] = $data;
    		$data_info['token'] = $token;

    		$consultation = new Consultation();
    		$consultation->setCensusUserId($census_user_id);
    		$consultation->setData($data);
    		$consultation->setToken($token);
    		
    		$entity_manager->persist($consultation);
    		$entity_manager->flush();
    		
    		// Release the token in census user
    		$census_user->setToken(NULL);
    		$entity_manager->persist($census_user);
    		$entity_manager->flush();
    		
    		// Tractis TSA sign
				
    		// Create an API Key here: https://www.tractis.com/webservices/tsa/apikeys
    		$tractis_api_identifier = $this->container->getParameter('tractis_api_identifier');
    		$tractis_api_secret     = $this->container->getParameter('tractis_api_secret');
    			
    		// Fetch the chain sign TSA file
    		$tsa_cert_chain_file = $this->container->get('kernel')->locateResource('@MunicipalesBundle/Lib/tractis/chain.txt');
    			
    		// Init the Symfony Tractis TSA Api
    		$symfony_tractis_api = new SymfonyTractisApi($tractis_api_identifier, $tractis_api_secret, $tsa_cert_chain_file);
    			
    		// Sign a valid vote
    		try
    		{
    			$response = $symfony_tractis_api::sign(serialize($data_info));
    		}
    		catch(\Exception $e)
    		{
    			if($e->getMessage() == 'The Timestamp was not found')
    			{
    				return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    						'warning' => 'Tu voto ha sido correctamente emitido, pero esta pendiente de ser sellado,
							ya que la plataforma de sellado Tractis en estos momentos no esta disponible,
							recibiras un correo cuando la plataforma de sellado este actíva de nuevo y
							tu voto será totalmente procesado y válido.',
    				));
    			}
    			else
    			{
    				return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    						'error' => 'Error grave en el firmado de voto TSA. Respuesta de tractis: ' . $e->getMessage(),
    				));
    			}
    		}
    			
    		// Check response data
    		if(empty($response))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error en el firmado de voto TSA. Respuesta vacía',
    			));
    		}
    		
    		if(empty($response['response_string']))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error en el firmado de voto TSA. Respuesta con cadena vacía',
    			));
    		}
    		
    		if(empty($response['response_time']))
    		{
    			return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    					'error' => 'Error en el firmado de voto TSA. Respuesta con tiempo vacía',
    			));
    		}
    		
    		// Fetch the data response if valid
    		$response_string = $response['response_string'];
    		$response_time   = $response['response_time'];
    			
    		// Store the sign TSA result in database
    		$consultation->setResponseString($response_string);
    		$consultation->setResponseTime($response_time);
    			
    		$entity_manager->persist($consultation);
    		$entity_manager->flush();

    		
    		// Send mail with vote confirmation
    		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    		
    		$message = \Swift_Message::newInstance()
    		->setSubject('Tu voto ha sido emitido correctamente')
    		->setFrom('info@' . rtrim($host, '.'), 'Informacion Censo')
    		->setTo($census_user_email)
    		->setBody(
    				$this->renderView(
    						'MunicipalesBundle:Mail:vote_finished.html.twig',
    						array(
    								'name' => $census_user_name,
    								'response_string' => $response_string,
    								'response_time' => $response_time,
    						)
    				), 'text/html'
    		);
    		 
    		$this->get('mailer')->send($message);
    		
    		
    		
	    	return $this->render('MunicipalesBundle:Consultation:step3.html.twig', array(
	    			'token' => $token,
	    		)
	    	);
    	}
    }
    
    public function resultAction(Request $request)
    {
    	$is_closed = $this->checkAdminOpenTime();
    	
    	if(!empty($is_closed))
    	{
    		return $is_closed;
    	}

    	$entity_manager = $this->getDoctrine()->getManager();
    	 
    	$session = $this->getRequest()->getSession();
    	
    	$consultation_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Consultation');
    	$consultations = $consultation_repository->findAll();
    	
    	$votes_emitted = 0;
    	$counter = array();
    	$counter['first']['a'] = 0;
    	$counter['first']['b'] = 0;
    	$counter['second']['a'] = 0;
    	$counter['second']['b'] = 0;
  
    	foreach($consultations as $consultation)
    	{
    		if(!empty($consultation->getResponseTime()) && !empty($consultation->getResponseString()))
    		{
    			$data = $consultation->getData();
    			
    			if(isset($data['first']) && $data['first'] == 'a')
    			{
    				$counter['first']['a'] += 1;
    			} 
    			
    			if(isset($data['first']) && $data['first'] == 'b')
    			{
    				$counter['first']['b'] += 1;
    				
    				if(isset($data['second']) && $data['second'] == 'a')
    				{
    					$counter['second']['a'] += 1;
    				}
    				
    				if(isset($data['second']) && $data['second'] == 'b')
    				{
    					$counter['second']['b'] += 1;
    				}
    			}
    			
    			$votes_emitted += 1;
    		}
    	}
    	
    	return $this->render('MunicipalesBundle:Consultation:result.html.twig', array(
    			'votes_emitted' => $votes_emitted,
    			'counter' => $counter,
    		)
    	);
    }
}
