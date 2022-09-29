<?php

namespace App\Exports;

use App\PurchaseOrder;
use App\PurchaseOrderItem;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DailyExport implements FromView
{
    public function __construct($strMonth, $result)
    {
        $this->strMonth = $strMonth;
        $this->result = $result;
    }

    public function view(): View
    {
        return view('exports.report-daily', [
            'strMonth' => $this->strMonth,
            'results' =>  $this->result
        ]);
    }
}
