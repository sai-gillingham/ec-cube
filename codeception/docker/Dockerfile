FROM codeception/codeception
CMD echo "W"
RUN apt-get update && apt install -y netcat

CMD echo "W"
COPY docker-php-entrypoint.sh /usr/local/bin/
WORKDIR /var/www/html



ENTRYPOINT ["/usr/local/bin/docker-php-entrypoint.sh"]
