# Arvan Cloud Challenge

## Installation
To install the application please run the following commands
* Run `git clone https://github.com/mohammad19khodaei/R1_VOD.git` to clone the application
* Run `cd R1_VOD` to enter the application directory
* Run `cp .env.example .env` to have an env config and edit .env file as you wish
* Run `docker-compose up -d` and wait for all the containers goes live
* Run `docker-compose exec --user=arvan app bash` to access the app container shell
* Run `composer install` to install the dependencies
* Run `php artisan key:generate` to generate application key
* Run `php artisan migrate --seed` to migrate db with Seeder
* Run `php artisan jwt:secret` to generate jwt secret key
* Run `php artisan queue:work --queue=email` to run for sending email

## Login Credentials
You can log in to the application using following credentials
* email: `admin@arvan.com`
* password: `secret`

## Database
It's the structure of new tables

![database](/home/mohammad/Downloads/arvan_vod_db.png)
