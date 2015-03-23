<?php

class Cardsave_Payment_IPN {

    public static $_paymentProcessor = null;
    protected     $component;
    
    public function main($module) {
        CRM_Core_Error::debug_log_message('Cardsave IPN: module = ' . $module . ', data = ' . print_r($_REQUEST, true));
    }

}   