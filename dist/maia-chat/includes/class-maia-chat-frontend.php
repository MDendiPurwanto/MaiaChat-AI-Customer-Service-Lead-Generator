<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Maia_Chat_Frontend {
    private $settings;
    private $api;

    public function __construct($settings, $api) {
        $this->settings = $settings;
        $this->api = $api;

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_chat_widget'));
        
        // AJAX hooks with new prefix
        add_action('wp_ajax_maia_chat_get_response', array($this, 'handle_chat_request'));
        add_action('wp_ajax_nopriv_maia_chat_get_response', array($this, 'handle_chat_request'));
    }

    public function enqueue_assets() {
        wp_enqueue_style('maia-chat-style', MAIA_CHAT_URL . 'assets/css/style.css', array(), MAIA_CHAT_VERSION);
        wp_enqueue_script('maia-chat-script', MAIA_CHAT_URL . 'assets/js/script.js', array('jquery'), MAIA_CHAT_VERSION, true);

        $options = $this->settings->get_settings();
        wp_localize_script('maia-chat-script', 'maiaChatData', array(
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('maia-chat-nonce'),
            'primary_color' => isset($options['primary_color']) ? $options['primary_color'] : '#6366f1',
            'assistant_name'=> isset($options['assistant_name']) ? $options['assistant_name'] : 'Maia Assistant',
            'welcome_msg'   => isset($options['welcome_msg']) ? $options['welcome_msg'] : __('Hello!', 'maia-chat'),
            'handoff_wording' => isset($options['handoff_wording']) ? $options['handoff_wording'] : __('Hubungkan ke Admin', 'maia-chat'),
            'whatsapp_number' => isset($options['whatsapp_number']) ? $options['whatsapp_number'] : '',
            'enable_lead_gen' => isset($options['enable_lead_gen']) ? $options['enable_lead_gen'] : false,
        ));
    }

    public function render_chat_widget() {
        $options = $this->settings->get_settings();
        $assistant_name = isset($options['assistant_name']) ? $options['assistant_name'] : 'Maia Assistant';
        $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#6366f1';
        ?>
        <div id="maia-chat-widget" style="--primary-color: <?php echo esc_attr($primary_color); ?>;">
            <button id="maia-chat-toggle" class="maia-chat-fab">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="maia-svg-icon"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
            </button>

            <div id="maia-chat-modal" class="maia-chat-modal hidden">
                <div class="maia-chat-header">
                    <div class="maia-chat-info">
                        <div class="maia-chat-avatar">
                            <span class="maia-chat-status"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><circle cx="12" cy="12" r="3"/><path d="M12 7v2m0 6v2m-4-3h2m6 0h2"/></svg>
                        </div>
                        <div class="maia-chat-titles">
                            <h3><?php echo esc_html($assistant_name); ?></h3>
                            <span><?php esc_html_e('Online', 'maia-chat'); ?></span>
                        </div>
                    </div>
                    <button id="maia-chat-close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="maia-svg-icon"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div id="maia-chat-messages" class="maia-chat-messages">
                    <?php if (isset($options['enable_lead_gen']) && $options['enable_lead_gen']): ?>
                    <div id="maia-chat-lead-form" class="maia-chat-lead-form">
                        <div class="maia-chat-lead-form-header">
                            <div class="icon-box">
                                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <p><?php esc_html_e('Halo! Senang melihat Anda.', 'maia-chat'); ?></p>
                            <span class="form-desc"><?php esc_html_e('Silakan isi data berikut untuk mulai mengobrol.', 'maia-chat'); ?></span>
                        </div>
                        <div class="maia-field-group">
                            <label for="maia-lead-name"><?php esc_html_e('Nama Lengkap', 'maia-chat'); ?></label>
                            <input type="text" id="maia-lead-name" placeholder="<?php esc_attr_e('Contoh: Budi Santoso', 'maia-chat'); ?>" required>
                        </div>
                        <div class="maia-field-group">
                            <label for="maia-lead-phone"><?php esc_html_e('Nomor WhatsApp', 'maia-chat'); ?></label>
                            <input type="text" id="maia-lead-phone" placeholder="<?php esc_attr_e('Contoh: 08123456789', 'maia-chat'); ?>" required>
                        </div>
                        <button id="maia-lead-submit"><?php esc_html_e('Mulai Percakapan', 'maia-chat'); ?></button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="maia-chat-footer">
                    <div class="maia-chat-input-wrapper">
                        <input type="text" id="maia-chat-input" placeholder="<?php esc_attr_e('Tulis pesan...', 'maia-chat'); ?>" autocomplete="off">
                        <button id="maia-chat-send">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="maia-svg-icon"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </div>
                    <div class="maia-chat-branding">Powered by <a href="https://maiarouter.ai" target="_blank">MAIAROUTER</a></div>
                </div>
            </div>
        </div>
        <?php
    }

    public function handle_chat_request() {
        check_ajax_referer('maia-chat-nonce', 'nonce');

        $user_message = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';
        $user_name    = isset($_POST['user_name']) ? sanitize_text_field(wp_unslash($_POST['user_name'])) : __('Guest', 'maia-chat');
        $user_phone   = isset($_POST['user_phone']) ? sanitize_text_field(wp_unslash($_POST['user_phone'])) : 'N/A';
        $history_raw  = isset($_POST['history']) ? wp_unslash($_POST['history']) : '[]'; 
        $history      = json_decode($history_raw, true);

        if ($user_name !== __('Guest', 'maia-chat')) {
            $log_entry = sprintf("[%s] Lead: %s | Phone: %s | Msg: %s\n", gmdate('Y-m-d H:i:s'), $user_name, $user_phone, $user_message);
            file_put_contents(MAIA_CHAT_PATH . 'logs/leads.log', $log_entry, FILE_APPEND);
        }
        
        if (empty($user_message)) {
            wp_send_json_error('Empty message');
        }

        $options = $this->settings->get_settings();
        $manual_context  = isset($options['company_context']) ? $options['company_context'] : 'You are a customer service assistant.';
        $fetched_context = isset($options['fetched_context']) ? $options['fetched_context'] : '';
        $file_context    = isset($options['file_context']) ? $options['file_context'] : '';
        
        $system_context = $manual_context . 
                          "\n\nKnowledge Base from Website:\n" . $fetched_context . 
                          "\n\nKnowledge Base from Document:\n" . $file_context;

        $messages = array();
        $messages[] = array('role' => 'system', 'content' => $system_context);
        
        if (is_array($history)) {
            $history = array_slice($history, -10);
            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = array(
                        'role' => sanitize_text_field($msg['role']),
                        'content' => sanitize_text_field($msg['content'])
                    );
                }
            }
        }

        $messages[] = array('role' => 'user', 'content' => $user_message);
        $response = $this->api->get_response($messages);

        if (isset($response['error'])) {
            wp_send_json_error($response['error']);
        }

        $bot_message = isset($response['choices'][0]['message']['content']) ? $response['choices'][0]['message']['content'] : __('Sorry, I could not process that.', 'maia-chat');

        wp_send_json_success(array(
            'message' => $bot_message
        ));
    }
}
