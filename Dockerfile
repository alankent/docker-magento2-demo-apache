FROM ubuntu:trusty
MAINTAINER Alan Kent

# Get Apache, mysql client, PHP etc (subset of a full LAMP stack - no MySQL server)
RUN apt-get update && apt-get install -y apache2 mysql-client php5 php5-curl php5-mcrypt php5-gd php5-mysql curl git

# mcrypt.ini appears to be missing from apt-get install. Needed for PHP mcrypt library to be enabled.
ADD config/20-mcrypt.ini /etc/php5/cli/conf.d/20-mcrypt.ini

# Environment variables from /etc/apache2/apache2.conf
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

# Enable Apache rewrite module
RUN a2enmod rewrite

# Add the Apache virtual host file
ADD config/apache_default_vhost /etc/apache2/sites-available/default

# Install Magento 2
RUN mkdir /var/www/magento2
ADD config/composer.json /var/www/magento2/composer.json
ADD scripts/install-magento2 /var/www/install-magento2
RUN bash -x /var/www/install-magento2

# Expose the web server port
EXPOSE 80

# Start up the Apache server
# (Commented out for now - we start up a bash shell instead while testing)
#ENTRYPOINT ["/usr/sbin/apache2"]
#CMD ["-D", "FOREGROUND"]
