<?php
/**
 * Twispay Payment Gateway Module
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://www.twispay.com
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @return array
 */
function twispay_MetaData()
{
    return array(
        'DisplayName' => 'Twispay',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * @return array
 */
function twispay_config()
{
    /** Calculate the base URL of the platform. */
    $baseurl = ((!empty($_SERVER['HTTPS'])) ? ('https://') : ('http://')) . $_SERVER['HTTP_HOST'];

    /** Hide the server to server default input */
    echo '<style>input[name="field[s2s_notification]"]{display:none !important;}</style>';

    /** Compose the return array. */
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Twispay',
            'Description' => '<br/><small>Pay by debit or credit card.</small>',
        ),

        /** Details and logic for field that contolls at wchich environment (production or staging) the platform connects. */
        'live_mode' => array(
            'FriendlyName' => 'Live Mode',
            'Type' => 'radio',
            'Options' => 'Yes,No',
            'Default' => 'No',
            'Description' => '<small>Select "Yes" if you want to use the payment gateway in Production Mode or "No" if you want to use it in Staging Mode.</small>
            <script>
            $(document).ready(function(){
                function toggleDisplay(selectedValue) {
                    if (\'Yes\' === selectedValue) {
                        $(\'input[name="field[staging_site_id]"]\').closest(\'tr\').hide();
                        $(\'input[name="field[staging_secret_key]"]\').closest(\'tr\').hide();
                        $(\'input[name="field[live_site_id]"]\').closest(\'tr\').show();
                        $(\'input[name="field[live_secret_key]"]\').closest(\'tr\').show();
                    } else if (\'No\' === selectedValue) {
                        $(\'input[name="field[staging_site_id]"]\').closest(\'tr\').show();
                        $(\'input[name="field[staging_secret_key]"]\').closest(\'tr\').show();
                        $(\'input[name="field[live_site_id]"]\').closest(\'tr\').hide();
                        $(\'input[name="field[live_secret_key]"]\').closest(\'tr\').hide();
                    }
                }

                toggleDisplay($(\'input[name="field[live_mode]"]:checked\').val());
                $(\'input[name="field[live_mode]"]\').change(function(){
                    toggleDisplay($(\'input[name="field[live_mode]"]:checked\').val());
                });
            });
            </script>
            ',
        ),

        /** Details of the live site ID field. */
        'live_site_id' => array(
            'FriendlyName' => 'Live Site ID',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '<br/><small>Enter the Site ID for Live Mode. You can get one from <a href="https://merchant.twispay.com/login">here</a>.</small>',
        ),

        /** Details of the live secret key field. */
        'live_secret_key' => array(
            'FriendlyName' => 'Live Secret Key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '<br/><small>Enter the Secret Key for Live Mode. You can get one from <a href="https://merchant.twispay.com/login">here</a>.</small>',
        ),

        /** Details of the staging site ID field. */
        'staging_site_id' => array(
            'FriendlyName' => 'Staging Site ID',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '<br/><small>Enter the Site ID for Staging Mode. You can get one from <a href="https://merchant-stage.twispay.com/login">here</a>.</small>',
        ),

        /** Details of the staging secret key field. */
        'staging_secret_key' => array(
            'FriendlyName' => 'Staging Secret Key',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '<br/><small>Enter the Secret Key for Staging Mode. You can get one from <a href="https://merchant-stage.twispay.com/login">here</a>:</small>',
        ),

        /** Details of the server to server field. */
        's2s_notification' => array(
            'FriendlyName' => 'Server-to-server notification URL',
            'Type' => 'text',
            'Size' => '100',
            'Default' => $baseurl . '/modules/gateways/callback/twispay_validate.php',
            'Description' => '<input type="text" style="width:100%;" value="' . $baseurl . '/modules/gateways/callback/twispay_validate.php" disabled/><br/><small>Put <a href="' . $baseurl . '/modules/gateways/callback/twispay_validate.php' . '">this URL</a> in your Twispay account, <a href="https://merchant.twispay.com/login">here for Production (Live) Mode</a> or <a href="https://merchant-stage.twispay.com/login">here for Staging (Test) Mode</a>.</small>',
        ),

        /** Details of the redirect to a custom page field. */
        'redirect_page' => array(
            'FriendlyName' => 'Redirect to custom page',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '<br/><small>Leave empty to redirect to order confirmation default page (Ex: <font color="#6495ed">/clientarea.php?action=services</font>).</small>',
        ),

        /** Details of the contact email field. */
        'contact_email' => array(
            'FriendlyName' => 'Contact email',
            'Type' => 'text',
            'Size' => '100',
            'Default' => '',
            'Description' => '<br/><small>This email will be used on the payment error page.</small>',
        ),
    );
}

/**
 * Payment link.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @return string
 */
use WHMCS\Database\Capsule;
function twispay_get_order_id($invoice_id = 0){
    if(!empty($invoice_id)) {
        $invoice_items = Capsule::table('tblinvoiceitems')->where('invoiceid', $invoice_id)->get();
        $multiple = array();
        foreach($invoice_items as $invoices){
            if($invoices->type == 'Invoice'){
                array_push($multiple, $invoices->relid);
            }
        }
        if(!empty($multiple)){
            $orderid = Capsule::table('tblorders')->whereIn('invoiceid',$multiple)->pluck('id','invoiceid');
        } else {
            $orderid = Capsule::table('tblorders')->where('invoiceid', $invoice_id)->pluck('id','invoiceid');
        }

        return $orderid;
    } else {
        return false;
    }

}
function twispay_link($params)
{

    $getorder = twispay_get_order_id($params['invoiceid']);
    $orders = array();
    if(!empty($getorder)){
        foreach($getorder as $k=>$v){
            $orders[] = localAPI('GetOrders', array('id'=>$v));
        }

    } else {
        $orders[] = localAPI('GetOrders', array('invoiceid'=>$params['invoiceid']));
    }

    $postfields = array();
    if(!empty($params['testMode'])) {
        $action = 'https://secure-stage.twispay.com';
        $postfields['siteId'] = $params['staging_site_id'];
        $privateKEY = $params['staging_secret_key'];
    } else {
        $action = 'https://secure.twispay.com';
        $postfields['siteId'] = $params['live_site_id'];
        $privateKEY = $params['live_secret_key'];

    }
    $string_orders = implode('-', $getorder);
    $langPayNow = $params['langpaynow'];


    /* Define data for form inputs */

    $postfields['identifier'] = '_' . $params['clientdetails']['userid'];
    $postfields['amount'] = $params['amount'];
    $postfields['backUrl'] = $params['systemurl'] . 'modules/gateways/callback/' . $params['paymentmethod'] . '.php';
    $postfields['currency'] = $params['currency'];
    $postfields['description'] = (empty($params['clientdetails']['companyname'])) ? trim(ucwords($params['clientdetails']['firstname'] . ' ' .        $params['clientdetails']['lastname'])) . ' - Invoice #' . $params['invoiceid'] :  $params["description"];
    $postfields['orderType'] = 'purchase';
    $postfields['orderId'] = $string_orders ;
    $postfields['orderId'] .= '_' . time();
    $postfields['firstName'] = $params['clientdetails']['firstname'];
    $postfields['lastName'] = $params['clientdetails']['lastname'];
    $postfields['country'] = $params['clientdetails']['country'];
    $postfields['city'] = $params['clientdetails']['city'];
    $postfields['firstName'] = $params['clientdetails']['firstname'];
    $postfields['zipCode'] = $params['clientdetails']['postcode'];
    $postfields['address'] = $params['clientdetails']['address1'];
    $postfields['address'] .= (!empty($params['clientdetails']['address2'])) ?', ' . $params['clientdetails']['address2'] : '';
    $postfields['phone'] = $params['clientdetails']['phonenumber'];
    $postfields['email'] = $params['clientdetails']['email'];
    $postfields['custom[original_invoice]'] = $params['invoiceid'];
    $i= 0;
     foreach($orders as $order){
         foreach($order['orders']['order'][0]['lineitems']['lineitem'] as $item){
            $postfields['item[' . $i . ']'] = $item['product'];
            $postfields['units[' . $i . ']'] = '1';
            $postfields['unitPrice[' . $i . ']'] = $item['amount']->toNumeric();
            $postfields['subTotal[' . $i . ']'] = number_format( ( float )$postfields['unitPrice[' . $i . ']'], 2 );
            $postfields['orderTags[' . $i . ']'] = $item['producttype'];
            $postfields['custom[' . $i . '][billingcycle]'] = ucfirst($item['billingcycle']);
                $postfields['custom[' . $i . '][siteordernumber]'] = (string)$order['orders']['order'][0]['ordernum'];
            ++$i;
        }
    }

    $page = explode('/',$_SERVER['PHP_SELF']);
    $page = trim($page[count($page)-1]);
     /* CardTransactionMode */
    $postfields['cardTransactionMode'] = 'authAndCapture';
    /* Checksum */
    ksort($postfields);
    $query = http_build_query($postfields);
    $encoded = hash_hmac('sha512', $query, $privateKEY, true);
    $checksum = base64_encode($encoded);

    $htmlOutput = '<form accept-charset="UTF-8" id="twispay_payment_form" method="POST" action="' . $action . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
    }
    $htmlOutput .= '<input type="hidden" name="checksum" value="' . $checksum . '" />';

    if($page !='cart.php') {
        $htmlOutput .= '<button type="submit" class="btn btn-success btn-sm" id="btnPayNow"><i class="fa fa-credit-card"></i>&nbsp; ' . $langPayNow . '</button>';
    }
    $htmlOutput .= '</form>';
    return $htmlOutput;

}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function twispay_refund($params)
{
    $p = 'test';
    $log_file = dirname(__FILE__).'/twispay_r_log.txt';
    @file_put_contents($log_file,$p.PHP_EOL, FILE_APPEND);
    $transid = $params['transid'];
    if(!empty($params['testMode'])){
        $url = 'https://api-stage.twispay.com/transaction/' . $transid;
        $apiKey = $params['staging_secret_key'];
    } else {
        $url = 'https://api.twispay.com/transaction/' . $transid;
        $apiKey = $params['live_secret_key'];
    }


    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Authorization: Bearer " . $apiKey, "Accept: application/json" ) );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

    $contents = curl_exec( $ch );
    curl_close( $ch );
    $json = json_decode( $contents );
    if($json->message == 'Success' ){
        return array(
            'status'    => 'success',
            'rawdata'   => $json,
            'transid'   => $json->data->transactionId,
        ) ;
    } else {
        return array(
            'status'    => 'failure',
            'rawdata'   => $json,
            'transid'   => $json->data->transactionId,
        ) ;
    }

}