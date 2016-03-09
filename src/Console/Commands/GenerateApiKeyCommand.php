<?php namespace Chrisbjr\ApiGuard\Console\Commands;

use App;
use Chrisbjr\ApiGuard\Models\ApiKey;
use Config;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class GenerateApiKeyCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'api-key:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an API key';

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
        $userId = $this->getOption('user-id', null);

        if ( ! empty($userId)) {

            // check whether this user already has an API key
            $apiKeyModel = App::make(Config::get('apiguard.models.apiKey', 'Chrisbjr\ApiGuard\Models\ApiKey'));

            $apiKey = $apiKeyModel->where('user_id', '=', $userId)->first();

            if ($apiKey) {
                $overwrite = $this->ask("This user already has an existing API key. Do you want to create another one? [y/n]");
                if ($overwrite == 'n') {
                    return;
                }
            }
        }

        $apiKey = ApiKey::make($this->getOption('user-id', null), $this->getOption('level', 10), $this->getOption('ignore-limits', 1));

        if (empty($apiKey->user_id)) {
            $this->info("You have successfully generated an API key:");
        } else {
            $this->info("You have successfully generated an API key for user ID#{$apiKey->user_id}:");
        }

        $this->info($apiKey->key);
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
            array('user-id', null, InputOption::VALUE_OPTIONAL, 'Link the generated API key to a user ID', null),
            array('level', null, InputOption::VALUE_OPTIONAL, 'Permission level of the generated API key', null),
            array('ignore-limits', null, InputOption::VALUE_OPTIONAL, 'Specify whether this API key will ignore limits or not', null),
        );
    }

    protected function getOption($name, $defaultValue)
    {
        $var = $this->option($name);

        if (is_null($var)) {
            return $defaultValue;
        }

        return $var;
    }

}