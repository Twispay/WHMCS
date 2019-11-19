<?php
/**
 * Twispay english messages files.
 *
 * @package     Twispay_Payment_Gateway
 * @author      Twispay
 */

$_LANG['TWISPAY_PLUGIN_NOT_ACTIVATED'] = 'Payment failed: The plugin is not activated.';
$_LANG['TWISPAY_CONFIGURATION_ERROR'] = 'Payment failed: Incomplete or missing configuration.';

/** General message. */
$_LANG['TWISPAY_GENERAL_ERROR_TITLE']          = 'An error has occured:';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_F']         = 'The payment could not be processed. Please';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_TRY_AGAIN'] = ' try again';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_OR']        = ' or';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_CONTACT']   = ' contact';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_S']         = ' the website administrator.';

/** Errors */
$_LANG['TWISPAY_NULL_RESPONSE'] = 'NULL response received.';
$_LANG['TWISPAY_DECRIPTION_FAILED'] = 'Failed to decript the response.';
$_LANG['TWISPAY_VALIDATION_FAILED'] = 'Failed to validate the response.';
$_LANG['TWISPAY_TRANSACTION_PROCESSED'] = 'Transaction allready processed.';
$_LANG['TWISPAY_RECURRENT_NOT_SUPPORTED'] = 'Recurrent orders not suported.';
$_LANG['TWISPAY_TRANSACTION_PARENT'] = 'Failed to extract the parent of the transaction.';
/** Validation errors. */
$_LANG['TWISPAY_EMPTY_STATUS'] = '[RESPONSE-ERROR]: Empty status';
$_LANG['TWISPAY_EMPTY_IDENTIFIER'] = '[RESPONSE-ERROR]: Empty identifier';
$_LANG['TWISPAY_EMPTY_EXTERNAL_ORDER_ID'] = '[RESPONSE-ERROR]: Empty externalOrderId';
$_LANG['TWISPAY_EMPTY_TRANSACTION_ID'] = '[RESPONSE-ERROR]: Empty transactionId';
$_LANG['TWISPAY_EMPTY_AMOUNT'] = '[RESPONSE-ERROR]: Empty amount';
$_LANG['TWISPAY_WRONG_STATUS'] = '[RESPONSE-ERROR]: Wrong status: ';
$_LANG['TWISPAY_RESPONSE_DATA'] = '[RESPONSE]: Data: ';
$_LANG['TWISPAY_VALIDATION_COMPLETE'] = '[RESPONSE]: Validating completed for order ID: ';

/** Responses */
$_LANG['TWISPAY_STATUS_FAILED'] = '[RESPONSE]: Status failed for order ID:';
$_LANG['TWISPAY_STATUS_SUCCESS'] = '[RESPONSE]: Status success for order ID:';
$_LANG['TWISPAY_STATUS_CANCEL'] = '[RESPONSE]: Status cancel-ok for order ID:';
$_LANG['TWISPAY_STATUS_PENDING'] = '[RESPONSE]: Status three-d-pending for order ID:';
$_LANG['TWISPAY_STATUS_REFUND'] = '[RESPONSE]: Status refund-ok for order ID:';
