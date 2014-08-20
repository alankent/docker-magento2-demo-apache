FROM ubuntu:trusty

MAINTAINER Alan Kent

RUN apt-get update && apt-get install -y apache2 mysql-client php5 php5-curl php5-mcrypt php5-gd php5-mysql

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# Enable Apache rewrite module
RUN a2enmod rewrite

# Add the Apache virtual host file
ADD apache_default_vhost /etc/apache2/sites-available/default

ADD scripts/install-magento2 /tmp/install-magento2
RUN bash -x /tmp/install-magento2
ADD composer.json /tmp/install-magento2

EXPOSE 80

ENTRYPOINT ["/usr/sbin/apache2"]
CMD ["-D", "FOREGROUND"]
