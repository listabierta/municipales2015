<?php 
namespace Listabierta\Bundle\MunicipalesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('census:token')
            ->setDescription('Fill the token field in census user entities')
            /*->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )*/
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
         $name = $input->getArgument('name');
        if ($name) {
            $text = 'Hello '.$name;
        } else {
            $text = 'Hello';
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }*/

    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUserRepository');
    	$census_users = $census_user_repository->findAll();
    	
    	if(!empty($census_users))
    	{
    		foreach($census_users as $census_user)
    		{
    			if(empty($census_user->getToken()))
    			{
    				$current_time = time();
    				$token = sha1($census_user->getId() + rand(0, 5000) + $current_time);
    				
    				$census_user->setToken($token);
    				$entity_manager->persist($census_user);
    				$output->writeln('Created token [' . .  ' ] for user .');
    			}
    		}
    		
    		$entity_manager->flush();
    	}
    	
        $output->writeln('Done.');
    }
}