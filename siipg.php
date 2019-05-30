<?php

class SIIPG {

    public $terminal  = NULL;
    public $username  = NULL;
    public $password  = NULL;
    public $amount    = NULL;
    public $callback  = NULL;
    public $order     = NULL;
    public $merchantid     = NULL;
    public $payerid   = 0;
    public $additionalData = '';
    public $mellat_wsdl_url  = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
    public $mellat_namespace = 'http://interfaces.core.sw.bps.com/';
    public $zarinpal_wsdl_url  = 'https://www.zarinpal.com/pg/services/WebGate/wsdl';
    public $Authority = NULL;


    public function set_config($data){
        if(isset($data['terminal'])){$this->terminal    =   $data['terminal'];}
        if(isset($data['username'])){$this->username    =   $data['username'];}
        if(isset($data['password'])){$this->password    =   $data['password'];}
        if(isset($data['amount'])){$this->amount        =   $data['amount'];}
        if(isset($data['order'])){$this->order          =   $data['order'];}
        if(isset($data['callback'])){$this->callback    =   $data['callback'];}
        if(isset($data['merchantid'])){$this->merchantid    =   $data['merchantid'];}
        if(isset($data['additionalData'])){$this->additionalData    =   $data['additionalData'];}
        if(isset($data['authority'])){$this->authority    =   $data['authority'];}
    }

}


class Mellat extends SIIPG {

    public function start(){
        $client             = new nusoap_client($this->mellat_wsdl_url,'wsdl');
        $localDate          = date('Ymd');
        $localTime          = date('His');
        $parameters = array(
            'terminalId'        =>      $this->terminal,
            'userName'          =>      $this->username,
            'userPassword'      =>      $this->password,
            'orderId'           =>      $this->order,
            'amount'            =>      $this->amount,
            'localDate'         =>      $localDate,
            'localTime'         =>      $localTime,
            'additionalData'    =>      $this->additionalData,
            'callBackUrl'       =>      $this->callback,
            'payerId'           =>      $this->payerid
        );

        $result = $client->call('bpPayRequest', $parameters, $this->mellat_namespace);
        if ($client->fault) {
            return false;
        }else{
            $res     = explode(',',$result['return']);
            $ResCode = $res[0];
            if ($ResCode == "0"){
                $url     =  'https://bpm.shaparak.ir/pgwchannel/payment.mellat?RefId=' . $res[1];
                $arr['status']   = 200;
                $arr['token']    = $res[1];
                $arr['url']      = $url;
                $arr['redirect'] = "<meta http-equiv='Refresh' content='0; url=$url'>";
                $arr['msg']      = 'OK';
                return $arr;
            }else{
                $arr['status']   = $result['return'];
                $arr['token']    = NULL;
                $arr['url']      = NULL;
                $arr['redirect'] = NULL;
                $arr['msg'] = $this->mellat_return_code($result['return']);
                return $arr;
            }
        }

    }

    protected function verify($post){
        if($post["ResCode"] == 0 ){
            $client             = new nusoap_client($this->mellat_wsdl_url,'wsdl');
            $SaleOrderId        = $post["SaleOrderId"];
            $SaleReferenceId    = $post["SaleReferenceId"];
            $RefId              = $post['RefId'];

            $parameters = array(
                'terminalId'            => $this->terminal,
                'userName'              => $this->username,
                'userPassword'          => $this->password,
                'orderId'               => $this->order,
                'saleOrderId'           => $SaleOrderId,
                'saleReferenceId'       => $SaleReferenceId
            );
            $result = $client->call('bpVerifyRequest', $parameters, $this->mellat_namespace);
            if ($client->fault){
                return false;
            }else{
                if($result['return'] == '0') {
                    return true;
                }
            }
        }else{
            return false;
        }
    }
    protected function settle($post){

        $client                     = new nusoap_client($this->mellat_wsdl_url,'wsdl');
        $SaleOrderId                = $post["SaleOrderId"];
        $SaleReferenceId            = $post["SaleReferenceId"];
        $RefId                      = $post['RefId'];

        $parameters = array(
            'terminalId'            => $this->terminal,
            'userName'              => $this->username,
            'userPassword'          => $this->password,
            'orderId'               => $this->order,
            'saleOrderId'           => $SaleOrderId,
            'saleReferenceId'       => $SaleReferenceId
        );
        $result = $client->call('bpSettleRequest', $parameters, $this->mellat_namespace);
        if ($client->fault){
            return false;
        }else{
            $err = $client->getError();
            if ($err) {
                return false;
            }else{
                if($result['return'] == '0') {
                    return true;
                }
            }
        }

        return false;
    }

    public function payment_check($post){
        $final['refid']   = $post['RefId'];
        $final['status']  = $post['ResCode'];
        $final['orderid'] = $post['SaleOrderId'];
        $final['msg']     = $this->mellat_return_code($post['ResCode']);

        if( $this->verify($post) == true ) {
            if( $this->settle($post) == true ) {
                $final['cardinfo']     = $post['CardHolderInfo'];
                $final['cardnumber']   = $post['CardHolderPan'];
                $final['amount']       = $post['FinalAmount'];
            }
        }

        return $final;
    }


