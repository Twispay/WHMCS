<?php
/**
 * Register hook function call.
 *
 * @param string $hookPoint The hook point to call
 * @param integer $priority The priority for the given hook function
 * @param string|function Function name to call or anonymous function.
 *
 * @return Depends on hook function point.
 */
add_hook('DailyCronJob', 1, function($vars)
{
    /** Load libraries needed for gateway module functions. */
    require('../../../init.php');
    $whmcs->load_function('invoice');
    $whmcs->load_function('gateway');

    /** Import helper class. */
    require_once(__DIR__ . "/twispay/lib/Twispay_Api.php");

    /** Extract all the services that are 'Pending' or 'Active'. */
    $services = WHMCS\Service\Service::where('paymentmethod', 'twispay')->whereNotNull('subscriptionid')->get();

    foreach ($services as $key => $service) {
        $serviceStatus = Twispay_Api::refund($service->subscriptionid);
    }
});
