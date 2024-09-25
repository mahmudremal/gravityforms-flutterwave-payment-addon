<?php
/**
 * The OpenAI ChatGPT-3.
 * https://www.npmjs.com/package/openai
 * https://www.npmjs.com/package/chatgpt
 * 
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;

class Gpt3 {
	use Singleton;
	private $base;
	protected function __construct() {
    	$this->base = [];
		$this->setup_hooks();
	}
	protected function setup_hooks() {
	}
  
}
