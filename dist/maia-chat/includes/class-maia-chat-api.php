<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Maia_Chat_API {
    private $api_key;
    private $model;
    private $endpoint = 'https://api.maiarouter.ai/v1/chat/completions';

    public function __construct() {
        $settings = get_option('maia_chat_settings');
        $this->api_key = isset($settings['maia_api_key']) ? $settings['maia_api_key'] : '';
        $this->model = isset($settings['maia_model']) ? $settings['maia_model'] : 'maia/gemini-2.5-flash';
    }

    public function get_response($messages) {
        if (empty($this->api_key)) {
            return array('error' => __('API Key not configured.', 'maia-chat'));
        }

        $body = array(
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false
        );

        $response = wp_remote_post($this->endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($status_code !== 200) {
            /* translators: %d: API status code */
            $error_msg = isset($data['error']['message']) ? $data['error']['message'] : sprintf(__('API Error %d', 'maia-chat'), $status_code);
            return array('error' => $error_msg);
        }

        return $data;
    }
}
