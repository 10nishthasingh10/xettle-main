<?php
namespace App\Helpers;


use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Bulk Export  class
 */


class ExportHelper implements FromQuery,WithHeadings, WithMapping
{
    use Exportable;

    /**
     * construct function
     *
     * @param string $batch_id
     */

    public function __construct(array $heading , object $sqlQuery)
    {
        $this->sqlQuery = $sqlQuery;
        $this->heading = $heading;
    }

    /**
     * query function
     *
     * @return void
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

    public function map($bulkPayout): array
    {
        return [
            $bulkPayout->batch_id,
            $bulkPayout->contact_first_name,
            $bulkPayout->contact_last_name,
            $bulkPayout->contact_email,
            $bulkPayout->contact_phone,
            $bulkPayout->contact_type,
            $bulkPayout->account_type,
            $bulkPayout->account_number,
            $bulkPayout->account_ifsc,
            $bulkPayout->account_vpa,
            $bulkPayout->payout_mode,
            $bulkPayout->payout_amount,
            $bulkPayout->payout_reference,
            $bulkPayout->order_ref_id,
            $bulkPayout->bank_reference,
            $bulkPayout->payout_purpose,
            $bulkPayout->payout_narration,
            $bulkPayout->message,
            $bulkPayout->status,
            $bulkPayout->note_1,
            $bulkPayout->note_2,
            $bulkPayout->created_at
        ];
    }
}