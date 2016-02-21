<?php namespace Chrisbjr\ApiGuard\Console\Commands;

use App;
use Chrisbjr\ApiGuard\Models\ApiKey;
use Config;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class DeleteApiKeyCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'api-key:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke/delete an API key';

    /**
     * Create a new command instance.
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
    public function fire()
    {
        $key = $this->option('api-key');

        if ( ! is_null($key)) {
            // we delete a specific API key
            $confirmation = $this->ask("Are you sure you want to delete this API key? [y/n]");

            if ($confirmation == 'y') {
                $apiKeyModel = App::make(Config::get('apiguard.models.apiKey', 'Chrisbjr\ApiGuard\Models\ApiKey'));
                $apiKey = $apiKeyModel->where('key', '=', $key)->first();

                if (empty($apiKey) || $apiKey->exists == false) {
                    $this->info("The API key you specified does not exist.");
                    return;
                }

                $this->info("The API key {$key} was deleted.");

                return;
            }

            return;
        }

        $this->error("Specify an API key to delete using the --api-key option. Example: --api-key=xxxxxxxxx");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('api-key', null, InputOption::VALUE_REQUIRED, 'The API key to delete.', null),
        );
    }

}