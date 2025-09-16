FROM ubuntu:latest

RUN apt update && apt install -y apache2

RUN apt install -y php libapache2-mod-php php-mysql php-zip php-gd php-xml php-mbstring php-cli php-intl php-zip

COPY ./code/ /var/www/html

COPY ./000-default.conf /etc/apache2/sites-available/

RUN rm -f /var/www/html/index.html

RUN chmod -R 777 /var/www/html/storage/logs

RUN chmod -R 777 /var/www/html/storage/framework

RUN a2enmod rewrite

#RUN a2dissite 000-default.conf

RUN a2ensite 000-default.conf

CMD ["apachectl","-D","FOREGROUND"]

EXPOSE 80
