<?php

namespace Webdeveloperpcpl\Multilayer\Console\Commands;

use Illuminate\Console\Command;
use File;
use Config;


class MakeMultilayer extends Command
{

    private $paths = [

        'controller' => '/App/Http/Controllers',
        'request' => '/App/Http/Requests',
        'manager' => '/App/Managers',
        'repository' => '/App/Repositories',
        'provider' => '/App/Providers',
        'config' => '/config',
    ];


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:multilayer {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $name = $this->argument('name');
        $name = $this->parseName($name);


        $this->dirname = $name['dirname'] == '.' ? '/' : '/' . $name['dirname'] . '/';
        $this->nspatch = $name['dirname'] == '.' ? '' : '\\' . $name['dirname'];


        $this->classL = lcfirst($name['basename']);
        $this->classU = ucfirst($name['basename']);

        $this->rename = array(

            'CLASS_NAME' => $this->classU,

            'REQUEST_USE' => $this->use($this->paths['request']) . 'Request',
            'MANAGER_INTERFACE_USE' => $this->use($this->paths['manager']) . 'ManagerInterface',
            'REPOSITORY_INTERFACE_USE' => $this->use($this->paths['repository']) . 'RepositoryInterface',

            'CONTROLLER_NAMESPACE' => $this->namespace($this->paths['controller']),
            'REQUEST_NAMESPACE' => $this->namespace($this->paths['request']),
            'MANAGER_NAMESPACE' => $this->namespace($this->paths['manager']),
            'REPOSITORY_NAMESPACE' => $this->namespace($this->paths['repository']),

            'MANAGER_INTERFACE' => $this->classU . 'ManagerInterface',
            'REQUEST' => "{$this->classU}Request"
        );


        $this->createFile('Controller', '/stubs/controller.stub', $this->paths['controller']);
        $this->createFile('Request', '/stubs/request.stub', $this->paths['request']);
        $this->createFile('ManagerInterface', '/stubs/manager_interface.stub', $this->paths['manager']);
        $this->createFile('Manager', '/stubs/manager.stub', $this->paths['manager']);
        $this->createFile('RepositoryInterface', '/stubs/repository_interface.stub', $this->paths['repository']);
        $this->createFile('Repository', '/stubs/repository.stub', $this->paths['repository']);

        $this->createFile(null, '/stubs/manager_service_provider.stub', $this->paths['provider'], 'ManagerServiceProvider');
        $this->createFile(null, '/stubs/repository_service_provider.stub', $this->paths['provider'], 'RepositoryServiceProvider');

        $this->updateConfig('manager', $this->paths['config']);
        $this->updateConfig('repository', $this->paths['config']);

        $isManager = false;
        $isRepository = false;
        foreach (config('app.providers') as $provider) {

            $isManager = (strpos($provider, 'ManagerServiceProvider')) ? true : $isManager;
            $isRepository = (strpos($provider, 'RepositoryServiceProvider')) ? true : $isRepository;
        }

        if (!$isManager) {

            $this->warn('Add the ManagerServiceProvider class manually in the app config file');
        }

        if (!$isRepository) {

            $this->warn('Add the RepositoryServiceProvider class manually in the app config file');
        }


    }

    protected Function createFile($name, $stub, $directory, $provider = null)
    {

        $parsed = $this->parseFile($stub, $this->rename);

        if ($provider) {

            $target = base_path() . $directory . '/' . $provider . '.php';
            $filename = $directory . '/' . $provider . '.php';
        } else {

            $target = base_path() . $directory . $this->dirname . $this->classU . $name . '.php';
            $filename = $directory . $this->dirname . $this->classU . $name . '.php';
        }
        if (File::exists($target)) {

            $this->warn("File $filename already exists.");
        } else {

            if (!File::isDirectory(base_path() . $directory . $this->dirname)) {

                File::makeDirectory(base_path() . $directory . $this->dirname);
            }
            File::put($target, $parsed);
            $this->info("Created: $filename");
        }
    }


    protected function updateConfig($name, $directory)
    {

        $target = base_path() . $directory . '/' . $name . '.php';
        $filename = $directory . '/' . $name . '.php';

        if (config::has($name)) {

            $config = config::get($name);
            $config['class'][] = $this->classU . ucfirst($name);

            $file = '<?php return ' . var_export($config, true) . ';';
            File::put($target, $file);
            $this->info("Updated: $filename");
            return;

        } else {

            $config = [
                'class' => array($this->classU . ucfirst($name)),
            ];

            $file = '<?php return ' . var_export($config, true) . ';';
            File::put($target, $file);
            $this->info("Created: $filename");
            return;
        }
    }

    protected function parseFile($stub, $rename)
    {

        $filename = dirname(__FILE__) . '/' . $stub;
        $content = File::get($filename);

        foreach ($rename as $key => $value) {

            $content = str_replace('$' . $key . '$', $value, $content);
        }

        return $content;
    }

    protected function parseName($model)
    {

        if ($a = preg_match('[^A-Za-z0-9_/\\\\]', $model)) {


            $this->error('Model name contains invalid characters.');
            return NULL;
        }
        return pathinfo(str_replace('\\', "/", $model));
    }

    protected function namespace($path)
    {

        return substr (str_replace('/', "\\", $path), 1)  . $this->nspatch;
    }

    protected function use($path) {

        return $this->namespace($path) . '\\' . $this->classU ;
    }

}
