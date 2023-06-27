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
			this.creditCard = creditCard;
			this.config.buildPath = fwpSiteConfig?.buildPath??'';
			this.i18n = {
				i_confirm_it: 'Yes I confirm it',
				...i18n
			};
			window.thisClass = this;
			this.setup_hooks();
			this.init_creditCard();
			// this.init_tagInputs();
			this.init_bulkAction();
			this.init_toast();

			this.init_settings_field();
		}
		setup_hooks() {
			const thisClass = this;var frame, element;
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
			const thisClass = this;var card;
			card = document.querySelector('.flutterwaves_credit_card');
			if(card) {
				creditCard.init_creditCardForm(thisClass, card);
			}
		}
		init_tagInputs() {
			var selectInput = document.querySelector('#subAccounts:not([data-handled])');
			if(!selectInput) {return;}
			selectInput.dataset.handled = true;
			var selectize = $(selectInput).selectize({
			delimiter: ',',
			persist: false,
			create: true,
			render: {
				option_create: function(data, escape) {
				return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
				}
			},
			onChange: function(value) {
				// Handle changes in selected tags
				console.log(value);
			}
			})[0].selectize;
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
								<tr>
									<th>Payment link</th>
									<td>${(config.payable_link)?`<button class="btn button copy_link" type="button" data-text="${config.payable_link}" title="${config.payable_link}">Copy link</button>`:'N/A'}</td>
								</tr>
							</table>
							<fieldset class="mt-2" style="background-image: url('${thisClass.config.buildPath}/icons/crm-crm (1).svg');">
								<button class="btn btn-primary button send-email-reminder" type="button">
								<span>Send Email Reminder</span>
								<div class="spinner-circular-tube"></div>
								</button>
							</fieldset>
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
						allowOutsideClick: false, // () => !Swal.isLoading(),
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
			document.querySelectorAll('#paymentReminder').forEach((el)=>{
				el.value = thisClass.stripslashes(el.value);
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
		
	}
	new FutureWordPress_Backend();
} )( jQuery );
