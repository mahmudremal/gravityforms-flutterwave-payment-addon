<?php
/**
 * Checkout video clip shortner template.
 * 
 * @package GravityformsFlutterwaveAddons
 */
global $fwpGravityforms;$settings = GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS;
$transaction_id    = get_query_var('transaction_id'); // (get_query_var('transaction_id') != '')?get_query_var('transaction_id'):get_query_var('tx_ref');
$payment_status    = get_query_var('status'); // (get_query_var('payment_status') != '')?get_query_var('payment_status'):get_query_var('status');
$tx_ref            = get_query_var('tx_ref');

if(empty($payment_status) && empty($tx_ref)) {
    $request = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', stripslashes(html_entity_decode($_GET['response']))), true);
    $tx_ref = $request['txRef'];
    $transaction_id = $request['id'];
    $payment_status = $request['status'];
}

// || $verify == $transaction_id

$expoldes = explode('.', $tx_ref);
$entry_id = end($expoldes);

$entry = (\GFAPI::entry_exists((int)$entry_id))?\GFAPI::get_entry((int)$entry_id):false;
if(!$entry || is_wp_error($entry)) {
    wp_die(__('OOPs. Something suspicious detected or it would be probably currupted your request.', 'gravitylovesflutterwave'));
}
$form = (\GFAPI::form_id_exists((int)$entry['form_id']))?\GFAPI::get_form((int)$entry['form_id']):false;

$backtoLink = site_url();$backtoText = __('Back to home', 'gravitylovesflutterwave');

if(isset($entry['source_url']) && !empty($entry['source_url'])) {
    $backtoLink = $entry['source_url'];$backtoText = __('Back to form', 'gravitylovesflutterwave');
    if($form && !is_wp_error($form)) {
        foreach($form['fields'] as $i => $field) {
            if(isset($field['type']) && $field['type'] == 'flutterwave_credit_card' && isset($settings['statusBtnLink']) && $settings['statusBtnLink'] == 'home') {
                $backtoLink = site_url();$backtoText = __('Back to home', 'gravitylovesflutterwave');
            } else if($field->type == 'flutterwave_credit_card' && isset($settings['statusBtnLink']) && $settings['statusBtnLink'] == 'home') {
                $backtoLink = site_url();$backtoText = __('Back to home', 'gravitylovesflutterwave');
            } else {}
        }
    }
}

// Here
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_PAYMENT_DONE') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_PAYMENT_DONE', true);

// add_action('init', function() {
//     if (!isset($_GET['transaction_id'])) {return;}
//     $this->api_key = $api_key = 'FLWSECK_TEST-efbcc17ffdfd40b3f431944232a2e7fc-X';
//     $transfer_id = $_GET['transaction_id'];
//     $amount = 350; // Amount in kobo (e.g., 1000 kobo = 10.00 NGN)
//     $currency = 'NGN';
//     $tx_ref = $_GET['tx_ref'];
//     $email = 'gefafe9993@dcbin.com';
//     // $result = $this->verifyBankTransfer($transfer_id, $amount, $currency, $tx_ref, $email, $api_key);
//     $result = $this->info($transfer_id);
//     print_r($result);wp_die();
// }, 1, 0);

