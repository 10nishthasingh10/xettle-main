<?php
namespace App\Helpers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ExcelSheetsHelper implements FromQuery, WithTitle,WithHeadings, ShouldQueue
{
    private $name;
    private $sqlQuery;
    private $heading;

    public function __construct(array $heading , object $sqlQuery, $name)
    {
        $this->name = $name;
        $this->sqlQuery = $sqlQuery;
        $this->heading = $heading;
        // dd($this->sqlQuery);
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->sqlQuery;
    }

    /**
     * Excel Heading Of column function
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->heading;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->name;
    }
}
?>