<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\Consultation\ConsultationStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Consultation\ConsultationStep2Type;

use Symfony\Component\Form\FormError;

class ConsultationController extends Controller
{
    public function step1Action(Request $request, $token = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	 
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío',
    		));
    		
    		// @todo pregenerate tokens in census
    		// @todo send email with tokens
    		// @todo search token in census and validate
    				// If already send
    				// Check vote date
    				
    	}
    	else
    	{
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
    				return $this->step2Action($request);
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
    	$session = $this->getRequest()->getSession();
    	
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío',
    		));
    	}
    	else
    	{
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
    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$session = $this->getRequest()->getSession();
    	 
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Consultation:unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío',
    		));
    	}
    	else
    	{
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
    		
    		//$consultation = new Consultation();
    		//$consultation->setId();
    		//$consultation->setData($data);
    		
    		//$consultation->setTimestamp($data);
    		
    		// Tractis here
    		
	    	return $this->render('MunicipalesBundle:Consultation:step3.html.twig', array(
	    			'token' => $token,
	    		)
	    	);
    	}
    }
}
