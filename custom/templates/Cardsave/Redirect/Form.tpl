        
  <input type="hidden" name="HashDigest" value="{$transaction->HashDigest}" />
  <input type="hidden" name="MerchantID" value="{$transaction->MerchantID}" />
  <input type="hidden" name="Amount" value="{$transaction->Amount}" />                                       
  <input type="hidden" name="CurrencyCode" value="{$transaction->CurrencyCode}" />
  <input type="hidden" name="OrderID" value="{$transaction->OrderID}" />
  <input type="hidden" name="TransactionType" value="{$transaction->TransactionType}" />
  <input type="hidden" name="TransactionDateTime" value="{$transaction->TransactionDateTime}" />
  <input type="hidden" name="CallbackURL" value="{$transaction->CallbackURL}" />
  <input type="hidden" name="OrderDescription" value="{$transaction->OrderDescription}" />
  <input type="hidden" name="CustomerName" value="{$transaction->CustomerName}" />
  <input type="hidden" name="Address1" value="{$transaction->Address1}" />
  <input type="hidden" name="Address2" value="{$transaction->Address2}" />
  <input type="hidden" name="Address3" value="{$transaction->Address3}" />
  <input type="hidden" name="Address4" value="{$transaction->Address4}" />
  <input type="hidden" name="City" value="{$transaction->City}" /> 
  <input type="hidden" name="State" value="{$transaction->State}" />
  <input type="hidden" name="PostCode" value="{$transaction->PostCode}" />
  <input type="hidden" name="CountryCode" value="{$transaction->CountryCode}" />
  <input type="hidden" name="CV2Mandatory" value="{$transaction->CV2Mandatory}" />
  <input type="hidden" name="Address1Mandatory" value="{$transaction->Address1Mandatory}" />
  <input type="hidden" name="CityMandatory" value="{$transaction->CityMandatory}" />
  <input type="hidden" name="PostCodeMandatory" value="{$transaction->PostCodeMandatory}" />
  <input type="hidden" name="StateMandatory" value="{$transaction->StateMandatory}" />
  <input type="hidden" name="CountryMandatory" value="{$transaction->CountryMandatory}" />
  <input type="hidden" name="ResultDeliveryMethod" value="POST" />
  <input type="hidden" name="ServerResultURL" value="" />
  <input type="hidden" name="PaymentFormDisplaysResult" value="false" />

{literal}
<script type="text/javascript">cj('.Cardsave_Redirect_Form').attr('action', '{/literal}{$transaction->GatewayURL}{literal}').submit();</script>
{/literal}