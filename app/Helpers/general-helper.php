<?php

use App\AcArDebitNote;
use App\AcArDebitNoteDtl;
use App\AcArInvoice;
use App\AcArInvoiceDtl;
use App\AcCreditTerms;
use App\AcDebtor;
use App\AcPdcDtl;
use App\AcPdcHeader;
use App\AcPdcKnockoffDtl;
use App\AcSalesAgent;
use App\AcSalesCreditNote;
use App\AcSalesCreditNoteDtl;
use App\AcSalesDebitNote;
use App\AcSalesDebitNoteDtl;
use App\AcSalesInvoiceDtl;
use App\AcSalesInvoice;
use App\AcSalesOrder;
use App\AcSalesOrderItem;
use App\AcStockItem;
use App\AcStockItemUom;
use App\AcTaxType;
use App\DataSyncApi;
use App\DataSyncDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

//Get date with timeDiff from $from
function getLatestDate($from, $timeDiff)
{
    if(empty($from))
        return null;

    // $date = new DateTime();
    $date = DateTime::createFromFormat('Y-m-d h:i:s', $from);
    $date->modify('-'.$timeDiff.' second');

    return $date->format('Y-m-d h:m:s');
}

//Create And Update ac_credit_terms data
function createOrupdateSalesAgentForAC($sa_data, $sync_at)
{

    $acSalesAgent = AcSalesAgent::find($sa_data->id);
    $result = false;
    if(!isset($acSalesAgent)){
        $acSalesAgent = new AcSalesAgent();
        $acSalesAgent->id = $sa_data->id;
        $acSalesAgent->customerId = $sa_data->customer_id;
        $acSalesAgent->created_at = $sa_data->created_at;
        $result = true;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acSalesAgent->salesAgent = $sa_data->salesagent;
    $acSalesAgent->description = $sa_data->description;
    $acSalesAgent->desc2 = $sa_data->desc2;
    $acSalesAgent->active = $sa_data->isactive;
    $acSalesAgent->lastUpdate = $sa_data->lastupdate;
    // $acSalesAgent->apiStatus = $sa_data->apistatus;
    // $acSalesAgent->subscrit = $sa_data->issubscrit;
    $acSalesAgent->lastSyncAt = $sync_at;
    $acSalesAgent->updated_at = $sa_data->updated_at;

    $acSalesAgent->save();

    return $result;
}

//Create And Update ac_credit_terms data
function createOrupdateTaxTypeForAC($sa_data, $sync_at)
{
    $acTaxType = AcTaxType::find($sa_data->id);
    $result = false;
    if(!isset($acTaxType)){
        $acTaxType = new AcTaxType();
        $acTaxType->id = $sa_data->id;
        $acTaxType->customerId = $sa_data->customer_id;
        $acTaxType->created_at = $sa_data->created_at;
        $result = true;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acTaxType->code = $sa_data->taxtype;
    $acTaxType->description = $sa_data->description;
    $acTaxType->taxRate = $sa_data->taxrate;
    $acTaxType->inclusive = $sa_data->inclusive;
    $acTaxType->taxTypeCategory = $sa_data->taxtypecategory;
    $acTaxType->irasTaxCode = $sa_data->irastaxcode;
    if (empty($sa_data->supplypurchase)) {
        $acTaxType->supplyPurchase = '_';
    } else {
        $acTaxType->supplyPurchase = $sa_data->supplypurchase;
    }

    $acTaxType->taxAccNo = $sa_data->taxaccno;
    $acTaxType->taxDefault = $sa_data->isdefault;
    $acTaxType->zeroRate = $sa_data->iszerorate;
    $acTaxType->useTrxTaxAccNo = $sa_data->usetrxtaxaccno;

    $acTaxType->active = $sa_data->isactive;
    $acTaxType->lastUpdate = $sa_data->lastupdate;
    $acTaxType->lastSyncAt = $sync_at;
    $acTaxType->updated_at = $sa_data->updated_at;

    $acTaxType->save();

    return $result;
}

//Create And Update ac_credit_terms data
function createOrupdateTermForAC($sa_data, $sync_at)
{
    $acTerms = AcCreditTerms::find($sa_data->id);
    $result = false;
    if(!isset($acTerms)){
        $acTerms = new AcCreditTerms();
        $acTerms->id = $sa_data->id;
        $acTerms->customerId = $sa_data->customer_id;
        $acTerms->created_at = $sa_data->created_at;
        $result = true;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acTerms->displayTerm = $sa_data->displayterm;
    $acTerms->terms = $sa_data->terms;
    $acTerms->termType = $sa_data->termtype;
    $acTerms->termDays = $sa_data->termdays;
    $acTerms->discountDays = $sa_data->discountdays;
    $acTerms->discountPercent = $sa_data->discountpercent;
    $acTerms->active = 1;
    $acTerms->lastUpdate = $sa_data->lastupdate;
    $acTerms->lastSyncAt = $sync_at;
    $acTerms->updated_at = $sa_data->updated_at;

    $acTerms->save();

    return $result;
}

//Create And Update ac_stock_item data
function createOrupdateStockItemForAC($sa_data, $sync_at)
{
    $acStockItem = AcStockItem::find($sa_data->id);
    $result = false;
    if(!isset($acStockItem)){
        $acStockItem = new AcStockItem();
        $acStockItem->id = $sa_data->id;
        $acStockItem->customerId = $sa_data->customer_id;
        $acStockItem->created_at = $sa_data->created_at;
        $result = true;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acStockItem->docKey = $sa_data->dockey;
    $acStockItem->itemCode = $sa_data->itemcode;
    $acStockItem->description = $sa_data->description;
    $acStockItem->desc2 = $sa_data->desc2;
    $acStockItem->itemGroup = $sa_data->itemgroup;
    $acStockItem->itemType = $sa_data->itemtype;
    $acStockItem->taxType = $sa_data->taxtype;
    $acStockItem->purchaseTaxType = $sa_data->purchasetaxtype;
    $acStockItem->salesUom = $sa_data->salesuom;
    $acStockItem->purchaseUom = $sa_data->purchaseuom;
    $acStockItem->reportUom = $sa_data->reportuom;
    $acStockItem->baseUom = $sa_data->baseuom;
    $acStockItem->active = $sa_data->isactive;;
    $acStockItem->lastUpdate = $sa_data->lastupdate;
    $acStockItem->lastSyncAt = $sync_at;
    $acStockItem->updated_at = $sa_data->updated_at;

    $acStockItem->save();

    $item_uom_data = $sa_data->itemuomlist;

    if(isset($item_uom_data)) {
        foreach($item_uom_data as $itemUom)
        {
            $acItemUom = AcStockItemUom::find($itemUom->id);
            if (!isset($acItemUom)) {
                $acItemUom = new AcStockItemUom();
                $acItemUom->id = $itemUom->id;
                $acItemUom->customerId = $itemUom->customer_id;
                $acItemUom->itemId = $acStockItem->id;
                $acItemUom->created_at = $itemUom->created_at;
            }
            $acItemUom->itemCode = $itemUom->itemcode;
            $acItemUom->uom = $itemUom->uom;
            $acItemUom->barcode = $itemUom->barcode;
            $acItemUom->rate = $itemUom->rate;
            $acItemUom->price = $itemUom->price;
            $acItemUom->cost = $itemUom->cost;
            $acItemUom->realCost = $itemUom->realcost;
            $acItemUom->minSalePrice = $itemUom->minsaleprice;
            $acItemUom->maxSalePrice = $itemUom->maxsaleprice;
            $acItemUom->balQty = $itemUom->balqty;
            $acItemUom->focQty = $itemUom->focqty;
            $acItemUom->focLevel = $itemUom->foclevel;
            $acItemUom->weight = $itemUom->weight;
            $acItemUom->weightUom = $itemUom->weightuom;
            $acItemUom->active = 1;
            $acItemUom->lastUpdate = $itemUom->lastupdate;
            $acItemUom->updated_at = $itemUom->updated_at;
            $acItemUom->lastSyncAt = $sync_at;

            $acItemUom->save();
        }
    }

    return $result;
}

//Create And Update ac_stock_item data
function createOrupdateDebtorForAC($sa_data, $sync_at)
{
    $acDebtor = AcDebtor::find($sa_data->id);
    $result = false;
    if(!isset($acDebtor)){
        $acDebtor = new AcDebtor();
        $acDebtor->id = $sa_data->id;
        $acDebtor->customerId = $sa_data->customer_id;
        $acDebtor->created_at = $sa_data->created_at;
        $result = true;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acDebtor->debtorType = $sa_data->debtortype;
    $acDebtor->accNo = $sa_data->accno;
    $acDebtor->companyName = $sa_data->companyname;
    $acDebtor->desc2 = $sa_data->desc2;
    $acDebtor->registrationNo = $sa_data->registrationno;
    $acDebtor->address1 = $sa_data->add1;
    $acDebtor->address2 = $sa_data->add2;
    $acDebtor->address3 = $sa_data->add3;
    $acDebtor->address4 = $sa_data->add4;
    $acDebtor->postCode = $sa_data->postcode;
    $acDebtor->deliverAddr1 = $sa_data->devadd1;
    $acDebtor->deliverAddr2 = $sa_data->devadd2;
    $acDebtor->deliverAddr3 = $sa_data->devadd3;
    $acDebtor->deliverAddr4 = $sa_data->devadd4;
    $acDebtor->deliverPostCode = $sa_data->devpostcode;
    $acDebtor->attention = $sa_data->attention;
    $acDebtor->phone1 = $sa_data->phone1;
    $acDebtor->phone2 = $sa_data->phone2;
    $acDebtor->fax1 = $sa_data->fax1;
    $acDebtor->fax2 = $sa_data->fax2;
    $acDebtor->areaCode = $sa_data->areacode;
    $acDebtor->salesAgent = $sa_data->salesagent;
    $acDebtor->displayTerm = $sa_data->displayterm;
    $acDebtor->taxType = $sa_data->taxtype;
    $acDebtor->taxRegisterNo = $sa_data->taxregisterno;
    $acDebtor->priceCategory = $sa_data->pricecategory;
    $acDebtor->active = $sa_data->isactive;
    $apiValue = $sa_data->apistatus;
    if ($apiValue == null) {
        $apistatus = 1;
    } else if($apiValue == 'APINEW') {
        $apistatus = 2;
    } else if($apiValue == 'APICREATED') {
        $apistatus = 3;
    }

    $acDebtor->apiStatus = $apistatus;
    $acDebtor->lastUpdate = $sa_data->lastupdate;
    $acDebtor->lastSyncAt = $sync_at;
    $acDebtor->updated_at = $sa_data->updated_at;

    $acDebtor->save();

    return $result;
}

//Create And Update ac_stock_item data
function createOrupdateARInvoiceForAC($sa_data, $sync_at)
{
    $acInvoice = AcArInvoice::find($sa_data->id);
    $result = false;
    $lastUpdate = 0;
    if(!isset($acInvoice)){
        $acInvoice = new AcArInvoice();
        $acInvoice->id = $sa_data->id;
        $acInvoice->customerId = $sa_data->customer_id;
        $acInvoice->created_at = $sa_data->created_at;
        $result = true;
    } else {
        $lastUpdate = $acInvoice->lastUpdate;
        if(isset($sa_data->updated_at))
            $lastUpdate++;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acInvoice->customerSubId = $sa_data->customer_sub_id;
    $acInvoice->docKey = $sa_data->dockey;
    $acInvoice->docNo = $sa_data->docno;
    $acInvoice->debtorCode = $sa_data->debtorcode;
    $acInvoice->journalType = $sa_data->journaltype;
    $acInvoice->displayTerm = $sa_data->docdate;
    $acInvoice->description = $sa_data->description;
    $acInvoice->salesAgent = $sa_data->salesagent;
    $acInvoice->currencyCode = $sa_data->currencycode;
    $acInvoice->currencyRate = $sa_data->currencyrate;
    $acInvoice->outstanding = $sa_data->outstanding;
    $acInvoice->lastModified = $sa_data->lastmodified;
    $acInvoice->sourceType = $sa_data->sourcetype;
    $acInvoice->sourceKey = $sa_data->sourcekey;
    $acInvoice->branchCode = $sa_data->branchcode;
    $acInvoice->inclusiveTax = $sa_data->inclusivetax;
    $acInvoice->roundingMethod = $sa_data->roundingmethod;
    $acInvoice->knockoffType = $sa_data->knockoff_type;
    $acInvoice->pdcDisDueDate = $sa_data->pdc_dis_duedate;
    $acInvoice->pdcDisAmt = $sa_data->pdc_dis_amt;
    $acInvoice->docDate = $sa_data->docdate;
    $acInvoice->dueDate = $sa_data->duedate;
    $acInvoice->cancelled = $sa_data->cancelled;
    $acInvoice->deleted = 0;
    $acInvoice->lastUpdate = $lastUpdate;
    $acInvoice->lastSyncAt = $sync_at;
    $acInvoice->updated_at = $sa_data->updated_at;

    $acInvoice->save();

    $arInvoiceDtls = $sa_data->arinvoicelist;

    if(isset($arInvoiceDtls)) {
        foreach($arInvoiceDtls as $item)
        {
            $arInvoiceDtl = AcArInvoiceDtl::find($item->id);
            if (!isset($arInvoiceDtl)) {
                $arInvoiceDtl = new AcArInvoiceDtl();
                $arInvoiceDtl->id = $item->id;
                $arInvoiceDtl->customerId = $item->customer_id;
                $arInvoiceDtl->arInvoiceId = $acInvoice->id;
                $arInvoiceDtl->created_at = $item->created_at;
            }
            $arInvoiceDtl->dtlKey = $item->dtlkey;
            $arInvoiceDtl->docKey = $item->dockey;
            $arInvoiceDtl->seq = $item->seq;
            $arInvoiceDtl->accNo = $item->accno;
            $arInvoiceDtl->toAccountRate = $item->toaccountrate;
            $arInvoiceDtl->description = $item->description;
            $arInvoiceDtl->projNo = $item->projno;
            $arInvoiceDtl->deptNo = $item->deptno;
            $arInvoiceDtl->taxType = $item->taxtype;
            $arInvoiceDtl->taxRate = $item->taxrate;
            $arInvoiceDtl->sourceDtlKey = $item->sourcedtlkey;
            $arInvoiceDtl->sourceDtlType = $item->sourcedtltype;
            $arInvoiceDtl->deleted = 0;
            $arInvoiceDtl->updated_at = $item->updated_at;
            $arInvoiceDtl->lastSyncAt = $sync_at;

            $arInvoiceDtl->save();
        }
    }

    return $result;
}

//Create And Update ac_stock_item data
function createOrupdateARDebitNoteForAC($sa_data, $sync_at)
{
    $acDebitNote = AcArDebitNote::find($sa_data->id);
    $result = false;
    $lastUpdate = 0;
    if(!isset($acDebitNote)){
        $acDebitNote = new AcArDebitNote();
        $acDebitNote->id = $sa_data->id;
        $acDebitNote->customerId = $sa_data->customer_id;
        $acDebitNote->created_at = $sa_data->created_at;
        $result = true;
    } else {
        $lastUpdate = $acDebitNote->lastUpdate;
        if(isset($sa_data->updated_at))
            $lastUpdate++;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acDebitNote->customerSubId = $sa_data->customer_sub_id;
    $acDebitNote->docKey = $sa_data->dockey;
    $acDebitNote->docNo = $sa_data->docno;
    $acDebitNote->debtorCode = $sa_data->debtorcode;
    $acDebitNote->journalType = $sa_data->journaltype;
    $acDebitNote->dnType = $sa_data->dntype;
    $acDebitNote->reference = $sa_data->reference;
    $acDebitNote->displayTerm = $sa_data->docdate;
    $acDebitNote->description = $sa_data->description;
    $acDebitNote->salesAgent = $sa_data->salesagent;
    $acDebitNote->currencyCode = $sa_data->currencycode;
    $acDebitNote->currencyRate = $sa_data->currencyrate;
    $acDebitNote->outstanding = $sa_data->outstanding;
    $acDebitNote->isJournal = $sa_data->isjournal;
    $acDebitNote->reason = $sa_data->reason;
    $acDebitNote->lastModified = $sa_data->lastmodified;
    $acDebitNote->sourceType = $sa_data->sourcetype;
    $acDebitNote->sourceKey = $sa_data->sourcekey;
    $acDebitNote->branchCode = $sa_data->branchcode;
    $acDebitNote->inclusiveTax = $sa_data->inclusivetax;
    $acDebitNote->roundingMethod = $sa_data->roundingmethod;
    $acDebitNote->knockoffType = $sa_data->knockoff_type;
    $acDebitNote->pdcDisDueDate = $sa_data->pdc_dis_duedate;
    $acDebitNote->pdcDisAmt = $sa_data->pdc_dis_amt;
    $acDebitNote->docDate = $sa_data->docdate;
    $acDebitNote->dueDate = $sa_data->duedate;
    $acDebitNote->cancelled = $sa_data->cancelled;
    $acDebitNote->deleted = 0;
    $acDebitNote->lastUpdate = $lastUpdate;
    $acDebitNote->lastSyncAt = $sync_at;
    $acDebitNote->updated_at = $sa_data->updated_at;

    $acDebitNote->save();

    $acDebitNoteDtls = $sa_data->ardebitnotelist;

    if(isset($acDebitNoteDtls)) {
        foreach($acDebitNoteDtls as $item)
        {
            $acDebitNoteDtl = AcArDebitNoteDtl::find($item->id);
            if (!isset($acDebitNoteDtl)) {
                $acDebitNoteDtl = new AcArDebitNoteDtl();
                $acDebitNoteDtl->id = $item->id;
                $acDebitNoteDtl->customerId = $item->customer_id;
                $acDebitNoteDtl->arDebitNoteId = $acDebitNote->id;
                $acDebitNoteDtl->created_at = $item->created_at;
            }
            $acDebitNoteDtl->dtlKey = $item->dtlkey;
            $acDebitNoteDtl->docKey = $item->dockey;
            $acDebitNoteDtl->seq = $item->seq;
            $acDebitNoteDtl->accNo = $item->accno;
            $acDebitNoteDtl->toAccountRate = $item->toaccountrate;
            $acDebitNoteDtl->description = $item->description;
            $acDebitNoteDtl->projNo = $item->projno;
            $acDebitNoteDtl->deptNo = $item->deptno;
            $acDebitNoteDtl->taxType = $item->taxtype;
            $acDebitNoteDtl->taxRate = $item->taxrate;
            $acDebitNoteDtl->sourceDtlKey = $item->sourcedtlkey;
            $acDebitNoteDtl->sourceDtlType = $item->sourcedtltype;
            $acDebitNoteDtl->deleted = 0;
            $acDebitNoteDtl->updated_at = $item->updated_at;
            $acDebitNoteDtl->lastSyncAt = $sync_at;

            $acDebitNoteDtl->save();
        }
    }

    return $result;
}

//Create And Update ac_sales_invoices and ac_sales_invoice_dtls data
function createOrupdateSalesInvoiceForAC($sa_data, $sync_at)
{
    $acSalesInvoice = AcSalesInvoice::find($sa_data->id);
    $result = false;
    $lastUpdate = 0;
    if(!isset($acSalesInvoice)){
        $acSalesInvoice = new AcSalesInvoice();
        $acSalesInvoice->id = $sa_data->id;
        $acSalesInvoice->customerId = $sa_data->customer_id;
        $acSalesInvoice->created_at = $sa_data->created_at;
        $result = true;
    } else {
        $lastUpdate = $acSalesInvoice->lastUpdate;
        if(isset($sa_data->updated_at))
            $lastUpdate++;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acSalesInvoice->customerSubId = $sa_data->customer_sub_id;
    $acSalesInvoice->docKey = $sa_data->dockey;
    $acSalesInvoice->docNo = $sa_data->docno;
    $acSalesInvoice->docDate = $sa_data->docdate;
    $acSalesInvoice->debtorCode = $sa_data->debtorcode;
    $acSalesInvoice->invAddr1 = $sa_data->invadd1;
    $acSalesInvoice->invAddr2 = $sa_data->invadd2;
    $acSalesInvoice->invAddr3 = $sa_data->invadd3;
    $acSalesInvoice->invAddr4 = $sa_data->invadd4;
    $acSalesInvoice->branchCode = $sa_data->branchcode;
    $acSalesInvoice->salesLocation = $sa_data->saleslocation;
    $acSalesInvoice->displayTerm = $sa_data->displayterm;
    $acSalesInvoice->salesAgent = $sa_data->salesagent;
    $acSalesInvoice->shipVia = $sa_data->shipvia;
    $acSalesInvoice->shipInfo = $sa_data->shipinfo;
    $acSalesInvoice->inclusiveTax = $sa_data->inclusivetax;
    $acSalesInvoice->toDocType = $sa_data->todoctype;
    $acSalesInvoice->toDocKey = $sa_data->todockey;
    $acSalesInvoice->toDtlKey = $sa_data->todtlkey;
    $acSalesInvoice->total = $sa_data->total;
    $acSalesInvoice->netTotal = $sa_data->nettotal;
    $acSalesInvoice->localNetTotal = $sa_data->localnettotal;
    $acSalesInvoice->analysisNetTotal = $sa_data->analysisnettotal;
    $acSalesInvoice->localAnalysisNetTotal = $sa_data->localanalysisnettotal;
    $acSalesInvoice->localTotalCost = $sa_data->localtotalcost;
    $acSalesInvoice->tax = $sa_data->tax;
    $acSalesInvoice->localTax = $sa_data->localtax;
    $acSalesInvoice->exTax = $sa_data->extax;
    $acSalesInvoice->localExTax = $sa_data->localextax;
    $acSalesInvoice->totalExTax = $sa_data->totalextax;
    $acSalesInvoice->taxableAmt = $sa_data->taxableamt;
    $acSalesInvoice->finalTotal = $sa_data->finaltotal;
    $acSalesInvoice->localTaxableAmt = $sa_data->localtaxableamt;
    $acSalesInvoice->syncStatus = $sa_data->sync_status;
    $acSalesInvoice->cancelled = $sa_data->cancelled;
    $acSalesInvoice->lastUpdate = $sa_data->lastupdate;
    $acSalesInvoice->lastSyncAt = $sync_at;
    $acSalesInvoice->updated_at = $sa_data->updated_at;

    $acSalesInvoice->save();

    $acSalesInvoiceDtls = $sa_data->invoicelist;

    if(isset($acSalesInvoiceDtls)) {
        foreach($acSalesInvoiceDtls as $item)
        {
            $acSalesInvoiceDtl = AcSalesInvoiceDtl::find($item->id);
            if (!isset($acSalesInvoiceDtl)) {
                $acSalesInvoiceDtl = new AcSalesInvoiceDtl();
                $acSalesInvoiceDtl->id = $item->id;
                $acSalesInvoiceDtl->customerId = $item->customer_id;
                $acSalesInvoiceDtl->invoiceId = $acSalesInvoice->id;
                $acSalesInvoiceDtl->created_at = $item->created_at;
            }
            $acSalesInvoiceDtl->dtlKey = $item->dtlkey;
            $acSalesInvoiceDtl->docKey = $item->dockey;
            $acSalesInvoiceDtl->itemCode = $item->itemcode;
            $acSalesInvoiceDtl->location = $item->location;
            $acSalesInvoiceDtl->description = $item->description;
            $acSalesInvoiceDtl->furtherDescription = $item->furtherdescription;
            $acSalesInvoiceDtl->uom = $item->uom;
            $acSalesInvoiceDtl->rate = $item->rate;
            $acSalesInvoiceDtl->qty = $item->qty;
            $acSalesInvoiceDtl->transferedQty = $item->transferedqty;
            $acSalesInvoiceDtl->smallestUnitPrice = $item->smallestunitprice;
            $acSalesInvoiceDtl->unitPrice = $item->unitprice;
            $acSalesInvoiceDtl->discount = $item->discount;
            $acSalesInvoiceDtl->discountAmt = $item->discountamt;
            $acSalesInvoiceDtl->taxType = $item->taxtype;
            $acSalesInvoiceDtl->taxRate = $item->taxrate;
            $acSalesInvoiceDtl->fromDocNo = $item->fromdocno;
            $acSalesInvoiceDtl->fromDocType = $item->fromdoctype;
            $acSalesInvoiceDtl->fromDocTtlKey = $item->fromdocdtlkey;
            $acSalesInvoiceDtl->focDtlKey = $item->focdtlkey;
            $acSalesInvoiceDtl->focQty = $item->focqty;
            $acSalesInvoiceDtl->tax = $item->tax;
            $acSalesInvoiceDtl->subTotal = $item->subtotal;
            $acSalesInvoiceDtl->localSubTotal = $item->localsubtotal;
            $acSalesInvoiceDtl->localTotalCost = $item->localtotalcost;
            $acSalesInvoiceDtl->localFocTotalCost = $item->localfoctotalcost;
            $acSalesInvoiceDtl->subTotalExTax = $item->subtotalextax;
            $acSalesInvoiceDtl->localTax = $item->localtax;
            $acSalesInvoiceDtl->taxableAmt = $item->taxableamt;
            $acSalesInvoiceDtl->localSubTotalExTax = $item->localsubtotalextax;
            $acSalesInvoiceDtl->localTaxableAmt = $item->localtaxableamt;
            $acSalesInvoiceDtl->updated_at = $item->updated_at;
            $acSalesInvoiceDtl->lastSyncAt = $sync_at;

            $acSalesInvoiceDtl->save();
        }
    }

    return $result;
}

//Create And Update ac_sales_credit_notes and ac_sales_credit_note_dtls data
function createOrupdateSalesCreditNoteForAC($sa_data, $sync_at)
{
    $acSalesCreditNote = AcSalesCreditNote::find($sa_data->id);
    $result = false;
    $lastUpdate = 0;
    if(!isset($acSalesCreditNote)){
        $acSalesCreditNote = new AcSalesCreditNote();
        $acSalesCreditNote->id = $sa_data->id;
        $acSalesCreditNote->customerId = $sa_data->customer_id;
        $acSalesCreditNote->created_at = $sa_data->created_at;
        $result = true;
    } else {
        $lastUpdate = $acSalesCreditNote->lastUpdate;
        if(isset($sa_data->updated_at))
            $lastUpdate++;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acSalesCreditNote->customerSubId = $sa_data->customer_sub_id;
    $acSalesCreditNote->docKey = $sa_data->dockey;
    $acSalesCreditNote->docNo = $sa_data->docno;
    $acSalesCreditNote->docDate = $sa_data->docdate;
    $acSalesCreditNote->debtorCode = $sa_data->debtorcode;
    $acSalesCreditNote->debtorName = $sa_data->debtorname;
    $acSalesCreditNote->invAddr1 = $sa_data->invadd1;
    $acSalesCreditNote->invAddr2 = $sa_data->invadd2;
    $acSalesCreditNote->invAddr3 = $sa_data->invadd3;
    $acSalesCreditNote->invAddr4 = $sa_data->invadd4;
    $acSalesCreditNote->branchCode = $sa_data->branchcode;
    $acSalesCreditNote->salesLocation = $sa_data->saleslocation;
    $acSalesCreditNote->displayTerm = $sa_data->displayterm;
    $acSalesCreditNote->salesAgent = $sa_data->salesagent;
    $acSalesCreditNote->cnType = $sa_data->cntype;
    $acSalesCreditNote->ourInvoiceNo = $sa_data->ourinvoiceno;
    $acSalesCreditNote->reason = $sa_data->reason;
    $acSalesCreditNote->inclusiveTax = $sa_data->inclusivetax;
    $acSalesCreditNote->total = $sa_data->total;
    $acSalesCreditNote->netTotal = $sa_data->nettotal;
    $acSalesCreditNote->localNetTotal = $sa_data->localnettotal;
    $acSalesCreditNote->analysisNetTotal = $sa_data->analysisnettotal;
    $acSalesCreditNote->localAnalysisNetTotal = $sa_data->localanalysisnettotal;
    $acSalesCreditNote->localTotalCost = $sa_data->localtotalcost;
    $acSalesCreditNote->tax = $sa_data->tax;
    $acSalesCreditNote->localTax = $sa_data->localtax;
    $acSalesCreditNote->exTax = $sa_data->extax;
    $acSalesCreditNote->localExTax = $sa_data->localextax;
    $acSalesCreditNote->totalExTax = $sa_data->totalextax;
    $acSalesCreditNote->taxableAmt = $sa_data->taxableamt;
    $acSalesCreditNote->finalTotal = $sa_data->finaltotal;
    $acSalesCreditNote->localTaxableAmt = $sa_data->localtaxableamt;
    $acSalesCreditNote->syncStatus = $sa_data->sync_status;
    $acSalesCreditNote->cancelled = $sa_data->cancelled;
    $acSalesCreditNote->lastUpdate = $sa_data->lastupdate;
    $acSalesCreditNote->lastSyncAt = $sync_at;
    $acSalesCreditNote->updated_at = $sa_data->updated_at;

    $acSalesCreditNote->save();

    $acSalesCreditNoteDtls = $sa_data->cnlist;

    if(isset($acSalesCreditNoteDtls)) {
        foreach($acSalesCreditNoteDtls as $item)
        {
            $acSalesCreditNoteDtl = AcSalesCreditNoteDtl::find($item->id);
            if (!isset($acSalesCreditNoteDtl)) {
                $acSalesCreditNoteDtl = new AcSalesCreditNoteDtl();
                $acSalesCreditNoteDtl->id = $item->id;
                $acSalesCreditNoteDtl->customerId = $item->customer_id;
                $acSalesCreditNoteDtl->cnId = $acSalesCreditNote->id;
                $acSalesCreditNoteDtl->created_at = $item->created_at;
            }
            $acSalesCreditNoteDtl->dtlKey = $item->dtlkey;
            $acSalesCreditNoteDtl->focdtlkey = $item->focdtlkey;
            $acSalesCreditNoteDtl->docKey = $item->dockey;
            $acSalesCreditNoteDtl->itemCode = $item->itemcode;
            $acSalesCreditNoteDtl->location = $item->location;
            $acSalesCreditNoteDtl->description = $item->description;
            $acSalesCreditNoteDtl->furtherDescription = $item->furtherdescription;
            $acSalesCreditNoteDtl->uom = $item->uom;
            $acSalesCreditNoteDtl->rate = $item->rate;
            $acSalesCreditNoteDtl->qty = $item->qty;
            $acSalesCreditNoteDtl->smallestUnitPrice = $item->smallestunitprice;
            $acSalesCreditNoteDtl->unitPrice = $item->unitprice;
            $acSalesCreditNoteDtl->discount = $item->discount;
            $acSalesCreditNoteDtl->discountAmt = $item->discountamt;
            $acSalesCreditNoteDtl->taxType = $item->taxtype;
            $acSalesCreditNoteDtl->taxRate = $item->taxrate;
            $acSalesCreditNoteDtl->fromDocNo = $item->fromdocno;
            $acSalesCreditNoteDtl->fromDocType = $item->fromdoctype;
            $acSalesCreditNoteDtl->fromDocTtlKey = $item->fromdocdtlkey;
            $acSalesCreditNoteDtl->tax = $item->tax;
            $acSalesCreditNoteDtl->subTotal = $item->subtotal;
            $acSalesCreditNoteDtl->localSubTotal = $item->localsubtotal;
            $acSalesCreditNoteDtl->subTotalExTax = $item->subtotalextax;
            $acSalesCreditNoteDtl->localTax = $item->localtax;
            $acSalesCreditNoteDtl->taxableAmt = $item->taxableamt;
            $acSalesCreditNoteDtl->localSubTotalExTax = $item->localsubtotalextax;
            $acSalesCreditNoteDtl->localTaxableAmt = $item->localtaxableamt;
            $acSalesCreditNoteDtl->updated_at = $item->updated_at;
            $acSalesCreditNoteDtl->lastSyncAt = $sync_at;

            $acSalesCreditNoteDtl->save();
        }
    }

    return $result;
}

//Create And Update ac_sales_debit_notes and ac_sales_debit_note_dtls data
function createOrupdateSalesDebitNoteForAC($sa_data, $sync_at)
{
    $acSalesDebitNote = AcSalesDebitNote::find($sa_data->id);
    $result = false;
    $lastUpdate = 0;
    if(!isset($acSalesDebitNote)){
        $acSalesDebitNote = new AcSalesDebitNote();
        $acSalesDebitNote->id = $sa_data->id;
        $acSalesDebitNote->customerId = $sa_data->customer_id;
        $acSalesDebitNote->created_at = $sa_data->created_at;
        $result = true;
    } else {
        $lastUpdate = $acSalesDebitNote->lastUpdate;
        if(isset($sa_data->updated_at))
            $lastUpdate++;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acSalesDebitNote->customerSubId = $sa_data->customer_sub_id;
    $acSalesDebitNote->docKey = $sa_data->dockey;
    $acSalesDebitNote->docNo = $sa_data->docno;
    $acSalesDebitNote->docDate = $sa_data->docdate;
    $acSalesDebitNote->debtorCode = $sa_data->debtorcode;
    $acSalesDebitNote->debtorName = $sa_data->debtorname;
    $acSalesDebitNote->invAddr1 = $sa_data->invadd1;
    $acSalesDebitNote->invAddr2 = $sa_data->invadd2;
    $acSalesDebitNote->invAddr3 = $sa_data->invadd3;
    $acSalesDebitNote->invAddr4 = $sa_data->invadd4;
    $acSalesDebitNote->branchCode = $sa_data->branchcode;
    $acSalesDebitNote->salesLocation = $sa_data->saleslocation;
    $acSalesDebitNote->displayTerm = $sa_data->displayterm;
    $acSalesDebitNote->salesAgent = $sa_data->salesagent;
    $acSalesDebitNote->dnType = $sa_data->dntype;
    $acSalesDebitNote->ourInvoiceNo = $sa_data->ourinvoiceno;
    $acSalesDebitNote->reason = $sa_data->reason;
    $acSalesDebitNote->inclusiveTax = $sa_data->inclusivetax;
    $acSalesDebitNote->total = $sa_data->total;
    $acSalesDebitNote->netTotal = $sa_data->nettotal;
    $acSalesDebitNote->localNetTotal = $sa_data->localnettotal;
    $acSalesDebitNote->analysisNetTotal = $sa_data->analysisnettotal;
    $acSalesDebitNote->localAnalysisNetTotal = $sa_data->localanalysisnettotal;
    $acSalesDebitNote->localTotalCost = $sa_data->localtotalcost;
    $acSalesDebitNote->tax = $sa_data->tax;
    $acSalesDebitNote->localTax = $sa_data->localtax;
    $acSalesDebitNote->exTax = $sa_data->extax;
    $acSalesDebitNote->localExTax = $sa_data->localextax;
    $acSalesDebitNote->totalExTax = $sa_data->totalextax;
    $acSalesDebitNote->taxableAmt = $sa_data->taxableamt;
    $acSalesDebitNote->finalTotal = 0;
    $acSalesDebitNote->localTaxableAmt = $sa_data->localtaxableamt;
    $acSalesDebitNote->syncStatus = $sa_data->sync_status;
    $acSalesDebitNote->cancelled = $sa_data->cancelled;
    $acSalesDebitNote->lastUpdate = $sa_data->lastupdate;
    $acSalesDebitNote->lastSyncAt = $sync_at;
    $acSalesDebitNote->updated_at = $sa_data->updated_at;

    $acSalesDebitNote->save();

    $acSalesDebitNoteDtls = $sa_data->dnlist;

    if(isset($acSalesDebitNoteDtls)) {
        foreach($acSalesDebitNoteDtls as $item)
        {
            $acSalesDebitNoteDtl = AcSalesDebitNoteDtl::find($item->id);
            if (!isset($acSalesDebitNoteDtl)) {
                $acSalesDebitNoteDtl = new AcSalesDebitNoteDtl();
                $acSalesDebitNoteDtl->id = $item->id;
                $acSalesDebitNoteDtl->customerId = $item->customer_id;
                $acSalesDebitNoteDtl->dnId = $item->dn_id;
                $acSalesDebitNoteDtl->created_at = $item->created_at;
            }
            $acSalesDebitNoteDtl->dtlKey = $item->dtlkey;
            $acSalesDebitNoteDtl->focdtlKey = $item->focdtlkey;
            $acSalesDebitNoteDtl->docKey = $item->dockey;
            $acSalesDebitNoteDtl->itemCode = $item->itemcode;
            $acSalesDebitNoteDtl->location = $item->location;
            $acSalesDebitNoteDtl->description = $item->description;
            $acSalesDebitNoteDtl->furtherDescription = $item->furtherdescription;
            $acSalesDebitNoteDtl->uom = $item->uom;
            $acSalesDebitNoteDtl->rate = $item->rate;
            $acSalesDebitNoteDtl->qty = $item->qty;
            $acSalesDebitNoteDtl->focQty = $item->focqty;
            $acSalesDebitNoteDtl->smallestUnitPrice = $item->smallestunitprice;
            $acSalesDebitNoteDtl->unitPrice = $item->unitprice;
            $acSalesDebitNoteDtl->discount = $item->discount;
            $acSalesDebitNoteDtl->discountAmt = $item->discountamt;
            $acSalesDebitNoteDtl->taxType = $item->taxtype;
            $acSalesDebitNoteDtl->taxRate = $item->taxrate;
            $acSalesDebitNoteDtl->tax = $item->tax;
            $acSalesDebitNoteDtl->subTotal = $item->subtotal;
            $acSalesDebitNoteDtl->localSubTotal = $item->localsubtotal;
            $acSalesDebitNoteDtl->subTotalExTax = $item->subtotalextax;
            $acSalesDebitNoteDtl->localTax = $item->localtax;
            $acSalesDebitNoteDtl->taxableAmt = $item->taxableamt;
            $acSalesDebitNoteDtl->localSubTotalExTax = $item->localsubtotalextax;
            $acSalesDebitNoteDtl->localTaxableAmt = $item->localtaxableamt;
            $acSalesDebitNoteDtl->updated_at = $item->updated_at;
            $acSalesDebitNoteDtl->lastSyncAt = $sync_at;

            $acSalesDebitNoteDtl->save();
        }
    }

    return $result;
}




//Create And Update ac_salesorder data
function createOrupdateSalesOrderForAC($so_data, $sync_at)
{
    $acSalesOrder = AcSalesOrder::find($so_data->id);
    $result = false;
    if(!isset($acSalesOrder)){
        $acSalesOrder = new AcSalesOrder();
        $acSalesOrder->id = $so_data->id;
        $acSalesOrder->customerId = $so_data->customer_id;
        $acSalesOrder->created_at = $so_data->created_at;
        $result = true;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acSalesOrder->docKey = $so_data->dockey;
    $acSalesOrder->docNo = $so_data->docno;
    $acSalesOrder->docDate = $so_data->docdate;
    $acSalesOrder->debtorCode = $so_data->debtorcode;
    $acSalesOrder->debtorName = $so_data->debtorname;
    $acSalesOrder->invAddr1 = $so_data->invadd1;
    $acSalesOrder->invAddr2 = $so_data->invadd2;
    $acSalesOrder->invAddr3 = $so_data->invadd3;
    $acSalesOrder->invAddr4 = $so_data->invadd4;
    $acSalesOrder->branchCode = $so_data->branchcode;
    $acSalesOrder->salesLocation = $so_data->saleslocation;
    $acSalesOrder->displayTerm = $so_data->displayterm;
    $acSalesOrder->salesAgent = $so_data->salesagent;
    $acSalesOrder->shipVia = $so_data->shipvia;
    $acSalesOrder->shipInfo = $so_data->shipinfo;
    $acSalesOrder->inclusiveTax = $so_data->inclusivetax;
    $acSalesOrder->remark1 = $so_data->remark1;
    $acSalesOrder->remark2 = $so_data->remark2;
    $acSalesOrder->remark3 = $so_data->remark3;
    $acSalesOrder->remark4 = $so_data->remark4;
    $acSalesOrder->toDocType = $so_data->todoctype;
    $acSalesOrder->toDocKey = $so_data->todockey;
    $acSalesOrder->toDtlKey = $so_data->todtlkey;
    $acSalesOrder->cancelled = $so_data->cancelled;
    $acSalesOrder->lastUpdate = $so_data->lastupdate;
    $acSalesOrder->deleted = $so_data->isdelete;
    $acSalesOrder->lastSyncAt = $sync_at;
    $acSalesOrder->updated_at = $so_data->updated_at;

    $acSalesOrder->save();

    $so_items_data = $so_data->solist;

    if(isset($so_items_data)) {
        foreach($so_items_data as $soItem)
        {
            $acSoItem = AcSalesOrderItem::find($soItem->id);
            if (!isset($acSoItem)) {
                $acSoItem = new AcSalesOrderItem();
                $acSoItem->id = $soItem->id;
                $acSoItem->customerId = $soItem->customer_id;
                $acSoItem->soId = $acSalesOrder->id;
                $acSoItem->created_at = $acSalesOrder->created_at;
            }
            $acSoItem->dtlKey = $soItem->dtlkey;
            $acSoItem->docKey = $soItem->dockey;
            $acSoItem->itemCode = $soItem->itemcode;
            $acSoItem->location = $soItem->location;
            $acSoItem->description = $soItem->description;
            $acSoItem->furtherDescription = $soItem->furtherdescription;
            $acSoItem->uom = $soItem->uom;
            $acSoItem->rate = $soItem->rate;
            $acSoItem->qty = $soItem->qty;
            $acSoItem->transferedQty = $soItem->transferedqty;
            $acSoItem->smallestUnitPrice = $soItem->smallestunitprice;
            $acSoItem->unitPrice = $soItem->unitprice;
            $acSoItem->discount = $soItem->discount;
            $acSoItem->discountAmt = $soItem->discountamt;
            $acSoItem->taxType = $soItem->taxtype;
            $acSoItem->taxRate = $soItem->taxrate;
            $acSoItem->fromDocNo = $soItem->fromdocno;
            $acSoItem->fromDocType = $soItem->fromdoctype;
            $acSoItem->fromDtlKey = $soItem->fromdocdtlkey;
            $acSoItem->updated_at = $soItem->updated_at;
            $acSoItem->lastSyncAt = $sync_at;

            $acSoItem->save();
        }
    }

    return $result;

}

function createOrupdateTemporaryReceiptForAC($tr_data, $sync_at)
{
    $acPdcheader = AcPdcHeader::find($tr_data->id);
    $result = false;
    $lastUpdate = 0;
    if(!isset($acPdcheader)){
        $acPdcheader = new AcPdcHeader();
        $acPdcheader->id = $tr_data->id;
        $acPdcheader->customerId = $tr_data->customer_id;
        $acPdcheader->created_at = $tr_data->created_at;
        $result = true;
    } else {
        $lastUpdate = $acPdcheader->lateupdate;
        $lastUpdate++;
    }

    if($sync_at == null)
        $sync_at = date('Y-m-d h:i:s');

    $acPdcheader->customerSubId = $tr_data->customer_sub_id;
    $acPdcheader->pdcHeaderId = $tr_data->pdcheader_id;
    $acPdcheader->docFormatId = $tr_data->doc_id;
    $acPdcheader->docFormat = $tr_data->doc_format;
    $acPdcheader->docFormatNo = $tr_data->doc_no;
    $acPdcheader->docNo = $tr_data->docno;
    $acPdcheader->debtorCode = $tr_data->debtorcode;
    $acPdcheader->projectCode = $tr_data->projectcode;
    $acPdcheader->departmentCode = $tr_data->departmentcode;
    $acPdcheader->currencyCode = $tr_data->currencycode;
    $acPdcheader->description = $tr_data->description;
    $acPdcheader->receivedDate = $tr_data->receiveddate;
    $acPdcheader->chequeDate = $tr_data->chequedate;
    $acPdcheader->secondReceiptNo = $tr_data->secondreceiptno;
    $acPdcheader->aRPaymentDocKey = $tr_data->arpaymentdockey;
    $acPdcheader->salesAgent = $tr_data->salesagent;
    $acPdcheader->createdBy = $tr_data->createdby;
    $acPdcheader->modifiedBy = $tr_data->modifiedby;

    $acPdcheader->cancelled = 0;
    $acPdcheader->lastUpdate = $lastUpdate;
    $acPdcheader->deleted = $tr_data->isdelete;
    $acPdcheader->lastSyncAt = $sync_at;
    $acPdcheader->updated_at = $tr_data->updated_at;

    $acPdcheader->save();

    $tr_details_data = $tr_data->pdcdetail;
    if(isset($tr_details_data)) {
        foreach($tr_details_data as $trDetail)
        {
            $acPdcDetail = AcPdcDtl::find($trDetail->id);
            if (!isset($acPdcDetail)) {
                $acPdcDetail = new AcPdcDtl();
                $acPdcDetail->id = $trDetail->id;
                $acPdcDetail->customerId = $trDetail->customer_id;
                $acPdcDetail->pdcId = $acPdcheader->id;
                $acPdcDetail->created_at = $acPdcheader->created_at;
            }
            $acPdcDetail->pdcDetailsId = $trDetail->pdcdetails_id;
            $acPdcDetail->paymentMethod = $trDetail->paymentmethod;
            $acPdcDetail->chequeNo = $trDetail->chequeno;
            $acPdcDetail->paymentAmount = $trDetail->paymentamount;
            $acPdcDetail->isRCHQ = $trDetail->isrchq;
            $acPdcDetail->rchqDate = $trDetail->rchqdate;
            $acPdcDetail->bankCharge = $trDetail->bankcharge;
            $acPdcDetail->toBankRate = $trDetail->tobankrate;
            $acPdcDetail->bankChargeTaxCode = $trDetail->bankchargetaxcode;
            $acPdcDetail->bankChargeTax = $trDetail->bankchargetax;
            $acPdcDetail->bankChargeBillNoGST = $trDetail->bankchargebillnogst;
            $acPdcDetail->paymentBy = $trDetail->paymentby;
            $acPdcDetail->bankChargeTaxRate = $trDetail->bankchargetaxrate;
            $acPdcDetail->bankChargeProjNo = $trDetail->bankchargeprojno;
            $acPdcDetail->pdcHeaderId = $trDetail->pdcheader_id;
            $acPdcDetail->deleted = 0;
            $acPdcDetail->updated_at = $trDetail->updated_at;
            $acPdcDetail->lastSyncAt = $sync_at;

            $acPdcDetail->save();
        }
    }

    $tr_knockoff_data = $tr_data->pdcknockoffdetail;
    if(isset($tr_knockoff_data)) {
        foreach($tr_knockoff_data as $trKnockoffDetail)
        {
            $acPdcKnockoffDetail = AcPdcKnockoffDtl::find($trKnockoffDetail->id);
            if (!isset($acPdcKnockoffDetail)) {
                $acPdcKnockoffDetail = new AcPdcKnockoffDtl();
                $acPdcKnockoffDetail->id = $trKnockoffDetail->id;
                $acPdcKnockoffDetail->customerId = $trKnockoffDetail->customer_id;
                $acPdcKnockoffDetail->pdcId = $acPdcheader->id;
                $acPdcKnockoffDetail->created_at = $acPdcheader->created_at;
            }
            $acPdcKnockoffDetail->pdcHeaderId = $trKnockoffDetail->pdcheader_id;
            $acPdcKnockoffDetail->pdcKnockOffId = $trKnockoffDetail->pdcknockoff_id;
            $acPdcKnockoffDetail->docType = $trKnockoffDetail->doctype;
            $acPdcKnockoffDetail->docKey = $trKnockoffDetail->dockey;
            $acPdcKnockoffDetail->paidAmount = $trKnockoffDetail->paidamount;
            $acPdcKnockoffDetail->discountAmount = $trKnockoffDetail->discountamount;
            $acPdcKnockoffDetail->deleted = 0;
            $acPdcKnockoffDetail->updated_at = $trKnockoffDetail->updated_at;
            $acPdcKnockoffDetail->lastSyncAt = $sync_at;

            $acPdcKnockoffDetail->save();
        }
    }

    return $result;

}



function startDataSync($id, $data_key)
{
    $lastSuccessSyncTS = null;
    $nowDate = date('Y-m-d h:i:s');
    $dataSyncApi = DataSyncApi::where('reference_key', $id)->first();

    if(isset($dataSyncApi)) {
        $dataSyncDetail = DataSyncDetail::where('data_sync_api_id', $dataSyncApi->id)->where('data_key', $data_key)->first();

        if(!isset($dataSyncDetail)) {
            $dataSyncDetail = new DataSyncDetail();
            $dataSyncDetail->data_sync_api_id = $dataSyncApi->id;
            $dataSyncDetail->data_key = $data_key;
            $dataSyncDetail->first_sync_ts = $nowDate;
        }

        if($dataSyncDetail->sync_mode == 0)
            $lastSuccessSyncTS = $dataSyncDetail->last_success_sync_ts;

        $dataSyncDetail->sync_mode == 1;
        $dataSyncDetail->sync_attempts = $dataSyncDetail->sync_attempts + 1;
        $dataSyncDetail->last_sync_ts = $nowDate;
        $dataSyncDetail->job_status = 2; //job running
        $dataSyncDetail->save();
    }

    return $lastSuccessSyncTS;
}

function updateDataSync($id, $data_key, $status)
{
    $nowDate = date('Y-m-d h:i:s');
    $dataSyncApi = DataSyncApi::where('reference_key', $id)->first();
    if(isset($dataSyncApi)) {
        $dataSyncDetail = DataSyncDetail::where('data_sync_api_id', $dataSyncApi->id)->where('data_key', $data_key)->first();
        if(!isset($dataSyncDetail)) {
            $dataSyncDetail = new DataSyncDetail();
            $dataSyncDetail->data_sync_api_id = $dataSyncApi->id;
            $dataSyncDetail->data_key = $data_key;
            $dataSyncDetail->first_sync_ts = $nowDate;
        }

        if($status == 8) {
            if($dataSyncDetail->sync_mode == 1) {
                $dataSyncDetail->sync_mode = 0;
                $dataSyncDetail->last_full_sync_ts = $dataSyncDetail->last_sync_ts;
            }

            $dataSyncDetail->last_success_sync_ts = $dataSyncDetail->last_sync_ts;
            $dataSyncDetail->sync_attempts = 0;
        }

        $dataSyncDetail->job_status = 8; //job running
        $dataSyncDetail->save();
    }
}

function getAllIdsNotDeletedByCustomer($dataKey, $customerId)
{
    $returnList = null;
    switch ($dataKey) {
        case 'CreditTerms':
            $returnList = AcCreditTerms::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'Debtor':
            $returnList = AcDebtor::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'SalesAgent':
            $returnList = AcSalesAgent::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'SalesOrder':
            $returnList = AcSalesOrder::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'StockItem':
            $returnList = AcStockItem::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'TaxType':
            $returnList = AcTaxType::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'ARInvoice':
            $returnList = AcArInvoice::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'ARDebitNote':
            $returnList = AcArDebitNote::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'PDC':
            $returnList = AcPdcHeader::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'SalesInvoice':
            $returnList = AcCreditTerms::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'SalesCreditNote':
            $returnList = AcSalesCreditNote::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
        case 'SalesDebitNote':
            $returnList = AcSalesDebitNote::where('customerId', $customerId)->where('deleted', 0)->get('id');
            break;
    }

    $ids = [];
    if(isset($returnList)) {
        foreach($returnList as $item)
            array_push($ids, $item->id);
    }

    return $ids;
}
