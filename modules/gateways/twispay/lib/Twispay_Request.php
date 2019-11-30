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
    /** Array containing the possible result statuses. */
    private static $intervalTypes = ['DAY' => 'day', 'MONTH' => 'month'];


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

        if (('' == $siteId) || ('' == $apiKey)) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'liveMode' => $params['live_mode'], 'siteId' => $siteId, 'apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], __FUNCTION__ . '::' . "Configuration error");
            Twispay_Notification::notice_to_checkout('TWISPAY_CONFIGURATION_ERROR');
            /** Stop the execution. */
            die();
        }
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'liveMode' => $params['live_mode'], 'siteId' => $siteId, 'apiKey' => $apiKey], __FUNCTION__ . '::' . "Configuration read");

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

        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'invoice' => $invoice], __FUNCTION__ . '::' . "Invoice extracted");

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
                                  , 'amount' => number_format(floatval($params['amount']), 2, '.', '')
                                  , 'currency' => $params['currency']
                                  , 'items' => $items
                                  ]
                     , 'cardTransactionMode' => 'authAndCapture'
                     , 'invoiceEmail' => ''
                     , 'backUrl' => Twispay_Config::getBackUrl()
                     ];
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'orderData' => $orderData], __FUNCTION__ . '::' . "Request JSON completed");

        /* Encode the data and calculate the checksum. */
        $jsonRequest = self::getBase64JsonRequest($orderData);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'jsonRequest' => $jsonRequest], __FUNCTION__ . '::' . "Encoded JSON request");
        $checksum = self::getBase64Checksum($orderData, $apiKey);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'checksum' => $checksum], __FUNCTION__ . '::' . "Encoded JSON checksum");

        return ['jsonRequest' => $jsonRequest, 'checksum' => $checksum, 'url' => $url];
    }


    /**
     * Function that builds the JSON that needs to be sent to the server
     *  for a recurring command.
     *
     * @param params Array containing all the data needed to build the request.
     *
     * @return Array Containing encoded 'jsonRequest' the 'checksum' and the 'url'.
     */
    public static function recurringRequest($params)
    {
        /** Import helper classes. */
        require_once(__DIR__ . "/Twispay_Config.php");
        require_once(__DIR__ . "/Twispay_Notification.php");

        /** Read the configuration values. */
        $url = Twispay_Config::getRedirectUrl();
        $siteId = Twispay_Config::getSiteId();
        $apiKey = Twispay_Config::getApiKey();

        if (('' == $siteId) || ('' == $apiKey)) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'liveMode' => $params['live_mode'], 'siteId' => $siteId, 'apiKey' => $apiKey, 'message' => Twispay_Notification::translate('TWISPAY_CONFIGURATION_ERROR')], __FUNCTION__ . '::' . "Configuration error");
            Twispay_Notification::notice_to_checkout('TWISPAY_CONFIGURATION_ERROR');
            /** Stop the execution. */
            die();
        }
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'liveMode' => $params['live_mode'], 'siteId' => $siteId, 'apiKey' => $apiKey], __FUNCTION__ . '::' . "Configuration read");

        /** Extract the customer details. */
        $customer = [ 'identifier' => 'r_wh_' . $params['clientdetails']['userid'] . '_' . date('YmdHis')
                    , 'firstName' => $params['clientdetails']['firstname']
                    , 'lastName' => $params['clientdetails']['lastname']
                    , 'country' => $params['clientdetails']['countrycode']
                    , 'city' => $params['clientdetails']['city']
                    , 'address' => (!empty($params['clientdetails']['address2'])) ? ($params['clientdetails']['address1'] . ', ' . $params['clientdetails']['address2']) : ($params['clientdetails']['address1'])
                    , 'zipCode' => preg_replace("/[^0-9]/", '', $params['clientdetails']['postcode'])
                    , 'phone' => '+' . preg_replace('/([^0-9]*)+/', '', $params['clientdetails']['phonenumber'])
                    , 'email' => $params['clientdetails']['email']
                    ];

        /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
        /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
        /* READ:  We presume that there will be ONLY ONE recurring profile product inside the order. */
        /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
        /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */

        /** Extract the recurringBillingValues for this invoice. */
        $recurringBillingValues = getRecurringBillingValues($params['invoiceid']);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['recurringBillingValues' => $recurringBillingValues], __FUNCTION__ . '::' . "Recurring billing values extracted");

        /** Extract the service for this invoice. */
        $service = WHMCS\Service\Service::findOrFail($recurringBillingValues['primaryserviceid']);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['service' => $service], __FUNCTION__ . '::' . "Service extracted");

        /** Extract the invoice transactions. */
        $invoice = localAPI(/*command*/'GetInvoice', /*postData*/['invoiceid' => $params['invoiceid']]);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'invoice' => $invoice], __FUNCTION__ . '::' . "Invoice extracted");

        /** Calculate the first billing date. */
        $daysTillFirstBillDate = 0;

        /** Set trial values to NULL. */
        $trialAmount = NULL;
        $firstBillDate = NULL;

        /** Check if there is a trial period. */
        if (isset($recurringBillingValues['firstcycleunits'])) {
            /** Save the trial amount. */
            $trialAmount = $recurringBillingValues['firstpaymentamount'];

            /** Set the first bill to today. */
            $firstBillDate = $service->registrationDate;

            /** Add 'firstCycleUnits' to the 'firstBillDate' date. */
            switch ($recurringBillingValues['firstcycleunits']) {
                case 'Days':
                    $firstBillDate->add(new DateInterval('P' . $recurringBillingValues['firstcycleperiod'] . 'D'));
                break;

                case 'Months':
                    $firstBillDate->add(new DateInterval('P' . $recurringBillingValues['firstcycleperiod'] . 'M'));
                break;

                case 'Years':
                    $firstBillDate->add(new DateInterval('P' . $recurringBillingValues['firstcycleperiod'] . 'Y'));
                break;

                default:
                    logTransaction(/*gatewayName*/'twispay', /*debugData*/['recurringBillingValues' => $recurringBillingValues['firstcycleunits']], __FUNCTION__ . '::' . "Unexpected subsbscription period");
                    Twispay_Notification::notice_to_checkout('TWISPAY_WRONG_PERIOD');
                    /** Stop the execution. */
                    die();
                break;
            }
        }

        /** Calculate the recurring profile's interval type and value. */
        $intervalType = '';
        $intervalValue = '';
        switch ($recurringBillingValues['recurringcycleunits']) {
            case 'Days':
                $intervalType = self::$intervalTypes['DAY'];
                $intervalValue = $recurringBillingValues['recurringcycleperiod'];
            break;

            case 'Months':
                $intervalType = self::$intervalTypes['MONTH'];
                $intervalValue = $recurringBillingValues['recurringcycleperiod'];
            break;

            case 'Years':
                $intervalType = self::$intervalTypes['MONTH'];
                $intervalValue = /*MONTHS/YEAR*/12 * $recurringBillingValues['recurringcycleperiod'];
            break;

            default:
                logTransaction(/*gatewayName*/'twispay', /*debugData*/['recurringBillingValues' => $recurringBillingValues['recurringcycleunits']], __FUNCTION__ . '::' . "Unexpected subsbscription period");
                Twispay_Notification::notice_to_checkout('TWISPAY_WRONG_PERIOD');
                /** Stop the execution. */
                die();
            break;
        }

        /* Build the data object to be posted to Twispay. */
        $orderData = [ 'siteId' => $siteId
                     , 'customer' => $customer
                     , 'order' => [ 'orderId' => $params['invoiceid']
                                  , 'type' => 'recurring'
                                  , 'amount' => number_format(floatval($recurringBillingValues['recurringamount']), 2, '.', '')
                                  , 'currency' => $params['currency']
                                  ]
                     , 'cardTransactionMode' => 'authAndCapture'
                     , 'invoiceEmail' => ''
                     , 'backUrl' => Twispay_Config::getBackUrl()
                     ];

        /* Add the recurring profile data. */
        $orderData['order']['intervalType'] = $intervalType;
        $orderData['order']['intervalValue'] = $intervalValue;
        if(NULL != $trialAmount){
            $orderData['order']['trialAmount'] = number_format(floatval($trialAmount), 2, '.', '');
            $orderData['order']['firstBillDate'] = $firstBillDate->format('c');
        }
        $orderData['order']['description'] = $intervalValue . " " . $intervalType . " subscription that contains: ";

        /** Extract the invoice items details. */
        foreach($invoice['items']['item'] as $item) {
            $orderData['order']['description'] .= $item['description'] . '(' . $item['amount'] . ' ' . $params['currency'] . '), ';
        }
        /** Remove last 2 characters. */
        substr($orderData['order']['description'], 0, -2);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['orderData' => $orderData], __FUNCTION__ . '::' . "Request JSON completed");

        /* Encode the data and calculate the checksum. */
        $jsonRequest = self::getBase64JsonRequest($orderData);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'jsonRequest' => $jsonRequest], __FUNCTION__ . '::' . "Encoded JSON request");
        $checksum = self::getBase64Checksum($orderData, $apiKey);
        logTransaction(/*gatewayName*/'twispay', /*debugData*/['invoiceid' => $params['invoiceid'], 'checksum' => $checksum], __FUNCTION__ . '::' . "Encoded JSON checksum");

        return ['jsonRequest' => $jsonRequest, 'checksum' => $checksum, 'url' => $url];
    }
}
endif; /* End if class_exists. */
