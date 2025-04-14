<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ReportExportHelper;
use Illuminate\Support\Facades\DB;

class MultipleExcelFileDownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     *
     * */

    private $startDate, $endDate, $userId, $reportName, $searchKey, $loginUserId, $type;
    public function __construct($loginUserId, $startDate, $endDate, $userId, $reportName, $type, $searchKey)
    {
       $this->startDate = $startDate;
       $this->endDate = $endDate;
       $this->reportName = $reportName;
       $this->userId = $userId;
       $this->loginUserId = $loginUserId;
       $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->fileName = date('Y-m-d').'/'.$this->loginUserId.'/'.$this->startDate.time().'.xlsx';
        (new ReportExportHelper($this->startDate, $this->endDate, $this->userId, $this->reportName, $this->type))->store($this->fileName);
        $resp['message'] = "Multple excel file download sucessfully.";
        $resp['status'] = true;
        $userId = 1;
        if (isset($this->userId)) {
            $userId = implode(",",$this->userId);
        }
        DB::table('excel_reports')
            ->insert([
                'user_id' => $this->loginUserId,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'file_name' => $this->reportName,
                'search_key' => $userId.' '.$this->reportName,
                'file_url' => $this->fileName
            ]);

        return response()->json($resp);
    }
}