<?php


namespace Codewiser\ResourceServer\Console\Commands;


use Codewiser\ResourceServer\Facades\ResourceServer;
use Illuminate\Console\Command;

class ShowAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resourceServer:accessToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show access_token';

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
     * @return int
     */
    public function handle()
    {
        $this->info(ResourceServer::getAccessToken()->getToken());
        return 0;
    }
}