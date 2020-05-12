# Social-tech Auth

When you find any errors please report, Thank you :)

## Prerequisites

Install [Docker](https://www.docker.com/docker-ubuntu).
and [docker-compose](https://docs.docker.com/compose/install/)

### Start virtual environment


Boot the Docker (containers also will be run)

    make install
    
Run containers
   
    make up

Check that docker is running    
    
    docker ps | grep socialtech

or

    docker-compose ps
    
Show all available commands for Make

    make

Execute tests

    make tests

Endpoint URL

    http://localhost:8880/
    
All Available API Endpoints on 

    http://localhost:8880/api/doc    

Database connection

    host:port/database: localhost:54320/socialtech
    login: dev
    password: dev

Tests database connection

    host:port/database: localhost:54319/socialtech_test
    login: test
    password: test

    
Add host mapping to localhost (if you like needed)    
    
    sudo vim /etc/hosts
    127.0.0.1       socialtech.lo  # add this

### Auth
``X-API-TOKEN`` is mandatory header for all tracking requests. You can get it by login or register endpoints

### Swagger
Swagger is available on ``http://localhost:8880/api/doc  ``
There is all available endpoints with sample data. You can test API directly from there
        
### Validation
Application have some default validations. 
1. Nickname for user should be unique
2. Age >= 18
3. Password > 4 symbols
4. Analytic id should be unique
5. All fields is mandatory

### Storage
All static data  save to this path:
```$xslt
    path_to_customer_storage: '%kernel.project_dir%/user_json'
    path_for_analytics: '%kernel.project_dir%/analytic_json'
```
### Workers:
As default supervisor runs automatically on 
``make install `` command. In this case supervisor start workers. But you can stop supervisor and run worker manually. For this purpose use
```$xslt
service supervisor stop
php /var/www/socialtech/bin/console run-worker-async
```

## XDebug
To use XDebug tool in backend project you should replace *LOCAL_IP* variable in .env file for your local ip address
