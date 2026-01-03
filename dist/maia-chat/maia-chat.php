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
define( 'MAIA_CHAT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAIA_CHAT_URL', plugin_dir_url( __FILE__ ) );
define( 'MAIA_CHAT_VERSION', '1.0.4' );

// Include necessary files
require_once MAIA_CHAT_PATH . 'includes/class-maia-chat-settings.php';
require_once MAIA_CHAT_PATH . 'includes/class-maia-chat-api.php';
require_once MAIA_CHAT_PATH . 'includes/class-maia-chat-frontend.php';

// Initialize the plugin
function maia_chat_init() {
	$settings = new Maia_Chat_Settings();
	$api      = new Maia_Chat_API();
	new Maia_Chat_Frontend( $settings, $api );
}
add_action( 'plugins_loaded', 'maia_chat_init' );

// Register activation hook
register_activation_hook( __FILE__, 'maia_chat_activate' );
function maia_chat_activate() {
    // Create logs directory
    $log_dir = MAIA_CHAT_PATH . 'logs';
    if ( ! file_exists( $log_dir ) ) {
        wp_mkdir_p( $log_dir );
    }

    // Default settings
    if ( ! get_option( 'maia_chat_settings' ) ) {
        update_option( 'maia_chat_settings', [
            'assistant_name'  => 'Maia Assistant',
            'primary_color'   => '#6366f1',
            'welcome_msg'     => 'Hello! How can I help you today?',
            'company_context' => 'You are a helpful customer service assistant.',
            'maia_api_key'    => '',
            'maia_model'      => 'maia/gemini-2.5-flash',
        ] );
    }
}
