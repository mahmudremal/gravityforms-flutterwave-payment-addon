<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
use \WP_Query;use \ZipArchive;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
class Export {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_ajax_futurewordpress/project/ajax/export/tables', [$this, 'export_tables'], 10, 0);
		add_action('wp_ajax_nopriv_futurewordpress/project/ajax/export/tables', [$this, 'export_tables'], 10, 0);

		add_action('wp_ajax_futurewordpress/project/ajax/export/plugins', [$this, 'export_plugins'], 10, 0);
		add_action('wp_ajax_nopriv_futurewordpress/project/ajax/export/plugins', [$this, 'export_plugins'], 10, 0);

		add_action('admin_post_futurewordpress/project/ajax/download/plugin', [$this, 'download_plugin'], 10, 0);
		add_action('admin_post_nopriv_futurewordpress/project/ajax/download/plugin', [$this, 'download_plugin'], 10, 0);

		// add_filter('query_vars', [$this, 'query_vars'], 10, 1);
	}
	public function query_vars($queries) {
		$queries[] = 'paged';
		return $queries;
	}
	public function export_tables() {
		global $wpdb;$json = ['hooks' => ['export_tables_response'], 'message' => __('Operation Failed', 'teddybearsprompts')];
	
		// Get all table names
		$tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
	
		// Create an array to store the exported data
		$export_data = [];
	
		// Loop through each table
		foreach ($tables as $table) {
			$table_name = $table[0];
	
			// Get the table data
			$table_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
	
			// Add the table data to the export array
			$export_data[$table_name] = $table_data;
		}
	
		$json['exports'] = $export_data;
		// Set the export headers & Output the export data
		wp_send_json_success($json);
	
		// Prevent any further output
		exit;
	}
	public function export_plugins() {
		$json = ['hooks' => ['export_tables_response'], 'message' => __('Operation Failed', 'teddybearsprompts')];
		
		/**
		 * Get all plugins.
		 */
		$plugins = get_plugins();$all_plugins = [];
		foreach ($plugins as $plugin_file => $plugin_data) {
			$plugin_data['path'] = (array) $plugin_data['path']??[];
			$plugin_data['path']['dir_path'] = plugin_dir_path($plugin_file);
			$plugin_data['path']['root_path'] = WP_PLUGIN_DIR . '/' . plugin_dir_path($plugin_file);
			$plugin_data['path']['is_active'] = function_exists('is_plugin_active')?is_plugin_active($plugin_file):'N/A';
			$all_plugins[] = $plugin_data;
		}

		$json['exports'] = [
			'all_plugins'		=> $all_plugins,
			'active_plugins'	=> get_option('active_plugins', [])
		];
		wp_send_json_success($json);
	}
	public function download_plugin() {
		/**
		 * wp-admin/admin-post.php?action=futurewordpress/project/ajax/download/plugin&plugin_path=/home/.../gp-limit-dates/
		 */
		$pluginPath = $_POST['plugin_path'] ?? ($_GET['plugin_path'] ?? false);
		if (!$pluginPath) {
			return;
		}
		// Create a temporary file for the ZIP archive
		$tempFile = tempnam(sys_get_temp_dir(), 'plugin');
	
		// Create a new ZIP archive
		$zip = new ZipArchive();
		if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
			// Add the plugin directory and its contents to the ZIP archive
			$zip->addEmptyDir(basename($pluginPath));
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($pluginPath),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			foreach ($files as $name => $file) {
				if (!$file->isDir()) {
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen($pluginPath) + 1);
					$zip->addFile($filePath, $relativePath);
				}
			}
	
			// Close the ZIP archive
			$zip->close();
	
			// Set the appropriate headers for downloading the ZIP file
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="' . basename($pluginPath) . '.zip"');
			header('Content-Length: ' . filesize($tempFile));
	
			// Read and output the ZIP file contents
			readfile($tempFile);
	
			// Delete the temporary ZIP file
			unlink($tempFile);
		} else {
			// Failed to create the ZIP archive
			echo 'Failed to create the ZIP archive.';
		}
	}
	
}
