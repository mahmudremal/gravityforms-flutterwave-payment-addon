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

if(empty($payment_status) && empty($tx_ref)) {
    $request = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_GET['response']))), true);
    $tx_ref = $request['txRef'];
    $transaction_id = $request['id'];
    $payment_status = $request['status'];
}

$verify = apply_filters( 'gravityformsflutterwaveaddons/project/payment/flutterwave/verify', $transaction_id, $payment_status );
// || $verify == $transaction_id
$backtoLink = site_url();$backtoText = __('Back to home', 'gravitylovesflutterwave');


$expoldes = explode('.', $tx_ref);
$entry_id = end($expoldes);

$entry = (\GFAPI::entry_exists((int)$entry_id))?\GFAPI::get_entry((int)$entry_id):false;
// if(!$entry && !is_wp_error($entry)) {return;}
$form = (\GFAPI::form_id_exists((int)$entry['form_id']))?\GFAPI::get_form((int)$entry['form_id']):false;
if(
    $entry && !is_wp_error($entry) && isset($entry['source_url']) && !empty($entry['source_url']) &&
    $form && !is_wp_error($form) && isset($form['statusBtnLink']) && $form['statusBtnLink'] == 'form'
) {
    $backtoLink = $entry['source_url'];$backtoText = __('Back to form', 'gravitylovesflutterwave');
}

if(in_array($payment_status, ['success', 'successful'])) {
    if($verify) {
        // $entry = GFAPI::get_entry_by_transaction_id($transaction_id);
        // Update the entry status
        $result = \GFAPI::update_entry_property($entry_id, 'transaction_id', $transaction_id);
        $result = \GFAPI::update_entry_property($entry_id, 'payment_status', $payment_status);
        $result = \GFAPI::update_entry_property($entry_id, 'payment_method', 'flutterwave');
        $result = \GFAPI::update_entry_property($entry_id, 'transaction_type', 'card');
        $result = \GFAPI::update_entry_property($entry_id, 'is_fulfilled', true);
        $result = \GFAPI::update_entry_property($entry_id, 'status', 'active');
        $result = \GFAPI::update_entry_property($entry_id, 'is_approved', true);

        // Here
        $notify = \GFCommon::send_notifications($entry, $form);

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
<?php if($payment_status): ?>
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
                                <a href="<?php echo esc_url($backtoLink); ?>/" class="btn btn-primary mt-3"><?php echo esc_html($backtoText); ?></a>
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
  wp_die(__('Something suspicious detected or it would be probably Failed your payment. Please try again.', 'domain'));
  ?>
<?php endif; ?>