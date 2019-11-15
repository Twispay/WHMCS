<?php 

/**
 * Twispay payment gateway request builder file.
 *
 * @package     Twispay_Payment_Gateway
 * @author      Twispay
 */
if ( ! class_exists( 'Twispay_Request' ) ) : /* Security class check */
class Twispay_Request
{
    /************************** Helper functions START **************************/
    /**
     * Get the `jsonRequest` parameter (order parameters as JSON and base64 encoded).
     *
     * @param orderData Array containing the order parameters.
     *
     * @return string
     */
    private static function getBase64JsonRequest(array $orderData)
    {
        return base64_encode(json_encode($orderData));
    }


    /**
     * Get the `checksum` parameter (the checksum computed over the `jsonRequest` and base64 encoded).
     *
     * @param orderData Array containing the order parameters.
     * @param secretKey The secret key (from Twispay) in string format.
     *
     * @return string
     */
    private static function getBase64Checksum(array $orderData, $secretKey)
    {
        $hmacSha512 = hash_hmac(/*algo*/'sha512', json_encode($orderData), $secretKey, /*raw_output*/true);
        return base64_encode($hmacSha512);
    }
    /************************** Helper functions END **************************/


    /**
     * Function that builds the JSON that needs to be sent to the server
     *  for a purchase command.
     * 
     * @param params Array containing all the data needed to build the request.
     * 
     * @return Array Containing encoded 'jsonRequest' the 'checksum' and the 'url'.
     */
    public static function purchaseRequest($params)
    {
        /** Import helper classes. */
        require_once(__DIR__ . "/Twispay_Config.php");
        require_once(__DIR__ . "/Twispay_Notification.php");

        /** Read the configuration values. */
        $url = Twispay_Config::getRedirectUrl();
        $siteId = Twispay_Config::getSiteId();
        $apiKey = Twispay_Config::getApiKey();

        if(('' == $siteId) || ('' == $apiKey)){
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'liveMode' => $params['live_mode'], 'siteId' => $siteId, 'apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], "Configuration Error");
            Twispay_Notification::notice_to_checkout('TWISPAY_CONFIGURATION_ERROR');
            /** Stop the execution. */
            die();
        }
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'liveMode' => $params['live_mode'], 'siteId' => $siteId, 'apiKey' => $apiKey], "Configuration Read");

        /** Extract the customer details. */
        $customer = [ 'identifier' => 'p_wh_' . $params['clientdetails']['userid'] . '_' . date('YmdHis')
                    , 'firstName' => $params['clientdetails']['firstname']
                    , 'lastName' => $params['clientdetails']['lastname']
                    , 'country' => $params['clientdetails']['countrycode']
                    , 'city' => $params['clientdetails']['city']
                    , 'address' => (!empty($params['clientdetails']['address2'])) ? ($params['clientdetails']['address1'] . ', ' . $params['clientdetails']['address2']) : ($params['clientdetails']['address1'])
                    , 'zipCode' => preg_replace("/[^0-9]/", '', $params['clientdetails']['postcode'])
                    , 'phone' => '+' . preg_replace('/([^0-9]*)+/', '', $params['clientdetails']['phonenumber'])
                    , 'email' => $params['clientdetails']['email']
                    ];

        /** Extract the invoice transactions. */
        $invoice = localAPI(/*command*/'GetInvoice', /*postData*/['invoiceid' => $params['invoiceid']]);

        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'invoice' => $invoice], "Invoice Extracted");

        /** Extract the invoice items details. */
        $items = [];
        foreach($invoice['items']['item'] as $item) {
            $items[] = [ 'item' => $item['description']
                       , 'units' =>  1
                       , 'unitPrice' => (string) number_format((float) $item['amount'], 2, '.', '')
                       ];
        }

        /* Build the data object to be posted to Twispay. */
        $orderData = [ 'siteId' => $siteId
                     , 'customer' => $customer
                     , 'order' => [ 'orderId' => $params['invoiceid']
                                  , 'type' => 'purchase'
                                  , 'amount' => $params['amount']
                                  , 'currency' => $params['currency']
                                  , 'items' => $items
                                  ]
                     , 'cardTransactionMode' => 'authAndCapture'
                     , 'invoiceEmail' => ''
                     , 'backUrl' => Twispay_Config::getBackUrl()
                     ];
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'orderData' => $orderData], "Request JSON Completed");

        /* Encode the data and calculate the checksum. */
        $jsonRequest = self::getBase64JsonRequest($orderData);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'jsonRequest' => $jsonRequest], "Encoded JSON Request");
        $checksum = self::getBase64Checksum($orderData, $apiKey);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'checksum' => $checksum], "Encoded JSON Checksum");

        return ['jsonRequest' => $jsonRequest, 'checksum' => $checksum, 'url' => $url];
    }
}
endif; /* End if class_exists. */
