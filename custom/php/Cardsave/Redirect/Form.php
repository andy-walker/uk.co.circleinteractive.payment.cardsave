<?php 

/**
 * Form class for Cardsave redirection page
 * @author  andyw@circle
 * @package uk.co.circleinteractive.payment.cardsave
 */
class Cardsave_Redirect_Form extends CRM_Core_Form {

    /**
     * buildQuickForm - add resources, assign templates vars, then call parent run method
     */
    public function buildQuickForm() {

        $this->assign('transaction', $_SESSION['cardsave_trxn']);
        return parent::buildQuickForm();
    
    }

};