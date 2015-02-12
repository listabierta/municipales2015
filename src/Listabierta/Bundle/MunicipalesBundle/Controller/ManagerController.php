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
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function verifyPhoneAction(Request $request = NULL, $phone = NULL)
	{
		$entity_manager = $this->getDoctrine()->getManager();
		$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
			
		$phone_verified = $phone_verified_repository->findOneBy(array('phone' => $phone));
		
		if(!empty($phone_verified) && $phone_verified->getTimestamp() == 0)
		{
			$phone_verified->setTimestamp(time());
			
			$entity_manager->persist($phone_verified);
			$entity_manager->flush();
		}
		
		return new Response('OK', 200);
	}
	
	public function purgeCandidaciesAction(Request $request = NULL)
	{
		return new Response('OK', 200);
	}
	
	public function purgeVotersAction(Request $request = NULL)
	{
		return new Response('OK', 200);
	}
	
	public function purgeCandidatesAction(Request $request = NULL)
	{
		return new Response('OK', 200);
	}
}
