/**
 * Frontend Script.
 * 
 * @package GravityformsFlutterwaveAddons
 */
// import IMask from "imask";
import creditCard from "./creditcard";

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
			this.creditCard = creditCard;
			this.i18n = {i_confirm_it: 'Yes I confirm it',...i18n};
			window.thisClass = this;
			this.setup_hooks();
			// this.checkoutpay_button();
			this.init_creditCard();
		}
		setup_hooks() {
			document.body.addEventListener('reload-page', () => {location.reload();});
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
	}
	new FutureWordPress_Frontend();
} )( jQuery );
