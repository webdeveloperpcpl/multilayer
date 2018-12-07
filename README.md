# Installation package for Laravel 5.7
Creates classes of managers and repository classes
Installation: composer require webdeveloperpcpl/multilayer

Example of use:
php artisan make:multilayer test
this command will create:
app/Http/Controllers/TestController.php
app/Http/Request/TestRequest.php
app/Managers/TestManagerInterface.php
app/Managers/TestManager.php
app/Repositories/TestRepositoryInterface.php
app/Repositories/TestRepository.php
config/manager.php
config/repository.php
app/Providers/ManagerServiceProvider
app/Providees/RepositoryServiceProvider
