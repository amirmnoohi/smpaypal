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
$ca->addToBreadCrumb('', Lang::trans('invoicenumber').$_REQUEST["id"]);
$ca->setTemplate('sminvoice');

$ca->initPage();
$ca->requireLogin();

$currentUser = new CurrentUser();
$authUser = $currentUser->user();

if ($authUser) {
    $userID = Capsule::table('tblusers_clients')->where('auth_user_id',$authUser->id)->first()->client_id;
    $invoiceID = $_REQUEST["id"];
    
    $invoice = new WHMCS\Invoice();
    try {
        $invoice->setID($invoiceID);
    } catch (Exception $e) {
        $ca->assign('fatalError', true);
        $ca->assign('fatalErrorMessage', $_LANG["smpaypal"]["invalidInvoice"]);
        $ca->output();
    }
    if($invoice->getData('userid') != $userID)
    {
        $ca->assign('fatalError', true);
        $ca->assign('fatalErrorMessage', $_LANG["smpaypal"]["accessDenied"]);
        $ca->output();
    }
    $ca->assign('payto', getGatewayVariables('smpaypal')["Email"]);
    $ca->assign('invoiceID', $invoice->getData('id'));
    $ca->assign('invoiceTitle',  Lang::trans('invoicenumber').$invoiceID);
    $ca->assign('status', $invoice->getData('status'));
    $ca->assign('invoiceitems', $invoice->getLineItems());
    $ca->assign('subtotal', number_format($invoice->getData('subtotal')).getCurrency($clientId)["suffix"]);
    $ca->assign('credit', number_format($invoice->getData('credit')).getCurrency($clientId)["suffix"]);
    $ca->assign('total', number_format($invoice->getData('total')).getCurrency($clientId)["suffix"]);
}
$ca->output();