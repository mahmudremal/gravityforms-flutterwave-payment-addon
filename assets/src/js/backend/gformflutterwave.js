/**
 * Script for individual form Payment support.
 * 
 */

import axios from "axios";
import Hooks from "./hooks";

class FlutterwavePayment extends Hooks {
    constructor() {
        super();
        this.config = {
            ajax_url: `${location.origin}/wp-admin/admin-ajax.php`
        }
        this.forms = [];
        this.init_forms();
    }
    init_forms() {
        // '#gform_' . formId
        document.querySelectorAll('.pay-flutterwave[data-form-id]').forEach(submitBtn => {
            var form_id = submitBtn.dataset?.formId;
            var form = document.querySelector('#gform_' + form_id);
            if (form && form?.nodeType) {
                var form_object = {
                    form: form,
                    form_id: form_id,
                    payment_done: false,
                    submit_button: submitBtn,
                    token: submitBtn.dataset?.token,
                    formId: submitBtn.dataset?.formId,
                    args: submitBtn.dataset?.args??'{}'
                };
                submitBtn.dataset.args = submitBtn.dataset.token = submitBtn.dataset.formId = false;
                try {
                    form_object.args = JSON.parse(form_object.args);
                    this.forms.push(form_object);
                    form.addEventListener('submit', async (event) => {
                        if (form_object.payment_done) {return true;}
                        event.preventDefault();event.stopPropagation();
                        return await this.processPayment(form_object);
                    });
                } catch (error) {
                    console.error(error);
                }
            }
        });
    }
    async processPayment(elements) {
        const robject = this;var args = {};
        await robject.makePayment(args, elements).then((intend) => {
            if (['success', 'successful', 'completed'].includes(intend.status.toLowerCase())) {
                // 
                // console.log(intend)
                // 
                var trx_ref = document.createElement('input');
                trx_ref.name = 'transaction_id';trx_ref.type = 'hidden';
                trx_ref.setAttribute('value', intend.transaction_id);
                elements.form.appendChild(trx_ref);
                // 
                var trx_amount = document.createElement('input');
                trx_amount.name = 'payment_amount';trx_amount.type = 'hidden';
                trx_amount.setAttribute('value', intend.charged_amount);
                elements.form.appendChild(trx_amount);
                // 
                elements.payment_done = true;
                elements.form.submit();
                return true;
            }
            return false;
        }).catch((err) => {
            console.error(err);
            return false;
        });
    }
    makePayment(args, elements) {
        const robject = this;
        return new Promise((resolve, reject) => {
            elements.submit_button.disabled = true;
            robject.get_public_key(elements).then((token_key) => {
                args = Object.assign({
                    amount: 0,
                    currency: "NGN",
                    public_key: token_key,
                    tx_ref: elements.args.tx_ref,
                    payment_options: "card, banktransfer, ussd",
                    // meta: {
                    //     source: "docs-inline-test",
                    //     consumer_mac: "92a3-912ba-1192a",
                    // },
                    // customer: {
                    //     email: "test@mailinator.com",
                    //     phone_number: "08100000000",
                    //     name: "Ayomide Jimi-Oni",
                    // },
                    // customizations: {
                    //     title: "Flutterwave Developers",
                    //     description: "Test Payment",
                    //     logo: "https://checkout.flutterwave.com/assets/img/rave-logo.png",
                    // },
                    callback: function (data){
                        resolve(data);
                    },
                    onclose: function() {
                        reject("Payment cancelled!");
                    }
                }, args);
                robject._args_implementation(args, elements);
                elements.submit_button.removeAttribute('disabled');
                FlutterwaveCheckout(args);
                console.log(args);
            }).catch((err) => {
                elements.submit_button.removeAttribute('disabled');
                reject(err);
            });
        });
    }
    get_public_key(elements) {
        const robject = this;
        return new Promise(function(resolve, reject) {
            if (robject?.public_key) {
                resolve(robject.public_key);
            }
            var objData = {
                action: 'gflutter/project/payment/get_token',
                time: new Date().getTime(),
                token: elements.token
            };
            var data = new FormData();
            Object.keys(objData).forEach(key => {
                data.append(key, objData[key]);
            });
            axios.post(robject.config.ajax_url, data)
            .then(result => result.data)
            .then(result => {
                if (result?.data && result.data?.token) {
                    robject.public_key = atob(result.data.token);
                    resolve(robject.public_key);
                }
                reject("Failed to fetch token key");
            }).catch(err => reject(err));
        });
    }
    _args_implementation(args, elements) {
        var fdata = new FormData(elements.form);var fSdata = [];
        for (var [key, value] of fdata) {
            fSdata.push({key, value});
        }
        ['name', 'phone', 'email', 'address'].forEach(key => {
            var bills_key = `billingInformation_${key}`;
            if (elements.args[bills_key] && elements.args[bills_key] != '') {
                if (typeof elements.args[bills_key] === 'string') {
                    // elements.args[bills_key] = parseInt(elements.args[bills_key]);
                }
                var is_found = fSdata.filter(row => row.key.startsWith('input_')).filter(row => row.key.replace('input_', '').split('.')[0] == elements.args[bills_key]).map(row => row.value);
                if (is_found.length) {
                    // elements.args[bills_key] = 
                    if (!['email'].includes(key)) {
                        is_found = is_found.join(' ');
                    }
                    
                    args.customer = args?.customer??{};
                    switch (key) {
                        case 'name':
                        case 'address':
                            args.customer[key] = is_found;
                            break;
                        case 'email':
                            args.customer.email = is_found[0];
                            break;
                        case 'phone':
                            args.customer.phone_number = is_found;
                            break;
                        default:
                            break;
                    }
                }
            }
        });
        var has_amount = fSdata.filter(row => row.key == `input_${elements.args.paymentAmount}`).map(row => row.value).find(row => row);
        if (has_amount.length > 0) {
            args.amount = has_amount.split(' ').map(amount => parseFloat(amount.replace(',', ''))).find(amount => typeof amount === 'number' && amount > 0);
        }
        // 
        // 
        if (elements.args.form_title) {
            args.customizations = args?.customizations??{};
            args.customizations.title = elements.args.form_title;
            args.customizations.description = elements.args.form_description;
        }
        // 
        // 
        return true;
    }
}

(function($) {
    new FlutterwavePayment();
})(jQuery);