<?php

namespace App\ExternalApi;
use Illuminate\Support\Facades;
use Twilio\Http\GuzzleClient;

class AutoCountApi {

    private $token;

    public function __construct($email, $password) {
        $this->baseUrl = 'https://aflexapi.com/api/';
        $this->email = $email;
        $this->password = $password;
    }

    private function sendRequest($url, $requestType, $params, $body='')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl.$url.$params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestType,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($http_status == 200) {
            $data = json_decode($response);
            $result['data'] = $data;
            $result['status'] = true;
        } else {
            $result['status'] = false;
            $result['message'] = json_decode($response);
        }
        curl_close($curl);

        return $result;
    }

    public function getToken()
    {
        $params = '?email='.$this->email.'&password='.$this->password;

        $response = $this->sendRequest('login', 'POST', $params);

        if($response['status']) {
            $token = $response['data']->api_token;
            $this->token = $token;
            $result = $token;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllSalesAgentID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllSalesAgentID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesAgent($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readSalesAgentByID/'.$id;
        else
            $url = 'v1_5_4_1/readSalesAgent';

        $response = $this->sendRequest($url, 'GET', $params);


        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestSalesAgent($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestSalesAgent/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllTaxTypeID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllTaxTypeID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readTaxType($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readTaxTypeByID/'.$id;
        else
            $url = 'v1_5_4_1/readTaxType';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestTaxType($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestTaxType/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    /** Term */
    public function readAllTermID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllTermsID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readTerm($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readTermsByID/'.$id;
        else
            $url = 'v1_5_4_1/readTerms';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestTerm($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestTerms/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readTermByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readTermByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** Stock Item */
    public function readAllStockItemID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllStockItemID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readStockItem($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readStockItemByID/'.$id;
        else
            $url = 'v1_5_4_1/readStockItem';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestStockItem($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestStockItem/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readStockItemByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readStockItemByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** Debtor */
    public function readAllDebtorID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllDebtorID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readDebtor($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readDebtorByID/'.$id;
        else
            $url = 'v1_5_4_1/readDebtor';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestDebtor($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestDebtor/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readDebtorByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readDebtorByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** SalesOrder */
    public function readAllSalesOrderID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllSalesOrderID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesOrder($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readSalesOrderByID/'.$id;
        else
            $url = 'v1_5_4_1/readSalesOrder';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesOrderByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readSalesOrderByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestSalesOrder($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestSalesOrder/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function createSalesOrder($request)
    {
        $params = "?api_token=".$this->token;
        $body = $request;

        $response = $this->sendRequest('v1_5_4_1/createSalesOrder', 'POST', $params, $body);

        if($response['status']) {
            $result['status'] = true;
            $result['result'] = $response['data'];
        } else {
            $result['status'] = false;
            $result['result'] = $response['message'];
        }

        return $result;
    }


    /** AR Invoice APIs */

    public function readAllARInvoiceID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllARInvoiceID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readARInvoice($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readARInvoiceByID/'.$id;
        else
            $url = 'v1_5_4_1/readARInvoice';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestARInvoice($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestARInvoice/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllARDebitNoteID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllARDebitNoteID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readARInvoiceByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readARInvoiceByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** AR DebitNote APIs */

    public function readARDebitNote($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readARDebitNote/'.$id;
        else
            $url = 'v1_5_4_1/readARDebitNote';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestARDebitNote($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestARDebitNote/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readARDebitNoteByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readARDebitNoteByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** Sales Invoice APIs */
    public function readAllSalesInvoiceID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllInvoiceID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesInvoice($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readInvoiceByID/'.$id;
        else
            $url = 'v1_5_4_1/readInvoice';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestSalesInvoice($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestInvoice/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesInvoiceByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readSalesInvoiceByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** Sales CreditNote */

    public function readAllSalesCreditNoteID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllCreditNoteID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesCreditNote($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readCreditNoteByID/'.$id;
        else
            $url = 'v1_5_4_1/readCreditNote';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestSalesCreditNote($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestCreditNote/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesCreditNoteByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readCreditNoteByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** Sales DebitNote */

    public function readAllSalesDebitNoteID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllDebitNoteID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesDebitNote($id)
    {
        $params = "?api_token=".$this->token;
        if($id)
            $url = 'v1_5_4_1/readDebitNoteByID/'.$id;
        else
            $url = 'v1_5_4_1/readDebitNote';

        $response = $this->sendRequest($url, 'GET', $params);
        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readAllLatestSalesDebitNote($latestDate)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllLatestDebitNote/'.str_replace(' ', '%20', $latestDate), 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function readSalesDebitNoteByGroup($min, $max)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readDebitNoteByGroup/'.$min.'/'.$max, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    /** Temporary Receipt */

    public function readAllTemporaryReceiptID()
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readAllFlexPDCID', 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }


    public function readTemporaryReceiptById($id)
    {
        $params = "?api_token=".$this->token;
        $response = $this->sendRequest('v1_5_4_1/readFlexPDCByID/'.$id, 'GET', $params);

        if($response['status']) {
            if(isset($response['data']->data))
                $result = $response['data']->data;
            else
                $result = false;
        } else {
            $result = false;
        }

        return $result;
    }

    public function createTemporaryReceipt($request)
    {
        $params = "?api_token=".$this->token;
        $body = $request;

        $response = $this->sendRequest('v1_5_4_1/createFlexPDC', 'POST', $params, $body);

        if($response['status']) {
            $result['status'] = true;
            $result['result'] = $response['data'];
        } else {
            $result['status'] = false;
            $result['result'] = $response['message'];
        }

        return $result;
    }

}
