docker run --rm --name magento2 -i -t -p 80:80 --link mysql:mysql -e MYSQL_USER=root -e MYSQL_PASSWORD=admin -e PUBLIC_HOST=akent1-8619.lvs01.dev.ebayc3.com docker-magento2-demo-apache $*
