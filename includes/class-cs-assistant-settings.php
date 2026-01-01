<?php

class CS_Assistant_Settings {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        $this->options = get_option('cs_assistant_settings');
    }

    public function add_plugin_page() {
        add_menu_page(
            'CS Assistant Settings',
            'CS Assistant',
            'manage_options',
            'cs-assistant-settings',
            array($this, 'create_admin_page'),
            'dashicons-smartwatch',
            100
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>CS Assistant Settings</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('cs_assistant_group');
                do_settings_sections('cs-assistant-settings');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'cs_assistant_group',
            'cs_assistant_settings',
            array($this, 'sanitize')
        );

        // General Section
        add_settings_section(
            'setting_section_id',
            'General Configuration',
            null,
            'cs-assistant-settings'
        );

        add_settings_field(
            'maia_api_key',
            'MaiaRouter API Key',
            array($this, 'maia_api_key_callback'),
            'cs-assistant-settings',
            'setting_section_id'
        );

        add_settings_field(
            'maia_model',
            'MaiaRouter Model',
            array($this, 'maia_model_callback'),
            'cs-assistant-settings',
            'setting_section_id'
        );

        add_settings_field(
            'assistant_name',
            'Assistant Name',
            array($this, 'assistant_name_callback'),
            'cs-assistant-settings',
            'setting_section_id'
        );

        add_settings_field(
            'primary_color',
            'Primary Color',
            array($this, 'primary_color_callback'),
            'cs-assistant-settings',
            'setting_section_id'
        );

        add_settings_field(
            'welcome_msg',
            'Welcome Message',
            array($this, 'welcome_msg_callback'),
            'cs-assistant-settings',
            'setting_section_id'
        );

        // S3 Section
        add_settings_section(
            's3_section_id',
            'S3 Storage Configuration (Optional)',
            null,
            'cs-assistant-settings'
        );

        foreach (['s3_bucket', 's3_region', 's3_access_key', 's3_secret_key'] as $field) {
            add_settings_field(
                $field,
                ucwords(str_replace('_', ' ', $field)),
                array($this, 's3_field_callback'),
                'cs-assistant-settings',
                's3_section_id',
                array('label_for' => $field)
            );
        }

        // Knowledge Base Section
        add_settings_section(
            'knowledge_section_id',
            'Knowledge Base',
            null,
            'cs-assistant-settings'
        );

        add_settings_field(
            'knowledge_file',
            'Upload Document (.txt, .md)',
            array($this, 'knowledge_file_callback'),
            'cs-assistant-settings',
            'knowledge_section_id'
        );

        add_settings_field(
            'knowledge_url_base',
            'Knowledge Source (URL)',
            array($this, 'knowledge_url_callback'),
            'cs-assistant-settings',
            'knowledge_section_id'
        );

        add_settings_field(
            'company_context',
            'Manual Context / Branding',
            array($this, 'company_context_callback'),
            'cs-assistant-settings',
            'knowledge_section_id'
        );

        add_settings_field(
            'fetched_context',
            'Fetched Content (Read Only)',
            array($this, 'fetched_context_callback'),
            'cs-assistant-settings',
            'knowledge_section_id'
        );

        // Tracking & Handoff Section
        add_settings_section(
            'tracking_section_id',
            'Tracking & Human Agent Handoff',
            null,
            'cs-assistant-settings'
        );

        add_settings_field(
            'whatsapp_number',
            'WhatsApp Number (for Human Agent)',
            array($this, 'whatsapp_number_callback'),
            'cs-assistant-settings',
            'tracking_section_id'
        );

        add_settings_field(
            'handoff_wording',
            'Handoff Button Wording',
            array($this, 'handoff_wording_callback'),
            'cs-assistant-settings',
            'tracking_section_id'
        );

        add_settings_field(
            'enable_lead_gen',
            'Enable Lead Collection (Email/HP)',
            array($this, 'enable_lead_gen_callback'),
            'cs-assistant-settings',
            'tracking_section_id'
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
            
            // If URL is provided, fetch content
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

        // Handle File Context Extraction if file ID changed
        if ( isset($input['knowledge_file_id']) && $input['knowledge_file_id'] !== (isset($this->options['knowledge_file_id']) ? $this->options['knowledge_file_id'] : '')) {
            $file_path = get_attached_file($input['knowledge_file_id']);
            if ($file_path && file_exists($file_path)) {
                $file_content = file_get_contents($file_path);
                // Basic text extraction (works for .txt, .md)
                // For PDF, would need a library, but let's do a placeholder
                if (pathinfo($file_path, PATHINFO_EXTENSION) === 'pdf') {
                    $new_input['file_context'] = "[PDF Content Analysis required. Text successfully uploaded to S3/Server]";
                } else {
                    $new_input['file_context'] = mb_substr(wp_strip_all_tags($file_content), 0, 5000);
                }
            }
        } else {
            // Keep existing if not changed
            $new_input['file_context'] = isset($this->options['file_context']) ? $this->options['file_context'] : '';
        }

        return $new_input;
    }

    public function maia_api_key_callback() {
        printf(
            '<input type="password" id="maia_api_key" name="cs_assistant_settings[maia_api_key]" value="%s" class="regular-text" />',
            isset( $this->options['maia_api_key'] ) ? esc_attr( $this->options['maia_api_key']) : ''
        );
    }

    public function maia_model_callback() {
        printf(
            '<input type="text" id="maia_model" name="cs_assistant_settings[maia_model]" value="%s" class="regular-text" placeholder="e.g. gpt-4o" />',
            isset( $this->options['maia_model'] ) ? esc_attr( $this->options['maia_model']) : 'gpt-3.5-turbo'
        );
    }

    public function assistant_name_callback() {
        printf(
            '<input type="text" id="assistant_name" name="cs_assistant_settings[assistant_name]" value="%s" class="regular-text" />',
            isset( $this->options['assistant_name'] ) ? esc_attr( $this->options['assistant_name']) : 'CS Assistant'
        );
    }

    public function primary_color_callback() {
        printf(
            '<input type="color" id="primary_color" name="cs_assistant_settings[primary_color]" value="%s" />',
            isset( $this->options['primary_color'] ) ? esc_attr( $this->options['primary_color']) : '#6366f1'
        );
    }

    public function welcome_msg_callback() {
        printf(
            '<textarea id="welcome_msg" name="cs_assistant_settings[welcome_msg]" rows="3" class="large-text">%s</textarea>',
            isset( $this->options['welcome_msg'] ) ? esc_textarea( $this->options['welcome_msg']) : ''
        );
    }

    public function company_context_callback() {
        printf(
            '<textarea id="company_context" name="cs_assistant_settings[company_context]" rows="5" class="large-text" placeholder="Branding guidelines, personality, etc...">%s</textarea>',
            isset( $this->options['company_context'] ) ? esc_textarea( $this->options['company_context']) : ''
        );
    }

    public function s3_field_callback($args) {
        $id = $args['label_for'];
        $type = (strpos($id, 'secret') !== false) ? 'password' : 'text';
        printf(
            '<input type="%s" id="%s" name="cs_assistant_settings[%s]" value="%s" class="regular-text" />',
            $type, $id, $id, isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : ''
        );
    }

    public function knowledge_file_callback() {
        $file_id = isset( $this->options['knowledge_file_id'] ) ? $this->options['knowledge_file_id'] : '';
        $file_url = $file_id ? wp_get_attachment_url($file_id) : '';
        ?>
        <div class="cs-assistant-upload-wrapper">
            <input type="hidden" id="knowledge_file_id" name="cs_assistant_settings[knowledge_file_id]" value="<?php echo esc_attr($file_id); ?>" />
            <div id="knowledge_file_preview" style="margin-bottom:10px;">
                <?php if ($file_url): ?>
                    <code><?php echo esc_html(basename($file_url)); ?></code>
                <?php endif; ?>
            </div>
            <button type="button" class="button cs-assistant-upload-btn" id="cs_assistant_upload_btn">Select File</button>
            <button type="button" class="button cs-assistant-remove-btn" id="cs_assistant_remove_btn" <?php echo $file_id ? '' : 'style="display:none;"'; ?>>Remove</button>
            <p class="description">Upload file .txt atau .md. Jika Anda menggunakan plugin S3 (seperti WP Offload Media), file ini otomatis aman di S3.</p>
        </div>
        <script>
            jQuery(document).ready(function($){
                var frame;
                $('#cs_assistant_upload_btn').on('click', function(e) {
                    e.preventDefault();
                    if (frame) { frame.open(); return; }
                    frame = wp.media({ title: 'Select Knowledge File', button: { text: 'Use this file' }, multiple: false });
                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#knowledge_file_id').val(attachment.id);
                        $('#knowledge_file_preview').html('<code>' + attachment.filename + '</code>');
                        $('#cs_assistant_remove_btn').show();
                    });
                    frame.open();
                });
                $('#cs_assistant_remove_btn').on('click', function() {
                    $('#knowledge_file_id').val('');
                    $('#knowledge_file_preview').empty();
                    $(this).hide();
                });
            });
        </script>
        <?php
    }

    public function knowledge_url_callback() {
        printf(
            '<input type="url" id="knowledge_url" name="cs_assistant_settings[knowledge_url]" value="%s" class="large-text" placeholder="https://example.com/about" />
            <p class="description">Masukkan URL halaman bantuan atau "About Us" untuk diambil informasinya.</p>',
            isset( $this->options['knowledge_url'] ) ? esc_url( $this->options['knowledge_url']) : ''
        );
    }

    public function fetched_context_callback() {
        $content = isset( $this->options['fetched_context'] ) ? $this->options['fetched_context'] : 'No content fetched yet. Save settings to fetch.';
        printf(
            '<textarea id="fetched_context" readonly rows="10" class="large-text" style="background:#f0f0f1;">%s</textarea>
            <p class="description">Ini adalah teks yang berhasil diambil dari URL di atas.</p>',
            esc_textarea( $content )
        );
    }

    public function whatsapp_number_callback() {
        printf(
            '<input type="text" id="whatsapp_number" name="cs_assistant_settings[whatsapp_number]" value="%s" class="regular-text" placeholder="62812345678" />
            <p class="description">Gunakan format internasional tanpa tanda + (contoh: 628xxx).</p>',
            isset( $this->options['whatsapp_number'] ) ? esc_attr( $this->options['whatsapp_number']) : ''
        );
    }

    public function handoff_wording_callback() {
        printf(
            '<input type="text" id="handoff_wording" name="cs_assistant_settings[handoff_wording]" value="%s" class="regular-text" placeholder="Chat dengan Admin" />',
            isset( $this->options['handoff_wording'] ) ? esc_attr( $this->options['handoff_wording']) : 'Hubungkan ke Agen Manusia'
        );
    }

    public function enable_lead_gen_callback() {
        $checked = isset($this->options['enable_lead_gen']) && $this->options['enable_lead_gen'] ? 'checked' : '';
        printf(
            '<input type="checkbox" name="cs_assistant_settings[enable_lead_gen]" value="1" %s /> Aktifkan formulir Nama/No HP sebelum mulai chat.',
            $checked
        );
    }

    public function get_settings() {
        return get_option('cs_assistant_settings');
    }
}
