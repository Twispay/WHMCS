<?php
/** Load libraries needed for gateway module functions. */
require("../../../init.php");
$whmcs->load_function("invoice");
$whmcs->load_function("gateway");

/** Import helper classes. */
require_once(__DIR__ . "/../twispay/lib/Twispay_Notification.php");
require_once(__DIR__ . "/../twispay/lib/Twispay_Response.php");
require_once(__DIR__ . "/../twispay/lib/Twispay_Config.php");

/** Read the module parameters. */
$gatewayParams = getGatewayVariables("twispay");

/** Die if module is not active. */
if (!$gatewayParams['type']) {
    Twispay_Notification::notice_to_cart('TWISPAY_PLUGIN_NOT_ACTIVATED');
    die(Twispay_Notification::translate('TWISPAY_PLUGIN_NOT_ACTIVATED'));
}

/** Read the configuration values. */
$apiKey = Twispay_Config::getApiKey();
if ('' == $apiKey) {
  logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], "BackUrl: Configuration Error");
  Twispay_Notification::notice_to_checkout('TWISPAY_CONFIGURATION_ERROR');
  die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
}

/** Check if NO RESPONSE has been received. */
if ((FALSE == isset($_POST['opensslResult'])) && (FALSE == isset($_POST['result']))) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_NULL_RESPONSE')], "BackUrl: Null Response");
    Twispay_Notification::notice_to_cart('TWISPAY_NULL_RESPONSE');
    die(Twispay_Notification::translate('TWISPAY_NULL_RESPONSE'));
}

/** Decrypt the response. */
$decrypted = Twispay_Response::decrypt(/*tw_encryptedResponse*/(isset($_POST['opensslResult'])) ? ($_POST['opensslResult']) : ($_POST['result']), /*secretKey*/$apiKey);
if (FALSE == $decrypted) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_DECRIPTION_FAILED')], "BackUrl: Decription Failed");
    Twispay_Notification::notice_to_cart('TWISPAY_DECRIPTION_FAILED');
    die(Twispay_Notification::translate('TWISPAY_DECRIPTION_FAILED'));
}

/** Validate the decripted response. */
$orderValidation = Twispay_Response::validate($decrypted);
if (FALSE == $orderValidation) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_VALIDATION_FAILED')], "BackUrl: Validation Failed");
    Twispay_Notification::notice_to_cart('TWISPAY_VALIDATION_FAILED');
    die(Twispay_Notification::translate('TWISPAY_VALIDATION_FAILED'));
}

/** Validate the received invoid ID. */
$invoiceId = checkCbInvoiceID($decrypted['externalOrderId'], /*gatewayName*/'twispay');

/** Validate that the transaction ID han not been processed before. */
if (Twispay_Response::checkTransID($decrypted['transactionId'])) {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['transactionId' => $decrypted['transactionId'], 'message' => Twispay_Notification::translate('TWISPAY_TRANSACTION_PROCESSED')], "BackUrl: Transaction Processed");
    Twispay_Notification::notice_to_checkout('TWISPAY_TRANSACTION_PROCESSED');
    die(Twispay_Notification::translate('TWISPAY_TRANSACTION_PROCESSED'));
}

/** Chech the response type and proces the response. */
if ('p' == $decrypted['identifier'][0]) {
    $statusUpdate = Twispay_Response::processResponse_purchase_backUrl($invoiceId, $decrypted);

    if (FALSE === $statusUpdate) {
        redirSystemURL('id=' . $invoiceId . '&paymentfailed=true', 'viewinvoice.php');
    }

    /** Redirect the user to the success page. */
    redirSystemURL('id=' . $invoiceId . '&paymentsuccess=true', 'viewinvoice.php');
} else {
    logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceId' => $invoiceId, 'message' => Twispay_Notification::translate('TWISPAY_RECURRENT_NOT_SUPPORTED')], "BackUrl: Recurrent orders not suported");
    redirSystemURL('id=' . $invoiceId . '&paymentfailed=true', 'viewinvoice.php');
}
