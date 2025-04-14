<?php

namespace App\Console\Commands;

use App\Helpers\ExportGlobal;
use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExcelFileExportSendToEmail extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel_table_send:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Excel file Send on email';

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
        $resp['status'] = false;
        $resp['message'] = "Export table initiate.";
        $GlobalConfig = DB::table('global_config')
                ->select('attribute_1', 'attribute_2', 'attribute_4', 'attribute_3', 'attribute_5')
                ->where(['slug' => 'export_excel_send_email'])
                ->first();
        if (isset($GlobalConfig) && $GlobalConfig->attribute_1 == 1) {
            $tables = isset($GlobalConfig->attribute_2) ? $GlobalConfig->attribute_2 : "orders";
            $heading = isset($GlobalConfig->attribute_3) ? $GlobalConfig->attribute_3 : "Order Table";
            $date = isset($GlobalConfig->attribute_4) ? $GlobalConfig->attribute_4 : date('Y-m-d',strtotime("-2 days")).'-'.date('Y-m-d',strtotime("-1 days"));
            $dateArray = explode("/",$date);
            $fromDate = $dateArray[0];
            $toDate = $dateArray[1];
            $to = isset($GlobalConfig->attribute_5) ? $GlobalConfig->attribute_5 : "aditya.yadav@mahagram.in";
            $tablesArray = explode(",",$tables);
            $headingAarray = explode(",",$heading);
            $toEmail = explode(",",$to);
            if (count($headingAarray) == count($tablesArray) ) {
                foreach ($tablesArray as $key => $tablesArrays) {
                    $filename = $date.'-'.time().$tablesArrays.'.xlsx';
                    $file = Excel::store(new ExportGlobal($tablesArrays, $fromDate, $toDate), $filename);
                    $data = array('data' => $headingAarray[$key], 'table' => $tablesArrays, 'date' => $date);
                    Mail::send('emails.sendData', $data, function($message) use($tablesArrays, $headingAarray, $key, $toEmail, $filename){
                    $message->to($toEmail)
                        ->subject($headingAarray[$key]);
                        $message->attach(url('/').'/storage/app/'.$filename);
                    });
                }
                    $resp['message'] = "Export table data on emails.";
                    $resp['status'] = true;
            } else {
                $resp['message'] = "Heading and table records not matched.";
            }
        } else {
            $resp['message'] = "Excel export disabled from admin.";
        }

        $this->info($resp['message']);
    }
}
