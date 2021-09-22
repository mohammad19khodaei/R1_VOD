# Arvan Cloud Challenge

## Installation and Run
To install the application please run the following commands
* Run `git clone https://github.com/mohammad19khodaei/R1_VOD.git` to clone the application
* Run `cd R1_VOD` to enter the application directory
* Run `cp .env.example .env` to have an env config and edit .env file as you wish
* Run `docker-compose up -d` and wait for all the containers goes live
* Run `docker-compose exec app bash` to access the app container shell
* Run `composer install` inside the container to install the dependencies
* Run `php artisan key:generate` inside the container to generate application key
* Run `php artisan migrate` inside the container to migrate db
