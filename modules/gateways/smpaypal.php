<?php
/**
 * Send Money Gateway for Paypal By AMIRMNOOHI
 *
 *
 * @copyright Copyright (c) AMIRMNOOHI
 * @license http://noohi.org/license/ MIT
 */

use WHMCS\Database\Capsule;



if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function smpaypal_MetaData()
{
    return array(
        'DisplayName' => 'Send Money Gateway Module for Paypal',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function smpaypal_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'smpaypal',
        ),
        'Email' => array(
            'FriendlyName' => 'آدرس ایمیل',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'آدرس ایمیلی که مشتریان واریزهای خود را به آن انجام میدهند.',
        ),
        'ClientID' => array(
            'FriendlyName' => 'کد کاربر',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'کد کاربری موجود در اکانت پیپال شما',
        ),

        'SecretID' => array(
            'FriendlyName' => 'کد رمز',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'کد رمز موجود در اکانت پیپال شما',
        ),
        'proxyStatus' => array(
            'FriendlyName' => 'وضعیت پروکسی',
            "Type" => "yesno",
            "Description" => "در صورتی که قصد استفاده از پروکسی در درخواست های خود دارید این قسمت را فعال کنید."

        ),
        'proxyAddress' => array(
            'FriendlyName' => 'آدرس پروکسی',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'آدرس پروکسی خود را در صورت نیاز در این قسمت وارد کنید',
        ),
        'proxyPort' => array(
            'FriendlyName' => 'پورت پروکسی',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'پورت پروکسی خود را در صورت نیاز در این قسمت وارد کنید',
        ),
        'proxyUsername' => array(
            'FriendlyName' => 'نام کاربری پروکسی',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'نام کاربری پروکسی خود را در صورت نیاز در این قسمت وارد کنید',
        ),
        'proxyPassword' => array(
            'FriendlyName' => 'رمزعبور پروکسی',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'رمزعبور پروکسی خود را در صورت نیاز در این قسمت وارد کنید',
        )
    );
}

function smpaypal_link($params)
{
    $htmlOutput = '<form method="get" action="sminvoice.php">';
    $htmlOutput .= '<input type="hidden" name="id" value="' . $params['invoiceid'] .'">';
    $htmlOutput .= '<input type="submit" value="' . $params['langpaynow'] . '" />';
    $htmlOutput .= '</form>';
    return $htmlOutput;
}
