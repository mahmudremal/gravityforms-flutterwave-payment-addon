<?php
/**
 * Custom field settings.
 * 
 * @package GravityformsFlutterwaveAddons
 */
$enableCardFeatures = GRAVITYFORMS_FLUTTERWAVE_ADDONS_ENABLE_CARD_FEATURE;
$subAccountInput = true;
?>

<?php $field = isset($field)?$field:$form; ?>
<fieldset id="gform-settings-section-flutterwave-payment" class="gform-settings-panel gform-settings-panel--with-title" data-style="display: none;">
  <legend class="gform-settings-panel__title gform-settings-panel__title--header">
    <?php esc_html_e( 'Flutterwave Payment', 'gravitylovesflutterwave' ); ?>
  </legend>
  <div class="gform-settings-panel__content">

    <div class="enable-card-payment-method" style="<?php echo esc_attr(($enableCardFeatures)?'':'display: none;') ?>">
      <input type="checkbox" id="field_enable_multiple_payment_methods" onclick="SetFieldProperty('enableCardPaymentMethod', this.checked);" onkeypress="SetFieldProperty('enableCardPaymentMethod', this.checked);" <?php // echo checked(rgar($field,'enableCardPaymentMethod'), true); ?> />
      <label for="field_enable_multiple_payment_methods" class="inline">
        <!-- Enable multiple payment methods. Payer will be able to pay through card & direct checkout method. -->
        <?php esc_html_e( 'Enable Card Payment', 'gravitylovesflutterwave' ); ?>
        <?php gform_tooltip( 'form_alsocard' ); ?>
      </label>
      <input type="checkbox" id="field_flutterwave_default_mode" data-js="flutterwave_default_mode" onclick="SetFieldProperty('flutterwaveDefaultModeCard', this.checked);" onkeypress="SetFieldProperty('flutterwaveDefaultModeCard', this.checked);" <?php // echo checked(rgar($field,'flutterwaveDefaultModeCard'), true); ?> />
      <label for="field_flutterwave_default_mode" class="inline">
          <?php esc_html_e( 'Set Card as default.', 'gravitylovesflutterwave' ); ?>
          <?php gform_tooltip( 'form_field_flutterwave_default_mode' ); ?>
      </label>
    </div>
    <!-- <div id="link_email_field_container" class="d-none">
        <label for="link_email_field" class="section_label">
            <?php // esc_html_e( 'Link Email Field', 'gravitylovesflutterwave' ); ?>
        </label>
        <select id="link_email_field" data-js="link_email_field" class="inline">
            <?php // echo implode( '', $options['email'] ); ?>
        </select>
    </div> -->
    <div class="mt-2" style="<?php echo esc_attr(($enableCardFeatures)?'':'display: none;') ?>">
        <input type="checkbox" id="field_enable_preview_field" data-js="enable_preview_field" onclick="SetFieldProperty('enablePreviewField', this.checked);" onkeypress="SetFieldProperty('enablePreviewField', this.checked);" <?php // echo checked( rgar( $field, 'enablePreviewField' ), true ); ?> />
        <label for="field_enable_preview_field" class="inline">
            <?php esc_html_e( 'Show preview card', 'gravitylovesflutterwave' ); ?>
            <?php gform_tooltip( 'form_field_enable_card_preview' ); ?>
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

    <!-- <div id="gform_setting_enableCard" class="gform-settings-field gform-settings-field__checkbox">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="enableCard">Enable Card Payment</label>
        <?php echo gform_tooltip('form_alsocard', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <input type="checkbox" id="description-enableCard" value="1" aria-describedby="description-enableCard" onchange="SetFieldProperty('enableCreditCard', this.checked);" onkeypress="SetFieldProperty('enableCreditCard', this.checked);" <?php checked(rgar($field,'enableCreditCard'), 1, true); ?> />
        <label for="description-enableCard" class="inline">
            <?php esc_html_e( 'Enable credit card payment as well.', 'gravitylovesflutterwave' ); ?>
            <?php gform_tooltip( 'form_field_enable_credit_card' ); ?>
        </label>
      </span>
    </div> -->

    <!-- <div class="gform_setting_subaccounts_card-body">
      <div id="gform_setting_subAccounts" class="gform-settings-field gform-settings-field__checkbox">
        <div class="gform-settings-field__header">
          <label class="gform-settings-label" for="subAccounts"><?php esc_html_e('Sub Accounts', 'gravitylovesflutterwave'); ?></label>
          <?php echo gform_tooltip('form_subaccounts', '', true); ?>
        </div>
        <span class="gform-settings-input__container">
          
          <?php $getSubAccounts = $this->gforms_sub_accounts(); ?>
          <?php if(!$subAccountInput && count($getSubAccounts)<=0): ?>
            <div class="card">
              <p class="text-muted">
                <?php esc_html_e('Flutterwave sub-accounts not found. If you believe sub-accounts should be available, please contact the administrator for assistance.', 'gravitylovesflutterwave'); ?>
              </p>
            </div>
          <?php else: ?>
            <?php if(true): ?>
              <?php foreach(['client', 'partner', 'staff'] as $type): ?>
                <div class="gform-settings-tab">
                  <div class="gform-settings-tab__header">
                    <span><?php echo esc_html(ucfirst($type).' subaccount'); ?></span>
                  </div>
                  <div class="gform-settings-tab__body">
                    <div id="gform-settings-<?php echo esc_attr(($subAccountInput)?'text':'select'); ?>-subaccounts<?php echo esc_attr($type); ?>" class="gform-settings-field gform-settings-field__<?php echo esc_attr(($subAccountInput)?'text':'select'); ?>">
                      <label for="subaccounts-<?php echo esc_attr($type); ?>">
                        <span><?php echo ($subAccountInput)?esc_html__('Account ID', 'gravitylovesflutterwave'):esc_html__('Select Account', 'gravitylovesflutterwave'); ?></span>
                        <?php echo ($subAccountInput)?gform_tooltip('form_accountidinput', '', true):gform_tooltip('form_accountidselect', '', true); ?>
                      </label>
                      <?php if($subAccountInput): ?>
                        <span class="gform-settings-input__container">
                          <input type="text" id="subaccounts-<?php echo esc_attr($type); ?>" onchange="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);matchComissionAccount(this, this.value);" onkeypress="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);matchComissionAccount(this, this.value);" value="<?php echo esc_attr(rgar($field, 'comissionAccount-'.esc_attr($type))); ?>">
                        </span>
                      <?php else: ?>
                        <select id="subaccounts-<?php echo esc_attr($type); ?>" onchange="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);" onkeypress="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);">
                          <?php foreach($getSubAccounts as $i => $subAC):
                            if(!isset($subAC['id'])) {continue;} ?>
                              <option value="<?php echo esc_attr($subAC['id']); ?>" <?php selected(rgar($field, 'comissionAccount-'.esc_attr($type)), $subAC['id'], true); ?>><?php echo esc_html($subAC['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                      <?php endif; ?>
                    </div>
                    <div id="gform_setting_comissionType-<?php echo esc_attr($type); ?>" class="gform-settings-field gform-settings-field__radio">
                      <div class="gform-settings-field__header">
                        <label class="gform-settings-label" for="comissionType-<?php echo esc_attr($type); ?>">Comission type</label>
                        <?php echo gform_tooltip('form_comissiontype', '', true); ?>
                      </div>
                      <span class="gform-settings-input__container">
                        <div id="gform-settings-radio-select-comissionType" class="gform-settings-select">
                          <select id="comissionType-<?php echo esc_attr($type); ?>" onchange="SetFieldProperty('comissionType-<?php echo esc_attr($type); ?>', this.value);" onkeypress="SetFieldProperty('comissionType-<?php echo esc_attr($type); ?>', this.value);">
                            <option value="percentage" <?php selected(rgar($field,'comissionType-'.esc_attr($type)), 'percentage', true); ?>><?php esc_html_e('Percentage', 'gravitylovesflutterwave'); ?></option>
                            <option value="flatamount" <?php selected(rgar($field,'comissionType-'.esc_attr($type)), 'flatamount', true); ?>><?php esc_html_e('Flat amount', 'gravitylovesflutterwave'); ?></option>
                          </select>
                        </div>
                      </span>
                    </div>
                    <div id="gform_setting_comissionAmount-<?php echo esc_attr($type); ?>" class="gform-settings-field gform-settings-field__text">
                      <div class="gform-settings-field__header">
                        <label class="gform-settings-label" for="comissionAmount-<?php echo esc_attr($type); ?>"><?php esc_html_e('Commission', 'gravitylovesflutterwave'); ?></label>
                        <?php echo gform_tooltip('form_comissionamount', '', true); ?>
                      </div>
                      <span class="gform-settings-input__container">
                        <?php
                          $comission = rgar($field, 'comissionAmount-'.esc_attr($type));
                          $comission = (!$comission || empty($comission))?GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['defaultComission-'.esc_attr($type)]:$comission;
                        ?>
                        <input type="text" id="comissionAmount-<?php echo esc_attr($type); ?>"  onchange="SetFieldProperty('comissionAmount-<?php echo esc_attr($type); ?>', this.value);" onkeypress="SetFieldProperty('comissionAmount-<?php echo esc_attr($type); ?>', this.value);" value="<?php echo esc_attr($comission); ?>" data-default="<?php echo esc_attr(GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['defaultComission-'.esc_attr($type)]); ?>" <?php echo esc_attr(
                          (GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['enableReadOnly'] == 'on')?'hi-data':'disabled'
                        ); ?> step="0.01">
                      </span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <?php foreach($getSubAccounts as $i => $subAC):
                if(!isset($subAC['value'])) {continue;} ?>
                <div id="gform-settings-checkbox-choice-subaccounts<?php echo esc_attr($subAC['value']); ?>" class="gform-settings-choice">
                  <input type="checkbox" data_format="bool" id="subaccounts<?php echo esc_attr($subAC['value']); ?>" data-js="<?php echo esc_attr($subAC['name']); ?>"  onchange="SetFieldProperty('<?php echo esc_attr($subAC['name']); ?>', this.checked);" onkeypress="SetFieldProperty('<?php echo esc_attr($subAC['name']); ?>', this.checked);" <?php echo checked(rgar($field, esc_attr($subAC['name'])), true); ?>>
                  <label for="subaccounts<?php echo esc_attr($subAC['value']); ?>">
                    <span><?php echo esc_html($subAC['label']); ?></span>
                  </label>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php endif; ?>


        </span>
      </div>
    </div> -->

    <div id="gform_setting_submitBtnText" class="gform-settings-field gform-settings-field__text">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="submitBtnText"><?php esc_html_e('Submit button text', 'gravitylovesflutterwave'); ?></label>
        <?php echo gform_tooltip('form_submittext', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <input type="text" id="submitBtnText" onchange="SetFieldProperty('submitBtnText', this.value);" onkeypress="SetFieldProperty('submitBtnText', this.value);" value="<?php echo esc_attr(empty(rgar($field, 'submitBtnText'))?__('Submit', 'gravitylovesflutterwave'):rgar($field, 'submitBtnText')); ?>">
      </span>
    </div>
    <!-- <div id="gform_setting_statusBtnLink" class="gform-settings-field gform-settings-field__radio">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="statusBtnLink"><?php // esc_html_e('Success page link', 'gravitylovesflutterwave'); ?></label>
        <?php // echo gform_tooltip('form_statusBtnLink', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <div id="gform-settings-radio-select-statusBtnLink0" class="gform-settings-select">
          <select id="statusBtnLink" onchange="SetFieldProperty('statusBtnLink', this.value);" onkeypress="SetFieldProperty('statusBtnLink', this.value);">
            <?php // $linkType = in_array(rgar($field,'statusBtnLink'), ['form', 'home'])?rgar($field,'statusBtnLink'):'form'; ?>
            <option value="form" <?php // selected($linkType, 'form', true); ?>><?php // esc_html_e('Form page', 'gravitylovesflutterwave'); ?></option>
            <option value="home" <?php // selected($linkType, 'home', true); ?>><?php // esc_html_e('Home page', 'gravitylovesflutterwave'); ?></option>
          </select>
        </div>
      </span>
    </div> -->
    <div id="gform_setting_fluttercardMessage" class="gform-settings-field gform-settings-field__textarea">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="fluttercardMessage"><?php esc_html_e('Card message', 'gravitylovesflutterwave'); ?></label>
        <?php echo gform_tooltip('form_fluttercard_message', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <textarea allow_html="1" default="<?php esc_attr_e('You will be redirected to a secure payment page.', 'gravitylovesflutterwave'); ?>" rows="4" editor_height="200" id="fluttercardMessage" onchange="SetFieldProperty('fluttercardMessage', this.value);" onkeypress="SetFieldProperty('fluttercardMessage', this.value);"><?php echo esc_textarea(rgar($field, 'fluttercardMessage')); ?></textarea>
      </span>
    </div>
    <div id="gform_setting_requireAmountMessage" class="gform-settings-field gform-settings-field__textarea">
      <div class="gform-settings-field__header">
        <label class="gform-settings-label" for="requireAmountMessage"><?php esc_html_e('Amount', 'gravitylovesflutterwave'); ?></label>
        <?php echo gform_tooltip('form_require_amount_message', '', true); ?>
      </div>
      <span class="gform-settings-input__container">
        <textarea data-js="_gform_setting_requireAmountMessage" allow_html="1" default="<?php esc_attr_e('You must calculate an amount to make pay and proceed. Currently calculated amount is zero or less then zero!', 'gravitylovesflutterwave'); ?>" rows="4" editor_height="200" id="requireAmountMessage" onclick="SetFieldProperty('requireAmountMessage', this.checked);" onkeypress="SetFieldProperty('requireAmountMessage', this.checked);" <?php echo checked(rgar($field,'requireAmountMessage'), true); ?>><?php echo esc_textarea(__('Amount', 'gravitylovesflutterwave')); ?></textarea>
      </span>
    </div>
  </div>
</fieldset>