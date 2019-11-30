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
     * Function that extracts the parent ID of a transaction.
     *
     * @param transactionId The ID of the transaction for which to extract the parent ID.
     *
     * @return String The parent transaction ID.
     */
    public function getParentTransactionId($transactionId)
    {
        /** Import helper class. */
        require_once(__DIR__ . "/Twispay_Notification.php");
        require_once(__DIR__ . "/Twispay_Config.php");

        /** Read the configuration values. */
        $apiKey = Twispay_Config::getApiKey();
        if ('' == $apiKey) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], __FUNCTION__ . '::' . 'Configuration Error');
            die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
        }

        /** Compose the request URL. */
        $url  = Twispay_Config::getApiUrl() . '/transaction/' . $transactionId;

        /* Make the server request. */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
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
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['transactionId' => $transactionId], __FUNCTION__ . '::' . 'Parent Extraction Failed');
            die(Twispay_Notification::translate('TWISPAY_TRANSACTION_PARENT'));
        }
    }


    /**
     * Function that extracts the status of a subscription.
     *
     * @param subscriptionId The ID of the subscription for which to extract the status.
     *
     * @return String The subscription status.
     */
    public function getSubscriptionStatus($subscriptionId)
    {
        /** Import helper class. */
        require_once(__DIR__ . "/Twispay_Notification.php");
        require_once(__DIR__ . "/Twispay_Config.php");

        /** Read the configuration values. */
        $apiKey = Twispay_Config::getApiKey();
        if ('' == $apiKey) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], __FUNCTION__ . '::' . 'Configuration Error');
            die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
        }

        /** Compose the request URL. */
        $url  = Twispay_Config::getApiUrl() . '/order/' . $subscriptionId;

        /* Make the server request. */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Authorization: ' . $apiKey]);

        /* Send the request. */
        $response = curl_exec($ch);
        curl_close($ch);
        /* Decode the response. */
        $response = json_decode($response);

        /* Check if the decryption was successful, the response code is 200 and message is 'Success'. */
        if ((NULL !== $response) && (200 == $response->code) && ('Success' == $response->message)) {
            return $response->data->orderStatus;
        } else {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['subscriptionId' => $subscriptionId], __FUNCTION__ . '::' . 'Subscription Status Extraction Failed');
            die(Twispay_Notification::translate('TWISPAY_SUBSCRIPTION_STATUS'));
        }
    }


    /**
     * Function that performs a refund.
     *
     * @param params Array containing all the data needed to build the request.
     *
     * @return JSON Response received from server.
     */
    public function refund($params)
    {
        /** Import helper class. */
        require_once(__DIR__ . "/Twispay_Notification.php");
        require_once(__DIR__ . "/Twispay_Config.php");

        /** Read the configuration values. */
        $apiKey = Twispay_Config::getApiKey();
        if ('' == $apiKey) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], __FUNCTION__ . '::' . 'Configuration Error');
            die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
        }

        /** Compose the request URL. */
        $url  = Twispay_Config::getApiUrl() . '/transaction/' . $params['transid'];

        logTransaction(/*gatewayName*/'twispay', /*debugData*/['url' => $url], __FUNCTION__ . '::' . 'Refund url');

        /* Create the DELETE data arguments. */
        $postData = 'amount=' . $params['amount'] . '&' . 'message=' . 'Refund for order ' . $params['invoiceid'];
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['postData' => $postData], __FUNCTION__ . '::' . 'Refund postData');

        /* Make the server request. */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Authorization: ' . $apiKey]);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        /* Send the request. */
        $response = curl_exec($ch);
        curl_close($ch);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $response], __FUNCTION__ . '::' . 'Refund response');
        /* Decode the response. */
        $response = json_decode($response);

        /* Check if the decryption was successful, the response code is 200 and message is 'Success'. */
        if ((NULL !== $response) && (200 == $response->code) && ('Success' == $response->message)) {
            /* Log message and create a response array. */
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $response], __FUNCTION__ . '::' . 'Refund successful');
            return ['status' => 'success', 'rawdata' => $response->data, 'transid' => $response->data->transactionId];
        } else {
            /* Log message and create a response array. */
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $response], __FUNCTION__ . '::' . 'Refund failed');
            return ['status' => 'declined', 'rawdata' => $response->data];
        }
    }


    /**
     * Function that cancela a subscription.
     *
     * @param params Array containing all the data needed to build the request.
     *
     * @return JSON Response received from server.
     */
    public function cancel($params)
    {
        /** Import helper class. */
        require_once(__DIR__ . "/Twispay_Notification.php");
        require_once(__DIR__ . "/Twispay_Config.php");

        /** Read the configuration values. */
        $apiKey = Twispay_Config::getApiKey();
        if ('' == $apiKey) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], __FUNCTION__ . '::' . 'Configuration Error');
            die(Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR'));
        }

        /** Compose the request URL. */
        $url  = Twispay_Config::getApiUrl() . '/order/' . $params['subscriptionID'];

        logTransaction(/*gatewayName*/'twispay', /*debugData*/['url' => $url], __FUNCTION__ . '::' . 'Cancel subscription url');

        /* Create the DELETE data arguments. */
        $postData = 'message=' . Twispay_Notification::translate('TWISPAY_CANCEL_SUBSCRIPTION');
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['postData' => $postData], __FUNCTION__ . '::' . 'Cancel subscription postData');

        /* Make the server request. */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Authorization: ' . $apiKey]);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        /* Send the request. */
        $response = curl_exec($ch);
        curl_close($ch);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $response], __FUNCTION__ . '::' . 'Cancel subscription response');
        /* Decode the response. */
        $response = json_decode($response);

        /* Check if the decryption was successful, the response code is 200 and message is 'Success'. */
        if ((NULL !== $response) && (200 == $response->code) && ('Success' == $response->message)) {
            /* Log message and create a response array. */
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $response], __FUNCTION__ . '::' . 'Cancel subscription successful');
            return ['status' => 'success', 'rawdata' => $response->data, 'transid' => $response->data->transactionId];
        } else {
            /* Log message and create a response array. */
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $response], __FUNCTION__ . '::' . 'Cancel subscription failed');
            return ['status' => 'declined', 'rawdata' => $response->data];
        }
    }
}
endif; /* End if class_exists. */
