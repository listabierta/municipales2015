<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep2Type;

class TownController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	public function vote1Action($address = NULL, Request $request = NULL)
	{
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		 
		if(empty($admin_candidacy) || empty($address))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección <b>' . $address . '</b>',
			));
		}
		
		$town = $admin_candidacy->getTown();
		
		$form = $this->createForm(new TownStep1Type(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step1', array('address' => $address)),
				'method' => 'POST',
			)
		);
		 
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$name     = $form['name']->getData();
			$lastname = $form['lastname']->getData();
			$dni      = $form['dni']->getData();
			$username = $form['username']->getData();
			$password = $form['password']->getData();
			$email    = $form['email']->getData();
			$phone    = $form['phone']->getData();
			
			$entity_manager = $this->getDoctrine()->getManager();
			$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
			
			$voter_username = $voter_repository->findOneBy(array('username' => $username));
			 
			if(!empty($voter_username))
			{
				$form->addError(new FormError('Ya existe un usuario votante registrado con el nombre de usuario ' . $username));
				$ok = FALSE;
			}
			
			$voter_email = $voter_repository->findOneBy(array('email' => $email));
			 
			if(!empty($voter_email))
			{
				$form->addError(new FormError('Ya existe un usuario votante registrado con el email ' . $email));
				$ok = FALSE;
			}
			
			$voter_phone = $voter_repository->findOneBy(array('phone' => $phone));
			 
			if(!empty($voter_phone))
			{
				$form->addError(new FormError('Ya existe un usuario registrado con el teléfono ' . $phone));
				$ok = FALSE;
			}
			
			if($ok)
			{
				$entity_manager = $this->getDoctrine()->getManager();
				 
				// Store info in database AdminCandidacy
				$voter = new Voter();
				$voter->setName($name);
				$voter->setLastname($lastname);
				$voter->setDni($dni);
				$voter->setUsername($username);
				 
				$factory = $this->get('security.encoder_factory');
				$encoder = $factory->getEncoder($voter);
				$encodedPassword = $encoder->encodePassword($password, $voter->getSalt());
			
				$voter->setPassword($encodedPassword);
				$voter->setEmail($email);
				$voter->setPhone($phone);
				 
				$entity_manager->persist($voter);
				$entity_manager->flush();
				 
				// Store email and phone in database as pending PhoneVerified without timestamp
				$phone_verified = new PhoneVerified();
				$phone_verified->setPhone($phone);
				$phone_verified->setEmail($email);
				$phone_verified->setTimestamp(0);
			
				$entity_manager->persist($phone_verified);
				$entity_manager->flush();
			
				$session->set('voter_id', $voter->getId());
				$session->set('voter_name', $name);
				$session->set('voter_lastname', $lastname);
				$session->set('voter_dni', $dni);
				$session->set('voter_phone', $phone);

				$form2 = $this->createForm(new CandidacyStepVerifyType(), NULL, array(
						'action' => $this->generateUrl('municipales_candidacy_step_verify'),
						'method' => 'POST',
				));
				 
				$form2->handleRequest($request);
				 
				return $this->render('MunicipalesBundle:Candidacy:step_verify.html.twig', array(
						'form' => $form2->createView()
					)
				);
			}
		}

		return $this->render('MunicipalesBundle:Town:step1.html.twig', array(
				'town' => $town, 
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}

	public function vote2Action($town = NULL, Request $request = NULL)
	{
		$form = $this->createForm(new TownStep1Type(), NULL, array(
				'action' => $this->generateUrl('municipales_candidacy_step1'),
				'method' => 'POST',
		)
		);
			
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
	
		}
	
		return $this->render('MunicipalesBundle:Town:step1.html.twig', array(
				'town' => $town,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}
	
	public function resultsAction($town = NULL, Request $request = NULL)
	{
		return $this->render('MunicipalesBundle:Town:step_results.html.twig', array(
				'town' => $town,
		));
	}	
	
}
