<?php
/**
 * Plugin Name: MaiaChat: AI Customer Service & Lead Generator
 * Plugin URI: https://maiarouter.ai
 * Description: Premium AI Customer Service Assistant for WordPress, powered by MaiaRouter. Features Knowledge Base, Lead Capture, and WhatsApp Handoff. 
 * Version: 1.0.4
 * Author: MaiaRouter
 * Author URI: https://maiarouter.ai
 * License: GPLv2 or later
 * Text Domain: maia-chat
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Constants
define( 'CS_ASSISTANT_PATH', plugin_dir_path( __FILE__ ) );
define( 'CS_ASSISTANT_URL', plugin_dir_url( __FILE__ ) );
define( 'CS_ASSISTANT_VERSION', '1.0.4' );

// Include necessary files
require_once CS_ASSISTANT_PATH . 'includes/class-cs-assistant-settings.php';
require_once CS_ASSISTANT_PATH . 'includes/class-cs-assistant-api.php';
require_once CS_ASSISTANT_PATH . 'includes/class-cs-assistant-frontend.php';

// Initialize the plugin
function cs_assistant_init() {
	$settings = new CS_Assistant_Settings();
	$api      = new CS_Assistant_API();
	new CS_Assistant_Frontend( $settings, $api );
}
add_action( 'plugins_loaded', 'cs_assistant_init' );

// Register activation hook
register_activation_hook( __FILE__, 'cs_assistant_activate' );
function cs_assistant_activate() {
    // Default settings
    if ( ! get_option( 'cs_assistant_settings' ) ) {
        update_option( 'cs_assistant_settings', [
            'assistant_name'  => 'CS Assistant',
            'primary_color'   => '#6366f1',
            'welcome_msg'     => 'Hello! How can I help you today?',
            'company_context' => 'You are a helpful customer service assistant.',
            'maia_api_key'    => '',
            'maia_model'      => 'maia/gemini-2.5-flash', // Default model placeholder
        ] );
    }
}
