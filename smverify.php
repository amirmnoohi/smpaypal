<?php
/**
 * Send Money Gateway for Paypal By AMIRMNOOHI
 *
 *
 * @copyright Copyright (c) AMIRMNOOHI
 * @license http://noohi.org/license/ MIT
 */


use WHMCS\Authentication\CurrentUser;
use WHMCS\ClientArea;
use WHMCS\Database\Capsule;

define('CLIENTAREA', true);


require __DIR__ . '/init.php';


$ca = new ClientArea();

$ca->setPageTitle($_LANG['smpaypal']['pageTitle']);
$ca->addToBreadCrumb('clieantare.php', Lang::trans('globalsystemname'));
$ca->addToBreadCrumb('clieantare.php', Lang::trans('clientareanavhome'));
$ca->addToBreadCrumb('clientarea.php?action=invoices', Lang::trans('invoices'));
$ca->addToBreadCrumb('', Lang::trans('invoicenumber').$_REQUEST["invoiceID"]);
$ca->setTemplate('sminvoice');

$ca->initPage();
$ca->requireLogin();

$currentUser = new CurrentUser();
$authUser = $currentUser->user();

if ($authUser) {
    $gateway = getGatewayVariables('smpaypal');
    
    $userID = Capsule::table('tblusers_clients')->where('auth_user_id',$authUser->id)->first()->client_id;
    
    $invoiceID = $_REQUEST["invoiceID"];
    $transactionID = $_REQUEST["transactionID"];
    $invoice = new WHMCS\Invoice();
    
    // Check if invoice exists
    try {
        $invoice->setID($invoiceID);
    } catch (Exception $e) {
        $ca->assign('fatalError', true);
        $ca->assign('fatalErrorMessage', $_LANG["smpaypal"]["invalidInvoice"]);
        $ca->output();
    }
    
    // Check Invoice can be accessed by user
    if($invoice->getData('userid') != $userID)
    {
        $ca->assign('fatalError', true);
        $ca->assign('fatalErrorMessage', $_LANG["smpaypal"]["accessDenied"]);
        $ca->output();
    }
    
    // Check if invoice is in unpaid status
    if($invoice->getData('status') != "Unpaid"){
        header('Location: ' . '/viewinvoice.php?id=' . $invoiceID);
    }
    
    
    $ca->assign('payto', $gateway["Email"]);
    $ca->assign('invoiceID', $invoice->getData('id'));
    $ca->assign('invoiceTitle',  Lang::trans('invoicenumber').$invoiceID);
    $ca->assign('status', $invoice->getData('status'));
    $ca->assign('invoiceitems', $invoice->getLineItems());
    $ca->assign('subtotal', number_format($invoice->getData('subtotal')).getCurrency($clientId)["suffix"]);
    $ca->assign('credit', number_format($invoice->getData('credit')).getCurrency($clientId)["suffix"]);
    $ca->assign('total', number_format($invoice->getData('total')).getCurrency($clientId)["suffix"]);
    
    
    // Get Token
    $paypalClientID = $gateway["ClientID"];
    $paypalSecretID = $gateway["SecretID"];
    $paypalEncoded = base64_encode($paypalClientID.":".$paypalSecretID);
    $proxyStatus = $gateway["proxyStatus"];
    $proxy = $gateway["proxyAddress"].":".$gateway["proxyPort"];
    $proxyauth = $gateway["proxyUsername"].":".$gateway["proxyPassword"];
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api-m.paypal.com/v1/oauth2/token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Basic '.$paypalEncoded,
        'Content-Type: application/x-www-form-urlencoded'
      ),
    ));
    
    if($proxyStatus == "on"){
        curl_setopt($curl, CURLOPT_PROXY, $proxy);
        if($proxyauth and $gateway["proxyUsername"] and $gateway["proxyPassword"]){
            curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyauth);
        }
    }
    
    $response = json_decode(curl_exec($curl),true);
    
    
    // Couldn't get token from paypal
    if(!array_key_exists("access_token", $response)){
        $ca->assign('normalError', true);
        $ca->assign('normalErrorMessage', $_LANG["smpaypal"]["getTokenError"]);
        $ca->output();
    }
    
    $token = $response["access_token"];

    
    // Get Transaction
    $endDate = (new DateTime('now', new DateTimeZone('-0700')))->format(DateTime::ISO8601);
    $startDate = (new DateTime('-25 days', new DateTimeZone('-0700')))->format(DateTime::ISO8601);

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api-m.paypal.com/v1/reporting/transactions?start_date="
      .$startDate."&end_date=".$endDate."&transaction_id=".$transactionID,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer '.$token
      ),
    ));
    
    $response = json_decode(curl_exec($curl),true);
    
    curl_close($curl);
    
    
    // Couldn't get token from paypal
    if(!isset($response["transaction_details"])){
        $ca->assign('normalError', true);
        $ca->assign('normalErrorMessage', $_LANG["smpaypal"]["getTransactionError"]);
        $ca->output();
    }
    
    // Check if Transaction Exists
    if(count($response["transaction_details"]) != 1){
        $ca->assign('normalError', true);
        $ca->assign('normalErrorMessage', $_LANG["smpaypal"]["transactionNotFound"]);
        $ca->output();
    }

    $transaction = $response["transaction_details"][0]["transaction_info"];

    // Check transaction status
    if($transaction["transaction_status"] != "S"){
        $ca->assign('normalError', true);
        $ca->assign('normalErrorMessage', $_LANG["smpaypal"]["transactionNotCompeleted"]);
        $ca->output();
    
    }
    
    // Check prices to be same
    if(doubleval($transaction["transaction_amount"]["value"]) != doubleval($invoice->getData('total'))){
        $ca->assign('normalError', true);
        $ca->assign('normalErrorMessage', $_LANG["smpaypal"]["transactionAmountNotMatch"]);
        $ca->output();
    }
    
    // Check not be repeatative
    if(Capsule::table('tblaccounts')->where('transid', $transactionID)->count()){
        $ca->assign('normalError', true);
        $ca->assign('normalErrorMessage', $_LANG["smpaypal"]["repeatedTransaction"]);
        $ca->output();
    }

    // Pay invoice
    logTransaction('smpaypal', $response, 'Success');
    addInvoicePayment(
                    $invoiceID,
                    $transactionID,
                    $invoice->getData('total'),
                    0,
                    'smpaypal'
                );
    

    // redirect to invoice with success message
    header('Location: ' . '/viewinvoice.php?id=' . $invoiceID);

    
}