$successStatuses = ['success', 'successful', 'completed', 'complete'];
if(in_array($payment_status, $successStatuses)) {
    $verify = apply_filters('gflutter/project/payment/flutterwave/verify', $transaction_id, $successStatuses);
    // wp_die(sprintf('Status: %s', $verify));
    // 
    if($verify) {
        // $entry = GFAPI::get_entry_by_transaction_id($transaction_id);
        // Update the entry status
        $is_updated = \GFAPI::update_entry_property($entry_id, 'transaction_id', $transaction_id);
        // $is_updated = \GFAPI::update_entry_property($entry_id, 'payment_status', $payment_status);
        $is_updated = \GFAPI::update_entry_property($entry_id, 'payment_method', 'flutterwave');
        $is_updated = \GFAPI::update_entry_property($entry_id, 'transaction_type', 'card');
        $is_updated = \GFAPI::update_entry_property($entry_id, 'is_fulfilled', true);
        // $is_updated = \GFAPI::update_entry_property($entry_id, 'status', 'active');
        // $is_updated = \GFAPI::update_entry_property($entry_id, 'is_approved', true);

        // Notification sends from Here
        // do_action('gform_post_payment_completed', $entry, $action);

        // if (class_exists('GFPaymentAddOn')) {
            // \GFPaymentAddOn::complete_payment($entry, $payment);
            do_action('gform_post_payment_completed', $entry, [
                'payment_status'        => 'Paid',
                'payment_date'          => gmdate('Y-m-d H:i:s'),
                'type'                  => 'complete_payment'
            ]);
            do_action('gform_post_payment_action', $entry, [
                'payment_status'        => 'Paid',
                'payment_date'          => gmdate('Y-m-d H:i:s'),
                'type'                  => 'complete_payment'
            ]);
        // }
        
        $is_updated = \GFAPI::update_entry_property($entry_id, 'payment_status', 'Completed');
        $is_updated = \GFAPI::update_entry_property($entry_id, 'status', 'active');
        $notify = \GFCommon::send_form_submission_notifications($entry, $form);
        $fwpGravityforms->process_payment_and_send_emails($entry);

        if(is_wp_error($is_updated)) {
            // Handle error case
            $error_message = $is_updated->get_error_message();
            // echo "Error updating entry status: $error_message";
        } else {
            // Entry status updated successfully
            // echo "Entry status updated to: active";
        }
    } else {
        wp_die(
            sprintf(
                __('We can\'t verify this transaction. Please contact with support. Transaction no is. %s', 'gravitylovesflutterwave'),
                '<b>'.$transaction_id.'</b>'
            ), __('Verification failed!', 'gravitylovesflutterwave')
        );
    }
}
else if(in_array($payment_status, ['cancelled', 'failed']) && $verify) {
    do_action('gform_post_payment_' . $payment_status, $entry, [
        'payment_status'        => 'Paid',
        'payment_date'          => gmdate('Y-m-d H:i:s'),
        'type'                  => 'fail_payment'
    ]);
} else {}
?>
<?php if($payment_status): ?>
    <?php
        do_action('gflutter/project/assets/register_styles');
        do_action('gflutter/project/assets/register_scripts');
        wp_enqueue_style('GravityformsFlutterwaveAddons');wp_enqueue_script('imask');
        wp_enqueue_script('GravityformsFlutterwaveAddons');
    ?>
    <?php get_header(); ?>
        <div class="wrapper">
            <section class="fltrwv__payment-status-content overflow-hidden container">
                <div class="fltrwv__row no-gutters align-items-center fltrwv__bg-white my-5">      
                    <div class="fltrwv__col-md-12 fltrwv__col-lg-6 fltrwv__align-self-center">
                        <div class="fltrwv__row justify-content-center pt-5">
                            <div class="fltrwv__col-md-8">
                                <div class="fltrwv__card fltrwv__d-flex fltrwv__justify-content-center mb-0">
                                    <div class="fltrwv__card-body">
                                        <h2 class="fltrwv__mt-3 fltrwv__mb-4">
                                            <?php echo esc_html(in_array($payment_status, $successStatuses)?__('Payment Successful', 'gravitylovesflutterwave'):__( 'Payment Failed',   'gravitylovesflutterwave')); ?>
                                        </h2>
                                        <p class="cnf-mail mb-1"><?php echo wp_kses_post(in_array($payment_status, $successStatuses)?stripslashes($settings['paymentSuccess']):stripslashes($settings['paymentFailed'])); ?></p>
                                        <div class="fltrwv__d-inline-block fltrwv__w-100">
                                        <a href="<?php echo esc_url($backtoLink); ?>" class="fltrwv__btn fltrwv__btn-primary fltrwv__mt-3 btn button"><?php echo esc_html($backtoText); ?></a>

                                        <?php if(false && !in_array($payment_status, $successStatuses)): ?>
                                            <a href="<?php echo esc_url(gform_get_meta($entry['id'], '_paymentlink')); ?>" class="fltrwv__btn fltrwv__btn-primary fltrwv__mt-3 btn button"><?php esc_html_e('Try again', 'gravitylovesflutterwave'); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>   
                    
                    </div>
                    <div class="fltrwv__col-lg-6 fltrwv__d-lg-block fltrwv__p-0 overflow-hidden">
                        <img alt="images" loading="lazy" data-src="<?php echo esc_url(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI.'/icons/Card Payment_Monochromatic.svg'); ?>" class="fltrwv__img-fluid fltrwv__gradient-main lazyloaded" src="<?php echo esc_url(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI.'/icons/Card Payment_Monochromatic.svg'); ?>">
                        <noscript>
                            <img src="<?php echo esc_url(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI.'/icons/Card Payment_Monochromatic.svg'); ?>" class="fltrwv__img-fluid fltrwv__gradient-main" alt="images" loading="lazy">
                        </noscript>
                    </div>
                </div>
            </section>
        </div>
        <style>
            @media (max-width: 47.99em) {.payment-status-content > .row > div:last-child {order: -1;text-align: center;}}
        </style>
    <?php get_footer(); ?>
    <?php else:
  wp_die(__('Something suspicious detected or it would be probably Failed your payment. Please try again.', 'gravitylovesflutterwave'));
  ?>
<?php endif; ?>