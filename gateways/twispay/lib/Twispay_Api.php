<?php

/**
 * Twispay payment gateway api file.
 *
 * @package     Twispay_Payment_Gateway
 * @author      Twispay
 */
if ( ! class_exists( 'Twispay_Api' ) ) : /* Security class check */
class Twispay_Api
{
    /**
     * Function that returns the backUrl.
     *
     * @param transactionId The ID of the transaction for which to extract the parent ID.
     *
     * @return String The parent ttansaction ID.
     */
    public function getParentTransactionId($transactionId)
    {
        /** Import helper class. */
        require(__DIR__ . "/Twispay_Config.php");

        /** Read the configuration values. */
        $apiKey = Twispay_Config::getApiKey();
        if ('' == $apiKey) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], "Configuration Error");
            die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
        }

        /** Compose the request URL. */
        $url  = Twispay_Config::getApiUrl() . '/transaction/' . $transactionId;

        /* Make the server request. */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Authorization: ' . $apiKey]);

        /* Send the request. */
        $response = curl_exec($ch);
        curl_close($ch);
        /* Decode the response. */
        $response = json_decode($response);

        /* Check if the decryption was successful, the response code is 200 and message is 'Success'. */
        if ((NULL !== $response) && (200 == $response->code) && ('Success' == $response->message)) {
            return $response->data->parrentTransactionId . '';
        } else {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_TRANSACTION_PARENT')], "Parent Extraction Failed");
            die(Twispay_Notification::translate('TWISPAY_TRANSACTION_PARENT'));
        }
    }
}
endif; /* End if class_exists. */
