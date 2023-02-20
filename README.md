# New Parser for AppCake

# **Project Docker Setup**

This repository contains a Docker setup for a Symfony project, including the required services for the application to run, such as PHP, Nginx, MySQL and RabbitMQ. The following instructions will guide you through setting up the project and running it.

## Stack:

*   Symfony 5.4
*   Php 7.4
*   Mysql
*   Bootstrap 5.1
*   Docker (docker-compose)
*   RabbitMQ

## **Requirements**

Before proceeding, you need to make sure you have the following tools installed on your machine:

*   [Docker](https://docs.docker.com/get-docker/)
*   [Docker Compose](https://docs.docker.com/compose/install/)

## **Setup**

**Clone the repository** 

`**e.g. to news-parser/**`

Copy the contents of the **docker** folder to the root of your project:

```bash
cp -R docker/* news-parser/
```

Copy the **.env.example** file and name it **.env**:

```bash
cp news-parser/.env.example news-parser/.env
```

Update the **.env** file with your project-specific environment variables.

Build and start the containers:

```bash
cd news-parser/
docker-compose up -d --build
```

Install dependencies and create database:

```bash
docker exec php74-container composer install
docker exec php74-container php bin/console doctrine:database:create
docker exec php74-container php bin/console doctrine:migrations:migrate
docker exec php74-container php bin/console doctrine:fixtures:load --append
```

Import RabbitMQ config file

```bash
docker exec rabbitmq-container rabbitmqctl import_definitions rabbit_config.json     
```

Visit **http://localhost:8080** in your browser to see the application running.

Visit **http://localhost:15672/** in your browser to open RabbitMQ management gui.

## **Running commands**

To run Symfony commands, execute the following command:

```bash
docker exec php74-container php bin/console <command>
```

For example, to run the **app:news:consume** command to **fetch** fresh news from **highload.today/**:

```bash
docker exec php74-container php bin/console app:news:consume
```

## **Stopping the containers**

To stop the containers, execute the following command:

```bash
docker-compose down
```

## **Contributing**

Feel free to contribute to this repository by opening a pull request.

## Directory Hierarchy

```
|—— LICENSE
|—— app
|    |—— .env
|    |—— .gitignore
|    |—— LICENSE
|    |—— bin
|        |—— console
|    |—— composer.json
|    |—— composer.lock
|    |—— config
|        |—— bundles.php
|        |—— packages
|            |—— cache.yaml
|            |—— doctrine.yaml
|            |—— doctrine_migrations.yaml
|            |—— framework.yaml
|            |—— knp_paginator.yaml
|            |—— old_sound_rabbit_mq.yaml
|            |—— routing.yaml
|            |—— security.yaml
|            |—— translation.yaml
|            |—— twig.yaml
|            |—— validator.yaml
|            |—— web_profiler.yaml
|        |—— preload.php
|        |—— routes
|            |—— annotations.yaml
|            |—— framework.yaml
|            |—— web_profiler.yaml
|        |—— routes.yaml
|        |—— security.yaml
|        |—— services.yaml
|    |—— docker-compose.override.yml
|    |—— docker-compose.yml
|    |—— migrations
|        |—— .gitignore
|        |—— Version20230215120000.php
|    |—— public
|        |—— index.php
|    |—— src
|        |—— Command
|            |—— NewsConsumerCommand.php
|        |—— Consumer
|            |—— .gitignore
|        |—— Controller
|            |—— .gitignore
|            |—— DashboardController.php
|            |—— NewsController.php
|            |—— Response.php
|            |—— SecurityController.php
|        |—— DataFixtures
|            |—— AppFixtures.php
|            |—— UserSeeder.php
|        |—— Entity
|            |—— .gitignore
|            |—— News.php
|            |—— User.php
|        |—— Form
|            |—— Type
|                |—— LoginType.php
|        |—— Kernel.php
|        |—— MessageHandler
|            |—— NewsConsumer.php
|        |—— Repository
|            |—— .gitignore
|            |—— NewsRepository.php
|            |—— UserRepository.php
|        |—— Security
|            |—— AdminAuthenticator.php
|    |—— symfony.lock
|    |—— templates
|        |—— base.html.twig
|        |—— dashboard.html.twig
|        |—— security
|            |—— login.html.twig
|    |—— translations
|        |—— .gitignore
|    |—— var
|        |—— ...
|        |—— log
|    |—— vendor
|        |—— autoload.php
|        |—— autoload_runtime.php
|        |—— bin
|            |—— carbon
|            |—— doctrine
|            |—— doctrine-dbal
|            |—— doctrine-migrations
|            |—— patch-type-declarations
|            |—— php-parse
|            |—— sql-formatter
|            |—— var-dump-server
|            |—— yaml-lint
|        |—— composer
|            |—— ClassLoader.php
|            |—— InstalledVersions.php
|            |—— LICENSE
|            |—— autoload_classmap.php
|            |—— autoload_files.php
|            |—— autoload_namespaces.php
|            |—— autoload_psr4.php
|            |—— autoload_real.php
|            |—— autoload_static.php
|            |—— installed.json
|            |—— installed.php
|            |—— platform_check.php
|        |—— ...
|—— crontab
|—— docker-compose.yml
|—— mysql
|    |—— auto.cnf
|    |—— ca.pem
|    |—— client-cert.pem
|    |—— ib_buffer_pool
|    |—— ib_logfile0
|    |—— ib_logfile1
|    |—— ibdata1
|    |—— ibtmp1
|    |—— mysql
|        |—— ...
|    |—— news_db
|        |—— db.opt
|        |—— doctrine_migration_versions.frm
|        |—— doctrine_migration_versions.ibd
|        |—— news.frm
|        |—— news.ibd
|        |—— users.frm
|        |—— users.ibd
|    |—— performance_schema
|        |—— ...
|    |—— public_key.pem
|    |—— server-cert.pem
|    |—— sys
|        |—— ...
|—— nginx
|    |—— default.conf
|—— php
|    |—— Dockerfile
|    |—— crontab
|—— rabbit_config.json
```