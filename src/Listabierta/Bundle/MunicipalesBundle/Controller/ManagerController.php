<?php
namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;

class ManagerController extends Controller
{

	public function indexAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			return new Response('<ul>
									<li><a href="' . $this->generateUrl('manager_verify_phone', array('phone' => 1234)) . '">Verify phone 1234</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_phones') . '">Purge phones</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_candidacies') . '">Purge candidacies</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_candidates') . '">Purge candidates</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_voters') . '">Purge voters</a></li>
									</ul>', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function verifyPhoneAction(Request $request = NULL, $phone = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$result = '';
			$entity_manager = $this->getDoctrine()->getManager();
			$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
				
			$phones_verified = $phone_verified_repository->findBy(array('phone' => $phone));
			
			foreach($phones_verified as $phone_verified)
			{	
				if(!empty($phone_verified) && $phone_verified->getTimestamp() == 0)
				{
					$email = $phone_verified->getEmail();
					$phone_verified->setTimestamp(time());
					
					$entity_manager->persist($phone_verified);
					$entity_manager->flush();
					
					$result .= 'Verified ' . $phone . ' with mail ' . $email . '<br />';
				}
			}
			
			return new Response('OK ' . $result, 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}

	public function purgePhonesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
				
			$phones = $phone_verified_repository->findAll();
				
			if(!empty($phones))
			{
				foreach($phones as $phone)
				{
					$entity_manager->remove($phone);
				}
	
				$entity_manager->flush();
			}
				
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function purgeCandidaciesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
			
			$candidacies = $admin_candidacy_repository->findAll();
			
			if(!empty($candidacies))
			{
				foreach($candidacies as $candidacy)
				{
					$entity_manager->remove($candidacy);
				}
				
				$entity_manager->flush();
			}
			
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function purgeVotersAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
				
			$voters = $voter_repository->findAll();
				
			if(!empty($voters))
			{
				foreach($voters as $voter)
				{
					$entity_manager->remove($voter);
				}
			
				$entity_manager->flush();
			}

			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function purgeCandidatesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			
			$entity_manager = $this->getDoctrine()->getManager();
			$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
				
			$candidates = $candidate_repository->findAll();
				
			if(!empty($candidates))
			{
				foreach($candidates as $candidate)
				{
					$entity_manager->remove($candidate);
				}
			
				$entity_manager->flush();
			}
			
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function listCandidaciesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
				
			$candidacies = $admin_candidacy_repository->findAll();
				
			if(!empty($candidacies))
			{
				
			}
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}		
	
	/**
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function spamTestAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'prod')
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
			 
			$admin_email = $this->container->getParameter('admin_email');
			
			$message = \Swift_Message::newInstance()
			->setSubject('Prueba de correo a ' . $admin_email . ' con host ' . $host)
			->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
			->setTo($admin_email)
			->setBody(
					$this->renderView(
							'MunicipalesBundle:Mail:candidate_rejected.html.twig',
							array(
									'name' => $host,
									'admin_email' => $admin_email
							)
					), 'text/html'
			);
			 
			$this->get('mailer')->send($message);
		}
		else
		{
			return new Response('Access only enabled in prod mode', 403);
		}
	}
}
