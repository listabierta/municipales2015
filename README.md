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