    protected function mellat_return_code($return){
        switch ($return) {
        case "0":
            return "Transaction successfully completed";
            break;
        case "21":
            return "Invalid receiver";
            break;
        case "61":
            return "Error in deposit";
            break;
        case "55":
            return "Invalid transaction";
            break;
        case "54":
            return "Reference transaction is not available";
            break;
        case "51":
            return "Transaction is duplicate";
            break;
        case "421":
            return "Invalid IP";
            break;
        case "419":
            return "The number of times the data entered is past the limit";
            break;
        case "418":
            return "Problems in defining customer information";
            break;
        case "417":
            return "Invalid payee ID";
            break;
        case "416":
            return "Error in recording information";
            break;
        case "415":
            return "The session is over";
            break;
        case "414":
            return "Invoice Exporter is invalid";
            break;
        case "413":
            return "Payment ID is incorrect";
            break;
        case "412":
            return "Billing ID is incorrect";
            break;
        case "49":
            return "Transaction Refund not found";
            break;
        case "48":
            return "Transaction Reverse";
            break;
        case "47":
            return "Settle transaction could not be found";
            break;
        case "46":
            return "The transaction has not been settled";
            break;
        case "45":
            return "Transaction Settle";
            break;
        case "44":
            return "Verify request could not be found";
            break;
        case "43":
            return "An approval request has already been submitted";
            break;
        case "42":
            return "Sale transaction not found";
            break;
        case "41":
            return "The request number is a duplicate";
            break;
        case "35":
            return "Invalid date";
            break;
        case "34":
            return "System error";
            break;
        case "33":
            return "Invalid account";
            break;
        case "32":
            return "The format of the information entered is not correct";
            break;
        case "31":
            return "The answer is invalid";
            break;
        case "25":
            return "Amount is invalid";
            break;
        case "24":
            return "Invalid user information is invalid";
            break;
        case "23":
            return "Security error occurred";
            break;
        case "114":
            return "The cardholder is not allowed to do this transaction";
            break;
        case "113":
            return "No response from card issuer";
            break;
        case "112":
            return "Error switching card issuer";
            break;
        case "111":
            return "Card Exporter is invalid";
            break;
        case "19":
            return "The excess withdrawal amount is allowed";
            break;
        case "18":
            return "The expiration date of the card is past";
            break;
        case "17":
            return "The user has dissipated from the transaction";
            break;
        case "16":
            return "Times exceeded";
            break;
        case "15":
            return "Invalid card";
            break;
        case "14":
            return "The number of times an encrypted password is exceeded";
            break;
        case "13":
            return "The password is incorrect";
            break;
        case "12":
            return "Inventory is not enough";
            break;
        case "11":
            return "Invalid card number";
            break;
        default:
            return "Unknown error";
        }

    }
}



class Zarinpal extends SIIPG {
    public function start(){
        $client = new nusoap_client($this->zarinpal_wsdl_url, 'wsdl');
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call('PaymentRequest', [
            [
            'MerchantID'     => $this->merchantid,
            'Amount'         => $this->amount,
            'Description'    => $this->additionalData,
            'Email'          => '',
            'Mobile'         => '',
            'CallbackURL'    => $this->callback,
            ],
            ]);

        if ($result['Status'] == 100) {
            $url     =  'https://www.zarinpal.com/pg/StartPay/'.$result['Authority'];
            $arr['status']   = 200;
            $arr['token']    = $result['Authority'];
            $arr['url']      = $url;
            $arr['redirect'] = "<meta http-equiv='Refresh' content='0; url=$url'>";
            $arr['msg']      = 'OK';
            return $arr;
        } else {
            $arr['status']   = $result['Status'];
            $arr['token']    = NULL;
            $arr['url']      = NULL;
            $arr['redirect'] = NULL;
            $arr['msg'] = $this->zarinpal_return_code($result['Status']);
            return $arr;
        }
    }

    public function payment_check($post){

        if ($post['Status'] == 'OK') {
            $client = new nusoap_client($this->zarinpal_wsdl_url, 'wsdl');
            $client->soap_defencoding = 'UTF-8';
            $result = $client->call('PaymentVerification', [
                [
                'MerchantID'     => $this->merchantid,
                'Authority'      => $post['Authority'],
                'Amount'         => $this->amount,
                ],
                ]);
            if ($result['Status'] == 100) {
                $final['refid']        =$result['RefID'];
                $final['authority']   = $post['Authority'];
                $final['status']     = $result['Status'];
                $final['msg']     = $result['Status'];
            } else {
                $final['refid']        = null;
                $final['authority']   = $post['Authority'];
                $final['status']     = $result['Status'];
                $final['msg']     = $this->zarinpal_return_code($result['Status']);
            }
        } else {
            $final['refid']        = null;
            $final['authority']   = $post['Authority'];
            $final['status']     = 200;
            $final['msg']     = $this->zarinpal_return_code(200);
        }



        return $final;
    }

    protected function zarinpal_return_code($return){
        switch ($return) {
        case "-1":
            return "Information submitted is incomplete.";
            break;
        case "-2":
            return "Merchant ID or Acceptor IP is not correct.";
            break;
        case "-3":
            return "Amount should be above 100 Toman.";
            break;
        case "-4":
            return "Approved level of Acceptor is Lower than the.";
            break;
        case "-11":
            return "Request Not found.";
            break;
        case "-21":
            return "Financial operations for this transaction was not";
            break;
        case "-22":
            return "Transaction is unsuccessful.";
            break;
        case "-33":
            return "Transaction amount does not match the amount";
            break;
        case "-34":
            return "Limit the number of transactions or number has crossed the divide";
            break;
        case "-40":
            return "There is no access to the method.";
            break;
        case "-41":
            return "Additional Data related to information submitted is invalid.";
            break;
        case "-54":
            return "Request archived.";
            break;
        case "100":
            return "Operation was successful.";
            break;
        case "101":
            return "Operation was successful but PaymentVerification operation on this transaction have already been done.";
            break;
        case "200":
            return "Transaction canceled by user";
            break;
        default:
            return "Unknown error";
        }

    }


}
