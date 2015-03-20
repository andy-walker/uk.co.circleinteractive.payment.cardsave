<?php

/* 
 * Cardsave Extension for CiviCRM - Circle Interactive 2015
 * Author: andyw@circle
 *
 * Distributed under AGPL
 * http://www.gnu.org/licenses/agpl-3.0.html
 */

define('CARDSAVE_EXTENSION_NAME', 'uk.co.circleinteractive.payment.cardsave');
define('CARDSAVE_EXTENSION_DIR', __DIR__);

/**
 * Implementation of hook_civicrm_config
 */
function cardsave_civicrm_config(&$config) {

    # initialize include path
    set_include_path(__DIR__ . '/custom/php/' . PATH_SEPARATOR . get_include_path());

    # initialize template path
    $templates = &CRM_Core_Smarty::singleton()->template_dir;
    
    if (!is_array($templates))
        $templates = array($templates);
    
    array_unshift($templates, __DIR__ . '/custom/templates');

    # register autoloader for owned classes
    spl_autoload_register(function($class) {
        if (strpos($class, 'Cardsave_') === 0)
            if ($file = stream_resolve_include_path(strtr($class, '_', '/') . '.php')) 
                require_once $file;
    });
}

/**
 * Implementation of hook_civicrm_managed
 */
function cardsave_civicrm_managed(&$entities) {

    $entities[] = array(
        
        'module'  => CARDSAVE_EXTENSION_NAME,
        'name'    => 'Cardsave',
        'entity'  => 'PaymentProcessorType',
        'params'  => array(
            
            'version'         => 3,
            'name'            => 'Cardsave',
            'title'           => 'Cardsave',
            'description'     => 'Cardsave payment processor integration',
            'class_name'      => 'Payment_Cardsave',
            'billing_mode'    => 'notify',
            'user_name_label' => 'Merchant ID',
            'password_label'  => 'Password',
            'signature_label' => 'Pre-Shared Key',
            'is_recur'        => 0,
            'payment_type'    => 1,
            
            'url_site_default'      => 'https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx',
            'url_site_test_default' => 'https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx'

        )

    );

}

/**
 * Implementation of hook_civicrm_xmlMenu
 */
function cardsave_civicrm_xmlMenu(&$files) {
    $files[] = __DIR__ . '/custom/xml/routes.xml';
}

