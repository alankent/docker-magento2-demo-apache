FROM ubuntu:trusty
MAINTAINER Alan Kent

# Get Apache, mysql client, PHP etc (subset of a full LAMP stack - no MySQL server)
RUN apt-get update && apt-get install -y apache2 mysql-client php5 php5-curl php5-mcrypt php5-gd php5-mysql php5-intl curl git

# mcrypt.ini appears to be missing from apt-get install. Needed for PHP mcrypt library to be enabled.
ADD config/20-mcrypt.ini /etc/php5/cli/conf.d/20-mcrypt.ini
ADD config/20-mcrypt.ini /etc/php5/apache2/conf.d/20-mcrypt.ini

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
ADD config/apache_default_vhost /etc/apache2/sites-enabled/magento2.conf
RUN rm -f /etc/apache2/sites-enabled/000-default.conf

# Add the MySQL client configuration file (no server settings)
ADD config/my.cnf /etc/mysql/my.cnf

# Install Magento 2
RUN mkdir /var/www/magento2
ADD config/composer.json /var/www/magento2/composer.json
ADD scripts/install-magento2 /var/www/install-magento2
RUN bash -x /var/www/install-magento2

# Add images used for demo queries.
ADD media/MJ01-YE_main_cropped.jpg /var/www/magento2/htdocs/pub/media/catalog/product/m/j/mj01-ye_main_cropped.jpg
ADD media/MJ01-YE_back_cropped.jpg /var/www/magento2/htdocs/pub/media/catalog/product/m/j/mj01-ye_back_cropped.jpg
ADD media/MJ03-GN_main_cropped.jpg /var/www/magento2/htdocs/pub/media/catalog/product/m/j/mj03-gn_main_cropped.jpg
ADD media/MJ07-BL_main_cropped.jpg /var/www/magento2/htdocs/pub/media/catalog/product/m/j/mj07-bl_main_cropped.jpg
ADD dump/magento2.sql /var/www/magento2/magento2.sql
RUN chown www-data:www-data -R /var/www/magento2/htdocs/pub/media/catalog

# Expose the web server port
EXPOSE 80

# Start up the Apache server
ADD scripts/runserver /usr/local/bin/runserver
RUN chmod +x /usr/local/bin/runserver
ENTRYPOINT ["bash", "-c"]
CMD ["/usr/local/bin/runserver"]
