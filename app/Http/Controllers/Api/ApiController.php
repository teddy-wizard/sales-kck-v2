<?php

namespace App\Http\Controllers\Api;

use App\AcArDebitNote;
use App\AcArInvoice;
use App\AcCreditTerms;
use App\AcDebtor;
use App\AcPdcDtl;
use App\AcPdcHeader;
use App\AcPdcKnockoffDtl;
use App\AcSalesAgent;
use App\AcSalesCreditNote;
use App\AcSalesDebitNote;
use App\AcSalesInvoice;
use App\AcSalesOrder;
use App\AcSalesOrderItem;
use App\AcSalessOrder;
use App\AcStockItem;
use App\AcStockItemUom;
use App\AcTaxType;
use App\B1Customer;
use App\B1CustomerAddress;
use App\B1Item;
use App\B1ItemUom;
use App\B1PaymentTerm;
use App\B1SalesOrder;
use App\B1SalesOrderItem;
use App\B1SalesPeople;
use App\B1TaxType;
use App\Branch;
use App\Company;
use App\DataSyncApi;
use App\ExternalApi\AutoCountApi;
use App\Http\Requests\StoreUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\MsCompany;
use App\MsSalesArea;
use App\MsSalesPeople;
use App\MsSalesPersonMapping;
use App\UserRole;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('username', $request->username)->first();
        if($user && Hash::check($request->password, $user->password) ) {
            if (in_array('4', $user->role) || in_array('5', $user->role)) {
                $salesperson = MsSalesPeople::where('userId', $user->id)->first();

                if($salesperson) {
                    $result['id'] = $user->id;
                    $result['username'] = $user->username;
                    $result['name'] = $user->name;
                    $result['email'] = $user->email;
                    $result['role'] = $user->role;
                    $result['salesAgentCode'] = $salesperson->code;
                    $sales_agent_mappings = MsSalesPersonMapping::where('salespersonId', $salesperson->id)->get();
                    $companies = [];
                    foreach($sales_agent_mappings as $sales_agent_mapping) {
                        $ac_sales_agent = $sales_agent_mapping->acSalesAgentInfo;
                        if(isset($ac_sales_agent) && ($ac_sales_agent->active == 1) && ($ac_sales_agent->deleted == 0)) {
                            $company = [];
                            $company['code'] = $sales_agent_mapping->companyInfo->code;
                            $company['name'] = $sales_agent_mapping->companyInfo->name;
                            $company['salesAgent'] = $ac_sales_agent->salesAgent;
                            $company['sysType'] = $sales_agent_mapping->companyInfo->sysType;
                            $company['displayName'] = $sales_agent_mapping->companyInfo->displayName;
                            $company['address'] = $sales_agent_mapping->companyInfo->address;
                            $company['phone'] = $sales_agent_mapping->companyInfo->phone;
                            $company['fax'] = $sales_agent_mapping->companyInfo->fax;
                            $company['email'] = $sales_agent_mapping->companyInfo->email;
                            $company['website'] = $sales_agent_mapping->companyInfo->website;
                            $company['lbbNo'] = $sales_agent_mapping->companyInfo->lbbNo;
                            $company['gstRegNo'] = $sales_agent_mapping->companyInfo->gstRegNo;
                            $company['additionalInfo'] = $sales_agent_mapping->companyInfo->additionalInfo;
                            array_push($companies, $company);
                        }
                    }

                    $result['companies'] = $companies;


                    return response()->json(['result' => true, $result]);
                } else {
                    return response()->json(['result' => false, 'error' => 'This is a non-sales user.'], 200);
                }
            } else {
                return response()->json(['result' => false, 'error' => 'This user has not a role for sales'], 200);
            }
        } else {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }
    }

    public function reset(Request $request)
    {
        $user = User::where('username', $request->username)->first();
        if($user) {
            $password = $this->generatePassword();
            $data = array('username'=>$user->name, 'password'=>$password);
            $this->mail = $user->email;
            Mail::send('mail', $data, function($message) {
                $message->to($this->mail, 'KCK Sales')
                        ->subject('KCK Sales App Password Request.');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            $user->password = bcrypt($password);;
            $user->save();
            $data['result'] = true;
            return response()->json($data);
        } else {
            $data['result'] = false;
            $data['error'] = 'Cannot find that user.';
            return response()->json($data);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        if(isset($request->password)) {
            if(Hash::check($request->password, $user->password))
                return response()->json(['result' => false, 'error' => 'Not match with old password'], 200);
        }

        if(isset($request->newPassword))
            $newPassword = $request->newPassword;

        if(isset($request->confirmPassword))
            $confirmPassword = $request->confirmPassword;

        if($newPassword != $confirmPassword)
            return response()->json(['result' => false, 'error' => 'Not match with confirm password'], 200);

        $user->password = bcrypt($newPassword);
        // $user->save();
        $data['result'] = true;
        return response()->json($data);
    }

    public function getCustomersByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        // $user = User::where('username', $username)->first();
        // if(!isset($user)) {
        //     $data['status'] = false;
        //     $data['customers'] = null;
        //     return response()->json($data);
        // }

        $options = null;
        if(isset($request->companyCode))
            $options['companyCode'] = $request->companyCode;

        $customers = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();

        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if (($options != null) && (isset($options['companyCode'])) && ($options['companyCode'] != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);
            if($acSalesAgent) {
                $acDebtors = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                        ->where('salesAgent', $acSalesAgent->salesAgent)
                                        ->where('active', 1)->get();

                foreach ($acDebtors as $acDebtor) {
                    $customer['custId'] = $acDebtor->id;
                    $customer['companyCode'] = $companyCode;
                    $customer['accNo'] = $acDebtor->accNo;
                    $customer['name'] = $acDebtor->companyName;
                    $customer['addr1'] = $acDebtor->address1;
                    $customer['addr2'] = $acDebtor->address2;
                    $customer['addr3'] = $acDebtor->address3;
                    $customer['addr4'] = $acDebtor->address4;
                    $customer['attention'] = $acDebtor->attention;
                    $customer['defDisplayTerm'] = $acDebtor->displayTerm;
                    $customer['taxType'] = $acDebtor->taxType;
                    $customer['phone1'] = $acDebtor->phone1;
                    $customer['phone2'] = $acDebtor->phone2;
                    $customer['isActive'] = $acDebtor->active;
                    $customer['rev'] = $acDebtor->lastUpdate;
                    $customer['deleted'] = $acDebtor->deleted;
                    $customer['createdAt'] = $acDebtor->created_at;
                    $customer['updatedAt'] = $acDebtor->updated_at;

                    array_push($customers, $customer);
                }
            }


            $b1Salesperson = B1SalesPeople::find($salesPersonMapping->b1SalespersonId);
            if($b1Salesperson) {
                $b1Customers = B1Customer::where('companyId', $b1Salesperson->companyId)
                                        ->where('salesAgentCode', $b1Salesperson->salesAgentCode)->get();

                foreach ($b1Customers as $b1Customer) {
                    $customer['custId'] = $b1Customer->id;
                    $customer['companyCode'] = $companyCode;
                    $customer['accNo'] = $b1Customer->customerCode;
                    $customer['name'] = $b1Customer->customerName;

                    $shipAddress = B1CustomerAddress::where("companyId", $b1Customer->companyId)
                                                    ->where("customerCode", $b1Customer->customerCode)
                                                    ->where("addressType", "S")
                                                    ->first();

                    if(isset($shipAddress)) {
                        $customer['addr1'] = $shipAddress->street;
                        $customer['addr2'] = $shipAddress->streetNo;
                        $customer['addr3'] = $shipAddress->block;
                        $customer['addr4'] = $shipAddress->city;
                    }

                    $customer['attention'] = $b1Customer->phone1;
                    $customer['defDisplayTerm'] = $b1Customer->pymntGroup;
                    $customer['taxType'] = $b1Customer->taxType;
                    $customer['phone1'] = $b1Customer->phone1;
                    $customer['phone2'] = $b1Customer->phone2;
                    $customer['isActive'] = $b1Customer->active;
                    $customer['rev'] = $b1Customer->lastUpdate;
                    $customer['deleted'] = $b1Customer->deleted;
                    $customer['createdAt'] = $b1Customer->created_at;
                    $customer['updatedAt'] = $b1Customer->updated_at;

                    array_push($customers, $customer);
                }
            }
        }

        $data['result'] = true;
        $data['customers'] = $customers;
        return response()->json($data);
    }

    public function getItemsByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        $options = null;
        if(isset($request->companyCode))
            $options['companyCode'] = $request->companyCode;

        if(isset($request->lastSyncAt))
            $options['lastSyncAt'] = $request->lastSyncAt; //date format convert

        $items = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();

        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if (($options != null) && (isset($options['companyCode'])) && ($options['companyCode'] != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);

            if($acSalesAgent) {
                if (($options != null) && (isset($options['lastSyncAt'])))
                    $acItems = AcStockItem::where('customerId', $acSalesAgent->customerId)->where('active', 1)->where('deleted', 0)->where('lastSyncAt', '>', $options['lastSyncAt'])->get();
                else
                    $acItems = AcStockItem::where('customerId', $acSalesAgent->customerId)->where('active', 1)->where('deleted', 0)->get();

                foreach ($acItems as $acItem) {
                    if($acItem->id == 0)
                        continue;
                    $item['itemId'] = $acItem->id;
                    $item['companyCode'] = $companyCode;
                    $item['code'] = $acItem->itemCode;
                    $item['description'] = $acItem->description;
                    $item['taxType'] = $acItem->taxType;
                    $item['isActive'] = $acItem->active;
                    $item['rev'] = $acItem->lastUpdate;
                    $item['deleted'] = $acItem->deleted;
                    $item['createdAt'] = $acItem->created_at;
                    $item['updatedAt'] = $acItem->updated_at;

                    $acStockItemUoms = AcStockItemUom::where('itemId', $acItem->id)->where('active', 1)->where('deleted', 0)->get();
                    $uoms = [];
                    foreach ($acStockItemUoms as $acStockItemUom) {
                        $uom['uomId'] = $acStockItemUom->id;
                        $uom['itemId'] = $acItem->id;
                        $uom['uom'] = $acStockItemUom->uom;
                        $uom['price'] = $acStockItemUom->price;
                        $uom['minPrice'] = $acStockItemUom->minSalePrice;
                        $uom['maxPrice'] = $acStockItemUom->maxSalePrice;
                        $uom['isActive'] = $acStockItemUom->active;
                        $uom['rev'] = $acStockItemUom->lastUpdate;
                        $uom['deleted'] = $acStockItemUom->deleted;
                        $uom['createdAt'] = $acStockItemUom->created_at;
                        $uom['updatedAt'] = $acStockItemUom->updated_at;

                        array_push($uoms, $uom);
                    }
                    $item['uom'] = $uoms;

                    array_push($items, $item);
                }
            }
        }

        $data['result'] = true;
        $data['items'] = $items;
        return response()->json($data);
    }

    public function getTermsByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        $options = null;
        if(isset($request->companyCode))
            $options['companyCode'] = $request->companyCode;


        $terms = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();

        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if (($options != null) && (isset($options['companyCode'])) && ($options['companyCode'] != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);
            if($acSalesAgent) {
                $acTerms = AcCreditTerms::where('customerId', $acSalesAgent->customerId)->get();

                foreach ($acTerms as $acTerm) {
                    $term['termId'] = $acTerm->id;
                    $term['companyCode'] = $companyCode;
                    $term['displayTerm'] = $acTerm->displayTerm;
                    $term['terms'] = $acTerm->terms;
                    $term['isActive'] = $acTerm->active;
                    $term['rev'] = $acTerm->lastUpdate;
                    $term['deleted'] = $acTerm->deleted;
                    $term['createdAt'] = $acTerm->created_at;
                    $term['updatedAt'] = $acTerm->updated_at;

                    array_push($terms, $term);
                }
            }

            $b1Salesperson = B1SalesPeople::find($salesPersonMapping->b1SalespersonId);
            if($b1Salesperson) {
                $b1Terms = B1PaymentTerm::where('companyId', $b1Salesperson->companyId)->get();

                foreach ($b1Terms as $b1Term) {
                    $term['termId'] = $b1Term->id;
                    $term['companyCode'] = $companyCode;
                    $term['displayTerm'] = $b1Term->payTermName;
                    $term['terms'] = $b1Term->payTermCode;
                    $term['isActive'] = $b1Term->active;
                    $term['rev'] = $b1Term->lastUpdate;
                    $term['deleted'] = $b1Term->deleted;
                    $term['createdAt'] = $b1Term->created_at;
                    $term['updatedAt'] = $b1Term->updated_at;

                    array_push($terms, $term);
                }
            }
        }

        $data['result'] = true;
        $data['terms'] = $terms;
        return response()->json($data);
    }

    public function getSalesOrdersByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        $condCompanyCode = null;
        $condLastSyncAt = null;
        $condFromDate = null;

        if(isset($request->companyCode))
            $condCompanyCode = $request->companyCode;

        if(isset($request->fromTS))
            $condLastSyncAt = $request->fromTS;

        // $date = new DateTime();
        // $date->modify('-'.env('API_MOBSALES_HISTORY_DAYS_SALESORDER').' days');
        // $condFromDate = $date->format('Y-m-d h:m:s');

        $salesOrders = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();

        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if ($condCompanyCode != null && ($condCompanyCode != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);

            if($acSalesAgent) {
                $customers = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                        ->where('salesAgent', $acSalesAgent->salesAgent)
                                        ->where('active', 1)->pluck('accNo')->toArray();
                if (!isset($customers))
                    continue;

                if(($condFromDate == null) && ($condLastSyncAt == null)) {
                    $acSos = AcSalesOrder::where('customerId', $acSalesAgent->customerId)
                                                ->whereIn('debtorCode', $customers)
                                                ->get();
                } else {
                    if(($condFromDate != null) && ($condLastSyncAt != null)) {
                        $acSos = AcSalesOrder::where('customerId', $acSalesAgent->customerId)
                                                        ->whereIn('debtorCode', $customers)
                                                        ->where('docDate', '>', $condFromDate)
                                                        ->where('lastSyncAt', '>', $condLastSyncAt)
                                                        ->get();

                    } else {
                        if($condFromDate != null) {
                            $acSos = AcSalesOrder::where('customerId', $acSalesAgent->customerId)
                                                        ->whereIn('debtorCode', $customers)
                                                        ->where('docDate', '>', $condFromDate)
                                                        ->get();
                        }

                        if($condLastSyncAt != null) {
                            $acSos = AcSalesOrder::where('customerId', $acSalesAgent->customerId)
                                                        ->whereIn('debtorCode', $customers)
                                                        ->where('lastSyncAt', '>', $condLastSyncAt)
                                                        ->get();
                        }
                    }
                }

                foreach ($acSos as $acSo) {
                    $salesOrder = [];
                    $salesOrder['soId'] = $acSo->id;
                    $salesOrder['companyCode'] = $companyCode;
                    $salesOrder['custAccNo'] = $acSo->debtorCode;
                    $salesOrder['custName'] = $acSo->debtorName;
                    $salesOrder['docNo'] = $acSo->docNo;
                    $salesOrder['docDate'] = $acSo->docDate;
                    $salesOrder['invAddr1'] = $acSo->invAddr1;
                    $salesOrder['invAddr2'] = $acSo->invAddr2;
                    $salesOrder['invAddr3'] = $acSo->invAddr3;
                    $salesOrder['invAddr4'] = $acSo->invAddr4;
                    $salesOrder['branchCode'] = $acSo->branchcode;
                    $salesOrder['salesLocation'] = $acSo->salesLocation;
                    $salesOrder['shipVia'] = $acSo->shipVia;
                    $salesOrder['shipInfo'] = $acSo->shipInfo;
                    $salesOrder['attention'] = "";
                    $salesOrder['displayTerm'] = $acSo->displayTerm;
                    $salesOrder['salesAgent'] = $acSo->salesAgent;
                    $salesOrder['inclusiveTax'] = $acSo->inclusiveTax;
                    $salesOrder['subtotalAmt'] = $acSo->invAddr1;;
                    $salesOrder['taxAmt'] = '';
                    $salesOrder['totalAmt']='';
                    $salesOrder['remark1'] = $acSo->remark1;
                    $salesOrder['remark2'] = $acSo->remark2;
                    $salesOrder['remark3'] = $acSo->remark3;
                    $salesOrder['remark4'] = $acSo->remark4;
                    $salesOrder['cancelled'] = $acSo->cancelled;
                    $salesOrder['rev'] = $acSo->lastUpdate;
                    $salesOrder['deleted'] = $acSo->deleted;
                    $salesOrder['createdAt'] = $acSo->created_at;
                    $salesOrder['updatedAt'] = $acSo->updated_at;

                    $acSoItems = AcSalesOrderItem::where('soId', $acSo->id)->get();
                    $salesOrderItems = [];
                    foreach ($acSoItems as $acSoItem) {
                        $salesOrderItem = [];
                        $salesOrderItem['itemId'] = $acSoItem->id;
                        $salesOrderItem['code'] = $acSoItem->itemCode;
                        $salesOrderItem['description'] = $acSoItem->description;
                        $salesOrderItem['location'] = $acSoItem->location;
                        $salesOrderItem['remarks'] = $acSoItem->furtherDescription;
                        $salesOrderItem['uom'] = $acSoItem->uom;
                        $salesOrderItem['qty'] = $acSoItem->qty;
                        $salesOrderItem['transferredQty'] = $acSoItem->transferedQty;
                        $salesOrderItem['unitPrice'] = $acSoItem->unitPrice;
                        $salesOrderItem['taxType'] = $acSoItem->taxType;
                        $salesOrderItem['taxRate'] = $acSoItem->taxRate;
                        $salesOrderItem['deleted'] = $acSoItem->deleted;
                        $salesOrderItem['createdAt'] = $acSoItem->created_at;
                        $salesOrderItem['updatedAt'] = $acSoItem->updated_at;
                        array_push($salesOrderItems, $salesOrderItem);
                    }

                    $salesOrder['items'] = $salesOrderItems;
                    array_push($salesOrders, $salesOrder);
                }
            }
            /*
            $b1Salesperson = B1SalesPeople::find($salesPersonMapping->b1SalespersonId);
            if($b1Salesperson) {
                $customers = B1Customer::where('companyId', $b1Salesperson->companyId)
                                        ->where('salesAgentCode', $b1Salesperson->salesAgentCode)->pluck('customerCode')->toArray();;

                if (!isset($customers))
                    continue;

                foreach ($customers as $customer) {

                    $b1Sos = B1SalesOrder::where('companyId', $acSalesAgent->customerId)
                                                ->whereIn('soCustomerCode', $customers)
                                                ->get();
                    foreach ($b1Sos as $b1So) {
                        $salesOrder = [];
                        $salesOrder['soId'] = $b1So->id;
                        $salesOrder['companyCode'] = $companyCode;
                        $salesOrder['custAccNo'] = $b1So->soCustomerCode;
                        $salesOrder['custName'] = $b1So->soCustomerName;
                        $salesOrder['docNo'] = $b1So->soDocNum;
                        $salesOrder['docDate'] = $b1So->soDocDate;
                        $soShipAddress = $b1So->soShipAddress;
                        if ($soShipAddress != null) {
                            $shipAddressString = preg_split("\r", $soShipAddress);
                            for ($j = 0; $j < sizeof($shipAddressString); $j++) {
                                if ($j == 0)
                                    $salesOrder['invAddr1'] = $shipAddressString[0];
                                if ($j == 1)
                                    $salesOrder['invAddr2'] = $shipAddressString[1];
                                if ($j == 2)
                                    $salesOrder['invAddr3'] = $shipAddressString[2];
                                if ($j == 3)
                                    $salesOrder['invAddr4'] = $shipAddressString[3];
                            }
                        } else {
                            $salesOrder['invAddr1'] = "";
                            $salesOrder['invAddr2'] = "";
                            $salesOrder['invAddr3'] = "";
                            $salesOrder['invAddr4'] = "";
                        }

                        $salesOrder['attention'] = "";
                        $salesOrder['branchCode'] = "";
                        $salesOrder['salesLocation'] = "";
                        $salesOrder['shipVia'] = "";
                        $salesOrder['shipInfo'] = "";

                        $salesOrder['displayTerm'] = $b1So->soDisplayTerm;
                        $salesOrder['salesAgent'] = $b1So->soSalesPerson;
                        $salesOrder['inclusiveTax'] = 0;

                        $soComment = $b1So->soComment;
                        if ($soComment != null) {
                            $remarkString = preg_split("\r", $soComment);
                            for ($j = 0; $j < sizeof($remarkString); $j++) {
                            if ($j == 0)
                                $salesOrder['remark1'] = ($remarkString[0] == null) ? "" : $remarkString[0];
                            if ($j == 1)
                                $salesOrder['remark2'] = ($remarkString[1] == null) ? "" : $remarkString[1];
                            if ($j == 2)
                                $salesOrder['remark3'] = ($remarkString[2] == null) ? "" : $remarkString[2];
                            if ($j == 3)
                                $salesOrder['remark4'] = ($remarkString[3] == null) ? "" : $remarkString[3];
                            }
                        } else {
                            $salesOrder['remark1'] = "";
                            $salesOrder['remark2'] = "";
                            $salesOrder['remark3'] = "";
                            $salesOrder['remark4'] = "";
                        }

                        $salesOrder['cancelled'] = 0;
                        $salesOrder['rev'] = $b1So->lastUpdate;
                        $salesOrder['deleted'] = $b1So->deleted;
                        $salesOrder['createdAt'] = $b1So->created_at;
                        $salesOrder['updatedAt'] = $b1So->updated_at;

                        $b1SoItems = B1SalesOrderItem::where('soId', $b1So->id)->get();
                        $salesOrderItems = [];
                        foreach ($b1SoItems as $b1SoItem) {
                            $salesOrderItem = [];
                            $salesOrderItem['itemId'] = $b1SoItem->id;
                            $salesOrderItem['code'] = $b1SoItem->soItemCode;
                            $salesOrderItem['description'] = $b1SoItem->soItemDesc;
                            $salesOrderItem['uom'] = $b1SoItem->soItemUom;
                            $salesOrderItem['qty'] = $b1SoItem->soItemQuantity;
                            $salesOrderItem['unitPrice'] = $b1SoItem->soItemPrice;
                            $salesOrderItem['taxType'] = $b1SoItem->soTaxCode;
                            $salesOrderItem['taxRate'] = $b1SoItem->soTaxRate;
                            $salesOrderItem['deleted'] = $b1SoItem->deleted;
                            $salesOrderItem['createdAt'] = $b1SoItem->created_at;
                            $salesOrderItem['updatedAt'] = $b1SoItem->updated_at;
                            array_push($salesOrderItems, $salesOrderItem);
                        }

                        $salesOrder['items'] = $salesOrderItems;
                        array_push($salesOrders, $salesOrder);
                    }
                }
            }
            */
        }

        $data['result'] = true;
        $data['salesOrders'] = $salesOrders;
        return response()->json($data);

    }

    public function getTaxTypesByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        $condCompanyCode = null;
        if(isset($request->companyCode))
            $condCompanyCode = $request->companyCode;

        $taxTypes = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();

        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if ($condCompanyCode != null && ($condCompanyCode != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);
            if($acSalesAgent) {
                $acTaxTypes = AcTaxType::where('customerId', $acSalesAgent->customerId)->get();

                foreach ($acTaxTypes as $acTaxType) {
                    $taxType['typeId'] = $acTaxType->id;
                    $taxType['companyCode'] = $companyCode;
                    $taxType['code'] = $acTaxType->code;
                    $taxType['desc'] = $acTaxType->description;
                    $taxType['rate'] = $acTaxType->taxRate;
                    $taxType['supplyOrPurchase'] = $acTaxType->supplyPurchase;
                    $taxType['isDefault'] = $acTaxType->default;
                    $taxType['isInclusive'] = $acTaxType->inclusive;
                    $taxType['isZeroRate'] = $acTaxType->zeroRate;
                    $taxType['isActive'] = $acTaxType->active;
                    $taxType['rev'] = $acTaxType->lastUpdate;
                    $taxType['deleted'] = $acTaxType->deleted;
                    $taxType['createdAt'] = $acTaxType->created_at;
                    $taxType['updatedAt'] = $acTaxType->updated_at;

                    array_push($taxTypes, $taxType);
                }
            }

            $b1Salesperson = B1SalesPeople::find($salesPersonMapping->b1SalespersonId);
            if($b1Salesperson) {
                $b1TaxTypes = B1TaxType::where('companyId', $b1Salesperson->companyId)->get();

                foreach ($b1TaxTypes as $b1TaxType) {
                    $taxType['typeId'] = $b1TaxType->id;
                    $taxType['companyCode'] = $companyCode;
                    $taxType['code'] = $b1TaxType->code;
                    $taxType['desc'] = $b1TaxType->description;
                    $taxType['rate'] = $b1TaxType->taxRate;
                    $taxType['supplyOrPurchase'] = $b1TaxType->supplyPurchase;
                    $taxType['isDefault'] = $b1TaxType->default;
                    $taxType['isInclusive'] = $b1TaxType->inclusive;
                    $taxType['isZeroRate'] = $b1TaxType->zeroRate;
                    $taxType['isActive'] = $b1TaxType->active;
                    $taxType['rev'] = $b1TaxType->lastUpdate;
                    $taxType['deleted'] = $b1TaxType->deleted;
                    $taxType['createdAt'] = $b1TaxType->created_at;
                    $taxType['updatedAt'] = $b1TaxType->updated_at;

                    array_push($taxTypes, $taxType);
                }
            }
        }

        $data['result'] = true;
        $data['taxTypes'] = $taxTypes;
        return response()->json($data);
    }

    public function getInvoicesByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        $this->syncByAPI();

        $condCompanyCode = null;
        if(isset($request->companyCode))
            $condCompanyCode = $request->companyCode;

        $invoices = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();

        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if ($condCompanyCode != null && ($condCompanyCode != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);

            if($acSalesAgent) {
                $customers = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                        ->where('salesAgent', $acSalesAgent->salesAgent)
                                        ->where('active', 1)->pluck('accNo')->toArray();

                if (!isset($customers))
                    continue;

                foreach ($customers as $customer) {

                    $acArInvoices = AcArInvoice::where('customerId', $acSalesAgent->customerId)
                                                ->whereIn('debtorCode', $customers)
                                                ->get();

                    foreach ($acArInvoices as $acArInvoice) {
                        $invoice = [];
                        $invoice['companyCode'] = $companyCode;
                        $invoice['docType'] = $acArInvoice->knockoffType;
                        $invoice['docKey'] = $acArInvoice->docKey;
                        $invoice['docNo'] = $acArInvoice->docNo;
                        $invoice['docDate'] = $acArInvoice->docDate;
                        $invoice['outstandingAmount'] = $acArInvoice->outstanding;
                        $invoice['custAccNo'] = $acArInvoice->debtorCode;
                        $invoice['cancelled'] = $acArInvoice->cancelled;
                        $invoice['deleted'] = $acArInvoice->deleted;
                        $invoice['createdAt'] = $acArInvoice->created_at;
                        $invoice['updatedAt'] = $acArInvoice->updated_at;

                        array_push($invoices, $invoice);
                    }
                }
            }

            $b1Salesperson = B1SalesPeople::find($salesPersonMapping->b1SalespersonId);
            if($b1Salesperson) {

            }
        }

        $data['result'] = true;
        $data['invoices'] = $invoices;
        return response()->json($data);
    }


    public function getOutstandingARsByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }
        $condCompanyCode = null;
        if(isset($request->companyCode))
            $condCompanyCode = $request->companyCode;

        $condAccNo = null;
        if(isset($request->accNo))
            $condAccNo = $request->accNo;

        $outstandingARs = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();
        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;

            if ($condCompanyCode != null && ($condCompanyCode != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);
            if($acSalesAgent) {

                if($condAccNo != null) {
                    $customers = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                            ->where('salesAgent', $acSalesAgent->salesAgent)
                                            ->where('active', 1)
                                            ->where('accNo', $condAccNo)->pluck('accNo')->toArray();
                } else {
                    $customers = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                        ->where('salesAgent', $acSalesAgent->salesAgent)
                                        ->where('active', 1)->pluck('accNo')->toArray();
                }

                if (!isset($customers))
                    continue;

                foreach ($customers as $customer) {

                    $acArInvoices = AcArInvoice::where('customerId', $acSalesAgent->customerId)
                                                ->whereIn('debtorCode', $customers)
                                                ->get();

                    foreach ($acArInvoices as $acArInvoice) {

                        if ($acArInvoice->outstanding == 0)
                            continue;

                        $outstandingAR = [];
                        $outstandingAR['companyCode'] = $companyCode;
                        $outstandingAR['docType'] = $acArInvoice->knockoffType;
                        $outstandingAR['docKey'] = $acArInvoice->docKey;
                        $outstandingAR['docNo'] = $acArInvoice->docNo;
                        $outstandingAR['docDate'] = $acArInvoice->docDate;
                        $outstandingAR['outstandingAmount'] = $acArInvoice->outstanding;
                        $outstandingAR['custAccNo'] = $acArInvoice->debtorCode;
                        $outstandingAR['cancelled'] = $acArInvoice->cancelled;
                        $outstandingAR['deleted'] = $acArInvoice->deleted;
                        $outstandingAR['createdAt'] = $acArInvoice->created_at;
                        $outstandingAR['updatedAt'] = $acArInvoice->updated_at;

                        array_push($outstandingARs, $outstandingAR);
                    }

                    $acArDebitNotes = AcArDebitNote::where('customerId', $acSalesAgent->customerId)
                                                ->whereIn('debtorCode', $customers)
                                                ->get();

                    foreach ($acArDebitNotes as $acArDebitNote) {

                        if ($acArDebitNote->outstanding == 0)
                            continue;

                        $outstandingAR = [];
                        $outstandingAR['companyCode'] = $companyCode;
                        $outstandingAR['docType'] = $acArDebitNote->knockoffType;
                        $outstandingAR['docKey'] = $acArDebitNote->docKey;
                        $outstandingAR['docNo'] = $acArDebitNote->docNo;
                        $outstandingAR['docDate'] = $acArDebitNote->docDate;
                        $outstandingAR['outstandingAmount'] = $acArDebitNote->outstanding;
                        $outstandingAR['custAccNo'] = $acArDebitNote->debtorCode;
                        $outstandingAR['cancelled'] = $acArDebitNote->cancelled;
                        $outstandingAR['deleted'] = $acArDebitNote->deleted;
                        $outstandingAR['createdAt'] = $acArDebitNote->created_at;
                        $outstandingAR['updatedAt'] = $acArDebitNote->updated_at;

                        array_push($outstandingARs, $outstandingAR);
                    }
                }
            }

            $b1Salesperson = B1SalesPeople::find($salesPersonMapping->b1SalespersonId);
            if($b1Salesperson) {

            }
        }

        $data['result'] = true;
        $data['outstandingARs'] = $outstandingARs;
        return response()->json($data);
    }

    public function getTemporaryReceiptsByUser(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        $condCompanyCode = null;
        if(isset($request->companyCode))
            $condCompanyCode = $request->companyCode;

        $condAccNo = null;
        if(isset($request->accNo))
            $condAccNo = $request->accNo;

        Log::info("here1 - " . $condCompanyCode);
        $tempReceipts = [];
        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $salesPersonMappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();
        Log::info("here1 sub - " . count($salesPersonMappings));
        foreach($salesPersonMappings as $salesPersonMapping) {
            $companyCode = $salesPersonMapping->companyInfo->code;
            Log::info("here2 - " . $companyCode);

            if ($condCompanyCode != null && ($condCompanyCode != $companyCode))
                continue;

            $acSalesAgent = AcSalesAgent::find($salesPersonMapping->acSalesAgentId);

            if($acSalesAgent) {
                if($condAccNo != null) {
                    $customers = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                            ->where('salesAgent', $acSalesAgent->salesAgent)
                                            ->where('active', 1)
                                            ->where('accNo', $condAccNo)->pluck('accNo')->toArray();
                } else {
                    $customers = AcDebtor::where('customerId', $acSalesAgent->customerId)
                                        ->where('salesAgent', $acSalesAgent->salesAgent)
                                        ->where('active', 1)->pluck('accNo')->toArray();
                }

                Log::info("here3");
                if (!isset($customers))
                    continue;

                Log::info("here4 " . count($customers));
                foreach ($customers as $customer) {

                    $acPdcHeaders = AcPdcHeader::where('customerId', $acSalesAgent->customerId)
                                                ->whereIn('debtorCode', $customers)
                                                ->get();
                    Log::info("here5 " . count($acPdcHeaders));
                    foreach ($acPdcHeaders as $acPdcHeader) {
                        $tempReceipt = [];
                        $tempReceipt['trId'] = $acPdcHeader->id;
                        $tempReceipt['companyCode'] = $companyCode;
                        $tempReceipt['trNo'] = $acPdcHeader->docNo;
                        $tempReceipt['custAccNo'] = $acPdcHeader->debtorCode;
                        $tempReceipt['cancelled'] = $acPdcHeader->cancelled;
                        $tempReceipt['deleted'] = $acPdcHeader->deleted;
                        $tempReceipt['salesAgent'] = $acPdcHeader->salesAgent;
                        $tempReceipt['createdAt'] = $acPdcHeader->created_at;
                        $tempReceipt['updatedAt'] = $acPdcHeader->updated_at;

                        $acPdcDtl = AcPdcDtl::where('pdcId', $acPdcHeader->id)->first();

                        if(isset($acPdcDtl)) {
                            $tempReceipt['chequeNo'] = $acPdcDtl->chequeNo;
                            $tempReceipt['paymentMethod'] = $acPdcDtl->paymentMethod;
                            $tempReceipt['paymentAmount'] = $acPdcDtl->paymentAmount;
                        }

                        $trKnockoffDetails = [];
                        $acPdcKnockoffDtls = AcPdcKnockoffDtl::where('pdcId', $acPdcHeader->id)->get();
                        Log::info("here6 " . count($acPdcKnockoffDtls));
                        foreach ($acPdcKnockoffDtls as $acPdcKnockoffDtl) {
                            $trKnockoffDetail = [];
                            if($acPdcKnockoffDtl->docType == 'RI') {
                                $invoice = AcArInvoice::where('customerId', $acSalesAgent->customerId)
                                                        ->where('docKey', $acPdcKnockoffDtl->docKey)->first();
                                if (!isset($invoice))
                                    continue;
                                $trKnockoffDetail['docDate'] = $invoice->docDate;
                                $trKnockoffDetail['docNo'] = $invoice->docNo;
                            } else {
                                $debitNote = AcArDebitNote::where('customerId', $acSalesAgent->customerId)
                                                            ->where('docKey', $acPdcKnockoffDtl->docKey)->first();
                                if (!isset($debitNote))
                                    continue;
                                $trKnockoffDetail['docDate'] = $debitNote->docDate;
                                $trKnockoffDetail['docNo'] = $debitNote->docNo;
                            }
                            $trKnockoffDetail['id'] = $acPdcKnockoffDtl->id;
                            $trKnockoffDetail['docType'] = $acPdcKnockoffDtl->docType;
                            $trKnockoffDetail['docKey'] = $acPdcKnockoffDtl->docKey;
                            $trKnockoffDetail['paidAmount'] = $acPdcKnockoffDtl->paidAmount;
                            $trKnockoffDetail['deleted'] = $acPdcKnockoffDtl->deleted;
                            $trKnockoffDetail['createdAt'] = $acPdcKnockoffDtl->created_at;
                            $trKnockoffDetail['updatedAt'] = $acPdcKnockoffDtl->updated_at;

                            array_push($trKnockoffDetails, $trKnockoffDetail);
                        }
                        $item['knockoffDetails'] = $trKnockoffDetails;

                        array_push($tempReceipts, $tempReceipt);
                    }
                }
            }
        }

        $data['result'] = true;
        $data['tempReceipts'] = $tempReceipts;
        return response()->json($data);
    }

    public function createSalesOrder(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        if(!isset($request->companyCode))
            return response()->json(['result' => false, 'error' => 'No company. Please input company code.'], 200);

        $company = MsCompany::where('code', $request->companyCode)->first();

        $sysType = '';
        if($company) {
            $sysType = $company->sysType;
        } else {
            return response()->json(['result' => false, 'error' => 'Please select a correct company code.'], 200);
        }

        if ($sysType == 'ATCNT') {
            $dataSyncApi = DataSyncApi::where('reference_key', $request->companyCode)->first();
            $acApi = new AutoCountApi($dataSyncApi->login_name, $dataSyncApi->login_pass_enc);//'keluargaCTH@123'
            $token = $acApi->getToken();

            if(!$token)
                return response()->json(['result' => false, 'error' => 'AutoCountAPI error'], 200);

            $response = $this->createAutoCountSaleOrder($user, $request->getContent(), $acApi);
        } else {
            // $acApi = new AutoCountApi('keluargacth@gmail.com', 'keluargaCTH@123');
            // $token = $acApi->getToken();

            // if(!$token)
            //     return response()->json(['error' => 'AutoCountAPI error'], 200);

            // $response = $this->createSAPSaleOrder($request, $token);
        }

        if(isset($response)) {
            if($response['status']) {
                $so_id  = $response['result']->data->id;
                $so_data = $acApi->readSalesOrder($so_id);

                $result = createOrupdateSalesOrderForAC($so_data[0], null);

                if($result)
                    $message = "Create new Sales Order.";
                else
                    $message = "Update a Sales Order.";

                $data['result'] = true;
                $data['message'] = $message;
                return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
            } else {
                $error = json_encode($response['result']);
                return response()->json(['result' => false, 'error' => $error], 200);
            }
        } else {
            return response()->json(['result' => false, 'error' => 'Failed creating new Sales Order. Unknown Error!'], 200);
        }

    }

    public function createTemporaryReceipt(Request $request)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
            }
        } catch (Exception $e) {
            return response()->json(['result' => false, 'error' => 'Unauthorized'], 200);
        }

        if(!isset($request->companyCode))
            return response()->json(['result' => false, 'error' => 'No company. Please input company code.'], 200);

        $company = MsCompany::where('code', $request->companyCode)->first();

        $sysType = '';
        if($company) {
            $sysType = $company->sysType;
        } else {
            return response()->json(['result' => false, 'error' => 'Please select a correct company code.'], 200);
        }

        if ($sysType == 'ATCNT') {
            $dataSyncApi = DataSyncApi::where('reference_key', $request->companyCode)->first();
            $acApi = new AutoCountApi($dataSyncApi->login_name, $dataSyncApi->login_pass_enc);//'keluargaCTH@123'
            $token = $acApi->getToken();

            if(!$token)
                return response()->json(['result' => false, 'error' => 'AutoCountAPI error'], 200);

            $response = $this->createAutoCountTemporaryReceipt($user, $request->getContent(), $acApi);
        } else {
            // $response = $this->createSAPSaleOrder($request, $token);
        }

        if($response) {
            if($response['status']) {
                $tr_id  = $response['result']->data->id;
                $tr_data = $acApi->readTemporaryReceiptById($tr_id);

                $result = createOrupdateTemporaryReceiptForAC($tr_data[0], null);

                if($result)
                    $message = "Create new Temporary Receipt.";
                else
                    $message = "Update a Temporary Receipt.";

                $data['result'] = true;
                $data['message'] = $message;
                return response()->json($data, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
            } else {
                $error = json_encode($response['result']);
                return response()->json(['result' => false, 'error' => $error], 200);
            }
        } else {
            return response()->json(['result' => false, 'error' => 'Failed creating new Temporary Receipt'], 200);
        }
    }

    private function createAutoCountSaleOrder($user, $soRequest, $acApi)
    {
        $salesOrderRequest = $this->validateSalesOrder($user, $soRequest);
        if(!$salesOrderRequest)
            return false;

        $response = $acApi->createSalesOrder($salesOrderRequest);

        return $response;
    }

    private function createSAPSaleOrder($soRequest, $user)
    {

    }

    private function createAutoCountTemporaryReceipt($user, $soRequest, $acApi)
    {
        $temporaryReceiptRequest = $this->validateTemporaryReceipt($user, $soRequest);
        if(!$temporaryReceiptRequest)
            return false;

        $response = $acApi->createTemporaryReceipt($temporaryReceiptRequest);

        return $response;
    }

    private function validateSalesOrder($user, $soRequest) {
        return $soRequest;
        if (!in_array('4', $user->role) && !in_array('5', $user->role))
            return false;

        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $sales_agent_mappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();
        $acAgent = null;
        foreach($sales_agent_mappings as $sales_agent_mapping) {
            if(($sales_agent_mapping->companyInfo->code == $soRequest->companyCode) && ($sales_agent_mapping->acSalesAgentInfo != null)) {
                $acAgent = $sales_agent_mapping->acSalesAgentInfo;
            }
        }

        if($acAgent == null)
            return false;

        $salesOrderRequest = null;
        $salesOrderRequest['docNo'] = $soRequest->docNo;
        $salesOrderRequest['docDate'] = $soRequest->docDate;
        $salesOrderRequest['debtorCode'] = $soRequest->custAccNo;
        $salesOrderRequest['debtorName'] = $soRequest->custName;
        $salesOrderRequest['invAddr1'] = $soRequest->invAddr1;
        $salesOrderRequest['invAddr2'] = $soRequest->invAddr2;
        $salesOrderRequest['invAddr3'] = $soRequest->invAddr3;
        $salesOrderRequest['invAddr4'] = $soRequest->invAddr4;
        $salesOrderRequest['branchCode'] = "";
        $salesOrderRequest['salesLocation'] = "";
        $salesOrderRequest['displayTerm'] = $soRequest->displayTerm;
        $salesOrderRequest['salesAgentCode'] = $acAgent->salesAgent;
        $salesOrderRequest['shipVia'] = "";
        $salesOrderRequest['shipInfo'] = "";
        $salesOrderRequest['remark1'] = $soRequest->remark1;
        $salesOrderRequest['remark2'] = $soRequest->remark2;
        $salesOrderRequest['remark3'] = $soRequest->remark3;
        $salesOrderRequest['remark4'] = $soRequest->remark4;
        $salesOrderRequest['inclusiveTax'] = $soRequest->inclusiveTax;
        $reqItems = [];
        if(isset($soRequest->items)) {
            foreach ($soRequest->items as $soReqItemVM) {
                $salesOrderReqItem = [];
                $salesOrderReqItem['code'] = $soReqItemVM['code'];
                $salesOrderReqItem['location'] = "";
                $salesOrderReqItem['description'] = $soReqItemVM['description'];
                $salesOrderReqItem['furtherDescription'] = "";
                $salesOrderReqItem['uom'] = $soReqItemVM['uom'];
                $salesOrderReqItem['qty'] = $soReqItemVM['qty'];
                $salesOrderReqItem['focQty'] = "0";
                $salesOrderReqItem['smallestUnitPrice'] = "0";
                $salesOrderReqItem['unitPrice'] = $soReqItemVM['unitPrice'];
                $salesOrderReqItem['discount'] = "";
                $salesOrderReqItem['discountAmt'] = "0";
                $salesOrderReqItem['taxType'] = $soReqItemVM['taxType'];
                $salesOrderReqItem['taxRate'] = $soReqItemVM['taxRate'];
                $salesOrderReqItem['localId'] = $soReqItemVM['localId'];
                $salesOrderReqItem['rate'] = AcStockItemUom::find($soReqItemVM['uomId'])->rate;
                array_push($reqItems, $salesOrderReqItem);
            }
        }
        $salesOrderRequest['items'] = $reqItems;
        return $salesOrderRequest;
    }

    private function validateTemporaryReceipt($user, $soRequest) {
        return $soRequest;
        if (!in_array('4', $user->role) && !in_array('5', $user->role))
            return false;

        $salespersonId = MsSalesPeople::where('userId', $user->id)->first()->id;
        $sales_agent_mappings = MsSalesPersonMapping::where('salespersonId', $salespersonId)->get();
        $acAgent = null;
        foreach($sales_agent_mappings as $sales_agent_mapping) {
            if(($sales_agent_mapping->companyInfo->code == $soRequest->companyCode) && ($sales_agent_mapping->acSalesAgentInfo != null)) {
                $acAgent = $sales_agent_mapping->acSalesAgentInfo;
            }
        }

        if($acAgent == null)
            return false;

        $salesOrderRequest = null;
        $salesOrderRequest['docNo'] = $soRequest->docNo;
        $salesOrderRequest['docDate'] = $soRequest->docDate;
        $salesOrderRequest['debtorCode'] = $soRequest->custAccNo;
        $salesOrderRequest['debtorName'] = $soRequest->custName;
        $salesOrderRequest['invAddr1'] = $soRequest->invAddr1;
        $salesOrderRequest['invAddr2'] = $soRequest->invAddr2;
        $salesOrderRequest['invAddr3'] = $soRequest->invAddr3;
        $salesOrderRequest['invAddr4'] = $soRequest->invAddr4;
        $salesOrderRequest['branchCode'] = "";
        $salesOrderRequest['salesLocation'] = "";
        $salesOrderRequest['displayTerm'] = $soRequest->displayTerm;
        $salesOrderRequest['salesAgentCode'] = $acAgent->salesAgent;
        $salesOrderRequest['shipVia'] = "";
        $salesOrderRequest['shipInfo'] = "";
        $salesOrderRequest['remark1'] = $soRequest->remark1;
        $salesOrderRequest['remark2'] = $soRequest->remark2;
        $salesOrderRequest['remark3'] = $soRequest->remark3;
        $salesOrderRequest['remark4'] = $soRequest->remark4;
        $salesOrderRequest['inclusiveTax'] = $soRequest->inclusiveTax;
        $reqItems = [];
        if(isset($soRequest->items)) {
            foreach ($soRequest->items as $soReqItemVM) {
                $salesOrderReqItem = [];
                $salesOrderReqItem['code'] = $soReqItemVM['code'];
                $salesOrderReqItem['location'] = "";
                $salesOrderReqItem['description'] = $soReqItemVM['description'];
                $salesOrderReqItem['furtherDescription'] = "";
                $salesOrderReqItem['uom'] = $soReqItemVM['uom'];
                $salesOrderReqItem['qty'] = $soReqItemVM['qty'];
                $salesOrderReqItem['focQty'] = "0";
                $salesOrderReqItem['smallestUnitPrice'] = "0";
                $salesOrderReqItem['unitPrice'] = $soReqItemVM['unitPrice'];
                $salesOrderReqItem['discount'] = "";
                $salesOrderReqItem['discountAmt'] = "0";
                $salesOrderReqItem['taxType'] = $soReqItemVM['taxType'];
                $salesOrderReqItem['taxRate'] = $soReqItemVM['taxRate'];
                $salesOrderReqItem['localId'] = $soReqItemVM['localId'];
                $salesOrderReqItem['rate'] = AcStockItemUom::find($soReqItemVM['uomId'])->rate;
                array_push($reqItems, $salesOrderReqItem);
            }
        }
        $salesOrderRequest['items'] = $reqItems;
        return $salesOrderRequest;
    }

    function generatePassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function syncByAPI() {
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

            // $data = $this->syncSalesAgent($autoCountApi, $id);
            // $data = $this->syncTaxType($autoCountApi, $id);
            // $data = $this->syncTerm($autoCountApi, $id);
            // $data = $this->syncStockItem($autoCountApi, $id);
            // $data = $this->syncDebtor($autoCountApi, $id);
            // $data = $this->syncSalesOrder($autoCountApi, $id);
            $data = $this->syncInvoice($autoCountApi, $id);
            // $data = $this->syncDebitNote($autoCountApi, $id);
            // $data = $this->syncSalesInvoice($autoCountApi, $id);
            // $data = $this->syncSalesCreditNote($autoCountApi, $id);
            // $data = $this->syncSalesDebitNote($autoCountApi, $id);
        }
    }

    public function syncInvoice($api, $id)
    {
        Log::info("Start syncInvoice - " . $id);
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
        $data['message'] = "Created ".$newRecords. ' records and Updated '.$updateRecords.' records for ARInvoice';

        Log::info("End syncInvoice - " . $id);
        return $data;
    }
}
