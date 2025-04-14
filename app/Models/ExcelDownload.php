<?php

namespace App\Models;
use Cashfree;
use CommonHelper;
use Illuminate\Database\Eloquent\Model;

use App\Models\Contact;

class ExcelDownload extends Model
{
    protected $with = ['User'];
    protected $table = 'excel_reports';

    public function User()
    {
        return $this->belongsTo(User::class);
    }

}
