<?php

/**
 * Payment processor class for Cardsave
 * @author  andyw@circle
 * @package uk.co.circleinteractive.payment.cardsave
 */
class CRM_Core_Payment_Cardsave extends CRM_Core_Payment {

    protected $_mode           = null;
    static private $_singleton = null;

    /**
     * Constructor
     */
    public function __construct($mode, &$paymentProcessor) {

        $this->_mode             = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName    = 'Cardsave';
        
    }

    public static function &singleton($mode = 'test', &$paymentProcessor, &$paymentForm = null, $force = false) {
        
        $processorName = $paymentProcessor['name'];
        if (is_null(self::$_singleton[$processorName]))
            self::$_singleton[$processorName] = new CRM_Core_Payment_Cardsave($mode, $paymentProcessor);
        return self::$_singleton[$processorName];
    
    }
    

    public function checkConfig() {
        
        $errors = array();
        
        if (!$this->_paymentProcessor['user_name']) 
            $errors[] = 'No username supplied for Cardsave payment processor';
        
        if (!empty($errors)) 
            return '<p>' . implode('</p><p>', $errors) . '</p>';
        
        return null;
    
    }

    protected function createHash($transaction) {

        $values = array(
            'PreSharedKey'              => $this->_paymentProcessor['signature'],
            'MerchantID'                => $this->_paymentProcessor['user_name'],
            'Password'                  => $this->_paymentProcessor['password'],
            'Amount'                    => $transaction->Amount,
            'CurrencyCode'              => $transaction->CurrencyCode,
            'OrderID'                   => $transaction->OrderID,
            'TransactionType'           => $transaction->TransactionType,
            'TransactionDateTime'       => $transaction->TransactionDateTime,
            'CallbackURL'               => $transaction->CallbackURL,
            'OrderDescription'          => $transaction->OrderDescription,
            'CustomerName'              => $transaction->CustomerName,
            'Address1'                  => $transaction->Address1,
            'Address2'                  => $transaction->Address2,
            'Address3'                  => $transaction->Address3,
            'Address4'                  => $transaction->Address4,
            'City'                      => $transaction->City,
            'State'                     => $transaction->State,
            'PostCode'                  => $transaction->PostCode,
            'CountryCode'               => $transaction->CountryCode,
            'CV2Mandatory'              => $transaction->CV2Mandatory,
            'Address1Mandatory'         => $transaction->Address1Mandatory,
            'CityMandatory'             => $transaction->CityMandatory,
            'PostCodeMandatory'         => $transaction->PostCodeMandatory,
            'StateMandatory'            => $transaction->StateMandatory,
            'CountryMandatory'          => $transaction->CountryMandatory,
            'ResultDeliveryMethod'      => 'POST',
            'ServerResultURL'           => '',
            'PaymentFormDisplaysResult' => 'false'         
        );

        foreach ($values as $key => &$value)
            $value = "$key=$value";

        return sha1(implode('&', $values));

    }
  
    # not req'd for billingMode notify
    public function doDirectPayment(&$params) {}

    /**
     * Initialize transaction
     * @param array  $params     data relating to the transaction
     * @param string $component  'contribute' or 'event'
     */
    public function doTransferCheckout(&$params, $component = 'contribute') {
  
        if (!in_array($component, array('contribute', 'event')))
            CRM_Core_Error::fatal(ts('Component is invalid'));

        $config      = CRM_Core_Config::singleton();
        $transaction = &$_SESSION['cardsave_trxn'];

        $callbackParams = sprintf(
            'mo=%s&processor_id=%s',
            $component,
            $this->_paymentProcessor['id'] 
        );
 
        $callbackURL  = CRM_Utils_System::url('civicrm/payment/ipn', $callbackParams, true, null, false, true, false);
        $currencyCode = CRM_Core_DAO::singleValueQuery("
            SELECT numeric_code FROM civicrm_currency WHERE name = %1
        ", array(
              1 => array($params['currencyID'], 'String')
           )
        );
        
        # get contact 
        try {
            $contact = civicrm_api3('contact', 'getsingle', array(
                'id' => $params['contactID']
            ));
        } catch (CiviCRM_API3_Exception $e) {
            CRM_Core_Error::fatal(ts('Unable to get contact'));
        }

