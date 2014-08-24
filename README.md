# Magento 2 (alpha) Demo

[Magento](http://magento.com/) is an open source ecommerce engine,
developed by eBay Inc powering 240,000+ online ecommerce sites.
Magento 2 is the next major platform release of Magento.

The Magento 2 code base is pushed weekly to GitHub during development.
This Dockerfile is part of a [Panamax](http://panamax.io) contest
submission, but was really done as an experiment to see how well Panamax could
be used to simplify deployment of Magento 2 while it is under development
and in a state of flux. Using [Docker](http://docker.com) and Panamax, all of
the configuration is predone. You just deploy the template and sit back.

## Containers

Only two containers are currently involved - a standard MySQL database
container ('mysql') and the Magento 2 container. The Dockerfile contains
instructions to build Magento 2 using Composer (PHP packaging system). It
also loads a SQL dump of a database with a few sample records so you
can perform queries out of the box. Trying querying for 'jacket'.

To run the default 'mysql' container, please name the container 'mysql'
and expose port 3306 for the Magento 2 container to use. For the purpose
of this demo, set the 'root' account password to 'admin'.

    docker run --name mysql -p 3306:3306 -e MYSQL_ROOT_PASSWORD=admin

The Magento 2 container requires a linkage to the MySQL container and
several additional environment variables.

    docker run --rm --name magento2 -p 80:80 --link mysql:mysql \
       -e MYSQL_USER=root -e MYSQL_PASSWORD=admin \
       -e PUBLIC_HOST=yourhost.example.com \
       alankent/docker-magento2-demo-apache:0.1.0-alpha89

To use the panamax template this will all be set up for you, although
you will need to change the PUBLIC_HOST as Magento uses this to form
URLs to the site.

When this container is started, it loops waiting for a succesful
connection to the MySQL database. Once achieved it runs a PHP script
to create the database. A MySQL dump is then loaded to put a few
products into the database. These two steps can take up to 30 seconds.
Finally Apache is run. After it is running site can be connected to
using a web browser.

## Using Magento 2

Magento 2 is still under active development. A developer beta is due
for release late 2014 with GA in 2015. The steps for installing
Magento 2 are not finalized and are subject to change before final
release. That is in part why this demonstration was put together.
To make it easier for someone to get Magento 2 running.

To access the store front, use http://yourhost.example.com (the host
name supplied as PUBLIC_HOST above). To access the admin interface,
use http://yourhost.example.com/index.php/backend with a username
of 'admin' and password of 'admin123'. (Obviously this is not
intended for production usage!)

Be aware if you start a new container instance, it will wipe the
database and start again.

## Panamax

This container definition was written specifically to work with a
Panamax template. (To date, I have not been able to commit the template
due to GitHub rate limit errors.)

Also note - when you make changes to the Panamax template, it will
currently rebuild the server which includes wiping and recreating the
database. So any database changes you make will be lost. I thought
this suitable for the purpose of this demo (to start from a clean
installation), but this would not be desirable in production.

An interestiong experiment for the reader is to replace the 'mysql'
container with a Maria DB instance.

## About the Author

I am an employee of eBay paid to work on Magento. The Panamax competition
entry was done on my own time in the evenings, but I am exploring Docker
at work as well.
