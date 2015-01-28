<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStepVerifyType;
use Listabierta\Bundle\MunicipalesBundle\Entity\Candidate;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;

class CandidateController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	public function step1Action($address = NULL, Request $request = NULL)
	{
		$session = $this->getRequest()->getSession();
		
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');

		$address_slug = $this->get('slugify')->slugify($address);
		
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
		
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));		
		}
		
		$candidacy_from = $admin_candidacy->getFromdate();
		$candidacy_to = $admin_candidacy->getTodate();
		
		if(empty($candidacy_from))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe fecha de candidatura de inicio para ' . $address_slug,
			));
		}
		
		if(empty($candidacy_to))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe fecha de candidatura de inicio para ' . $address_slug,
			));
		}
		
		$now = new \Datetime('NOW');
		//$now->add(\DateInterval::createFromDateString('+7 days')); // Debugging

		if($now->getTimestamp() - $candidacy_from->getTimestamp() < 0)
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'La candidatura aún no esta abierta para ' . $address_slug . ', la fecha de apertura es ' . $candidacy_from->format('d-m-Y'),
			));
		}
		
		if($now->getTimestamp() - $candidacy_to->getTimestamp() > 0)
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'La candidatura esta cerrada para ' . $address_slug . ', la fecha de finalización fue ' . $candidacy_to->format('d-m-Y'),
			));
		}

		$form = $this->createForm(new CandidateStep1Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step1', array('address' => $address)),
				'method' => 'POST',
			)
		);
		 
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$name     = $form['name']->getData();
			$lastname = $form['lastname']->getData();
			$dni      = $form['dni']->getData();
			$email    = $form['email']->getData();
			$phone    = $form['phone']->getData();
			
			
			$entity_manager = $this->getDoctrine()->getManager();
			 
			// Store info in database AdminCandidacy
			$candidate = new Candidate();
			$candidate->setName($name);
			$candidate->setLastname($lastname);
			$candidate->setDni($dni);
			$candidate->setEmail($email);
			$candidate->setPhone($phone);
			 
			$entity_manager->persist($candidate);
			$entity_manager->flush();
			 
			// Store email and phone in database as pending PhoneVerified without timestamp
			$phone_verified = new PhoneVerified();
			$phone_verified->setPhone($phone);
			$phone_verified->setEmail($email);
			$phone_verified->setTimestamp(0);
			
			$entity_manager->persist($phone_verified);
			$entity_manager->flush();
			
			$session->set('candidate_id', $candidate->getId());
			$session->set('candidate_name', $name);
			$session->set('candidate_lastname', $lastname);
			$session->set('candidate_dni', $dni);
			$session->set('candidate_email', $email);
			$session->set('candidate_phone', $phone);
			
			$form2 = $this->createForm(new CandidateStepVerifyType(), NULL, array(
					'action' => $this->generateUrl('candidate_step_verify', array('address' => $address)),
					'method' => 'POST',
			));
			 
			$form2->handleRequest($request);
			
			return $this->render('MunicipalesBundle:Candidacy:step_verify.html.twig', array(
					'form' => $form2->createView()
				)
			);
			
		}

		return $this->render('MunicipalesBundle:Candidate:step1.html.twig', array(
				'address' => $address, 
				'town' => $admin_candidacy->getTown(),
				'todate' => $candidacy_to,
				'fromdate' => $candidacy_from,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}
}
