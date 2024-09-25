/**
 * Frontend Script.
 * 
 * @package GravityformsFlutterwaveAddons
 */
// import IMask from "imask";
import creditCard from "./creditcard";
import Swal from "sweetalert2";
import toastify from "toastify-js";
// import axios from 'axios';
// const flutterwave = require('flutterwave-node-v3');

( function ( $ ) {
	class FutureWordPress_Frontend {
		/**
		 * Constructor
		 */
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl??'';
			this.config = fwpSiteConfig?.config??'';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce??'';
			this.profile = fwpSiteConfig?.profile??false;
			this.lastAjax = false;this.noToast = true;
			var i18n = fwpSiteConfig?.i18n??{};
			this.toastify = toastify;
			window.creditCard = creditCard;
			this.i18n = {i_confirm_it: 'Yes I confirm it',...i18n};
			window.thisClass = this;
			this.setup_hooks();
			// this.checkoutpay_button();
			this.init_creditCard();
			// this.demo_fillup();
		}
		setup_hooks() {
			const thisClass = this;var html;
			document.body.addEventListener('reload-page', () => {location.reload();});
			document.body.addEventListener('card_subac_recieved', () => {
				var args = {
					public_key: thisClass.config?.public_key??'',
					tx_ref: thisClass.lastCardData?.tx_ref??'',
					amount: thisClass.lastCardData?.amount??0.00,
					currency: thisClass.lastCardData?.currency??'NGN',
					// redirect_url: thisClass.lastCardData?.redirect_url??'',
					customer: {email: thisClass.lastCardData?.email??''},
					subaccounts: thisClass.lastJson?.subaccounts??[],
					callback: function(response) {
					  if(response.status === 'successful') {
						thisClass.liveCheckout.close();
						thisClass.toastify({text: thisClass.i18n?.livepaydone??"Payment success. Please make sure the rest of the required fields are done properly and submit form.",className: "warning", duration: 4000, stopOnFocus: true, style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
						document.querySelectorAll('form.flutterwave_card').forEach((el)=>{
							el.classList.add('payment_success');
						});
					  } else {
						thisClass.toastify({text: response.message,className: "warning", duration: 3000, stopOnFocus: true, style: {background: "linear-gradient(to right, rgb(255 145 102), rgb(250 202 157))"}}).showToast();
					  }
					},
				};
				// console.log(args);
				setTimeout(() => {thisClass.liveCheckout = FlutterwaveCheckout(args);}, 2000);
			});
			document.body.addEventListener('card_issued_falied', (event) => {
				if(creditCard.lastSubmitBtn) {creditCard.lastSubmitBtn.removeAttribute('disabled');}
				if(typeof FlutterwaveCheckout === 'function' && thisClass.lastCardData) {
					// console.log('Payment failed');
					var formdata = new FormData();
					formdata.append('action', 'gflutter/project/payment/flutterwave/getsubac');
					formdata.append('form_id', parseInt($(creditCard.lastSubmitBtn).parents('form').attr('id').replace('gform_', '')));
					formdata.append('_nonce', thisClass.ajaxNonce);
					thisClass.sendToServer(formdata);
				} else {
					thisClass.toastify({text: thisClass.i18n?.pyment_failed??'Payment process failed',className: "warning", duration: 3000, stopOnFocus: true, style: {background: "linear-gradient(to right, rgb(255 145 102), rgb(250 202 157))"}}).showToast();
				}
			});
			document.body.addEventListener('card_issued_success', (event) => {
				if(!creditCard.lastSubmitBtn) {return;}
				creditCard.lastSubmitBtn.removeAttribute('disabled');
				window.issuedData = thisClass.issuedData = thisClass.lastJson.issuedData;
				if(thisClass.issuedData.data.auth_model.toLowerCase() == 'pin') {
					html = `
					<div class="dynamic_popup">
						<div class="popup_body" data-action="#">
							<div class="popup_step step_visible" data-step="1">
								<fieldset class="mt-2 d-block" style="background-image: url('${thisClass.config.buildPath}/icons/crm-crm (1).svg');">
									<label class="text-danger text-center">${thisClass.issuedData.data.processor_response}</label>
									<label for="opt_field">Enter your OTP (One time password).</label>
									<input id="opt_field" maxlength="20" type="text" name="otp" data-name="otp" pattern="[0-9]*" inputmode="numeric" required="">
									<button class="btn btn-primary button send-otp" type="button">
									<span>Submit</span>
									<div class="spinner-circular-tube"></div>
									</button>
								</fieldset>
							</div>
						</div>
						<div class="popup_foot"></div>
					</div>
					`;
					Swal.fire({
						html: html,
						title: thisClass.i18n?.paymentprocess??'Payment Process',
						showConfirmButton: false,
						showCancelButton: false,
						showCloseButton: true,
						backdrop: `rgba(0,0,123,0.4)`,
						showLoaderOnConfirm: true,
						allowOutsideClick: () => !Swal.isLoading(),
						didOpen: () => {
							setTimeout(() => {
								document.querySelectorAll('.send-otp').forEach((el)=>{
									el.addEventListener('click', (event)=>{
										var input = el.previousElementSibling;
										if(input.value =='') {input.focus();return;}
										
										var formdata = new FormData();
										formdata.append('action', 'gflutter/project/payment/flutterwave/cardotp');
										formdata.append('flw_ref', window.issuedData.data?.flw_reference??window.issuedData.data.flw_ref);
										formdata.append('_nonce', thisClass.ajaxNonce);
										formdata.append('otp', input.value);
										thisClass.sendToServer(formdata);
									});
								});
							}, 800);
						},
						preConfirm: (login) => {
							return true;
						},
						allowOutsideClick: false
					}).then((result) => {
					});
				}
			});
			document.body.addEventListener('cardotp_falied', (event) => {
				if(Swal.isVisible()) {Swal.close();}
			});
			document.body.addEventListener('cardotp_success', (event) => {
				document.querySelectorAll('form.flutterwave_card').forEach((el)=>{
					el.classList.add('payment_success');
				});
			});
		}
		
		sendToServer( data ) {
			const thisClass = this;var message;
			$.ajax({
				url: thisClass.ajaxUrl,
				type: "POST",
				data: data,    
				cache: false,
				contentType: false,
				processData: false,
				success: function(json) {
					thisClass.lastJson = json.data;
					var message = ( typeof json.data.message === 'string') ? json.data.message : (
						( typeof json.data === 'string') ? json.data : false
					);
					if( message ) {
						thisClass.toastify({text: message,className: "info", duration: 3000, stopOnFocus: true, style: {background: (json.success)?"linear-gradient(to right, #00b09b, #96c93d)":"linear-gradient(to right, rgb(255, 95, 109), rgb(255, 195, 113))"}}).showToast();
					}
					if( json.data.hooks ) {
						json.data.hooks.forEach(( hook ) => {
							document.body.dispatchEvent( new Event( hook ) );
						});
					}
				},
				error: function( err ) {
					if( err.responseText ) {
						thisClass.toastify({text: err.responseText,className: "warning", duration: 3000, stopOnFocus: true, style: {background: "linear-gradient(to right, rgb(255 145 102), rgb(250 202 157))"}}).showToast();
					}
					console.log( err.responseText );
				}
			});
		}
		generate_formdata(form=false) {
			const thisClass = this;
			let data;
			form = (form)?form:document.querySelector('form[name="acfgpt3_popupform"]');
			if (form && typeof form !== 'undefined') {
			  const formData = new FormData(form);
			  const entries = Array.from(formData.entries());
		  
			  data = entries.reduce((result, [key, value]) => {
				const keys = key.split('[').map(k => k.replace(']', ''));
		  
				let nestedObj = result;
				for (let i = 0; i < keys.length - 1; i++) {
				  const nestedKey = keys[i];
				  if (!nestedObj.hasOwnProperty(nestedKey)) {
					nestedObj[nestedKey] = {};
				  }
				  nestedObj = nestedObj[nestedKey];
				}
		  
				const lastKey = keys[keys.length - 1];
				if (lastKey === 'acfgpt3' && typeof nestedObj.acfgpt3 === 'object') {
				  nestedObj.acfgpt3 = {
					...nestedObj.acfgpt3,
					...thisClass.transformObjectKeys(Object.fromEntries(new FormData(value))),
				  };
				} else if (Array.isArray(nestedObj[lastKey])) {
				  nestedObj[lastKey].push(value);
				} else if (nestedObj.hasOwnProperty(lastKey)) {
				  nestedObj[lastKey] = [nestedObj[lastKey], value];
				} else if ( lastKey === '') {
				  if (!Array.isArray(nestedObj[keys[keys.length - 2]])) {
					nestedObj[keys[keys.length - 2]] = [];
				  }
				  nestedObj[keys[keys.length - 2]].push(value);
				} else {
				  nestedObj[lastKey] = value;
				}
		  
				return result;
			  }, {});
		  
			  data = {
				prompt: '',
				max_tokens: 700,
				temperature: 0.7,
				img_sizes: '512x512',
				content_type: 'text',
				model: 'text-davinci-003',
				...data?.acfgpt3??data,
			  };
		  
			  data.max_tokens = parseInt(data.max_tokens);
			  data.temperature = parseInt(data.temperature);
			  data.inclexcl = [];
			  data.inclexcl.push( ((data?.keys2incl??'')=='')?'':`Keywords to Include: ${data?.keys2incl??''}.` );
			  data.inclexcl.push( ((data?.keys2excl??'')=='')?'':`Keywords to Exclude: ${data?.keys2excl??''}` );
			  data.inclexcl = data.inclexcl.join(' ');
			  data.inclexcl = (data.inclexcl=='')?'':' ' + data.inclexcl;
			  thisClass.lastFormData = data;
			} else {
			  thisClass.lastFormData = thisClass.lastFormData ? thisClass.lastFormData : {};
			}
			
			return thisClass.lastFormData;
		}
		transformObjectKeys(obj) {
			const transformedObj = {};
		  
			for (const key in obj) {
			  if (obj.hasOwnProperty(key)) {
				const value = obj[key];
		  
				if (key.includes('[') && key.includes(']')) {
				  // Handle keys with square brackets
				  const matches = key.match(/(.+?)\[(\w+)\]/);
				  if (matches && matches.length === 3) {
					const newKey = matches[1];
					const arrayKey = matches[2];
		  
					if (!transformedObj[newKey]) {
					  transformedObj[newKey] = [];
					}
		  
					transformedObj[newKey][arrayKey] = value;
				  }
				} else {
				  // Handle regular keys
				  const newKey = key.replace(/\[(\w+)\]/g, '.$1').replace(/^\./, '');
		  
				  if (typeof value === 'object') {
					transformedObj[newKey] = this.transformObjectKeys(value);
				  } else {
					const keys = newKey.split('.');
					let currentObj = transformedObj;
		  
					for (let i = 0; i < keys.length - 1; i++) {
					  const currentKey = keys[i];
					  if (!currentObj[currentKey]) {
						currentObj[currentKey] = {};
					  }
					  currentObj = currentObj[currentKey];
					}
		  
					currentObj[keys[keys.length - 1]] = value;
				  }
				}
			  }
			}
		  
			return transformedObj;
		}
		checkoutpay_button() {
			const thisClass = this;
			thisClass.allowSubmit = false;
			thisClass.PaymentWrap = document.querySelector('#flutterwave_addons_wrap');

			// jQuery(thisClass.PaymentWrap).hide();thisClass.paymentButtonHandler();

			document.querySelector('#wc-checkout-payment-button').addEventListener('click', (event) => {
				event.preventDefault();
				return thisClass.paymentButtonHandler();
			});
			thisClass.PaymentWrap.querySelector('form#order_review').addEventListener('submit', (event) => {
				event.preventDefault();
				return thisClass.paymentButtonHandler();
			});
		}
		paymentButtonHandler() {
			const thisClass = this;
			$(thisClass.PaymentWrap).hide();

			if ( thisClass.allowSubmit ) {
				thisClass.allowSubmit = false;
				return true;
			}

			let $form    = $( 'form#payment-form, form#order_review' ),
			flutterwave_txnref = $form.find( 'input.tbz_wc_flutterwave_txnref' );
			flutterwave_txnref.val( '' );

			let flutterwave_callback = function( response ) {

				console.log(response);
				$form.append( '<input type="hidden" class="tbz_wc_flutterwave_txnref" name="tbz_wc_flutterwave_txnref" value="' + response.transaction_id + '"/>' );
				$form.append( '<input type="hidden" class="tbz_wc_flutterwave_order_txnref" name="tbz_wc_flutterwave_order_txnref" value="' + response.tx_ref + '"/>' );

				thisClass.allowSubmit = true;

				$form.submit();
				$( 'body' ).block(
					{
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						},
						css: {
							cursor: "wait"
						}
					}
				);
			};

			FlutterwaveCheckout({
				public_key: thisClass.config.public_key,
				tx_ref: thisClass.config.txref,
				amount: thisClass.config.amount,
				currency: thisClass.config.currency,
				country: thisClass.config.country,
				meta: thisClass.config.meta,
				customer: {
					email: thisClass.config.customer_email,
					name: thisClass.config.customer_name,
				},
				customizations: {
					title: thisClass.config.custom_title,
					description: thisClass.config.custom_desc,
					logo: thisClass.config.custom_logo,
				},
				callback: flutterwave_callback,
				onclose: function() {
					$(thisClass.PaymentWrap).show();
					$( this.el ).unblock();
				}
			});

			return false;
		}

		init_creditCard() {
			const thisClass = this;var card;
			card = document.querySelector('.flutterwaves_credit_card');
			if(card) {
				creditCard.init_creditCardForm(thisClass, card);
			}
		}

		demo_fillup() {
			var fields = {
				'input_1.3': 'Remal',
				'input_1.6': 'Mahmud',
				input_2: 'mahudremal@yahoo.com',
				'input_2_2': 'mahudremal@yahoo.com',
				input_11: '01814118328',
				input_9: 20,
			}
			document.addEventListener("keypress", (event)=> {
				if (event.shiftKey && event.keyCode === 70) {
					event.preventDefault();
					Object.keys(fields).forEach(key => {
						document.querySelector('input[name="'+key+'"]').value = fields[key];
					});
				}
			});
		}
	}
	new FutureWordPress_Frontend();
} )( jQuery );
