<?php

namespace App\Console\Commands;

use App\AcArDebitNote;
use App\AcArInvoice;
use App\AcDebtor;
use App\AcPdcHeader;
use App\AcSalesAgent;
use App\AcSalesCreditNote;
use App\AcSalesDebitNote;
use App\AcSalesInvoice;
use App\AcSalesOrder;
use App\AcStockItem;
use App\AcTaxType;
use App\DataSyncApi;
use App\ExternalApi\AutoCountApi;
use App\MsCompany;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $fiscalDate = '2020-07-01 00:00:00';

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
     * @return mixed
     */
    public function handle()
    {
        Log::info("==== New Cron ====");
        Log::info("Start!!!");
        $syncApis = DataSyncApi::where('active', 1)->where('api_type', 'AC')->get();

        foreach ($syncApis as $key=>$syncApi) {

            $id = $syncApi->reference_key;

            $autoCountApi = new AutoCountApi($syncApi->login_name, $syncApi->login_pass_enc);
            $token = $autoCountApi->getToken();

            $lastSuccessSyncTS = null;
            $syncTS = null;
            $seq = 0;
            $newRecords = 0;
            $latestDate = null;

            set_time_limit(100000);
            ini_set('memory_limit','256M');

            $data = $this->syncSalesAgent($autoCountApi, $id);
            Log::info("SalesAgent - " . json_encode($data));
            $data = $this->syncTaxType($autoCountApi, $id);
            Log::info("TaxType - " . json_encode($data));
            $data = $this->syncTerm($autoCountApi, $id);
            Log::info("AcCreditTerms - " . json_encode($data));
            $data = $this->syncStockItem($autoCountApi, $id);
            Log::info("StockItem - " . json_encode($data));
            $data = $this->syncDebtor($autoCountApi, $id);
            Log::info("Debtor - " . json_encode($data));
            $data = $this->syncSalesOrder($autoCountApi, $id);
            Log::info("SalesOrder - " . json_encode($data));
            $data = $this->syncInvoice($autoCountApi, $id); //need testing
            Log::info("Invoice - " . json_encode($data));
            $data = $this->syncDebitNote($autoCountApi, $id);
            Log::info("DebitNote - " . json_encode($data));
            $data = $this->syncSalesInvoice($autoCountApi, $id);  //need testing
            Log::info("SalesInvoice - " . json_encode($data));
            $data = $this->syncSalesCreditNote($autoCountApi, $id); //need testing
            Log::info("SalesCreditNote - " . json_encode($data));
            $data = $this->syncSalesDebitNote($autoCountApi, $id); //need testing
            Log::info("SalesDebitNote - " . json_encode($data));
        }
        Log::info("End!!!");
    }


    public function syncSalesAgent($api, $id)
    {
        Log::info("Start syncSalesAgent - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesAgent');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestSalesAgent($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'SalesAgent', 7);
                $data['result'] = false;
                $data['error_id'] = $id;
                $data['message'] = 'No data to update from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $salesAgent = $api->readSalesAgent($latestID->id);

                        if(!$salesAgent) {
                        } else {
                            if (createOrupdateSalesAgentForAC($salesAgent[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }

        } else {
            $salesAgents = $api->readSalesAgent(null);
            if(!$salesAgents) {
                updateDataSync($id, 'SalesAgent', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestSalesAgent from AutoCount';
                return $data;
            } else {
                if(!empty($salesAgents)) {
                    foreach($salesAgents as $salesAgent) {
                        if(createOrupdateSalesAgentForAC($salesAgent, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'SalesAgent', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for SalesAgent';

        Log::info("End syncSalesAgent - " . $id);
        return $data;
    }

    public function syncTaxType($api, $id)
    {
        Log::info("Start syncTaxType - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'TaxType');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestTaxType($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'TaxType', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestTaxType from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $taxType = $api->readTaxType($latestID->id);

                        if(!$taxType) {
                        } else {
                            if (createOrupdateTaxTypeForAC($taxType[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }

        } else {
            $taxTypes = $api->readTaxType(null);
            if(!$taxTypes) {
                updateDataSync($id, 'TaxType', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestTaxType from AutoCount';
                return $data;
            } else {
                if(!empty($taxTypes)) {
                    foreach($taxTypes as $taxType) {
                        if(createOrupdateTaxTypeForAC($taxType, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'TaxType', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for TaxType';

        Log::info("End syncTaxType - " . $id);
        return $data;
    }

    public function syncTerm($api, $id)
    {
        Log::info("Start syncTerm - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'CreditTerms');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestTerm($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'CreditTerms', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestTerm from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $creditTerm = $api->readTerm($latestID->id);

                        if(!$creditTerm) {
                        } else {
                            if (createOrupdateTermForAC($creditTerm[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }

        } else {
            $creditTerms = $api->readTerm(null);
            if(!$creditTerms) {
                updateDataSync($id, 'CreditTerms', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestTerm from AutoCount';
                return $data;
            } else {
                if(!empty($creditTerms)) {
                    foreach($creditTerms as $creditTerm) {
                        if(createOrupdateTermForAC($creditTerm, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'CreditTerms', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for Term';

        Log::info("End syncTerm - " . $id);
        return $data;
    }

    public function syncStockItem($api, $id)
    {
        Log::info("Start syncStockItem - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'StockItem');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestStockItem($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'StockItem', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestStockItem from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $stockItem = $api->readStockItem($latestID->id);

                        if(!$stockItem) {
                        } else {
                            if (createOrupdateStockItemForAC($stockItem[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }

        } else {
            $stockItems = $api->readStockItem(null);
            if(!$stockItems) {
                updateDataSync($id, 'StockItem', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestStockItem from AutoCount';
                return $data;
            } else {
                if(!empty($stockItems)) {
                    foreach($stockItems as $stockItem) {
                        if(createOrupdateStockItemForAC($stockItem, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'StockItem', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for StockItem';

        Log::info("End syncStockItem - " . $id);
        return $data;
    }

    public function syncDebtor($api, $id)
    {
        Log::info("Start syncDebtor - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'Debtor');

        $latestDate = $this->fiscalDate;

        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestDebtor($latestDate);

        if($latestIDs === false) {
            updateDataSync($id, 'Debtor', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestDebtor';
            return $data;
        } else {

            if(!empty($latestIDs)) {
                Log::info("special log = readAllLatestDebtor - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readDebtorByGroup($stepMin, $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readDebtorByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if (createOrupdateDebtorForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        /*
        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestDebtor($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'Debtor', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestDebtor from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $stockItem = $api->readDebtor($latestID->id);

                        if(!$stockItem) {
                        } else {
                            if (createOrupdateDebtorForAC($stockItem[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }
        } else {
            $stockItems = $api->readDebtor(null);
            if(!$stockItems) {
                updateDataSync($id, 'Debtor', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllDebtor from AutoCount';
                return $data;
            } else {
                if(!empty($stockItems)) {
                    foreach($stockItems as $stockItem) {
                        if(createOrupdateDebtorForAC($stockItem, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }
        */

        updateDataSync($id, 'Debtor', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for Debtor';

        Log::info("End syncDebtor - " . $id);
        return $data;
    }

    public function syncSalesOrder($api, $id)
    {
        Log::info("Start syncSalesOrder - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesOrder');

        $latestDate = $this->fiscalDate;

        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestSalesOrder($latestDate);

        if($latestIDs === false) {
            updateDataSync($id, 'SalesOrder', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesOrder';
            return $data;
        } else {

            if(!empty($latestIDs)) {
                Log::info("special log = readAllLatestSalesOrder - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readSalesOrderByGroup($stepMin, $stepMax);
                    Log::info("special log = readSalesOrderByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readSalesOrderByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if (createOrupdateSalesOrderForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        updateDataSync($id, 'SalesOrder', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for SalesOrder';

        Log::info("End syncSalesOrder - " . $id);
        return $data;
    }

    public function syncInvoice($api, $id)
    {
        Log::info("Start syncInvoice - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'ARInvoice');

        $latestDate = $this->fiscalDate;

        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestARInvoice($latestDate);

        if($latestIDs === false) {
            updateDataSync($id, 'ARInvoice', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestARInvoice';
            return $data;
        } else {

            if(!empty($latestIDs)) {

                Log::info("special log = readAllLatestARInvoice - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readARInvoiceByGroup($stepMin, $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readARInvoiceByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if (createOrupdateARInvoiceForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        updateDataSync($id, 'ARInvoice', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for ARInvoice';

        Log::info("End syncInvoice - " . $id);
        return $data;
    }

    public function syncDebitNote($api, $id)
    {
        Log::info("Start syncDebitNote - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'ARDebitNote');

        $latestDate = $this->fiscalDate;
        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestARDebitNote($latestDate);

        if(!$latestIDs) {
            updateDataSync($id, 'ARDebitNote', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestARDebitNote from AutoCount';
            return $data;
        } else {
            if(!empty($latestIDs)) {
                Log::info("special log = readAllLatestARDebitNote - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readARDebitNoteByGroup($stepMin, $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readARDebitNoteByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if (createOrupdateARDebitNoteForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        updateDataSync($id, 'ARDebitNote', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for ARDebitNote';

        Log::info("End syncDebitNote - " . $id);
        return $data;
    }

    public function syncSalesInvoice($api, $id)
    {
        Log::info("Start syncSalesInvoice - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesInvoice');

        $latestDate = $this->fiscalDate;
        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestSalesInvoice($latestDate);
        Log::info("special log = readAllLatestSalesInvoice API call");

        if($latestIDs === false) {
            Log::info("special log = readAllLatestSalesInvoice API call failed");
            updateDataSync($id, 'SalesInvoice', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesInvoice';
            return $data;
        } else {
            if(!empty($latestIDs)) {
                Log::info("special log = readAllLatestSalesInvoice - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readSalesInvoiceByGroup($stepMin, $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readSalesInvoiceByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if (createOrupdateSalesInvoiceForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        updateDataSync($id, 'SalesInvoice', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for SalesInvoice';

        Log::info("End syncSalesInvoice - " . $id);
        return $data;
    }

    public function syncSalesCreditNote($api, $id)
    {
        Log::info("Start syncSalesCreditNote - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesCreditNote');

        $latestDate = $this->fiscalDate;
        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestSalesCreditNote($latestDate);

        if($latestIDs === false) {
            updateDataSync($id, 'SalesCreditNote', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesCreditNote';
            return $data;
        } else {
            if(!empty($latestIDs)) {
                Log::info("special log = readAllLatestSalesCreditNote - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readSalesCreditNoteByGroup($stepMin, $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readSalesCreditNoteByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if ($item->updated_at)
                                    if (createOrupdateSalesCreditNoteForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        updateDataSync($id, 'SalesCreditNote', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for SalesCreditNote';

        Log::info("End syncSalesCreditNote - " . $id);
        return $data;
    }

    public function syncSalesDebitNote($api, $id)
    {
        Log::info("Start syncSalesDebitNote - " . $id);
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesDebitNote');

        $latestDate = $this->fiscalDate;
        if(isset($lastSuccessSyncTS))
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
        Log::info("special log (latestDate) = " . $latestDate);

        $latestIDs = $api->readAllLatestSalesDebitNote($latestDate);

        if($latestIDs === false) {
            updateDataSync($id, 'SalesDebitNote', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesDebitNote';
            return $data;
        } else {
            if(!empty($latestIDs)) {
                Log::info("special log = readAllLatestSalesDebitNote - " . count($latestIDs));

                $minId = reset($latestIDs)->id;
                $maxId = end($latestIDs)->id;
                foreach($latestIDs as $rows) {
                    if ($minId > $rows->id)
                        $minId = $rows->id;

                    if ($maxId < $rows->id)
                        $maxId = $rows->id;
                }

                $stepMin = $minId;
                $stepMax = $minId + 4999;
                for(;;){
                    if ($maxId <= $stepMax)
                        $stepMax = $maxId;

                    $items = $api->readSalesDebitNoteByGroup($stepMin, $stepMax);

                    if($items === false) {
                        Log::info("error - special log = readSalesDebitNoteByGroup - MinID = " . $stepMin .", MaxID = " . $stepMax);
                    } else {
                        if(!empty($items)) {
                            foreach($items as $item) {
                                if (strtotime($item->updated_at) > strtotime($latestDate)) {
                                    if (createOrupdateSalesDebitNoteForAC($item, $syncTS))
                                        $newRecords++;
                                    else
                                        $updateRecords++;
                                }
                            }
                            $items = null;
                        }
                    }

                    if ($stepMax == $maxId) {
                        break;
                    } else {
                        $stepMin += 5000;
                        $stepMax += 5000;
                    }
                }
            }
        }

        updateDataSync($id, 'SalesDebitNote', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for SalesDebitNote';

        Log::info("End syncSalesDebitNote - " . $id);
        return $data;
    }



    public function checkAutoCountDeletion($id) {

        $id = 'CTH'; //referenceKey on data sync table
        $autoCountApi = new AutoCountApi('keluargacth@gmail.com', 'keluargaCTH@123');
        // $dataSyncApi = DataSyncApi::where('reference_key', $request->companyCode)->first();
        //     $acApi = new AutoCountApi($dataSyncApi->login_name, $dataSyncApi->login_pass_enc);//'keluargaCTH@123'
        $token = $autoCountApi->getToken();
        $customerId = MsCompany::where('code', $id)->first()->extId;

        //SalesAgent
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllSalesAgentID());
        $dbIDs =  $this->getOnlyIDs(AcSalesAgent::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcSalesAgent::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //TaxType
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllTaxTypeID());
        $dbIDs =  $this->getOnlyIDs(AcTaxType::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcTaxType::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //Debtor
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllDebtorID());
        $dbIDs =  $this->getOnlyIDs(AcDebtor::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcDebtor::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //StockItem
        $cloudDebtorIDs = $this->getOnlyIDs($autoCountApi->readAllStockItemID());
        $dbIDs =  $this->getOnlyIDs(AcStockItem::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcStockItem::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //SalesOrder
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllSalesOrderID());
        $dbIDs =  $this->getOnlyIDs(AcSalesOrder::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcSalesOrder::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //ARInvoice
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllARInvoiceID());
        $dbIDs =  $this->getOnlyIDs(AcArInvoice::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcArInvoice::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //ARDebitNotes
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllARDebitNoteID());
        $dbIDs =  $this->getOnlyIDs(AcArDebitNote::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcArDebitNote::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //TemporaryReceipts
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllTemporaryReceiptID());
        $dbIDs =  $this->getOnlyIDs(AcPdcHeader::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcPdcHeader::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //SalesInvoice
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllSalesInvoiceID());
        $dbIDs =  $this->getOnlyIDs(AcSalesInvoice::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcSalesInvoice::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //SalesCreditNote
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllSalesCreditNoteID());
        $dbIDs =  $this->getOnlyIDs(AcSalesCreditNote::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcSalesCreditNote::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

        //SalesDebitNote
        $cloudIDs = $this->getOnlyIDs($autoCountApi->readAllSalesDebitNoteID());
        $dbIDs =  $this->getOnlyIDs(AcSalesDebitNote::where('customerId', $customerId)->where('deleted', 0)->get('id'));
        $deletedIDs = array_diff($cloudIDs, $dbIDs);

        if(!empty($deletedIDs))
            AcSalesDebitNote::where('customerId', $customerId)->whereIn('id', $deletedIDs)->update(['deleted' => 1]);

    }


    function getOnlyIDs($data) {
        $ids = [];
        if(isset($data)) {
            foreach($data as $item)
                array_push($ids, $item->id);
        }

        return $ids;
    }
}
