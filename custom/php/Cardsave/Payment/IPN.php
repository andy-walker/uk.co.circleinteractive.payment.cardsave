<?php

class Cardsave_Payment_IPN {

    public static $_paymentProcessor = null;
    protected     $component;
    
    public function main($module) {
        watchdog('Cardsave IPN', 'module = ' . $module . ', data = <pre>' . print_r($_REQUEST, true) . '</pre>');
    }

}   