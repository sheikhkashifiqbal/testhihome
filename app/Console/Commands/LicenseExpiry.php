<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Admin\Services\StoreService;

class LicenseExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check if the seller license is about to expire and will send email notification to seller.';

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
        StoreService::sendLicenseExpiryNotification();
    }
}
