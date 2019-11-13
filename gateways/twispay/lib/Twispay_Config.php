<?php 

/**
 * Twispay payment gateway configuration file.
 *
 * @package     Twispay_Payment_Gateway
 * @author      Twispay
 */
class Twispay_Config
{
    /* The URLs for production and staging. */
    private static $LIVE_HOST_NAME = 'https://secure.twispay.com';
    private static $STAGE_HOST_NAME = 'https://secure-stage.twispay.com';

    /* The API URLs for production and staging. */
    private static $LIVE_API_HOST_NAME = 'https://api.twispay.com';
    private static $STAGE_API_HOST_NAME = 'https://api-stage.twispay.com';


    /**
     * Function that extracts the value of the "live_mode" from
     *  the config.
     * 
     * @return Bool|NULL The value of the live mode parameter.
     */
    public static function getLiveMode()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        return (isset($params['live_mode'])) ? (('Yes' === $params['live_mode']) ? (TRUE) : (FALSE)) : (NULL);
    }


    /**
     * Function that extracts the value "contact_email" from
     *  the config.
     * 
     * @return String The value of the contact email parameter.
     */
    public static function getContactEmail()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        return (isset($params['contact_email'])) ? ($params['contact_email']) : ('');
    }


    /**
     * Function that extracts the value "redirect_page" from
     *  the config.
     * 
     * @return String The value of the success page parameter.
     */
    public static function getSuccessPage()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        return (isset($params['redirect_page'])) ? ($params['redirect_page']) : ('');
    }


    /**
     * Function that extracts the value of the "secret_key" from
     *  the config depending of the "live_mode" value.
     * 
     * @return String The value of the secret key parameter.
     */
    public static function getApiKey()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        if (TRUE === self::getLiveMode($params)) {
            return $params['live_secret_key'];
        } elseif (FALSE === self::getLiveMode($params)) {
            return $params['staging_secret_key'];
        } else {
            '';
        }
    }


    /**
     * Function that extracts the value of the "site_id" from
     *  the config depending of the "live_mode" value.
     *
     * @return String The value of the site ID parameter.
     */
    public static function getSiteId()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        if (TRUE === self::getLiveMode($params)) {
            return $params['live_site_id'];
        } elseif (FALSE === self::getLiveMode($params)) {
            return $params['staging_site_id'];
        } else {
            '';
        }
    }


    /**
     * Function that extracts the value of the "url"
     *  depending of the "live_ode" value.
     *
     * @return String The value of the redirect URL parameter.
     */
    public static function getRedirectUrl()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        if (TRUE === self::getLiveMode($params)) {
            return self::$LIVE_HOST_NAME;
        } elseif (FALSE === self::getLiveMode($params)) {
            return self::$STAGE_HOST_NAME;
        } else {
            '';
        }
    }


    /**
     * Function that extracts the value of the "api url"
     *  depending of the "live_ode" value.
     *
     * @return String The value of the api URL parameter.
     */
    public function getApiUrl()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        if (TRUE === self::getLiveMode($params)) {
            return self::$LIVE_API_HOST_NAME;
        } elseif (FALSE === self::getLiveMode($params)) {
            return self::$STAGE_API_HOST_NAME;
        } else {
            '';
        }
    }


    /**
     * Function that returns the backUrl.
     *
     * @return String The backURL.
     */
    public function getBackUrl()
    {
        /** Extract the configuration values. */
        $params = getGatewayVariables('twispay');

        return $params['systemurl'] . 'modules/gateways/callback/' . $params['paymentmethod'] . '.php';
    }
}
