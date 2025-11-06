
- composer create-project laravel/laravel .
- composer dump-autoload
- php artisan make:model -a links
- php artisan make:model -a link_hits
- php artisan install:api
- php artisan make:controller API/LinksController --api
- php artisan make:middleware ValidateApiKey
- php artisan make:middleware ThrottleByApiKey

### Running
- composer dump-autoload
- php artisan optimize:clear

### Migrate
- set .env file database configuration
- php artisan migrate --pretend
- php artisan migrate --step

### Testing
- php artisan make:command MakeIntegrationTest
- php artisan make:test LinkTest --unit
- php artisan test

### Factory


## Admin Panel
- php artisan make:view links.index

