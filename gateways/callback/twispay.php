<?php

/* Require libraries needed for gateway module functions.*/
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
$baseurl = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
$baseurl .= $_SERVER['HTTP_HOST'];

/* Detect module name from filename.*/
$gatewayModuleName = basename(__FILE__, '.php');

/* Fetch gateway configuration parameters.*/
$gatewayParams = getGatewayVariables($gatewayModuleName);

/* Die if module is not active.*/
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
if(empty(trim($gatewayParams['redirect_page']))){
    $page_to_redirect = $baseurl. '/cart.php?a=complete';
} else {
    $page_to_redirect = trim($gatewayParams['redirect_page']);
    if(stripos($page_to_redirect,'/') !==0){
        $page_to_redirect = '/'. $page_to_redirect;
    }
    $page_to_redirect = $baseurl. $page_to_redirect;
    stream_context_set_default(
        array(
            'http' => array(
                'method' => 'HEAD'
            )
        )
    );
    $headers = @get_headers($page_to_redirect);
    $status = substr($headers[0], 9, 3);
    if (!($status >= 200 && $status < 400 )) {
        $page_to_redirect = $baseurl. '/cart.php?a=complete';
    }

}

if (!defined(_DB_PREFIX_)){
    define('_DB_PREFIX_','tbl');
}
use WHMCS\Database\Capsule;
$log_file = dirname(__FILE__).'/twispay_log.txt';

if(filesize($log_file) > 2097152){
    @file_put_contents($log_file, PHP_EOL.PHP_EOL);
}

