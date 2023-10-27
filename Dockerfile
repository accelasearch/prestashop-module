FROM prestashop/prestashop:1.7.6.9

RUN apt update && apt install -y nano wget curl sshpass git unzip zip

RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer

COPY entrypoint.sh /tmp/post-install-scripts/entrypoint.sh