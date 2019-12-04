<?php
/** Load libraries needed for gateway module functions. */
require('../../../init.php');
$whmcs->load_function('invoice');
$whmcs->load_function('gateway');

/** Import helper classes. */
require_once(__DIR__ . '/../twispay/lib/Twispay_Notification.php');
require_once(__DIR__ . '/../twispay/lib/Twispay_Response.php');
require_once(__DIR__ . '/../twispay/lib/Twispay_Config.php');

/** Read the module parameters. */
$gatewayParams = getGatewayVariables('twispay');

/** Die if module is not active. */
if (!$gatewayParams['type']) {
    die(Twispay_Notification::translate('TWISPAY_PLUGIN_NOT_ACTIVATED'));
}

/** Read the configuration values. */
$apiKey = Twispay_Config::getApiKey();
if ('' == $apiKey) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], 'Twispay IPN: Configuration Error');
    die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
}

/** Check if NO RESPONSE has been received. */
if ((FALSE == isset($_POST['opensslResult'])) && (FALSE == isset($_POST['result']))) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_NULL_RESPONSE')], 'Twispay IPN: Null Response');
    die(Twispay_Notification::translate('TWISPAY_NULL_RESPONSE'));
}

/** Decrypt the response. */
$decrypted = Twispay_Response::decrypt(/*tw_encryptedResponse*/(isset($_POST['opensslResult'])) ? ($_POST['opensslResult']) : ($_POST['result']), /*secretKey*/$apiKey);
if (FALSE == $decrypted) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_DECRIPTION_FAILED')], 'Twispay IPN: Decription failed');
    die(Twispay_Notification::translate('TWISPAY_DECRIPTION_FAILED'));
}

/** Validate the decripted response. */
$orderValidation = Twispay_Response::validate($decrypted);
if (FALSE == $orderValidation) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_VALIDATION_FAILED')], 'Twispay IPN: Validation failed');
    die(Twispay_Notification::translate('TWISPAY_VALIDATION_FAILED'));
}

/** Validate the received invoid ID. */
$invoiceId = checkCbInvoiceID($decrypted['externalOrderId'], /*gatewayName*/'twispay');

/** Validate that the transaction ID han not been processed before. */
if (Twispay_Response::checkTransID($decrypted['transactionId'])) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['transactionId' => $decrypted['transactionId'], 'message' => Twispay_Notification::translate('TWISPAY_TRANSACTION_PROCESSED')], 'Twispay IPN: Transaction processed');
    /** Exit with success as transaction has allready been processed. */
    die('OK');
}

/** Proces the response. */
$statusUpdate = Twispay_Response::processResponse_IPN($invoiceId, $decrypted);

if (FALSE === $statusUpdate) {
    die(Twispay_Notification::translate('TWISPAY_STATUS_FAILED') . $invoiceId);
}

die('OK');
