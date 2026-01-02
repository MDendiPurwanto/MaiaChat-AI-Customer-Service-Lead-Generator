<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Maia_Chat_Settings {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        $this->options = get_option('maia_chat_settings');
    }

    public function add_plugin_page() {
        add_menu_page(
            __('MaiaChat Settings', 'maia-chat'),
            __('MaiaChat', 'maia-chat'),
            'manage_options',
            'maia-chat-settings',
            array($this, 'create_admin_page'),
            'dashicons-smartwatch',
            100
        );

        add_submenu_page(
            'maia-chat-settings',
            __('Lead Logs', 'maia-chat'),
            __('Lead Logs', 'maia-chat'),
            'manage_options',
            'maia-chat-leads',
            array($this, 'create_lead_logs_page')
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('MaiaChat Settings', 'maia-chat'); ?></h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('maia_chat_group');
                do_settings_sections('maia-chat-settings');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'maia_chat_group',
            'maia_chat_settings',
            array($this, 'sanitize')
        );

        add_settings_section(
            'maia_chat_general_section',
            __('General Configuration', 'maia-chat'),
            array($this, 'general_section_callback'),
            'maia-chat-settings'
        );

        add_settings_field(
            'maia_api_key',
            __('MaiaRouter API Key', 'maia-chat'),
            array($this, 'maia_api_key_callback'),
            'maia-chat-settings',
            'maia_chat_general_section'
        );

        add_settings_field(
            'maia_model',
            __('MaiaRouter Model', 'maia-chat'),
            array($this, 'maia_model_callback'),
            'maia-chat-settings',
            'maia_chat_general_section'
        );

        add_settings_field(
            'assistant_name',
            __('Assistant Name', 'maia-chat'),
            array($this, 'assistant_name_callback'),
            'maia-chat-settings',
            'maia_chat_general_section'
        );

        add_settings_field(
            'primary_color',
            __('Primary Color', 'maia-chat'),
            array($this, 'primary_color_callback'),
            'maia-chat-settings',
            'maia_chat_general_section'
        );

        add_settings_field(
            'welcome_msg',
            __('Welcome Message', 'maia-chat'),
            array($this, 'welcome_msg_callback'),
            'maia-chat-settings',
            'maia_chat_general_section'
        );

        add_settings_section(
            'maia_chat_s3_section',
            __('S3 Storage Configuration (Optional)', 'maia-chat'),
            null,
            'maia-chat-settings'
        );

        foreach (['s3_bucket', 's3_region', 's3_access_key', 's3_secret_key'] as $field) {
            add_settings_field(
                $field,
                ucwords(str_replace('_', ' ', $field)),
                array($this, 's3_field_callback'),
                'maia-chat-settings',
                'maia_chat_s3_section',
                array('label_for' => $field)
            );
        }

        add_settings_section(
            'maia_chat_knowledge_section',
            __('Knowledge Base', 'maia-chat'),
            null,
            'maia-chat-settings'
        );

        add_settings_field(
            'knowledge_file',
            __('Upload Document (.txt, .md)', 'maia-chat'),
            array($this, 'knowledge_file_callback'),
            'maia-chat-settings',
            'maia_chat_knowledge_section'
        );

        add_settings_field(
            'knowledge_url_base',
            __('Knowledge Source (URL)', 'maia-chat'),
            array($this, 'knowledge_url_callback'),
            'maia-chat-settings',
            'maia_chat_knowledge_section'
        );

        add_settings_field(
            'company_context',
            __('Manual Context / Branding', 'maia-chat'),
            array($this, 'company_context_callback'),
            'maia-chat-settings',
            'maia_chat_knowledge_section'
        );

        add_settings_field(
            'fetched_context',
            __('Fetched Content (Read Only)', 'maia-chat'),
            array($this, 'fetched_context_callback'),
            'maia-chat-settings',
            'maia_chat_knowledge_section'
        );

        add_settings_section(
            'maia_chat_tracking_section',
            __('Tracking & Human Agent Handoff', 'maia-chat'),
            null,
            'maia-chat-settings'
        );

        add_settings_field(
            'whatsapp_number',
            __('WhatsApp Number (for Human Agent)', 'maia-chat'),
            array($this, 'whatsapp_number_callback'),
            'maia-chat-settings',
            'maia_chat_tracking_section'
        );

        add_settings_field(
            'handoff_wording',
            __('Handoff Button Wording', 'maia-chat'),
            array($this, 'handoff_wording_callback'),
            'maia-chat-settings',
            'maia_chat_tracking_section'
        );

        add_settings_field(
            'enable_lead_gen',
            __('Enable Lead Collection (Email/HP)', 'maia-chat'),
            array($this, 'enable_lead_gen_callback'),
            'maia-chat-settings',
            'maia_chat_tracking_section'
        );
    }

    public function general_section_callback() {
        printf('<p>%s <a href="%s" target="_blank">%s</a></p>', 
            __('Untuk mendapatkan API Key dan melihat daftar model yang didukung, silakan kunjungi', 'maia-chat'),
            'https://maiarouter.notion.site/MAIA-Router-API-Quick-Start-2a1e955fd85480738376ed283c352232',
            __('Dokumentasi Resmi MaiaRouter', 'maia-chat')
        );
    }

    public function sanitize($input) {
        $new_input = array();
        if( isset( $input['maia_api_key'] ) ) $new_input['maia_api_key'] = sanitize_text_field( $input['maia_api_key'] );
        if( isset( $input['maia_model'] ) ) $new_input['maia_model'] = sanitize_text_field( $input['maia_model'] );
        if( isset( $input['assistant_name'] ) ) $new_input['assistant_name'] = sanitize_text_field( $input['assistant_name'] );
        if( isset( $input['primary_color'] ) ) $new_input['primary_color'] = sanitize_hex_color( $input['primary_color'] );
        if( isset( $input['welcome_msg'] ) ) $new_input['welcome_msg'] = sanitize_textarea_field( $input['welcome_msg'] );
        if( isset( $input['company_context'] ) ) $new_input['company_context'] = sanitize_textarea_field( $input['company_context'] );
        if( isset( $input['whatsapp_number'] ) ) $new_input['whatsapp_number'] = sanitize_text_field( $input['whatsapp_number'] );
        if( isset( $input['handoff_wording'] ) ) $new_input['handoff_wording'] = sanitize_text_field( $input['handoff_wording'] );
        if( isset( $input['enable_lead_gen'] ) ) $new_input['enable_lead_gen'] = (int) $input['enable_lead_gen'];

        if( isset( $input['s3_bucket'] ) ) $new_input['s3_bucket'] = sanitize_text_field( $input['s3_bucket'] );
        if( isset( $input['s3_region'] ) ) $new_input['s3_region'] = sanitize_text_field( $input['s3_region'] );
        if( isset( $input['s3_access_key'] ) ) $new_input['s3_access_key'] = sanitize_text_field( $input['s3_access_key'] );
        if( isset( $input['s3_secret_key'] ) ) $new_input['s3_secret_key'] = sanitize_text_field( $input['s3_secret_key'] );
        if( isset( $input['knowledge_file_id'] ) ) $new_input['knowledge_file_id'] = sanitize_text_field( $input['knowledge_file_id'] );

        if( isset( $input['knowledge_url'] ) ) {
            $url = esc_url_raw( $input['knowledge_url'] );
            $new_input['knowledge_url'] = $url;
            if ( ! empty( $url ) ) {
                $response = wp_remote_get( $url );
                if ( ! is_wp_error( $response ) ) {
                    $body = wp_remote_retrieve_body( $response );
                    $clean_content = wp_strip_all_tags( $body );
                    $clean_content = preg_replace( '/\s+/', ' ', $clean_content );
                    $new_input['fetched_context'] = mb_substr( $clean_content, 0, 10000 );
                }
            }
        }

        if ( isset($input['knowledge_file_id']) && $input['knowledge_file_id'] !== (isset($this->options['knowledge_file_id']) ? $this->options['knowledge_file_id'] : '')) {
            $file_path = get_attached_file($input['knowledge_file_id']);
            if ($file_path && file_exists($file_path)) {
                $file_content = file_get_contents($file_path);
                if (pathinfo($file_path, PATHINFO_EXTENSION) === 'pdf') {
                    $new_input['file_context'] = "[PDF Content Analysis required]";
                } else {
                    $new_input['file_context'] = mb_substr(wp_strip_all_tags($file_content), 0, 5000);
                }
            }
        } else {
            $new_input['file_context'] = isset($this->options['file_context']) ? $this->options['file_context'] : '';
        }

        return $new_input;
    }

    public function maia_api_key_callback() {
        printf('<input type="password" id="maia_api_key" name="maia_chat_settings[maia_api_key]" value="%s" class="regular-text" />', 
            isset( $this->options['maia_api_key'] ) ? esc_attr( $this->options['maia_api_key']) : '');
    }

    public function maia_model_callback() {
        printf('<input type="text" id="maia_model" name="maia_chat_settings[maia_model]" value="%s" class="regular-text" placeholder="e.g. gpt-4o" />', 
            isset( $this->options['maia_model'] ) ? esc_attr( $this->options['maia_model']) : 'maia/gemini-2.5-flash');
    }

    public function assistant_name_callback() {
        printf('<input type="text" id="assistant_name" name="maia_chat_settings[assistant_name]" value="%s" class="regular-text" />', 
            isset( $this->options['assistant_name'] ) ? esc_attr( $this->options['assistant_name']) : 'Maia Assistant');
    }

    public function primary_color_callback() {
        printf('<input type="color" id="primary_color" name="maia_chat_settings[primary_color]" value="%s" />', 
            isset( $this->options['primary_color'] ) ? esc_attr( $this->options['primary_color']) : '#6366f1');
    }

    public function welcome_msg_callback() {
        printf('<textarea id="welcome_msg" name="maia_chat_settings[welcome_msg]" rows="3" class="large-text">%s</textarea>', 
            isset( $this->options['welcome_msg'] ) ? esc_textarea( $this->options['welcome_msg']) : '');
    }

    public function company_context_callback() {
        printf('<textarea id="company_context" name="maia_chat_settings[company_context]" rows="5" class="large-text">%s</textarea>', 
            isset( $this->options['company_context'] ) ? esc_textarea( $this->options['company_context']) : '');
    }

    public function s3_field_callback($args) {
        $id = $args['label_for'];
        $type = (strpos($id, 'secret') !== false) ? 'password' : 'text';
        printf('<input type="%s" id="%s" name="maia_chat_settings[%s]" value="%s" class="regular-text" />', 
            $type, $id, $id, isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : '');
    }

    public function knowledge_file_callback() {
        $file_id = isset( $this->options['knowledge_file_id'] ) ? $this->options['knowledge_file_id'] : '';
        $file_url = $file_id ? wp_get_attachment_url($file_id) : '';
        ?>
        <div class="maia-chat-upload-wrapper">
            <input type="hidden" id="knowledge_file_id" name="maia_chat_settings[knowledge_file_id]" value="<?php echo esc_attr($file_id); ?>" />
            <div id="knowledge_file_preview" style="margin-bottom:10px;">
                <?php if ($file_url): ?><code><?php echo esc_html(basename($file_url)); ?></code><?php endif; ?>
            </div>
            <button type="button" class="button" id="maia_chat_upload_btn"><?php _e('Select File', 'maia-chat'); ?></button>
            <button type="button" class="button" id="maia_chat_remove_btn" <?php echo $file_id ? '' : 'style="display:none;"'; ?>><?php _e('Remove', 'maia-chat'); ?></button>
            <p class="description"><?php _e('Upload file .txt atau .md.', 'maia-chat'); ?></p>
        </div>
        <script>
            jQuery(document).ready(function($){
                var frame;
                $('#maia_chat_upload_btn').on('click', function(e) {
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({ title: 'Select Knowledge File', button: { text: 'Use this file' }, multiple: false });
                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#knowledge_file_id').val(attachment.id);
                        $('#knowledge_file_preview').html('<code>' + attachment.filename + '</code>');
                        $('#maia_chat_remove_btn').show();
                    });
                    frame.open();
                });
                $('#maia_chat_remove_btn').on('click', function() {
                    $('#knowledge_file_id').val('');
                    $('#knowledge_file_preview').empty();
                    $(this).hide();
                });
            });
        </script>
        <?php
    }

    public function knowledge_url_callback() {
        printf('<input type="url" id="knowledge_url" name="maia_chat_settings[knowledge_url]" value="%s" class="large-text" />
            <p class="description">%s</p>',
            isset( $this->options['knowledge_url'] ) ? esc_url( $this->options['knowledge_url']) : '',
            __('Masukkan URL untuk pengambilan informasi.', 'maia-chat'));
    }

    public function fetched_context_callback() {
        $content = isset( $this->options['fetched_context'] ) ? $this->options['fetched_context'] : __('No content yet.', 'maia-chat');
        printf('<textarea id="fetched_context" readonly rows="10" class="large-text" style="background:#f0f1f2;">%s</textarea>', esc_textarea( $content ));
    }

    public function whatsapp_number_callback() {
        printf('<input type="text" id="whatsapp_number" name="maia_chat_settings[whatsapp_number]" value="%s" class="regular-text" />', 
            isset( $this->options['whatsapp_number'] ) ? esc_attr( $this->options['whatsapp_number']) : '');
    }

    public function handoff_wording_callback() {
        printf('<input type="text" id="handoff_wording" name="maia_chat_settings[handoff_wording]" value="%s" class="regular-text" />', 
            isset( $this->options['handoff_wording'] ) ? esc_attr( $this->options['handoff_wording']) : __('Hubungkan ke Agen', 'maia-chat'));
    }

    public function enable_lead_gen_callback() {
        $checked = isset($this->options['enable_lead_gen']) && $this->options['enable_lead_gen'] ? 'checked' : '';
        printf('<input type="checkbox" name="maia_chat_settings[enable_lead_gen]" value="1" %s /> %s', $checked, __('Aktifkan formulir Nama/No HP.', 'maia-chat'));
    }

    public function get_settings() {
        return get_option('maia_chat_settings');
    }

    public function create_lead_logs_page() {
        $log_file = MAIA_CHAT_PATH . 'logs/leads.log';
        ?>
        <div class="wrap">
            <h1><?php _e('MaiaChat Lead Logs', 'maia-chat'); ?></h1>
            <p><?php _e('Riwayat interaksi pelanggan yang mengisi formulir Lead Generation.', 'maia-chat'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="20%"><?php _e('Waktu', 'maia-chat'); ?></th>
                        <th width="20%"><?php _e('Nama Pelanggan', 'maia-chat'); ?></th>
                        <th width="20%"><?php _e('Nomor WhatsApp', 'maia-chat'); ?></th>
                        <th><?php _e('Pesan Terakhir', 'maia-chat'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (file_exists($log_file)) {
                    $lines = file($log_file);
                    $lines = array_reverse($lines); // Show newest first
                    
                    foreach ($lines as $line) {
                        // Expected format: [Time] Lead: Name | Phone: Number | Msg: Message
                        preg_match('/\[(.*?)\] Lead: (.*?) \| Phone: (.*?) \| Msg: (.*)/', $line, $matches);
                        
                        if (count($matches) >= 5) {
                            echo '<tr>';
                            echo '<td>' . esc_html($matches[1]) . '</td>';
                            echo '<td>' . esc_html($matches[2]) . '</td>';
                            echo '<td><a href="https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $matches[3])) . '" target="_blank">' . esc_html($matches[3]) . '</a></td>';
                            echo '<td>' . esc_html($matches[4]) . '</td>';
                            echo '</tr>';
                        }
                    }

                    if (empty($lines)) {
                        echo '<tr><td colspan="4">' . __('Belum ada data lead masuk.', 'maia-chat') . '</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">' . __('File log tidak ditemukan atau belum ada interaksi.', 'maia-chat') . '</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
