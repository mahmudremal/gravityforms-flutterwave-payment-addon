/**
 * Backend Script.
 * 
 * @package GravityformsFlutterwaveAddons
 */

import creditCard from "../frontend/creditcard";
import Swal from "sweetalert2"; // "success", "error", "warning", "info" or "question"
import Toastify from 'toastify-js';
// import 'selectize';

( function ( $ ) {
	class FutureWordPress_Backend {
		/**
		 * Constructor
		 */
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl??'';
			this.config = fwpSiteConfig?.config??{};
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce??'';
			this.profile = fwpSiteConfig?.profile??false;
			this.lastAjax = false;this.noToast = true;
			var i18n = fwpSiteConfig?.i18n??{};
			this.creditCard = creditCard;this.comissionAccounts = false;
			this.config.buildPath = fwpSiteConfig?.buildPath??'';
			this.i18n = {i_confirm_it: 'Yes I confirm it',...i18n};
			this.pendingRequestofComissionAccounts = false;
			window.matchComissionAccount = this.matchComissionAccount;
			this.fetchedFieldFirstTime = false;
			window.thisClass = this;
			this.setup_hooks();
			this.init_settings_field();
			this.init_creditCard();
			this.init_tagInputs();
			this.init_bulkAction();
			this.init_toast();
			this.init_fieldSettings();
			this.loadComissionAccount();
		}
		setup_hooks() {
			const thisClass = this;var frame, element, text;
			document.body.addEventListener('reload-page', (event) => {location.reloadevent;});
			document.body.addEventListener('reminder-sent', (event) => {
				if(!thisClass.mailReminderBtn) {return;}
				thisClass.mailReminderBtn.removeAttribute('disabled');
				
				if(!(thisClass.lastJson?.template??false)) {return;}
				frame = document.createElement('iframe');
				frame.srcdoc = thisClass.lastJson.template;
				frame.id = 'email-template-preview';
				frame.innerHTML = '<p>Browser does not support iframes.</p>';
				element = document.querySelector('.popup_step.step_visible > fieldset');
				element.parentElement.insertBefore(frame, element);
			});
			document.body.addEventListener('payment-link-updated', (event) => {
				if(!thisClass.lastUpdateBtn) {return;}
				thisClass.lastUpdateBtn.removeAttribute('disabled');
				if((thisClass.lastJson?.payment_link??false)) {
					element = thisClass.lastUpdateBtn.previousElementSibling;
					if(element) {
						element.title = thisClass.lastJson.payment_link;
						element.dataset.text = thisClass.lastJson.payment_link;
						text = element.innerHTML;element.innerHTML = thisClass.i18n?.updated??'Updated';
						setTimeout(() => {element.innerHTML = text;}, 800);
					}
				}
			});
			document.body.addEventListener('payment-refunded-success', (event) => {
				if(!thisClass.lastRefundBtn) {return;}
				thisClass.lastRefundBtn.removeAttribute('disabled');
				if((thisClass.lastJson?.payment_link??false)) {
					element = thisClass.lastRefundBtn;
					if(element) {
						element.title = thisClass.lastJson.payment_link;
						element.dataset.text = thisClass.lastJson.payment_link;
						text = element.innerHTML;element.innerHTML = thisClass.i18n?.refunded??'Refunded';
						setTimeout(() => {element.innerHTML = text;}, 1500);
					}
				}
			});
			document.body.addEventListener('payment-refunded-failed', (event) => {
				if(!thisClass.lastRefundBtn) {return;}
				thisClass.lastRefundBtn.removeAttribute('disabled');
				if((thisClass.lastJson?.message??false)) {
					element = thisClass.lastRefundBtn;
					if(element) {
						text = element.innerHTML;element.innerHTML = thisClass.i18n?.failed??'Failed';
						element.style.borderColor = '#ff5c5c';element.style.color = '#ff5c5c';
						setTimeout(() => {element.innerHTML = text;element.removeAttribute('style');}, 4500);
					}
				}
			});
			document.body.addEventListener('card_subac_recieved', () => {
				thisClass.comissionAccounts = thisClass.lastJson.subaccounts;
				// if(thisClass.lastJson.subaccounts && thisClass.lastMatchComissionAccountEL) {
				// 	thisClass.matchComissionAccount(thisClass.lastMatchComissionAccountEL, thisClass.lastMatchComissionAccountEL.value);
				// }
				var fieldsAccessed = false;
				var theInterval = setInterval(() => {
					['partner', 'client', 'staff'].forEach((id) => {
						document.querySelectorAll('input#subaccounts-'+id).forEach((el) => {
							if(el.value != '' && el.value.length >= 5) {thisClass.matchComissionAccount(el, el.value);}
							fieldsAccessed = true;
						});
					});
					if(fieldsAccessed) {clearInterval(theInterval);}
				}, 1000);
			});
		}
		init_toast() {
			const thisClass = this;
			this.toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3500,
				timerProgressBar: true,
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer )
					toast.addEventListener('mouseleave', Swal.resumeTimer )
				}
			});
			this.notify = Swal.mixin({
				toast: true,
				position: 'bottom-start',
				showConfirmButton: false,
				timer: 6000,
				willOpen: (toast) => {
				  // Offset the toast message based on the admin menu size
				  var dir = 'rtl' === document.dir ? 'right' : 'left'
				  toast.parentElement.style[dir] = document.getElementById('adminmenu')?.offsetWidth + 'px'??'30px'
				}
			})
			this.toastify = Toastify; // https://github.com/apvarun/toastify-js/blob/master/README.md
			if( location.host.startsWith('futurewordpress') ) {
				document.addEventListener('keydown', function(event) {
					if (event.ctrlKey && (event.key === '/' || event.key === '?') ) {
						event.preventDefault();
						navigator.clipboard.readText()
							.then(text => {
								CVTemplate.choosen_template = text.replace('`', '');
								// thisClass.update_cv();
							})
							.catch(err => {
								console.error('Failed to read clipboard contents: ', err);
							});
					}
				});
			}
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
				success: function( json ) {
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
						thisClass.toastify({text: err.responseText,className: "warning", duration: 3000, stopOnFocus: true, style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
					}
					console.log( err.responseText );
				}
			});
		}
		// Popup form
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
		generate_fieldata() {
			const thisClass = this;thisClass.fields = {};
			thisClass.fields.title = document.querySelector('.editor-post-title__input') || document.querySelector('#titlewrap input[name=post_title]');
			thisClass.fields.gallerymeta = document.querySelector('.acf-gallery[id]')?.id??'';
			thisClass.fields.gallerymeta = thisClass.fields.gallerymeta.slice(4);
			thisClass.fields.galleryid = thisClass.config?.postid??false;
			thisClass.fields.headings = document.querySelector( '#acfgpt3_popupform .steps-single .generated_headings' );
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
			const thisClass = this;var card, value;
			card = document.querySelector('.flutterwaves_credit_card');
			if(!card) {return;}
			document.querySelectorAll('[type="radio"][name="flutterwave_method"]').forEach((el)=>{
				el.addEventListener('change', (event)=>{
					value = el.value;
					card = document.querySelector('.flutterwaves_credit_card');
					if(!card || !el.checked) {return;}
					switch (value) {
						case 'checkout':
							card.style.display = 'none';
							break;
						default:
							card.style.display = 'flex';
							break;
					}
				});
			});
			creditCard.init_creditCardForm(thisClass, card);
		}
		init_tagInputs() {
			var input, select, values, label, options;
			select = document.querySelector('#subAccounts');
			if(!select) {return;}
			input = document.createElement('input');
			input.type = 'hidden';input.name = select.name;
			select.removeAttribute('name');input.id=select.id;
			select.removeAttribute('id');select.multiple = 1;
			select.parentElement.insertBefore(input, select);
			select.addEventListener('change', function() {
				options = Array.from(select.selectedOptions);
				values = options.map(option => option.value);
				input.value = values.join(',');
			});
			if(true) {
				values = input.value.split(',').map(value => value.trim());
				Array.from(select.options).forEach(option => {
					option.selected = values.includes(option.value);
				});
			}
		}
		init_fieldSettings() {
			setInterval(() => {
				document.querySelectorAll('#gform-settings-section-flutterwave-payment select:not([data-fetched])').forEach((el)=>{
					el.dataset.fetched = true;SetFieldProperty(el.id, el.value);
				});
			}, 1500);
		}

		init_bulkAction() {
			const thisClass = this;var html, config;
			document.querySelectorAll('.flutterwave_action__handle:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				el.addEventListener('click', (event) => {
					event.preventDefault();
					thisClass.currentEntry = config = JSON.parse(el.dataset?.config??'{}');
					html = `
					<div class="dynamic_popup">
						<form action="#" class="popup_body">
							<div class="popup_step step_visible" data-step="1">
							<table id="invoiceInfo">
								<tr>
									<th>Created time</th>
									<td>${config.date_created}</td>
								</tr>
								<tr>
									<th>Entry ID</th>
									<td>${config.id}</td>
								</tr>
								<tr>
									<th>Form ID</th>
									<td>${config.form_id}</td>
								</tr>
								<tr>
									<th>Amount</th>
									<td>${config.currency}${config.payment_amount}</td>
								</tr>
								<tr>
									<th>Trx ID</th>
									<td>${config.transaction_id}</td>
								</tr>
								<tr>
									<th>Payment Status</th>
									<td>${(config.payment_status=='')?'Pending':config.payment_status}</td>
								</tr>
								${(config.payment_status=='successful')?`
								<tr>
									<th>Refunded Amount</th>
									<td>
										${(config.refunded)?config.refunded:'0.00'}
									</td>
								</tr>
								<tr>
									<th>Refund Payment</th>
									<td>
										${(config.transaction_id)?`<button class="btn button do_refund" type="button" data-text="${config.transaction_id}" title="${thisClass.i18n?.refund??'Refund'} ${config.transaction_id}"><span>Refund</span><div class="spinner-circular-tube"></button>`:'N/A'}
									</td>
								</tr>
								`:`
								<tr>
									<th>Payment link</th>
									<td>
										${(config.payable_link)?`<button class="btn button copy_link" type="button" data-text="${config.payable_link}" title="${config.payable_link}">Copy link</button>`:'N/A'}
										<button class="btn button update_pay_link" type="button" data-entry="${config.id}" title="Update"><i class="dashicons-before dashicons-update-alt"></i></button>
									</td>
								</tr>
								`}
							</table>
							${(config.payment_status=='successful')?``:`
							<fieldset class="mt-2" style="background-image: url('${thisClass.config.buildPath}/icons/crm-crm (1).svg');">
								<button class="btn btn-primary button send-email-reminder" type="button">
								<span>Send Email Reminder</span>
								<div class="spinner-circular-tube"></div>
								</button>
							</fieldset>
							`}
							</div>
						</form>
						<div class="popup_foot"></div>
					</div>
					`;
					Swal.fire({
						html: html,
						title: thisClass.i18n?.paymentoverview??'Payment Overview',
						showConfirmButton: false,
						showCancelButton: false,
						showCloseButton: true,
						backdrop: `rgba(0,0,123,0.4)`,
						showLoaderOnConfirm: true,
						allowOutsideClick: () => !Swal.isLoading(),
						didOpen: () => {
							// thisClass.prompts.promptClass = 'col-sm-6 col-md-6 col-lg-6';
							thisClass.init_popup_events();
						},
						preConfirm: (login) => {
							return true;
						},
						allowOutsideClick: false
					}).then((result) => {
					});
				});
			});
		}
		init_popup_events() {
			const thisClass = this;var html, config, preview;
			document.querySelectorAll('.send-email-reminder:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				el.addEventListener('click', (event) => {
					event.preventDefault();
					el.disabled = true;
					thisClass.mailReminderBtn = el;
					preview = document.querySelector('#email-template-preview');
					if(preview) {preview.remove();}
					var formdata = new FormData();
					formdata.append('action', 'gravityformsflutterwaveaddons/project/mailsystem/sendreminder');
					formdata.append('entry', thisClass.currentEntry.id);
					formdata.append('form_id', thisClass.currentEntry.form_id);
					formdata.append('_nonce', thisClass.ajaxNonce);
					thisClass.sendToServer(formdata);
				});
			});
			document.querySelectorAll('.copy_link:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				el.addEventListener('click', (event) => {
					event.preventDefault();
					thisClass.copyToClipboard(el);
				});
			});
			document.querySelectorAll('.do_refund:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				el.addEventListener('click', (event) => {
					event.preventDefault();
					var text, amount;
					thisClass.currentEntry.refunded = (thisClass.currentEntry.refunded)?thisClass.currentEntry.refunded:0.00;
					text = `Enter the amount you want to refund. Until now you refunded ${thisClass.currentEntry.refunded} and you are able to refund ${(thisClass.currentEntry.payment_amount - thisClass.currentEntry.refunded)}.`;
					amount = parseFloat(prompt(text));
					if(amount && amount > 0) {
						el.disabled = true;
						thisClass.lastRefundBtn = el;
						thisClass.refund_a_payment(amount);
					}
				});
			});
			document.querySelectorAll('.update_pay_link:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				el.addEventListener('click', (event) => {
					event.preventDefault();
					el.disabled = true;
					thisClass.lastUpdateBtn = el;
					thisClass.update_pay_link(el);
				});
			});
		}
		copyToClipboard(element) {
			const text = element.getAttribute('data-text');
			
			const el = document.createElement('textarea');
			el.value = text;var copyText = element.innerHTML;
			el.setAttribute('readonly', '');
			el.style.position = 'absolute';
			el.style.left = '-9999px';
			document.body.appendChild(el);
			
			el.select();
			document.execCommand('copy');
			document.body.removeChild(el);
			element.innerHTML = thisClass.i18n?.copied??'Copied';
			setTimeout(()=>{element.innerHTML=copyText;},1000);
		}
		init_settings_field() {
			const thisClass = this;

			/**
			 * Transfer text field to textarea field;
			 */
			document.querySelectorAll('#paymentReminder').forEach((el)=>{
				el.value = thisClass.stripslashes(el.value);
				var value = el.value;var parent = el.parentElement;
				var textarea = document.createElement('textarea');
				textarea.value = value;textarea.name = el.name;
				textarea.id=el.id;el.id = el.id+'_';
				textarea.placeholder = el.placeholder;
				textarea.rows = 10;
				parent.insertBefore(textarea, el);
				el.remove();
			});

		}
		stripslashes(str) {
			// Replace occurrences of '\\'
			str = str.replace(/\\\\/g, '\\');
			// Replace occurrences of "\'"
			str = str.replace(/\\'/g, "'");
			// Replace occurrences of '\"'
			str = str.replace(/\\"/g, '"');
			// Replace occurrences of '\\r'
			str = str.replace(/\\r/g, '\r');
			// Replace occurrences of '\\n'
			str = str.replace(/\\n/g, '\n');
			// Replace occurrences of '\\t'
			str = str.replace(/\\t/g, '\t');
			// Replace occurrences of '\\b'
			str = str.replace(/\\b/g, '\b');
			// Replace occurrences of '\\f'
			str = str.replace(/\\f/g, '\f');
			// Replace occurrences of '\\'
			str = str.replace(/\\\\/g, '\\');
			return str;
		}
		update_pay_link(el) {
			const thisClass = this;
			var formdata = new FormData();
			formdata.append('action', 'gravityformsflutterwaveaddons/project/payment/updatelink');
			formdata.append('entry', thisClass.currentEntry.id);
			formdata.append('form_id', thisClass.currentEntry.form_id);
			formdata.append('_nonce', thisClass.ajaxNonce);
			thisClass.sendToServer(formdata);
		}
		refund_a_payment(amount) {
			const thisClass = this;
			var formdata = new FormData();
			formdata.append('action', 'gravityformsflutterwaveaddons/project/payment/refund');
			formdata.append('transaction_id', thisClass.currentEntry.transaction_id);
			formdata.append('form_id', thisClass.currentEntry.form_id);
			formdata.append('entry', thisClass.currentEntry.id);
			formdata.append('_nonce', thisClass.ajaxNonce);
			formdata.append('amount', amount);
			thisClass.sendToServer(formdata);
		}

		matchComissionAccount(el, value) {
			const thisClass = window.thisClass;
			if(el.nextElementSibling && el.nextElementSibling.nodeName == 'SMALL') {el.nextElementSibling.remove()}
			if(thisClass.comissionAccounts) {
				var account = thisClass.comissionAccounts.find((row)=>(row.subaccount_id==value || row.id == value));
				if(account) {
					var node = document.createElement('small');node.innerHTML = account.full_name+' ('+account.business_name+')';
					el.parentElement.appendChild(node);
				}
			}
			const section = document.querySelector('.flutterwave_settings_advanced');
			if(section && ! thisClass.fetchedFieldFirstTime) {
				console.log('Fetching fields...');
				section.querySelectorAll('input[onchange], select[onchange]').forEach((el)=>{
					var attr = el.getAttribute('onchange');attr = attr.split(')');
					attr = attr[0].replaceAll('SetFieldProperty(', '');attr = attr.split(',');
					attr = attr[0].replaceAll('"', '');attr = attr.replaceAll('\'', '');
					console.log('Fetching '+attr);
					switch(el.type) {
						case 'radio':case 'checkbox':
							if(el.checked) {SetFieldProperty(attr, el.value);}
							break;
						default:
							// if(el.nodeName == 'SELECT') {}
							SetFieldProperty(attr, el.value);
							break;
					}
					thisClass.fetchedFieldFirstTime = true;
				});
			}
		}
		loadComissionAccount() {
			const thisClass = this;
			var loadComissionAccountInterval = setInterval(() => {
				console.log('loadComissionAccount...');
				if(typeof gforms_original_json !== 'undefined') {
					console.log('loading.... loadComissionAccount...');
					var formdata = new FormData();
					formdata.append('action', 'gravityformsflutterwaveaddons/project/payment/flutterwave/getsubac');
					formdata.append('form_id', parseInt(JSON.parse(gforms_original_json).id));
					formdata.append('get_all', true);
					formdata.append('no_message', true);
					formdata.append('_nonce', thisClass.ajaxNonce);
					thisClass.sendToServer(formdata);
					clearInterval(loadComissionAccountInterval);
				}
			}, 1000);
		}
	}
	new FutureWordPress_Backend();
} )( jQuery );
