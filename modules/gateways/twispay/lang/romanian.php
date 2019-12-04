<?php
/**
 * Twispay romanian messages files.
 *
 * @package     Twispay_Payment_Gateway
 * @author      Twispay
 */

 /** General message. */
$_LANG['TWISPAY_SERVER_STATUS']                = ' Status server: ';
$_LANG['TWISPAY_GENERAL_ERROR_TITLE']          = 'A aparut o eroare:';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_F']         = 'Plata nu a putut fi procesata. Va rugam';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_TRY_AGAIN'] = ' incercati din nou';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_OR']        = ' sau';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_CONTACT']   = ' contactati';
$_LANG['TWISPAY_GENERAL_ERROR_DESC_S']         = ' administratorul site-ului.';
$_LANG['TWISPAY_CANCEL_SUBSCRIPTION']          = ' Anulare a abonamentului initiata din magazin.';

/** Errors */
$_LANG['TWISPAY_PLUGIN_NOT_ACTIVATED'] = 'Plata esuata: Plugin-ul nu este activat.';
$_LANG['TWISPAY_CONFIGURATION_ERROR'] = 'Plata esuata: Configuratie lipsa sau incompleta.';
$_LANG['TWISPAY_NULL_RESPONSE'] = 'Raspunsul primit este gol.';
$_LANG['TWISPAY_DECRIPTION_FAILED'] = 'Decriptarea raspunsului a esuat.';
$_LANG['TWISPAY_VALIDATION_FAILED'] = 'Validarea raspunsului a esuat.';
$_LANG['TWISPAY_TRANSACTION_PROCESSED'] = 'Tranzactie deja procesata.';
$_LANG['TWISPAY_RECURRENT_NOT_SUPPORTED'] = 'Comenzile recurente su sunt suportate.';
$_LANG['TWISPAY_TRANSACTION_PARENT'] = 'Extragerea parintelui tranzactiei a esuat.';
$_LANG['TWISPAY_SUBSCRIPTION_STATUS'] = 'Extragerea starii abonamentului a esuat.';
$_LANG['TWISPAY_WRONG_PERIOD'] = 'Ciclu de abonement neasteptat.';

/** Validation errors. */
$_LANG['TWISPAY_EMPTY_STATUS'] = '[RASPUNS-EROARE]: Lipseste starea';
$_LANG['TWISPAY_EMPTY_IDENTIFIER'] = '[RASPUNS-EROARE]: Lipseste identificatorul';
$_LANG['TWISPAY_EMPTY_EXTERNAL_ORDER_ID'] = '[RASPUNS-EROARE]: Lipseste externalOrderId';
$_LANG['TWISPAY_EMPTY_TRANSACTION_ID'] = '[RASPUNS-EROARE]: Lipeste transactionId';
$_LANG['TWISPAY_EMPTY_AMOUNT'] = '[RESPONSE-ERROR]: Lipseste suma';
$_LANG['TWISPAY_WRONG_STATUS'] = '[RASPUNS-EROARE]: Stare gresita: ';
$_LANG['TWISPAY_RESPONSE_DATA'] = '[RASPUNS]: Data: ';
$_LANG['TWISPAY_VALIDATION_COMPLETE'] = '[RASPUNS]: Validare completa pentru comanda ID: ';

/** Responses */
$_LANG['TWISPAY_STATUS_FAILED'] = '[RASPUNS]: Stare de "esuare" pentru comanda cu ID-ul:';
$_LANG['TWISPAY_STATUS_SUCCESS'] = '[RAPUNS]: Stare de "succes" pentru comanda cu ID-ul:';
$_LANG['TWISPAY_STATUS_CANCEL'] = '[RAPUNS]: Stare de "anulare" pentru comanda cu ID-ul:';
$_LANG['TWISPAY_STATUS_PENDING'] = '[RAPUNS]: Stare de "plata in asteptare" pentru comanda cu ID-ul:';
$_LANG['TWISPAY_STATUS_REFUND'] = '[RESPONSE]: Stare de "plata rambursata" pentru comanda cu ID-ul:';
