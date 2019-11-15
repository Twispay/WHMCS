<?php 

/**
 * Twispay payment gateway responce helper file.
 *
 * @package     Twispay_Payment_Gateway
 * @author      Twispay
 */
if ( ! class_exists( 'Twispay_Response' ) ) : /* Security class check */
class Twispay_Response
{
    /** Array containing the possible result statuses. */
    private static $resultStatuses = [ 'UNCERTAIN' => 'uncertain' /** No response from provider */
                                     , 'IN_PROGRESS' => 'in-progress' /** Authorized */
                                     , 'COMPLETE_OK' => 'complete-ok' /** Captured */
                                     , 'COMPLETE_FAIL' => 'complete-failed' /** Not authorized */
                                     , 'CANCEL_OK' => 'cancel-ok' /** Capture reversal */
                                     , 'REFUND_OK' => 'refund-ok' /** Settlement reversal */
                                     , 'VOID_OK' => 'void-ok' /** Authorization reversal */
                                     , 'CHARGE_BACK' => 'charge-back' /** Charge-back received */
                                     , 'THREE_D_PENDING' => '3d-pending' /** Waiting for 3d authentication */
                                     , 'EXPIRING' => 'expiring' /** The recurring order has expired */
    ];


    /**
     * Decrypt the response from Twispay server.
     *
     * @param tw_encryptedMessage: - The encripted server message.
     * @param tw_secretKey:        - The secret key (from Twispay).
     *
     * @return Array([key => value,]) - If everything is ok array containing the decrypted data.
     *         bool(FALSE)            - If decription fails.
     */
    public static function decrypt($tw_encryptedMessage, $tw_secretKey)
    {
        $encrypted = (string)$tw_encryptedMessage;

        if(!strlen($encrypted) || (FALSE === strpos($encrypted, ','))) {
            return FALSE;
        }

        /** Get the IV and the encrypted data */
        $encryptedParts = explode(/*delimiter*/',', $encrypted, /*limit*/2);
        $iv = base64_decode($encryptedParts[0]);
        if(FALSE === $iv) {
            return FALSE;
        }

        $encryptedData = base64_decode($encryptedParts[1]);
        if(FALSE === $encryptedData) {
           return FALSE;
        }

        /** Decrypt the encrypted data */
        $decryptedResponse = openssl_decrypt($encryptedData, /*method*/'aes-256-cbc', $tw_secretKey, /*options*/OPENSSL_RAW_DATA, $iv);
        if(FALSE === $decryptedResponse) {
           return FALSE;
        }

        /** JSON decode the decrypted data. */
        $decodedResponse = json_decode($decryptedResponse, /*assoc*/TRUE, /*depth*/4);

        /** Check if the decryption was successful. */
        if (NULL === $decodedResponse) {
            return FALSE;
        }

        return $decodedResponse;
    }


    /**
     * Function that validates a decripted response.
     *
     * @param tw_response The server decripted and JSON decoded response
     *
     * @return bool(FALSE)     - If any error occurs
     *         bool(TRUE)      - If the validation is successful
     */
    public static function validate($tw_response)
    {
        /** Import helper classes. */
        require_once(__DIR__ . "/Twispay_Notification.php");
        $tw_errors = array();

        if (!$tw_response) {
            return FALSE;
        }

        if (empty($tw_response['status']) && empty($tw_response['transactionStatus'])) {
            $tw_errors[] = Twispay_Notification::translate('TWISPAY_EMPTY_STATUS');
        }

        if (empty($tw_response['identifier'])) {
            $tw_errors[] = Twispay_Notification::translate('TWISPAY_EMPTY_IDENTIFIER');
        }

        if (empty($tw_response['externalOrderId'])) {
          $tw_errors[] = Twispay_Notification::translate('TWISPAY_EMPTY_EXTERNAL_ORDER_ID');
        }

        if (empty($tw_response['transactionId'])) {
          $tw_errors[] = Twispay_Notification::translate('TWISPAY_EMPTY_TRANSACTION_ID');
        }

        if (empty($tw_response['amount'])) {
          $tw_errors[] = Twispay_Notification::translate('TWISPAY_EMPTY_AMOUNT');
        }

        if (sizeof($tw_errors)) {
            logTransaction(/*gatewayName*/'twispay', /*debugData*/['response' => $tw_response, 'errors' => $tw_errors], "Validation Failed");

            return FALSE;
        } else {
            $data = [ 'externalOrderId' => explode('_', $tw_response['externalOrderId'])[0]
                    , 'status'          => (empty($tw_response['status'])) ? ($tw_response['transactionStatus']) : ($tw_response['status'])
                    , 'identifier'      => $tw_response['identifier']
                    , 'orderId'         => (int)$tw_response['orderId']
                    , 'transactionId'   => (int)$tw_response['transactionId']
                    , 'customerId'      => (int)$tw_response['customerId']
                    , 'cardId'          => (!empty($tw_response['cardId'])) ? (( int )$tw_response['cardId']) : (0)
                    , 'amount'          => (int)$tw_response['amount']];

            logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_RESPONSE_DATA') . json_encode($data)], "Response Data");

            if (!in_array($data['status'], self::$resultStatuses)) {
                logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_WRONG_STATUS') . $data['status']], "Wrong status");

                return FALSE;
            }

            logTransaction(/*gatewayName*/'twispay', /*debugData*/['message' => Twispay_Notification::translate('TWISPAY_VALIDATION_COMPLETE') . $data['externalOrderId']], "Validation Completed");

            return TRUE;
        }
    }


    /**
     * Functin that processes a back URL respone for a purchase order.
     *
     * @param invoiceId: The ID of the invoice for which the response if for.
     * @param response: The decrypted server response.
     *
     * @return bool(FALSE)     - If server status in: [COMPLETE_FAIL, THREE_D_PENDING]
     *         bool(TRUE)      - If server status in: [IN_PROGRESS, COMPLETE_OK]
     */
    public static function procesResponse_purchase_backUrl($invoiceId, $response)
    {
        switch ($response['status']) {
            case self::$resultStatuses['COMPLETE_FAIL']:
            case self::$resultStatuses['THREE_D_PENDING']:
                /** Save the transaction. */
                logTransaction(/*gatewayName*/'twispay', /*debugData*/$response, $response['status'], ['message' => Twispay_Notification::translate('TWISPAY_STATUS_FAILED') . $invoiceId]);
                return FALSE;
            break;

            case self::$resultStatuses['IN_PROGRESS']:
            case self::$resultStatuses['COMPLETE_OK']:
              /** Add payment. */
              addInvoicePayment($invoiceId, $response['transactionId'], $response['amount'], /*fees*/0, /*gateway*/'twispay');
              /** Save the transaction. */
              logTransaction(/*gatewayName*/'twispay', /*debugData*/$response, $response['status'], ['message' => Twispay_Notification::translate('TWISPAY_STATUS_SUCCESS') . $invoiceId]);
              return TRUE;
            break;

            default:
                /** Save the transaction. */
                logTransaction(/*gatewayName*/'twispay', /*debugData*/$response, $response['status'], ['message' => Twispay_Notification::translate('TWISPAY_WRONG_STATUS') . $response['status']]);
                return FALSE;
            break;
        }
    }
}
endif; /* End if class_exists. */
