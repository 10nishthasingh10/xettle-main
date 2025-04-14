<?php
namespace App\Helpers;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportGlobal implements FromCollection
{
    protected $table, $fromDate, $toDate;
    public function  __construct($table, $fromDate, $toDate)
    {
        $this->table = $table;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }
    public function collection()
    {
        $data = DB::table($this->table)
            ->where('created_at', '>=', $this->fromDate)
            ->where('created_at', '<=', $this->toDate)
            ->get();
            return $data;
    }
}
?>