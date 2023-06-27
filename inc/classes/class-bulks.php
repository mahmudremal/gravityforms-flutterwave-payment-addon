<?php
/**
 * Archive Settings
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Bulks {
	use Singleton;
	private $args;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_filter('gform_filter_links_entry_list', [$this, 'gform_filter_links_entry_list'], 10, 3);
		add_filter('gform_search_criteria_entry_list', [$this, 'gform_search_criteria_entry_list'], 10, 2);
		add_filter('gform_entries_first_column_actions', [$this, 'gform_entries_first_column_actions'], 10, 5);
	}


	public function gform_filter_links_entry_list($filter_links, $form, $include_counts) {
		global $wpdb;
		
		$form_id = absint($form['id']);
		$pending_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) as total_pending FROM {$wpdb->prefix}gf_entry WHERE status='pending_payment' AND form_id=%s;", $form_id
			)
		);
		
		$filter_links[] = [
			'id' => 'pending_payment',
			'field_filters' => [
				['key' => 'status', 'operator' => 'is', 'value' => 'pending_payment']
			],
			'count' => $pending_count,
			'label' => esc_html__('Pending payment', 'domain'),
		];
		return $filter_links;
	}
	public function gform_search_criteria_entry_list($search_criteria, $form_id) {
		if(
			isset($search_criteria['field_filters']) && 
			count($search_criteria['field_filters']) >= 1 &&
			isset($search_criteria['field_filters'][0]['key']) &&
			$search_criteria['field_filters'][0]['key'] == 'status' &&
			isset($search_criteria['field_filters'][0]['value']) &&
			$search_criteria['field_filters'][0]['value'] == 'pending_payment'
		) {
			$search_criteria['status'] = 'pending_payment';
		}
		return $search_criteria;
	}
	public function gform_entries_first_column_actions($form_id, $field_id, $value, $entry, $query_string) {
		if($entry['status'] != 'pending_payment') {return;}
		?>
		<span class="flutterwave_action">
			| <a class="flutterwave_action__handle" href="#" data-href="<?php echo esc_url('/admin.php?'.$query_string); ?>" data-config="<?php echo esc_attr(json_encode([
				'id' => $entry['id'], 'form_id' => $entry['form_id'], 'payment_amount' => $entry['payment_amount'], 'transaction_id' => $entry['transaction_id'], 'currency' => $entry['currency'], 'payable_link' => gform_get_meta($entry['id'], '_paymentlink'), 'payment_status' => ($entry['payment_status']===null)?false:$entry['payment_status'], 'date_created' => wp_date('M, d H:i', strtotime($entry['date_created']))
		])); ?>">Payment</a>
		</span>
		<?php
		// print_r([$form_id, $field_id, $value, $entry, $query_string]);
	}
}
