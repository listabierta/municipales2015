<?php 
namespace Listabierta\Bundle\MunicipalesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCommunitiesData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	
	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}
	
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
    	$fixture_file = $this->container->get('kernel')
    					->locateResource('@MunicipalesBundle/Resources/fixtures/autonomous_communities_spain.sql');
    	 
    	// Read file contents
    	$sql_data = file_get_contents($fixture_file); 
    	// Execute native SQL
    	$manager->getConnection()->exec($sql_data);  
    	
    	$manager->flush();
    	
    	echo 'Autonomus Communities for Spain loaded' . PHP_EOL;
    	
    	$fixture_file = $this->container->get('kernel')
    	->locateResource('@MunicipalesBundle/Resources/fixtures/provinces_spain.sql');
    	
    	// Read file contents
    	$sql_data = file_get_contents($fixture_file);
    	// Execute native SQL
    	$manager->getConnection()->exec($sql_data);
    	 
    	$manager->flush();
    	 
    	echo 'Provinces for Spain loaded' . PHP_EOL;
    	
    	$fixture_file = $this->container->get('kernel')
    	->locateResource('@MunicipalesBundle/Resources/fixtures/municipalities_spain.sql');
    	 
    	// Read file contents
    	$sql_data = file_get_contents($fixture_file);
    	// Execute native SQL
    	$manager->getConnection()->exec($sql_data);
    	
    	$manager->flush();
    	
    	echo 'Municipalities for Spain loaded' . PHP_EOL;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
    	return 1; // the order in which fixtures will be loaded
    }
}