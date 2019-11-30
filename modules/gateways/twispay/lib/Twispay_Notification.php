<?php

/**
 * Print HTML notices.
 *
 * @package  Twispay_Payment_Gateway
 * @author   Twistpay
 */
if ( ! class_exists( 'Twispay_Notification' ) ) : /* Security class check */
class Twispay_Notification
{
    /**
     * Function that prints a HTML notice with cart redirect button.
     *
     * @param text: Notice content.
     * @param extra: Extra data to be appended to the error message.
     *
     * @return void
     */
    public static function notice_to_cart($text = '', $extra = '')
    {
        Twispay_Notification::print_notice(App::getSystemUrl() . 'cart.php?a=view', $text, $extra);
    }


    /**
     * Function that prints a HTML notice with checkout redirect button.
     *
     * @param text: Notice content.
     * @param extra: Extra data to be appended to the error message.
     *
     * @return void
     */
    public static function notice_to_checkout($text = '', $extra = '')
    {
        Twispay_Notification::print_notice(App::getSystemUrl() . 'cart.php?a=checkout', $text, $extra);
    }


    /**
     * Function that returns a translated message.
     *
     * @param key: The key for identifing the message.
     *
     * @return String|'' The transtaled message if found or empty string.
     */
    public static function translate($key)
    {
        /** Include the module texts. */
        if ('romanian' == $CONFIG["Language"]) {
            require(__DIR__ . "/../lang/romanian.php");
        } else {
            require(__DIR__ . "/../lang/english.php");
        }

        return (isset($_LANG[$key]) ? ($_LANG[$key]) : ($key));
    }


    /**
     * Prints HTML notice.
     *
     * @param retry_url: URL of the notice redirect button that is printed.
     * @param text: Notice content.
     * @param extra: Extra data to be appended to the error message.
     *
     * @return void
     */
    private static function print_notice($retry_url, $text, $extra)
    {
        /** Include the module texts. */
        if ('romanian' == $CONFIG["Language"]) {
            require(__DIR__ . "/../lang/romanian.php");
        } else {
            require(__DIR__ . "/../lang/english.php");
        }
        /** Import helper class. */
        require_once(__DIR__ . "/Twispay_Config.php");


        echo '<div class="error notice" style="margin-top: 20px;">';
        echo '<h3>' . $_LANG['TWISPAY_GENERAL_ERROR_TITLE'] . '</h3>';
        if (strlen($text)) {
            echo '<span>' . $_LANG[$text] . $extra . '</span>';
        }

        if ('' == Twispay_Config::getContactEmail()) {
            echo '<p>' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_F'] . '<a href="' . $retry_url . '">' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_TRY_AGAIN'] . '</a>' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_OR'] . $_LANG['TWISPAY_GENERAL_ERROR_DESC_CONTACT'] . $_LANG['TWISPAY_GENERAL_ERROR_DESC_S'] . '</p>';
        } else {
            echo '<p>' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_F'] . '<a href="' . $retry_url . '">' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_TRY_AGAIN'] . '</a>' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_OR'] . '<a href="mailto:' . Twispay_Config::getContactEmail() . '">' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_CONTACT'] . '</a>' . $_LANG['TWISPAY_GENERAL_ERROR_DESC_S'] . '</p>';
        }
        echo '</div';
    }
}
endif; /* End if class_exists. */
