<?php 

/**
 * Print HTML notices.
 *
 * @package  Twispay_Payment_Gateway 
 * @author   Twistpay
 */
class Twispay_Notification
{
    /**
     * Print a HTML notice with cart redirect button.
     *
     * @param text: Notice content.
     * @param retry_url: URL of the notice redirect button that is printed.
     *
     * @return void
     */
    public static function notice_to_cart($text = '', $retry_url = '')
    {
        if (!strlen($retry_url)) {
            $retry_url = $CONFIG["SystemURL"] . 'cart.php?a=view';
        }
        Twispay_Notification::print_notice($retry_url, $text);
    }


    /**
     * Print a HTML notice with checkout redirect button.
     *
     * @param text: Notice content.
     * @param retry_url: URL of the notice redirect button that is printed.
     *
     * @return void
     */
    public static function notice_to_checkout($text = '', $retry_url = '')
    {
        if (!strlen($retry_url)) {
            $retry_url = $CONFIG["SystemURL"] . 'cart.php?a=checkout';
        }
        Twispay_Notification::print_notice($retry_url, $text);
    }
    /**
     * Prints HTML notice.
     *
     * @param retry_url: URL of the notice redirect button that is printed.
     * @param text: Notice content.
     *
     * @return void
     */
    private static function print_notice($retry_url, $text)
    {
        /** Include the module texts. */
        require_once(__DIR__ . "/../lang/english.php");
        require_once(__DIR__ . "/../lang/romanian.php");
        /** Import helper class. */
        require_once(__DIR__ . "/Twispay_Config.php");
        ?>
          <div class="error notice" style="margin-top: 20px;">
              <h3><?= $_LANG['TWISPAY_GENERAL_ERROR_TITLE']; ?></h3>
              <?php if (strlen($text)) { ?>
                  <span><?= $_LANG[$text]; ?></span>
              <?php } ?>

              <?php if ('' == Twispay_Config::getContactEmail()) { ?>
                  <p><?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_F']; ?> <a href="<?= $retry_url; ?>"><?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_TRY_AGAIN']; ?></a> <?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_OR'] . $_LANG['TWISPAY_GENERAL_ERROR_DESC_CONTACT'] . $_LANG['TWISPAY_GENERAL_ERROR_DESC_S']; ?></p>
              <?php } else { ?>
                  <p><?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_F']; ?> <a href="<?= $retry_url; ?>"><?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_TRY_AGAIN']; ?></a> <?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_OR']; ?> <a href="mailto:<?= Twispay_Config::getContactEmail(); ?>"><?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_CONTACT']; ?></a> <?= $_LANG['TWISPAY_GENERAL_ERROR_DESC_S']; ?></p>
              <?php } ?>
          </div>
        <?php
    }
}
