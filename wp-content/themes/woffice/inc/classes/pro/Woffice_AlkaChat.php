<?php
/**
 * Class Woffice_AlkaChat
 *
 * Manage everything related to the AlkaChat
 *
 * @since 2.5.1
 * @author Alkaweb
 */

if( ! class_exists( 'Woffice_AlkaChat' ) ) {
    class Woffice_AlkaChat
    {

        private $endpoint = 'http://localhost:8888/alkahub/public/api/chat/';

        /**
         * Woffice_AlkaChat constructor
         */
        public function __construct()
        {

            if(!function_exists('bp_is_active') || !is_user_logged_in()) {
                return null;
            }

            add_action( 'wp_footer',                        array($this, 'render'));
            add_action( 'wp_ajax_woffice_alka_chat',        array($this, 'ajaxCallback'));
            add_action( 'wp_ajax_nopriv_woffice_alka_chat', array($this, 'ajaxCallback'));
            add_filter( 'woffice_js_exchanged_data',        array($this, 'exchanger'));
            add_filter( 'fw_settings_form_saved',           array($this, 'createConversationsCheckTable'));

        }

        /**
         * Gets the conversations check table name
         *
         * @return string
         */
        private function getConversationsCheckTableName() {
            global $wpdb;
            return $wpdb->prefix . 'woffice_chat_conversations_check';
        }

        /**
         * Get a conversation last check for the current user
         *
         * @param integer $conversation_id
         * @return null|object
         */
        private function getLastConversationCheck($conversation_id) {
            global $wpdb;
            $query = array();
            $query['select']    = 'SELECT *';
            $query['from']      = 'FROM '.$this->getConversationsCheckTableName();
            $query['where']     = 'WHERE `user_id` = '.get_current_user_id().' AND `conversation_id` = %d';
            return $wpdb->get_row($wpdb->prepare( implode(" ",$query), $conversation_id));
        }

        /**
         * Create or update a conversation last check for the current user
         *
         * @param $conversation_id
         */
        private function setLastConversationCheck($conversation_id) {
            global $wpdb;
            $exist = ($this->getLastConversationCheck($conversation_id) !== null);
            $query = array();
            $now = time();
            if($exist) {
                $query['update']    = 'UPDATE '.$this->getConversationsCheckTableName();
                $query['set']       = 'SET `last_checked` = '.$now;
                $query['where']     = 'WHERE `user_id` = '.get_current_user_id().' AND `conversation_id` = %d';
            } else {
                $query['inset']     = 'INSERT INTO '.$this->getConversationsCheckTableName() .' (last_checked, conversation_id, user_id)';
                $query['values']    = 'VALUES ('.$now.', %d, '.get_current_user_id().')';
            }
            $wpdb->query($wpdb->prepare( implode(" ",$query), $conversation_id));

        }

        /**
         * Creates a woffice_chat_conversations_check table
         */
        public function createConversationsCheckTable() {

            global $wpdb;

            $table_name = $this->getConversationsCheckTableName();

            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name || !self::isEnabled())
                return;

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                last_checked INTEGER NOT NULL,
                conversation_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }

        /**
         * Pass data to the client
         *
         * @param array $data
         * @return array
         */
        public function exchanger($data) {

            $data['alka_chat'] = array(
                'actions' => array(
                    'new_conversation' => __('New conversation', 'woffice'),
                    //'connect' => __('Connect', 'woffice'),
                    'refresh' => __('Refresh', 'woffice'),
                ),
                'labels' => array(
                    'new_conversation' => __('Create the conversation', 'woffice'),
                    'new_conversation_conversations_placeholder' => __('Please type member ID(s) or username(s)', 'woffice'),
                    'new_conversation_title_label' => __('Conversation title', 'woffice'),
                    'new_conversation_title' => __('Conversation with'),
                    'send' => __('Send'),
                    'not_found' => __('No message found...Send one to start chatting.')
                ),
                'current_user' => get_current_user_id(),
                'nonce' => wp_create_nonce('woffice_alka_chat')
            );

            return $this->formatData($data);

        }

        /**
         * Formatting the data passed to the client
         *
         * @param array $data
         * @return array
         */
        private function formatData($data) {

            // Will be replaced by some options later
            $custom_tab_enabled = woffice_get_settings_option('alka_pro_chat_welcome_enabled');
            $custom_tab_title = woffice_get_settings_option('alka_pro_chat_welcome_title');
            $custom_tab_content = woffice_get_settings_option('alka_pro_chat_welcome_message');
            $has_emojis = woffice_get_settings_option('alka_pro_chat_emojis_enabled');
            $refresh_time = woffice_get_settings_option('alka_pro_chat_refresh_time');

            if(!$custom_tab_enabled)
                return $data;

            $data['alka_chat']['refresh_time'] = $refresh_time;
            $data['alka_chat']['has_emojis'] = $has_emojis;
            $data['alka_chat']['actions']['custom_tab'] = $custom_tab_title;
            $data['alka_chat']['custom_tab'] = $custom_tab_content;

            return $data;

        }

        /**
         * Receive the callbacks from the client
         */
        public function ajaxCallback() {

            // Quick validation
            if (!wp_verify_nonce($_POST['_nonce'], 'woffice_alka_chat' ) || !defined( 'DOING_AJAX' ) || !DOING_AJAX) {
                echo json_encode(array(
                   'type' => 'error',
                    'message' => __('There is a security issue in your request.','woffice')
                ));
                die();
            }

            // Requirements for the API call
            if(!isset($_POST['api_method']) || !isset($_POST['api_target'])) {
                echo json_encode(array(
                    'type' => 'error',
                    'message' => __('Some information is missing from the client request.','woffice')
                ));
                die();
            }

            // We set a default version as it's not a required parameter
            $payload = (!isset($_POST['api_payload'])) ? [] : $_POST['api_payload'];

            echo $this->hubApiHandler($_POST['api_method'], $_POST['api_target'], $payload);
            die();

        }

        /**
         * Handle the Alkaweb API Calls
         *
         * @param string $method - the HTTP method
         * @param string $target - the API target
         * @param array $payload - the data sent in the request
         * @return string - what's returned by the API (encoded in JSON)
         */
        private function hubApiHandler($method, $target, $payload){

            $args = array(
                'headers' => array(
                    'x-email' => base64_encode(get_option('admin_email')),
                    'x-productKey' => base64_encode(get_option('woffice_key'))
                ),
                'body' => $payload,
                'method' => $method
            );

            $response = wp_remote_request( $this->endpoint.$target, $args );

            if(!is_wp_error($response))
                return $this->responseFormat(json_decode($response['body'], true));
            else
                return json_encode(array(
                    'type' => 'error',
                    'message' => $response->get_error_message()
                ));

        }

        /**
         * We apply diverse changes to the request, using the data from the website
         *
         * @param array $response
         * @return string
         */
        private function responseFormat($response) {

            $formatted_response = $response;

            $conversations = (isset($formatted_response['conversations']['data'])) ? $formatted_response['conversations']['data'] : null;
            $conversation = (isset($formatted_response['conversation'])) ? $formatted_response['conversation'] : null;

            if($conversations) {
                foreach ($conversations as $key=>$entry) {
                    $conversations[$key] = $this->formatParticipants($entry);

                    // Last check
                    $last_check = $this->getLastConversationCheck($entry['id']);
                    $conversations[$key]['last_check'] = (!is_null($last_check)) ? $last_check->last_checked : $last_check;
                    $conversations[$key]['has_new'] = ((int) $entry['last_message_sender'] !== get_current_user_id() && $entry['last_message_time'] > $conversations[$key]['last_check']);
                }
                $formatted_response['conversations']['data'] = $conversations;
            }

            if($conversation) {
                $conversation = $this->formatParticipants($conversation);
                $formatted_response['conversation'] = $conversation;
                $this->setLastConversationCheck($conversation['id']);
            }

            /**
             * Filter to attach custom attributes to the API response
             *
             * @param array $formatted_response - the response
             */
            return json_encode(apply_filters('woffice_alka_chat_api_response', $formatted_response));

        }

        /**
         * Formats the participants by adding extra information
         *
         * @param Object $conversation
         * @return Object
         */
        private function formatParticipants($conversation) {

            if(!isset($conversation['participants']))
                return $conversation;

            /*
             * We attach attributes to the participants
             */
            foreach ($conversation['participants'] as $key2=>$participant) {
                if(get_userdata($participant) !== false) {
                    $conversation['participants'][$key2] = array(
                        '_id' => $participant,
                        '_name' => woffice_get_name_to_display($participant),
                        '_avatar' => get_avatar($participant),
                        '_profile' => bp_core_get_user_domain($participant)
                    );
                }
            }
            /*
             * We make sure the first one is not the current member
             * for the avatar on the frontend
             */
            if($conversation['participants'][0]['_id'] == get_current_user_id()){
                $first_participant = $conversation['participants'][0];
                $second_participant = $conversation['participants'][1];
                $conversation['participants'][0] = $second_participant;
                $conversation['participants'][1] = $first_participant;
            }

            return $conversation;

        }

        /**
         * Checks if the AlkaChat is enabled
         *
         * @return boolean
         */
        static function isEnabled() {

            $enabled = woffice_get_settings_option('alka_pro_chat_enabled', false);

            // We deactivate it for now
            return $enabled;

        }


        /**
         * Renders the markup
         */
        public function render() {

            if(!static::isEnabled())
                return;

            if(Woffice_Pro::is_pro() || current_user_can('administrator'))
                get_template_part('template-parts/chat');

            return;

        }

    }
}

/**
 * Let's fire it :
 */
new Woffice_AlkaChat();



