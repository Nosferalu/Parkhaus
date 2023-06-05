# Parkhaus 

To run the Webserver, execute commands "docker-compose up -d" and "symfony server:start -d"!
To Setup the Database, use the command "docker-compose exec www bin/console app:create-tables"
To Setup the Inital Parking Spot data, use the command "docker-compose exec www bin/console app:fill-database"


When there's an error with that, user "docker-compose exec www bash" and install "docker-php-ext-install pdo_mysql"
After that, restart the container with "docker-compose restart www"