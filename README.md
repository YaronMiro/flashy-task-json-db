# Flashy Database JSON Task

 The project is set up using **Docker** these are the required dependencies needed in order to run the Docker container on your local machine.  
* [Docker](https://docs.docker.com/install/)  `>= 20.10`
* [Docker Compose](https://docs.docker.com/compose/install/) `>= 2`

The other alternative is to use this project on a local environment that can run `php-7.4` and `Composer version 2.5.4`, the fastest and most simple way is to use docker to install and run the project.

I also decided to add  Unit Tests using `PHPUnit`, these test covers all required functionally of the given task, this was done for 2 reasons.

The first reason was that it's easy to see **real examples** of the implementation and **validate** that it works as expected, and the second was to enable me to test while developing and adjust the code without worrying too much about major changes while doing so. 

All tests can be found under the `./tests` directory. On how to run the `tests` see the last step on the "Installation" section.   

## Installation

All commands should be executed from with in the root directory.

### **[1] Install the Docker images and run all containers**   
use one the following commands. Can be either:   
```bash
# for live logs
docker-compose up

# containers run in the background
docker-compose up -d
```
once install finish successfully and all containers are running move to the next step.

### **[2] Installing the Composer dependencies**   
In order install the composer dependencies run the following command:

```bash
docker-compose exec app composer install
```
once install finish successfully move to the next step.   

### **[3] Validate by running all the tests**   
This is the final step and we can validate that everything was installed properly and also works as expected by running the `tests`.   

In order to run the tests you will to run the following command:   

```bash
docker-compose exec app vendor/bin/phpunit --testdox --colors ./tests/.
```
once all tests pass it means everything is working as expected :)

**Note:**: If you want to `SSH` into the container you will need to run the following command:
```bash
docker-compose exec app /bin/bash
```

![alt text](https://github.com/YaronMiro/jsonDB/raw/main/tests/images/output.JPG "tests")


## General notes.

**Error handling**   
Since this is a home task I decided that on that aspect I will just throw plain `Error` (exception) according to the required bussing logic of the component in hand. on real life project I would have defined custom `Exception` and would have also added a dedicated service to handle errors across the entire system. I thought it is wiser to invest my effort and concentrate my focus on other aspects of the task.  

**Complex DB queries handling**   
Since this is a home task I decided that on that aspect I will just add simple query mechanism for "create" | "select" | "update" for a given record, I thought it is wiser to invest my effort and concentrate my focus on other aspects of the task.   


## Vendors PHP packages used.   

**[PHP-DI 7](https://php-di.org/)**   
The dependency injection container, This entire project is wired using dependency injection design pattern, for more info on how it works see the [official documentation](https://php-di.org/doc/).

**[Json Stream Collection Parser](https://github.com/MAXakaWIZARD/JsonCollectionParser)**   
Event-based parser for large JSON collections (consumes small amount of memory). Built on top of JSON Streaming Parser, for more info on how it works see the [official documentation](http://json-schema.org/).

**[JSON Schema for PHP](https://github.com/justinrainbow/json-schema)**   
PHP Implementation for validating JSON Structures against a given Schema, this is being used to declare a given Entity schema and also used to validate a given "Entity" record data as being valid or not, for more info on how it works see the [official documentation](https://github.com/MAXakaWIZARD/JsonCollectionParser#readme).

**[Ramsey UUID](https://github.com/justinrainbow/json-schema)**   
is a PHP library for generating and working with universally unique identifiers (UUIDs), being used to generate unique ID for an Entity new record, for more info on how it works see the [official documentation](https://uuid.ramsey.dev/).