function createTransactionsTable() {
    $sql = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."twispay_transactions` (
                `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
                `status` varchar(16) NOT NULL,
                `invoice_id` int(11) NOT NULL,
                `identifier` int(11) NOT NULL,
                `customerId` int(11) NOT NULL,
                `orderId` int(11) NOT NULL,
                `cardId` int(11) NOT NULL,
                `transactionId` int(11) NOT NULL,
                `transactionKind` varchar(16) NOT NULL,
                `amount` float NOT NULL,
                `currency` varchar(8) NOT NULL,
                `date` DATETIME NOT NULL,
                PRIMARY KEY (`id_transaction`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
    return full_query($sql);
}

function checkTransactionTable(){
    $table = _DB_PREFIX_."twispay_transactions";
    $recs = 0;
    foreach (Capsule::table($table)->get() as $records) {
        ++$recs;
    }
    if($recs == 0){
        full_query("DROP TABLE `".$table."`");
        createTransactionsTable();

    }
}


function twispayDecrypt($encrypted)
{
    global $gatewayParams;

    if(!$gatewayParams) {
        return false;
    }

    $apiKey = (!empty($gatewayParams['testMode'])) ? $gatewayParams['staging_secret_key'] : $gatewayParams['live_secret_key'];

    $encrypted = (string)$encrypted;
    if(!strlen($encrypted)) {
        return null;
    }
    if(strpos($encrypted, ',') !== false) {
        $encryptedParts = explode(',', $encrypted, 2);
        $iv = base64_decode($encryptedParts[0]);
        if(false === $iv) {
            throw new Exception("Invalid encryption iv");
        }
        $encrypted = base64_decode($encryptedParts[1]);
        if(false === $encrypted) {
            throw new Exception("Invalid encrypted data");
        }
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $apiKey, OPENSSL_RAW_DATA, $iv);
        if(false === $decrypted) {
            throw new Exception("Data could not be decrypted");
        }
        return $decrypted;
    }
    return null;
}


function getResultStatuses() {
    return array("complete-ok");
}



function loggTransaction($data) {
    $data =json_decode(json_encode($data),TRUE);

    $columns = array(
        'status',
        'invoice_id',
        'identifier',
        'customerId',
        'orderId',
        'cardId',
        'transactionId',
        'transactionKind',
        'amount',
        'currency',
        'timestamp',
    );
    foreach($data as $key => $value) {
        if(!in_array($key, $columns)) {
            unset($data[$key]);
        }
    }
    $howmany = count($data['invoice_id'])-1;

    if(!empty($data['timestamp'])) {
        $data['date'] = date('Y-m-d H:i:s', $data['timestamp']);
        unset($data['timestamp']);
    }
    if(!empty($data['identifier'])) {
        $data['identifier'] = (int)str_replace('_', '', $data['identifier']);
    }
    for($i=0; $i<=$howmany; $i++){
        $datas = $data;
        $datas['invoice_id'] = $data['invoice_id'][$i];
        Capsule::table(_DB_PREFIX_ . 'twispay_transactions')->insert($datas);
    }

}

function makeDir($path)
{
    return is_dir($path) || mkdir($path);
}

function twispay_log($string = false) {
    global $log_file;

    if(!$string) {
        $string = PHP_EOL.PHP_EOL;
    } else {
        $string = "[".date('Y-m-d H:i:s')."] ".$string;
    }
    @file_put_contents($log_file, $string.PHP_EOL, FILE_APPEND);
}


function tl($string=''){
    return $string;
}

$wrong_status = array();


function checkValidation($json, $usingOpenssl = true) {
    global $wrong_status;
    global $page_to_redirect;

    twispay_log('[RESPONSE] decrypted string: '.json_encode($json));
    /* Validating the fields */
    $_errors = array();
    if(empty($json->externalOrderId)) {
        twispay_log('hop 1');
        twispay_log();
        $_errors[] = tl('Empty externalOrderId');
    } else {
        twispay_log('hop 2');
        twispay_log();
        $orders_id = explode('_', $json->externalOrderId);
        $orders_id = explode('-', $orders_id[0]);
        foreach($orders_id as $order_id){
            $orders = json_decode(json_encode(localAPI('GetOrders', array('id' => $order_id))));
            twispay_log('hop 3');
            twispay_log();
            if (empty($orders->totalresults) && $orders->totalresults != '1') {
                twispay_log('hop 4');
                twispay_log();
                twispay_log(sprintf(tl('[RESPONSE-ERROR] Order #%s could not be loaded'), $order_id));
                twispay_log();
                return false;
            } else {
                twispay_log('hop 5');
                twispay_log();
                $invoice_id[] = $orders->orders->order[0]->invoiceid;
                twispay_log(json_encode($orders));
                twispay_log();
                if (strtolower($orders->orders->order[0]->status) != 'pending') {
                    echo tl('Processing ...');
                    twispay_log('hop 6');
                    twispay_log();
                    twispay_log(sprintf(tl('[RESPONSE-ERROR] Order has no pending status, order id %s'), $order_id));
                    twispay_log();
                    echo '<meta http-equiv="refresh" content="2;url='. $page_to_redirect.'" />';
                    header('Refresh: 2;url=' . $page_to_redirect);
                    exit;
                } else {
                    twispay_log('hop 7');
                    twispay_log();

                }

            }

        }
    }

    twispay_log('hop si aici 1');
    twispay_log();
    if(empty($json->transactionStatus)) {
        $_errors[] = tl('Empty status');
    }
    if(empty($json->amount)) {
        $_errors[] = tl('Empty amount');
    }
    if(empty($json->currency)) {
        $_errors[] = tl('Empty currency');
    }
    if(empty($json->identifier)) {
        $_errors[] = tl('Empty identifier');
    }
    if(empty($json->orderId)) {
        $_errors[] = tl('Empty orderId');
    }
    if(empty($json->transactionId)) {
        $_errors[] = tl('Empty transactionId');
    }
    if(empty($json->transactionMethod)) {
        $_errors[] = tl('Empty transactionMethod');
    }


    if(sizeof($_errors)) {
        twispay_log('hop 8');
        twispay_log();
        foreach($_errors as $err) {
            twispay_log('[RESPONSE-ERROR] '.$err);
        }
        twispay_log();
        return false;
    } else {
        twispay_log('hop 9');
        twispay_log();
        $data = array(
            'invoice_id' => $invoice_id,
            'order_id' => $orders_id,
            'status' => $json->transactionStatus,
            'amount' => (float)$json->amount,
            'currency' => $json->currency,
            'identifier' => $json->identifier,
            'orderId' => (int)$json->orderId,
            'transactionId' => (int)$json->transactionId,
            'customerId' => (int)$json->customerId,
            'transactionKind' => $json->transactionMethod,
            'cardId' => (!empty($json->cardId)) ? (int)$json->cardId : 0,
            'timestamp' => (is_object($json->timestamp)) ? time() : $json->timestamp,
            'original_invoice' => (!empty($json->custom->original_invoice)) ? $json->custom->original_invoice : '0',
        );
        twispay_log('[RESPONSE] Data: '.json_encode($data));

        if(!in_array($data['status'], getResultStatuses())) {
            twispay_log('hop 10');
            twispay_log();
            $wrong_status['status'] = $data['status'];
            $wrong_status['message'] = $data['status'];

            twispay_log(sprintf(tl('[RESPONSE-ERROR] Wrong status (%s)'), $data['status']));
            twispay_log();

            return false;
        }
        twispay_log('[RESPONSE] Status complete-ok');

    }
    return json_decode(json_encode($data));
}


sleep(2);
if(!empty($_POST)){

    createTransactionsTable();
    checkTransactionTable();
    $datas = (!empty($_POST['opensslResult'])) ? json_decode(twispayDecrypt($_POST['opensslResult'])) : json_decode(twispayDecrypt($_POST['result']));
    twispay_log('hop 11');
    twispay_log();
    if(!empty($datas)){
        twispay_log('hop 12');
        twispay_log();
        twispay_log( 'datas '. json_encode($datas));
        twispay_log();
        $result = checkValidation($datas);
        twispay_log('hop 13');
        twispay_log();
        if(!empty($result)){
            twispay_log('hop 14');
            twispay_log();
            $err = '';
            $errors = array();
            foreach($result->invoice_id as $inv){
                $command = 'AddInvoicePayment';
                $postData = array(
                    'invoiceid' => $inv,
                    'transid' => $result->transactionId,
                    'gateway' => $gatewayParams['name'],
                    'date' => date('Y-m-d H:i:s', $result->timestamp),
                );
                $results_invoice = localAPI($command, $postData);
                twispay_log('[RESPONSE] AddInvoicePayment invoice id: ' . $inv . '  '. json_encode($results_invoice));
                if($results_invoice['result'] != 'success'){
                    echo tl('Failed adding payment to invoice').'<br/>';
                    $err = 'error';
                    $errors[] = 'AddInvoicePayment #'.$inv;
                }

            }

            foreach($result->order_id as $ord){
                $command = 'AcceptOrder';
                $postData = array(
                    'orderid' => $ord,
                    'autosetup' => true,
                    'sendemail' => true,
                );
                $results_order = localAPI($command, $postData);
                twispay_log('[RESPONSE] AcceptOrder id : ' . $ord.'  '. json_encode($results_order));
                if($results_order['result'] != 'success'){
                    echo tl('Order could not be activated').'<br/>';
                    $err = 'error';
                    $errors[] = 'AcceptOrder #'.$ord;
                }
            }

            if(!empty($err)){
                twispay_log('[RESPONSE] Processing errors: '. json_encode($errors));
                echo '<meta http-equiv="refresh" content="1;url='. $page_to_redirect.'" />';
                header('Refresh: 1;url=' . $page_to_redirect);
                exit;
            } else {
                twispay_log('hop 15');
                twispay_log();
                if(!empty($result->original_invoice)){
                    $command = 'AddInvoicePayment';
                    $postData = array(
                        'invoiceid' => $result->original_invoice,
                        'transid' => $result->transactionId,
                        'gateway' => $gatewayParams['name'],
                        'date' => date('Y-m-d H:i:s', $result->timestamp),
                    );
                    $results_invoice = localAPI($command, $postData);
                    twispay_log('[RESPONSE] AddInvoicePayment invoice id: ' . $result->original_invoice . '  '. json_encode($results_invoice));
                    if($results_invoice['result'] != 'success'){
                        echo tl('Failed adding payment to invoice').'<br/>';
                        $err = 'error';
                        $errors[] = 'AddInvoicePayment #'.$result->original_invoice;
                    }
                }
                loggTransaction($result);
                echo tl('Processing ...');
                echo '<meta http-equiv="refresh" content="1;url='. $page_to_redirect.'" />';
                header('Refresh: 1;url=' . $page_to_redirect);
            }

        } else {
            twispay_log('hop 16');
            twispay_log();
            echo tl('Processing   ..... ') ;

            if(!empty($wrong_status['status']) && !empty($wrong_status['message'])) {
                twispay_log(tl($wrong_status['message']));
                echo tl($wrong_status['message']);
            }
            echo '<meta http-equiv="refresh" content="2;url='. $page_to_redirect.'" />';
            header('Refresh: 2;url=' . $page_to_redirect);
            exit;
        }


    } else {
        echo "NO DATA";
        twispay_log('hop 17');
        twispay_log();
        /*empty data*/
    }
    twispay_log('hop 18');
    twispay_log();
} else {
    twispay_log('hop 19');
    twispay_log();
    echo tl('no posts ');
}
