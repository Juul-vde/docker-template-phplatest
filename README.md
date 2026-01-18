# Docker FoodPrepper for Web Development 1
This repository provides a starting web application regarding meal prepping.

It contains:
* NGINX webserver
* PHP FastCGI Process Manager with PDO MySQL support
* MariaDB (GPL MySQL fork)
* PHPMyAdmin

## Installation

1. Install Docker Desktop on Windows or Mac, or Docker Engine on Linux.
1. Clone the project

## Usage

In a terminal, from the cloned project folder, run:
```bash
docker compose up
```

NGINX will now serve files in the app/public folder. Visit localhost in your browser to check.
PHPMyAdmin is accessible on localhost:8080

If you want to stop the containers, press Ctrl+C. 

Or run:
```bash
docker compose down
```

## Test Credentials

Two test accounts are available:

### Regular User
**Email:** johndoe@test.com  
**Password:** secret123

### Admin User
**Email:** admin@admin.com  
**Password:** Admin123

If these credentials don't work, you can create a new account by clicking "Register here" on the login page.

## Default Database

The application uses a MariaDB database named `FoodPrepper`. You can access it via PHPMyAdmin at localhost:8080 with:
- Username: root OR developer
- Password: secret123

## Sample Data

The database is pre-populated with AI-generated sample data including:
- Ingredients (vegetables, fruits, grains, meat, plant-based proteins, dairy, oils, and seasonings)
- Nutritional information for each ingredient (calories, protein, carbs, fat)
- All nutritional values are based on 100g
- Tags
- Categories
- Recipes
- Recipe ingredients

This data is provided for testing and development purposes.
