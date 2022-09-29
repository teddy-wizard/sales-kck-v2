<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataSyncApi;
use App\ExternalApi\AutoCountApi;

class SyncController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('main.sync.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function syncAutoCountData(Request $request)
    {
        // $this->checkAutoCountDeletion('CTH');

        if(!isset($request->option)) {
            $data['result'] = false;
            return response()->json($data);
        }

        $option = $request->option;

        $syncApis = DataSyncApi::where('active', 1)->where('api_type', 'AC')->get();

        foreach ($syncApis as $syncApi) {

            $id = $syncApi->reference_key;

            $autoCountApi = new AutoCountApi($syncApi->login_name, $syncApi->login_pass_enc);
            $token = $autoCountApi->getToken();

            $lastSuccessSyncTS = null;
            $syncTS = null;
            $seq = 0;
            $newRecords = 0;
            $latestDate = null;

            set_time_limit(100000);

            switch ($option) {
                case 'SalesAgent':
                    $data = $this->syncSalesAgent($autoCountApi, $id);
                    break;
                case 'TaxType':
                    $data = $this->syncTaxType($autoCountApi, $id);
                    break;
                case 'Term':
                    $data = $this->syncTerm($autoCountApi, $id);
                    break;
                case 'StockItem':
                    $data = $this->syncStockItem($autoCountApi, $id);
                    break;
                case 'Debtor':
                    $data = $this->syncDebtor($autoCountApi, $id);
                    break;
                case 'SalesOrder':
                    $data = $this->syncSalesOrder($autoCountApi, $id);
                    break;
                case 'Invoice':
                    $data = $this->syncInvoice($autoCountApi, $id); //need testing
                    break;
                case 'DebitNote':
                    $data = $this->syncDebitNote($autoCountApi, $id);
                    break;
                case 'SalesInvoice':
                    $data = $this->syncSalesInvoice($autoCountApi, $id);  //need testing
                    break;
                case 'SalesCreditNote':
                    $data = $this->syncSalesCreditNote($autoCountApi, $id); //need testing
                    break;
                case 'SalesDebitNote':
                    $data = $this->syncSalesDebitNote($autoCountApi, $id); //need testing
                    break;
            }

        }
        return response()->json($data);
    }

    public function syncSalesAgent($api, $id)
    {
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
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for SalesAgent';

        return $data;
    }

    public function syncTaxType($api, $id)
    {
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
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for TaxType';

        return $data;
    }

    public function syncTerm($api, $id)
    {
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
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for Term';

        return $data;
    }

    public function syncStockItem($api, $id)
    {
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
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for StockItem';

        return $data;
    }

    public function syncDebtor($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'Debtor');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestDebtor($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'Debtor', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestTerm from AutoCount';
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
                $data['message'] = 'Failed getting readAllLatestDebtor from AutoCount';
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

        updateDataSync($id, 'Debtor', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for Debtor';

        return $data;
    }

    public function syncSalesOrder($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesOrder');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestSalesOrder($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'SalesOrder', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestSalesOrder from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $stockItem = $api->readSalesOrder($latestID->id);

                        if(!$stockItem) {
                        } else {
                            if (createOrupdateSalesOrderForAC($stockItem[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }
        } else {
            $stockItems = $api->readSalesOrder(null);
            if(!$stockItems) {
                updateDataSync($id, 'SalesOrder', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllSalesOrder from AutoCount';
                return $data;
            } else {
                if(!empty($stockItems)) {
                    foreach($stockItems as $stockItem) {
                        if(createOrupdateSalesOrderForAC($stockItem, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'SalesOrder', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for SalesOrder';

        return $data;
    }

    public function syncInvoice($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'ARInvoice');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestInvoices($latestDate);

            if(!$latestIDs) {
                updateDataSync($id, 'ARInvoice', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestARInvoice from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $item = $api->readARInvoice($latestID->id);

                        if(!$item) {
                        } else {
                            if (createOrupdateARInvoiceForAC($item[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }
        } else {
            $items = $api->readARInvoice(null);
            if(!$items) {
                updateDataSync($id, 'ARInvoice', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllARInvoice from AutoCount';
                return $data;
            } else {
                if(!empty($items)) {
                    foreach($items as $item) {
                        if(createOrupdateARInvoiceForAC($item, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'ARInvoice', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for ARInvoice';

        return $data;
    }

    public function syncDebitNote($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'ARDebitNote');

        if(isset($lastSuccessSyncTS)) {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $latestIDs = $api->readAllLatestARDebitNote($latestDate);
            if(!$latestIDs) {
                updateDataSync($id, 'ARDebitNote', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestARDebitNote from AutoCount';
                return $data;
            } else {
                if(!empty($latestIDs)) {
                    foreach ($latestIDs as $latestID) {
                        $salesAgent = $api->readARDebitNote($latestID->id);

                        if(!$salesAgent) {
                        } else {
                            if (createOrupdateARDebitNoteForAC($salesAgent[0], $syncTS))
                                $newRecords++;
                            else
                                $updateRecords++;
                        }
                    }
                }
            }

        } else {
            $debitNotes = $api->readARDebitNote(null);
            if(!$debitNotes) {
                updateDataSync($id, 'ARDebitNote', 7);
                $data['result'] = false;
                $data['message'] = 'Failed getting readAllLatestARDebitNote from AutoCount';
                return $data;
            } else {
                if(!empty($debitNotes)) {
                    foreach($debitNotes as $debitNote) {
                        if(createOrupdateARDebitNoteForAC($debitNote, $syncTS))
                            $newRecords++;
                        else
                            $updateRecords++;
                    }
                }
            }
        }

        updateDataSync($id, 'ARDebitNote', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for ARDebitNote';

        return $data;
    }

    public function syncSalesInvoice($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesInvoice');

        $idList = null;
        if(isset($lastSuccessSyncTS)) {
            $idList = $api->readAllSalesInvoiceID();
        } else {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $idList = $api->readAllLatestSalesInvoice($latestDate);
        }


        if(!$idList) {
            updateDataSync($id, 'SalesInvoice', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesInvoice from AutoCount';
            return $data;
        }


        foreach($idList as $latestId) {
            $stockItem = $api->readSalesInvoice($latestId->id);

            if(!$stockItem) {
            } else {
                if (createOrupdateSalesInvoiceForAC($stockItem[0], $syncTS))
                    $newRecords++;
                else
                    $updateRecords++;
            }
        }

        updateDataSync($id, 'SalesInvoice', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for SalesInvoice';

        return $data;
    }

    public function syncSalesCreditNote($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesCreditNote');

        $idList = null;
        if(isset($lastSuccessSyncTS)) {
            $idList = $api->readAllSalesCreditNoteID();
        } else {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $idList = $api->readAllLatestSalesCreditNote($latestDate);
        }


        if(!$idList) {
            updateDataSync($id, 'SalesCreditNote', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesCreditNote from AutoCount';
            return $data;
        }


        foreach($idList as $latestId) {
            $stockItem = $api->readSalesCreditNote($latestId->id);

            if(!$stockItem) {
            } else {
                if (createOrupdateSalesCreditNoteForAC($stockItem[0], $syncTS))
                    $newRecords++;
                else
                    $updateRecords++;
            }
        }

        updateDataSync($id, 'SalesCreditNote', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for SalesCreditNote';

        return $data;
    }

    public function syncSalesDebitNote($api, $id)
    {
        $newRecords = 0;
        $updateRecords = 0;
        $syncTS = date('Y-m-d h:i:s');
        $lastSuccessSyncTS = null;

        $lastSuccessSyncTS = startDataSync($id, 'SalesDebitNote');

        $idList = null;
        if(isset($lastSuccessSyncTS)) {
            $idList = $api->readAllSalesDebitNoteID();
        } else {
            $latestDate = getLatestDate($lastSuccessSyncTS, env('API_AC_SYNC_TIMEDIFF'));
            $idList = $api->readAllLatestSalesDebitNote($latestDate);
        }


        if(!$idList) {
            updateDataSync($id, 'SalesDebitNote', 7);
            $data['result'] = false;
            $data['message'] = 'Failed getting readAllLatestSalesDebitNote from AutoCount';
            return $data;
        }


        foreach($idList as $latestId) {
            $stockItem = $api->readSalesDebitNote($latestId->id);

            if(!$stockItem) {
            } else {
                if (createOrupdateSalesDebitNoteForAC($stockItem[0], $syncTS))
                    $newRecords++;
                else
                    $updateRecords++;
            }
        }

        updateDataSync($id, 'SalesDebitNote', 8); //8 means Sync success
        $data['result'] = true;
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$newRecords.' records for SalesDebitNote';

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
