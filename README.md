# Akeneo PIM Private Api Client

## Description
A little HTTP client that connects to Akeneo PIM using a user account and retrieves data from private API endpoints in Akeneo PIM, the same endpoints that are used by the UI, you can find them with web developer tools, by checking the network requests that are made by the PIM UI.


## Installation
To install the project, follow these steps:

1. Make sure you have Docker installed on your machine.

2. Run the following command in your terminal:

    ```bash
    docker run --rm -it -v $(pwd):/app -w /app webdevops/php-dev:8.0 composer install
    ```

   This command will use the webdevops/php-dev:8.0 Docker image to set up the project dependencies.

## Configuration
Edit the `getUsers.php` file to configure the following settings:

- **pim_url**: Replace with the URL of your PIM (Product Information Management) system.
- **admin_username**: Replace with the username of your admin account.
- **admin_password**: Replace with the password of your admin account.

```php
// getUsers.php

// Configuration settings
$configuration = [
    'pim_url' => 'https://yourinstance.demo.cloud.akeneo.com',
    'admin_username' => 'admin',
    'admin_password' => 'password'
];

// ... rest of the code
```

## Usage
To run the `getUsers.php` example script, use Docker with the `webdevops/php-dev:8.0` image. Run the following command:

```bash
docker run --rm -it -v $(pwd):/app -w /app webdevops/php-dev:8.0 php getUsers.php