        try {
            $address = civicrm_api3('address', 'getsingle', array(
                'contact_id' => $params['contactID'],
                'is_primary' => 1
            ));
        } catch (CiviCRM_API3_Exception $e) {
            CRM_Core_Error::fatal(ts('Unable to get address details for contact'));
        }           
        
        $stateProvince = '';

        if (isset($address['state_province_id']) and !empty($address['state_province_id']))
            $stateProvince = CRM_Core_DAO::singleValueQuery("
                SELECT name FROM civicrm_state_province WHERE id = %1
            ", array(
                  1 => array($state_province_id, 'Positive')
               )
            );

        if (isset($address['country_id']) and !empty($address['country_id']))
            $country_iso_code = CRM_Core_PseudoConstant::countryIsoCode($address['country_id']);

        $transaction = (object)array(
            'GatewayURL'                => $this->_paymentProcessor['url_site'],
            'MerchantID'                => $this->_paymentProcessor['user_name'],
            'CurrencyCode'              => $currencyCode,
            'Amount'                    => $params['amount'] * 100,
            'OrderID'                   => $params['invoiceID'],
            'TransactionType'           => 'SALE',
            'TransactionDateTime'       => date('Y-m-d H:i:s P', strtotime($params['receive_date'])),
            'CallbackURL'               => $callbackURL,
            'OrderDescription'          => $params['description'],
            'CustomerName'              => $params['first_name'] . ' ' . $params['last_name'],
            'Address1'                  => $address['street_address'],
            'Address2'                  => isset($address['supplemental_address_1']) ? $address['supplemental_address_1'] : '',
            'Address3'                  => isset($address['supplemental_address_2']) ? $address['supplemental_address_2'] : '',
            'Address4'                  => isset($address['supplemental_address_3']) ? $address['supplemental_address_3'] : '',
            'City'                      => isset($address['city']) ? $address['city'] : '',
            'State'                     => $stateProvince,
            'PostCode'                  => $address['postal_code'],
            'CountryCode'               => isset($country_iso_code) ? $this->getCountryCode($country_iso_code) : '',
            'CV2Mandatory'              => 'false',
            'Address1Mandatory'         => 'false',
            'CityMandatory'             => 'false',
            'PostCodeMandatory'         => 'false',
            'StateMandatory'            => 'false',
            'CountryMandatory'          => 'false',
            'ResultDeliveryMethod'      => 'POST',
            'ServerResultURL'           => '',
            'PaymentFormDisplaysResult' => 'false'         
        );

        $transaction->HashDigest = $this->createHash($transaction);
        watchdog('andyw', 'transaction = <pre>' . print_r($transaction, true) . '</pre>');

        CRM_Utils_Hook::alterPaymentProcessorParams($this, $params, $transaction);
 
        # redirect to payment page
        CRM_Utils_System::redirect(
            CRM_Utils_System::url('civicrm/cardsave/redirect', null, true, null, false, true, false)
        );

    }

    protected function getCountryCode($iso_code) {
        
        static $cache = null;

        $lookup = function($iso_code) use (&$cache) {
            if (isset($cache->$iso_code))
                return $cache->$iso_code;
            CRM_Core_Error::fatal(ts('Unable to look up numeric country code for %1', array(1 => $iso_code)));
        };

        if ($cache) {
            return $lookup($iso_code);
        } elseif ($data = file_get_contents(CARDSAVE_EXTENSION_DIR . '/custom/json/countries.json')) {
            $cache = json_decode($data);
            return $lookup($iso_code);
        } else {
            CRM_Core_Error::fatal(ts('Unable to load country codes file'));
        }

    }

    /**
     * Handle payment notifications
     */
    public function handlePaymentNotification() {

        switch ($module = CRM_Utils_Array::value('mo', $_GET)) {
            
            case 'contribute':
            case 'event':
                
                $ipn = new Cardsave_Payment_IPN();
                /*
                $result  = $ipn->verifyNotification();
                
                if (is_string($result))
                    return CRM_Core_Error::debug_log_message(
                        CARDSAVE_EXTENSION_NAME . ': ' . $result
                    );
                */
                $ipn->main($module);
                break;

            default:
                CRM_Core_Error::debug_log_message(ts('Invalid or missing module name'));
        
        }

    }

}