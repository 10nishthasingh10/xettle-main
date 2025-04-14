<?php

namespace App\Console\Commands;

use App\Models\GlobalConfig;
use App\Models\MApiLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MApiLogDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mapi_log_delete:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mongo api log delete';

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
        $messages = '';
        $globalConfig = GlobalConfig::where('slug', 'mapi_log_delete')->first();
        if (isset($globalConfig)) {
            if ($globalConfig->attribute_1 == 1) {
                $check = 0;
                $data =  DB::connection('mongodb')
                    ->table('api_logs')
                    ->where('user_id', 'LIKE', "%$globalConfig->attribute_2%");
                    $data = (object) $data->limit($globalConfig->attribute_3)
                    ->get();

                foreach($data as $res) {

                    $check =  DB::connection('mongodb')->table('api_logs')
                        ->where('_id',  $res['_id'])
                        ->delete();
                }

                if ($check) {
                    $messages = 'Mongo api log delete.';
                } else {
                    $messages = 'Record not delete.';
                }

            } else {
                $messages = 'mapi_log_delete not enable.';
            }
            $globalConfig->save();
        }
        $this->info($messages);
    }

}
