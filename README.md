# municipales2015
Lista abierta para las municipales 2015

== Municipales 2015 ==

La lista abierta para municipales 2015.

== Install ==

For install this project is pretty easy with composer:

    $ composer install
    
=== Creating the database ===

For create the database use the Symfony console:

    $ php app/console doctrine:database:create

If you need drop the entire database use (be careful, you will lose all the data):

    $ php app/console doctrine:database:drop --force
    
If you need show the schema updates between versions use:

    $ php app/console doctrine:schema:update --dump-sql

If you want apply the schema updates use:

    $ php app/console doctrine:schema:update --force
    
== Loading fixtures ==

You can load the base fixtures with:

    $ php app/console doctrine:fixtures:load

If you need the raw SQL queries you can load with the following commands:

    $ cat src/Listabierta/Bundle/MunicipalesBundle/Resources/fixtures/provinces_spain.sql | mysql listabierta
    $ cat src/Listabierta/Bundle/MunicipalesBundle/Resources/fixtures/municipalities_spain.sql | mysql listabierta
    
== Third parties ==

* Tractis TSA
    
    This project use Tractis TSA (https://www.tractis.com/home/webservices/tsa) to cipher vote results. You will need
    create an API key here: https://www.tractis.com/webservices/tsa/apikeys

* SMS inbound
    
    For validate mobile phones this project use a SMS inbound number. You will need reserve some number. Currently the
    project uses Nexmo API (nexmo.com) as free SMS inbound provider 
    
== Deploying with Capifony ==

This project could be deployed using Capifony.

For use capifony in Ubuntu you will need install the following dependencies

    $ sudo apt-get install -y nginx ruby git php5 php5-fpm php5-mysql php5-cli php5-curl php-apc acl
    $ sudo gem install capifony

Then you can clone the repository with:

    # git clone https://github.com/listabierta/municipales2015.git
    # cd municipales2015
    
Then perform a cold installation:

	# cap symfony:doctrine:database:create
	
The following command will ask you the initial parameters for configure the project

	# cap deploy:setup
	
Then perform a normal deploy:

	# cap deploy
	
For simply update the repository and deploy:

	# cap deploy:update
    
== Recommendations ==

We recomend use Percona 5.6 as database server. This script could install easyly for you on Ubuntu servers

    # wget https://raw.githubusercontent.com/shakaran/scripts/master/ubuntu-percona-5.6-setup.sh -O - | bash
