/**
 * Script for individual form settings support.
 * 
 */

import autocomplete from "autocompleter";
import Hooks from "./hooks";

class FlutterwaveSettings extends Hooks {
    constructor() {
        super();
        setTimeout(() => {
            this.init_settings_split_comissions();
        }, 1500);
    }
    init_settings_split_comissions() {
        this.subacid = 0;this.split_rules = [];this.live_subaccounts = [];
        // 
        var comissionRoots = document.querySelectorAll('#settings_split_comissions');
        // 
        if (comissionRoots.length > 0) {
            this.load_live_subaccounts();
        }
        // 
        comissionRoots.forEach(root => {
            if (! root.dataset?.comissions) {return;}root.innerHTML = '';
            var input = document.querySelector(`#${root.dataset?.storedOn??'sxk'}`);
            if (!input) {
                input = document.createElement('input');input.type = 'hidden';
                input.name = '_gform_setting__split_comissions';
                root.appendChild(input);
            }
            // input.setAttribute('value', JSON.stringify(comissions));
            root.dataset.comissions = input.value;
            this.split_rules_input = input;
            // 
            if (root.dataset.comissions == '') {root.dataset.comissions = '[]';}
            var comissions = JSON.parse(root.dataset.comissions);
            if (comissions.length <= 0) {comissions = [{}]}
            // 
            if (comissions.length <= 1) {root.classList.add('hide-minus');}
            // 
            comissions.forEach(comission => {
                this.add_another_rule(root, comission);
            });
            // 
            if (root.dataset?.showBy) {
                document.querySelectorAll(`#_gform_setting_${root.dataset.showBy}`).forEach(toggle => {
                    root.style.display = (toggle.checked)?'flex':'none';
                    toggle.addEventListener('change', (event) => {
                        event.preventDefault();event.stopPropagation();
                        root.style.display = (toggle.checked)?'flex':'none';
                    });
                });
            }
        });
    }
    add_another_rule(root, comission) {
        const currentRuleID = this.subacid;const ruleObjects = {id: currentRuleID};const gformSet = this;
        // 
        var rule = document.createElement('div');rule.classList.add('split_subaccount_rule', `split_subaccount_rule${currentRuleID}`);
        // 
        var subacType = document.createElement('select');subacType.classList.add('split_subaccount_ac_type');
        ruleObjects.actype = comission?.actype??'';// subacType.name = `split_subaccount${currentRuleID}.actype`;
        var acTypes = {
            '': this.i18n?.slctsubactype??'Select Subaccount type',
            client: this.i18n?.client??'Client',
            partner: this.i18n?.partner??'Partner',
            staff: this.i18n?.staff??'Stuff',
        };
        Object.keys(acTypes).forEach(key => {
            var option = document.createElement('option');option.value = key;option.innerHTML = wp.i18n.sprintf(this.i18n?.s_comisison??'%s Comission', acTypes[key]);
            if (comission?.actype == key) {option.selected = true;}
            subacType.appendChild(option);
        });
        subacType.addEventListener('change', (event) => {
            event.preventDefault();event.stopPropagation();
            ruleObjects.actype = event.target.value;
            this.update_split_rules_objects(currentRuleID);
        });
        rule.appendChild(subacType);
        // 
        var sucacc = document.createElement('input');sucacc.type = 'text';// sucacc.name = `split_subaccount${currentRuleID}.account`;
        sucacc.setAttribute('value', comission?.account??'');sucacc.placeholder = this.i18n?.sucaccidtext??'Enter Subaccount id';
        ruleObjects.account = comission?.account??'';
        sucacc.addEventListener('change', (event) => {
            event.preventDefault();event.stopPropagation();
            ruleObjects.account = event.target.value;
            this.update_split_rules_objects(currentRuleID);
        });
        autocomplete({
            input: sucacc,
            fetch: function (text, update) {
                text = text.toLowerCase();
                var suggestions = gformSet.live_subaccounts.filter((n) =>n.label.toLowerCase().startsWith(text));
                update(suggestions);
            },
            onSelect: function (item) {
                sucacc.value = item.value;
            },
        });
        rule.appendChild(sucacc);
        // 
        var cmsnType = document.createElement('select');// cmsnType.name = `split_subaccount${currentRuleID}.type`;
        var comissionTypes = {
            '': this.i18n?.slctsubactype??'Select Comission type',
            flat_subaccount: this.i18n?.flat_amount??'Flat amount',
            percentage_subaccount: this.i18n?.percentage??'Percentage',
        };
        Object.keys(comissionTypes).forEach(key => {
            var option = document.createElement('option');option.value = key;option.innerHTML = comissionTypes[key];
            if (comission?.type == key) {option.selected = true;}
            cmsnType.appendChild(option);
        });
        ruleObjects.type = comission?.type??'';
        cmsnType.addEventListener('change', (event) => {
            event.preventDefault();event.stopPropagation();
            ruleObjects.type = event.target.value;
            this.update_split_rules_objects(currentRuleID);
        });
        rule.appendChild(cmsnType);
        // 
        var amount = document.createElement('input');amount.type = 'number';amount.step = 'any';ruleObjects.amount = comission?.amount??0;
        amount.setAttribute('value', comission?.amount??'0');// amount.name = `split_subaccount${currentRuleID}.amount`;
        amount.addEventListener('change', (event) => {
            event.preventDefault();event.stopPropagation();
            ruleObjects.amount = event.target.value;
            this.update_split_rules_objects(currentRuleID);
        });
        rule.appendChild(amount);
        // 
        var plusNew = document.createElement('button');plusNew.type = 'button';plusNew.title = this.i18n?.addanotherrule??'add another rule';
        plusNew.classList.add('add_field_choice', 'gform-st-icon', 'gform-st-icon--circle-plus');
        plusNew.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();
            this.add_another_rule(root, {});
            root.classList.remove('hide-minus');
        });
        plusNew.style.margin = 0;
        rule.appendChild(plusNew);
        // 
        var minusThis = document.createElement('button');minusThis.type = 'button';minusThis.title = this.i18n?.removethisrule??'Remove this rule';
        minusThis.classList.add('add_field_choice', 'gform-st-icon', 'gform-st-icon--circle-minus');
        minusThis.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();
            if (root.children.length <= 1) {return false;}
            // 
            var confirmed = confirm(this.i18n?.rusure??'Are you sure you want to remove this rule?'); // true;// 
            if (confirmed) {
                var selectedRule = this.split_rules.find(rule => rule.id == currentRuleID);
                if (selectedRule) {
                    rule.remove();selectedRule.removed = true;
                    this.update_split_rules_objects(currentRuleID);
                    if (root.children.length <= 1) {root.classList.add('hide-minus');}
                } else {
                    console.log('Something went wrong removing this rule')
                }
            }
        });
        minusThis.style.margin = 0;
        rule.appendChild(minusThis);
        // 
        root.appendChild(rule);
        this.split_rules.push(ruleObjects);
        this.subacid++;
        return rule;
    }
    update_split_rules_objects(id = false) {
        // rule.id === id && 
        // Filterout rules those are already removed;
        this.split_rules = this.split_rules.filter(rule => rule?.removed != true);
        // 
        if (this?.split_rules_input) {
            this.split_rules_input.value = JSON.stringify(this.split_rules);
        }
        // 
    }
    load_live_subaccounts() {
        if (this.live_subaccounts && this?.wp_ajax_interval) {clearInterval(this.wp_ajax_interval);this.wp_ajax_interval = false;}
        if (this.live_subaccounts.length >= 1) {return this.live_subaccounts;}
        if (wp?.ajax && wp.ajax?.post) {
            wp.ajax.post('gflutter/project/payment/flutterwave/getsubac', {form_id: 86, get_all: true}).then(json => {
                this.live_subaccounts = (json?.subaccounts??[]).map(subac => {
                    return {
                        label: subac.subaccount_id,
                        value: subac.subaccount_id
                    };
                });
                return this.live_subaccounts;
            }).then(subaccounts => {
                console.log(subaccounts);
                return subaccounts;
            }).catch(error => console.log(error?.message??''));
        } else {
            if (this?.wp_ajax_interval) {return;}
            this.wp_ajax_interval = setInterval(() => {
                this.load_live_subaccounts();
            }, 300);
        }
    }
}

(function($) {
    new FlutterwaveSettings();
})(jQuery);