<?php
/**
 * Custom field settings.
 * 
 * @package GravityformsFlutterwaveAddons
 */
?>


<fieldset id="gform-settings-section-flutterwave-payment" class="gform-settings-panel gform-settings-panel--with-title" data-style="display: none;">
  <legend class="gform-settings-panel__title gform-settings-panel__title--header">
    <?php esc_html_e( 'Flutterwave Payment', 'domain' ); ?>
    <?php gform_tooltip( 'form_field_enable_card_payment_method' ); ?>
  </legend>
  <div class="gform-settings-panel__content">

    <div class="enable-card-payment-method">
      <input type="checkbox" id="field_enable_multiple_payment_methods" data-js="enable_multiple_payment_methods" onclick="SetFieldProperty('enableCardPaymentMethod', this.checked);" onkeypress="SetFieldProperty('enableCardPaymentMethod', this.checked);" <?php echo checked(rgar($field,'enableCardPaymentMethod'), true); ?> />
      <label for="field_enable_multiple_payment_methods" class="inline">
          <?php esc_html_e( 'Enable multiple payment methods. Payer will be able to pay through card & direct checkout method.', 'domain' ); ?>
          <?php gform_tooltip( 'form_field_enable_multiple_payment_methods' ); ?>
      </label>
      <div id="field_multiple_payment_methods_description">
          <?php
          // translators: variables are the markup to generate a link.
          printf( esc_html__( 'Available payment methods can be configured in your %1$sFlutterwave Dashboard%2$s.', 'domain' ), '<a href="https://dashboard.flutterwave.com/" target="_blank">', '</a>' );
          ?>
      </div>
      <input type="checkbox" id="field_flutterwave_default_mode" data-js="flutterwave_default_mode" onclick="SetFieldProperty('flutterwaveDefaultModeCard', this.checked);" onkeypress="SetFieldProperty('flutterwaveDefaultModeCard', this.checked);" <?php echo checked(rgar($field,'flutterwaveDefaultModeCard'), true); ?> />
      <label for="field_flutterwave_default_mode" class="inline">
          <?php esc_html_e( 'Set credit card as default selected method.', 'domain' ); ?>
          <?php gform_tooltip( 'form_field_flutterwave_default_mode' ); ?>
      </label>
    </div>
    <!-- <div id="link_email_field_container" class="d-none">
        <label for="link_email_field" class="section_label">
            <?php esc_html_e( 'Link Email Field', 'domain' ); ?>
        </label>
        <select id="link_email_field" data-js="link_email_field" class="inline">
            <?php echo implode( '', $options['email'] ); ?>
        </select>
    </div> -->
    <div class="mt-2">
        <input type="checkbox" id="field_enable_preview_field" data-js="enable_preview_field" onclick="SetFieldProperty('enablePreviewField', this.checked);" onkeypress="SetFieldProperty('enablePreviewField', this.checked);" <?php checked( rgar( $field, 'enablePreviewField' ), true ); ?> />
        <label for="field_enable_preview_field" class="inline">
            <?php esc_html_e( 'Show preview card', 'domain' ); ?>
            <?php gform_tooltip( 'form_field_enable_card_payment_method' ); ?>
        </label>
    </div>
  
    <!-- <div id="gform_setting_enableFlutterwave" class="gform-settings-field gform-settings-field__toggle">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="enableFlutterwave">Enable flutterwave Payment</label>
		    <?php echo gform_tooltip('form_flutterwave', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <input type="checkbox" data-js="_gform_setting_enableFlutterwave" id="_gform_setting_enableFlutterwave" value="1" checked="checked">
        <label class="gform-field__toggle-container" for="_gform_setting_enableFlutterwave" onclick="SetFieldProperty('enableFlutterwave', this.checked);" onkeypress="SetFieldProperty('enableFlutterwave', this.checked);" <?php echo checked(rgar($field,'enableFlutterwave'), true); ?> checked>
          <span class="gform-field__toggle-switch"></span>
        </label>
      </span>
    </div> -->
    <div id="gform_setting_successBtnLink" class="gform-settings-field gform-settings-field__radio">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="successBtnLink"><?php esc_html_e('Button Link', 'domain'); ?></label>
        <?php echo gform_tooltip('form_successBtnLink', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <div id="gform-settings-radio-choice-successBtnLink0" class="gform-settings-choice">
          <input type="radio" data-js="_gform_setting_successBtnLink" value="percentage" id="successBtnLink0" onclick="SetFieldProperty('successBtnLink', this.checked);" onkeypress="SetFieldProperty('successBtnLink', this.checked);" <?php echo checked(rgar($field,'successBtnLink'), true); ?>>
          <label for="successBtnLink0">
            <span><?php esc_html_e('Percentage', 'domain'); ?></span>
          </label>
        </div>
        <div id="gform-settings-radio-choice-successBtnLink1" class="gform-settings-choice">
          <input type="radio" data-js="_gform_setting_successBtnLink" value="flatamount" checked="checked" id="successBtnLink1" onclick="SetFieldProperty('successBtnLink', this.checked);" onkeypress="SetFieldProperty('successBtnLink', this.checked);" <?php echo checked(rgar($field,'successBtnLink'), true); ?>>
          <label for="successBtnLink1">
            <span><?php esc_html_e('Flat amount', 'domain'); ?></span>
          </label>
        </div>
      </span>
    </div>
    <div id="gform_setting_enableCard" class="gform-settings-field gform-settings-field__toggle">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="enableCard">Enable Card Payment</label>
        <?php echo gform_tooltip('form_alsocard', '', true); ?>
      </div>
      <span class="gform-settings-description" id="description-enableCard">Enable credit card payment as well.</span>
      <span class="gform-settings-input__container">
        <input type="checkbox" data-js="_gform_setting_enableCard" id="_gform_setting_enableCard" value="1" aria-describedby="description-enableCard" onclick="SetFieldProperty('enableCreditCard', this.checked);" onkeypress="SetFieldProperty('enableCreditCard', this.checked);" <?php echo checked(rgar($field,'enableCreditCard'), true); ?>>
        <label class="gform-field__toggle-container" for="_gform_setting_enableCard">
          <span class="gform-field__toggle-switch"></span>
        </label>
      </span>
    </div>
    <div id="gform_setting_comissionType" class="gform-settings-field gform-settings-field__radio">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="comissionType">Comission type</label>
        <?php echo gform_tooltip('form_comissiontype', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <div id="gform-settings-radio-choice-comissionType0" class="gform-settings-choice">
          <input type="radio" data-js="_gform_setting_comissionType" value="percentage" id="comissionType0" onclick="SetFieldProperty('comissionType', this.checked);" onkeypress="SetFieldProperty('comissionType', this.checked);" <?php echo checked(rgar($field,'comissionType'), true); ?>>
          <label for="comissionType0">
            <span> Percentage </span>
          </label>
        </div>
        <div id="gform-settings-radio-choice-comissionType1" class="gform-settings-choice">
          <input type="radio" data-js="_gform_setting_comissionType" value="flatamount" checked="checked" id="comissionType1" onclick="SetFieldProperty('comissionType', this.checked);" onkeypress="SetFieldProperty('comissionType', this.checked);" <?php echo checked(rgar($field,'comissionType'), true); ?>>
          <label for="comissionType1">
            <span> Flat amount </span>
          </label>
        </div>
      </span>
    </div>
    <div id="gform_setting_percentageAmount" class="gform-settings-field gform-settings-field__text">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="percentageAmount">Comission Percent</label>
        <?php echo gform_tooltip('form_comissionpercent', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <input type="text" data-js="_gform_setting_percentageAmount" value="5" id="percentageAmount"  onclick="SetFieldProperty('percentageAmount', this.checked);" onkeypress="SetFieldProperty('percentageAmount', this.checked);" <?php echo checked(rgar($field,'percentageAmount'), true); ?>>
      </span>
    </div>
    <div id="gform_setting_flatrateAmount" class="gform-settings-field gform-settings-field__text">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="flatrateAmount">Flat amount</label>
        <?php echo gform_tooltip('form_comissionflat', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <input type="text" data-js="_gform_setting_flatrateAmount" value="" id="flatrateAmount"  onclick="SetFieldProperty('flatrateAmount', this.checked);" onkeypress="SetFieldProperty('flatrateAmount', this.checked);" <?php echo checked(rgar($field,'flatrateAmount'), true); ?>>
      </span>
    </div>
    <div id="gform_setting_subAccounts" class="gform-settings-field gform-settings-field__checkbox">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="subAccounts">Sub Accounts</label>
        <?php echo gform_tooltip('form_subaccounts', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        
        <?php foreach($this->gforms_sub_accounts() as $i => $subAC):
          if(!isset($subAC['value'])) {continue;} ?>
          <div id="gform-settings-checkbox-choice-subaccounts<?php echo esc_attr($subAC['value']); ?>" class="gform-settings-choice">
            <input type="checkbox" data_format="bool" id="subaccounts<?php echo esc_attr($subAC['value']); ?>" data-js="<?php echo esc_attr($subAC['name']); ?>"  onclick="SetFieldProperty('<?php echo esc_attr($subAC['name']); ?>', this.checked);" onkeypress="SetFieldProperty('<?php echo esc_attr($subAC['name']); ?>', this.checked);" <?php echo checked(rgar($field, esc_attr($subAC['name'])), true); ?>>
            <label for="subaccounts<?php echo esc_attr($subAC['value']); ?>">
              <span><?php echo esc_html($subAC['label']); ?></span>
            </label>
          </div>
        <?php endforeach; ?>


      </span>
    </div>
    <div id="gform_setting_submitBtnText" class="gform-settings-field gform-settings-field__text">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="submitBtnText">Submit text</label>
        <?php echo gform_tooltip('form_submittext', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <input type="text" data-js="_gform_setting_submitBtnText" value="Submit" id="submitBtnText" onclick="SetFieldProperty('submitBtnText', this.checked);" onkeypress="SetFieldProperty('submitBtnText', this.checked);" <?php echo checked(rgar($field,'submitBtnText'), true); ?>>
      </span>
    </div>
    <div id="gform_setting_fluttercardMessage" class="gform-settings-field gform-settings-field__textarea">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="fluttercardMessage">Card message</label>
        <button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_require_amount_message" aria-label="<strong> Flutterwave card text </strong> Give here a short message that should be apear on flutterwave card above the submit button.">
          <i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
        </button>
      </div>
      <span class="gform-settings-input__container">
        <textarea data-js="_gform_setting_fluttercardMessage" allow_html="1" default="You must calculate an amount to make pay and proceed. Currently calculated amount is zero or less then zero!" rows="4" editor_height="200" id="fluttercardMessage">Require Amount</textarea>
      </span>
    </div>
    <div id="gform_setting_requireAmountMessage" class="gform-settings-field gform-settings-field__textarea">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="requireAmountMessage">Required Amount</label>
        <button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_require_amount_message" aria-label="<strong> Required Amount </strong> Give here a message that will show if form doesn't provide an amount to pay.">
          <i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
        </button>
      </div>
      <span class="gform-settings-input__container">
        <textarea data-js="_gform_setting_requireAmountMessage" allow_html="1" default="You must calculate an amount to make pay and proceed. Currently calculated amount is zero or less then zero!" rows="4" editor_height="200" id="requireAmountMessage" onclick="SetFieldProperty('requireAmountMessage', this.checked);" onkeypress="SetFieldProperty('requireAmountMessage', this.checked);" <?php echo checked(rgar($field,'requireAmountMessage'), true); ?>>Require Amount</textarea>
      </span>
    </div>
  </div>
</fieldset>