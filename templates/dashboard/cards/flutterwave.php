<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package GravityformsFlutterwaveAddons
 */

$settings = GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS;
$transaction_id    = get_query_var('transaction_id'); // (get_query_var('transaction_id') != '')?get_query_var('transaction_id'):get_query_var('tx_ref');
$payment_status    = get_query_var('status'); // (get_query_var('payment_status') != '')?get_query_var('payment_status'):get_query_var('status');
$tx_ref = get_query_var('tx_ref');
$verify = apply_filters( 'gravityformsflutterwaveaddons/project/payment/flutterwave/verify', $transaction_id, $payment_status );
// || $verify == $transaction_id
if(in_array($payment_status, ['success', 'successful'])) {
    if($verify) {
        $expoldes = explode('.', $tx_ref);
        $entry_id = end($expoldes);
        // print_r([ $transaction_id, $payment_status, $expoldes, $entry_id, $verify]);wp_die();

        // $entry = GFAPI::get_entry_by_transaction_id($transaction_id);
        // Update the entry status
        $result = GFAPI::update_entry_property($entry_id, 'transaction_id', $transaction_id);
        $result = GFAPI::update_entry_property($entry_id, 'payment_status', $payment_status);
        $result = GFAPI::update_entry_property($entry_id, 'payment_method', 'flutterwave');
        $result = GFAPI::update_entry_property($entry_id, 'transaction_type', 'card');
        $result = GFAPI::update_entry_property($entry_id, 'is_fulfilled', true);
        $result = GFAPI::update_entry_property($entry_id, 'status', 'active');
        if(is_wp_error($result)) {
            // Handle error case
            $error_message = $result->get_error_message();
            // echo "Error updating entry status: $error_message";
        } else {
            // Entry status updated successfully
            // echo "Entry status updated to: active";
        }
    } else {
        wp_die(
            sprintf(
                __('We can\'t verify this transaction. Please contact with support. Transaction no is. %s', 'domain'),
                '<b>'.$transaction_id.'</b>'
            ), __('Verification failed!', 'domain')
        );
    }
}
// else if(in_array($payment_status, ['cancelled', 'failed']) && $verify) {
// } else {}
?>
<?php if($payment_status) : ?>
  <?php get_header(); ?>
    <div class="wrapper">
        <section class="payment-status-content overflow-hidden">
            <div class="row no-gutters align-items-center bg-white my-5">      
                <div class="col-md-12 col-lg-6 align-self-center">
                    <div class="row justify-content-center pt-5">
                        <div class="col-md-8">
                            <div class="card  d-flex justify-content-center mb-0">
                            <div class="card-body">
                                <h2 class="mt-3 mb-4">
                                    <?php echo esc_html(in_array($payment_status, ['success','successful'])?__('Payment Successful', 'gravitylovesflutterwave'):__( 'Payment Failed',   'gravitylovesflutterwave')); ?>
                                </h2>
                                <p class="cnf-mail mb-1"><?php echo wp_kses_post(in_array($payment_status, ['success','successful'])?stripslashes($settings['paymentSuccess']):stripslashes($settings['paymentFailed'])); ?></p>
                                <div class="d-inline-block w-100">
                                <a href="<?php echo site_url(); ?>/" class="btn btn-primary mt-3"><?php esc_html_e( 'Back to home', 'gravitylovesflutterwave' ); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>   
                
                </div>
                <div class="col-lg-6 d-lg-block p-0  overflow-hidden">
                    <img src="<?php echo esc_url(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI.'/icons/Card Payment_Monochromatic.svg'); ?>" class="img-fluid gradient-main" alt="images" loading="lazy" >
                </div>
            </div>
        </section>
    </div>
    <style>
        @media (max-width: 47.99em) {.payment-status-content > .row > div:last-child {order: -1;text-align: center;}}
    </style>
  <?php get_footer(); ?>
    <?php else:
  wp_die( 'Failed your payment. Please try again.' );
  ?>
<?php endif; ?>