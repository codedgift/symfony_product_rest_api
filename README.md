# Symfony REST API

### **Task Overview**

This project is a Symfony-based REST API for managing products. The architecture follows the MVC pattern with the following components:

- Controller: Handles HTTP requests and responses.
- Service: Contains business logic.
- Repository: Handles data access and persistence.
- Entity: Represents the data model.
- The project uses MySQL as the database.
- PHP 8.2 is used.
- Composer is used for dependency management.

## Installation and Setup Process

1. **Clone the repository**:
   ```bash
   git clone https://github.com/codedgift/symfony_product_rest_api.git
   cd products_rest_api

2. **Extract the zip file**:
   ```bash
    cd products_rest_api
   
3. **Build and start the Docker containers:**
    ```bash
   docker-compose up --build
   
4. **Install Composer dependencies:**
    ```bash
   docker-compose exec php composer install
   
5. **Run database migrations:**
    ```bash
   docker-compose exec php php bin/console doctrine:migrations:migrate


## API Documentation
### Endpoints

**1. Create a Product**
- URL: `/api/products`
- Method: `POST`
- Request Body (json):
```sh
{
    "name" : "Product1",
    "description" : "The Best Product",
    "price" : 200,
    "quantity" : 10
}
```
- Request Response:
```sh 
{
    "product": {
        "id": 1,
        "name": "Product1",
        "description": "The Best Product",
        "price": "200",
        "quantity": 10
    },
    "message": "Product created successfully"
}
```
**2. Get a Single Product**
- URL: `/api/products/{id}`
- Method: `GET`
- Request Response:
```sh 
{
    "product": {
        "id": 1,
        "name": "Product1",
        "description": "The Best Product",
        "price": "200",
        "quantity": 10
    }
}
```
**3. Get All Products**
- URL: `/api/products`
- Method: `GET`
- Request Response:
```sh 
{
    "products": [
        {
            "id": 1,
            "name": "Product1",
            "description": "The Best Product",
            "price": "200",
            "quantity": 10
        },
        {
            "id": 2,
            "name": "Product2",
            "description": "The Best Product2",
            "price": "500",
            "quantity": 30
        }
    ]
}
```
**4. Update a Product**
- URL: `/api/products/{id}`
- Method: `PUT`
- Request Body (json):
```sh
{
    "name" : "Product20",
    "description" : "The Best Product20",
    "price" : 250,
    "quantity" : 30
}
```
- Request Response:
```sh 
{
    "product": {
        "id": 1,
        "name": "Product20",
        "description": "The Best Product20",
        "price": "250",
        "quantity": 30
    },
    "message": "Product updated successfully"
}
```
**5. Delete a Product**
- URL: `/api/products/{id}`
- Method: `DELETE`
- Request Response:
```sh 
{
    "message": "Product deleted successfully"
}
```

## Running Tests
To run the tests, use the following command:
```sh 
docker-compose exec php ./vendor/bin/phpunit
```

## Running PHPStan and PHPCS
PHPStan and PHPCS are installed in the PHP container and can be run using Docker Compose.

### PHPStan
To run PHPStan, use the following command:

```bash
docker-compose exec php vendor/bin/phpstan analyse
```

### PHPCS
To run PHPCS, use the following command:

```bash
docker-compose exec php vendor/bin/phpcs
```

## Run Application on your web browser
Once the containers are running, you can access your Symfony application in your web browser.
```sh 
http://localhost:8080/
```
Note: you can make use of your defined port number, not necessary you use this port number.

Made with ❤️ by Gift Amah
