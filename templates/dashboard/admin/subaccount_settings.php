<input type="hidden" name="gform_setting_flutterwave" value="update">
<div class="gform_setting_subaccounts_card-body">
    <div id="gform_setting_subAccounts" class="gform-settings-field gform-settings-field__checkbox" data-id="<?php echo esc_attr($form_id); ?>">
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
                        <span><?php echo esc_html(sprintf(__('%s subaccount', 'domain'), ucfirst(
                            ($type == 'client')?__('Service provider', 'domain'):(
                                ($type == 'staff')?__('Agent', 'domain'):$type
                            )
                        ))); ?></span>
                    </div>
                    <div class="gform-settings-tab__body">
                        <div id="gform-settings-<?php echo esc_attr(($subAccountInput)?'text':'select'); ?>-subaccounts<?php echo esc_attr($type); ?>" class="gform-settings-field gform-settings-field__<?php echo esc_attr(($subAccountInput)?'text':'select'); ?>">
                        <label for="subaccounts-<?php echo esc_attr($type); ?>">
                            <span><?php echo ($subAccountInput)?esc_html__('Account ID', 'gravitylovesflutterwave'):esc_html__('Select Account', 'gravitylovesflutterwave'); ?></span>
                            <?php echo ($subAccountInput)?gform_tooltip('form_accountidinput', '', true):gform_tooltip('form_accountidselect', '', true); ?>
                        </label>
                        <?php if($subAccountInput): ?>
                            <span class="gform-settings-input__container">
                            <input type="text" id="subaccounts-<?php echo esc_attr($type); ?>" name="_gform_setting_comissionAccount-<?php echo esc_attr($type); ?>" data-onchange="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);" onchange="matchComissionAccount(this, this.value);" data-onkeypress="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);" onkeypress="matchComissionAccount(this, this.value);" value="<?php echo esc_attr(rgar($form, 'comissionAccount-'.esc_attr($type))); ?>">
                            </span>
                        <?php else: ?>
                            <select id="subaccounts-<?php echo esc_attr($type); ?>" name="_gform_setting_comissionAccount-<?php echo esc_attr($type); ?>" data-onchange="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);" data-onkeypress="SetFieldProperty('comissionAccount-<?php echo esc_attr($type); ?>', this.value);">
                            <?php foreach($getSubAccounts as $i => $subAC):
                                if(!isset($subAC['id'])) {continue;} ?>
                                <option value="<?php echo esc_attr($subAC['id']); ?>" <?php selected(rgar($form, 'comissionAccount-'.esc_attr($type)), $subAC['id'], true); ?>><?php echo esc_html($subAC['label']); ?></option>
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
                            <select id="comissionType-<?php echo esc_attr($type); ?>" name="_gform_setting_comissionType-<?php echo esc_attr($type); ?>" data-onchange="SetFieldProperty('comissionType-<?php echo esc_attr($type); ?>', this.value);" data-onkeypress="SetFieldProperty('comissionType-<?php echo esc_attr($type); ?>', this.value);">
                                <option value="percentage" <?php selected(rgar($form,'comissionType-'.esc_attr($type)), 'percentage', true); ?>><?php esc_html_e('Percentage', 'gravitylovesflutterwave'); ?></option>
                                <option value="flatamount" <?php selected(rgar($form,'comissionType-'.esc_attr($type)), 'flatamount', true); ?>><?php esc_html_e('Flat amount', 'gravitylovesflutterwave'); ?></option>
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
                            $comission = rgar($form, 'comissionAmount-'.esc_attr($type));
                            $comission = (!$comission || empty($comission))?GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['defaultComission-'.esc_attr($type)]:$comission;
                            ?>
                            <input type="text" id="comissionAmount-<?php echo esc_attr($type); ?>"  name="_gform_setting_comissionAmount-<?php echo esc_attr($type); ?>" data-onchange="SetFieldProperty('comissionAmount-<?php echo esc_attr($type); ?>', this.value);" data-onkeypress="SetFieldProperty('comissionAmount-<?php echo esc_attr($type); ?>', this.value);" value="<?php echo esc_attr($comission); ?>" data-default="<?php echo esc_attr(GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['defaultComission-'.esc_attr($type)]); ?>" <?php echo esc_attr(
                                (isset(GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['enableReadOnly']) && GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['enableReadOnly'])?'disabled':''
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
                <input type="checkbox" data_format="bool" id="subaccounts<?php echo esc_attr($subAC['value']); ?>" data-js="<?php echo esc_attr($subAC['name']); ?>"  onchange="SetFieldProperty('<?php echo esc_attr($subAC['name']); ?>', this.checked);" data-onkeypress="SetFieldProperty('<?php echo esc_attr($subAC['name']); ?>', this.checked);" <?php echo checked(rgar($form, esc_attr($subAC['name'])), true); ?>>
                <label for="subaccounts<?php echo esc_attr($subAC['value']); ?>">
                    <span><?php echo esc_html($subAC['label']); ?></span>
                </label>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>


        </span>
    </div>
</div>