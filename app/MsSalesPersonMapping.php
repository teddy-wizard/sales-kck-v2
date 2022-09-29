<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MsSalesPersonMapping extends Model
{
    protected $fillable = ['companyId', 'acSalesAgentId', 'b1SalespersonId'];
    protected $appends = ['companyInfo', 'acSalesAgentInfo'];

    public function getCompanyInfoAttribute()
    {
        $company = MsCompany::find($this->companyId);
        return $company;
    }

    public function getAcSalesAgentInfoAttribute()
    {
        $acSalesAgent = AcSalesAgent::find($this->acSalesAgentId);
        return $acSalesAgent;
    }
}
