<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Admin\Services\OrderService;

class CleanOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command remove all the orders that are created as CARD payment but not paid. If the order is created an hour before, then will be removed';

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
       OrderService::clearOrdersData();
    }
}
