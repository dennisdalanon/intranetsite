<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = array(
    'alka-pro' => array(
        'title'   => __( 'Alka Pro (soon)', 'woffice' ),
        'type'    => 'tab',
        'options' => array(
            'main-pro-box' => array(
                'title'   => __( 'Features', 'woffice' ),
                'type'    => 'box',
                'options' => array()
            ),
            'chat-box' => array(
                'title'   => __( 'Chat', 'woffice' ),
                'type'    => 'box',
                'options' => array(
                    'alka_pro_chat_enabled' => array(
                        'label' => __( 'Enable the live chat ?', 'woffice' ),
                        'type'         => 'switch',
                        'right-choice' => array(
                            'value' => true,
                            'label' => __( 'Yep', 'woffice' )
                        ),
                        'left-choice'  => array(
                            'value' => false,
                            'label' => __( 'Nope', 'woffice' )
                        ),
                        'value'        => false,
                    ),
                    'alka_pro_chat_refresh_time' => array(
                        'label' => __( 'Refresh time', 'woffice' ),
                        'type'         => 'slider',
                        'value' => 8000,
                        'properties' => array(
                            'min' => 3000,
                            'max' => 60000,
                            'step' => 1000, // Set slider step. Always > 0. Could be fractional.
                        ),
                        'desc' => __( 'Live refresh time in milliseconds to fetch new messages once the chat is open, server performance are heavily affected by this.', 'woffice' ),
                    ),
                    'alka_pro_chat_emojis_enabled' => array(
                        'label' => __( 'Enable the Emojis picker?', 'woffice' ),
                        'type'         => 'switch',
                        'right-choice' => array(
                            'value' => true,
                            'label' => __( 'Yep', 'woffice' )
                        ),
                        'left-choice'  => array(
                            'value' => false,
                            'label' => __( 'Nope', 'woffice' )
                        ),
                        'value'        => false, // Todo turn on once Alka Pro released
                    ),
                    'alka_pro_chat_welcome_enabled' => array(
                        'label' => __( 'Enable welcome modal ?', 'woffice' ),
                        'type'         => 'switch',
                        'right-choice' => array(
                            'value' => true,
                            'label' => __( 'Yep', 'woffice' )
                        ),
                        'left-choice'  => array(
                            'value' => false,
                            'label' => __( 'Nope', 'woffice' )
                        ),
                        'value'        => false,
                        'help' => __('This modal will be displayed only one time for each user, it can provide detail or rules for the chat.','woffice'),
                    ),
                    'alka_pro_chat_welcome_title' => array(
                        'label' => __( 'Welcome title', 'woffice' ),
                        'type'         => 'text',
                        'value' => 'Welcome to the live chat',
                    ),
                    'alka_pro_chat_welcome_message' => array(
                        'type'  => 'wp-editor',
                        'label' => __( 'Welcome message', 'woffice' ),
                        'value'  => 'Here are the rules for the live chat... or any content you\'d like',
                        'media_buttons' => false,
                        'teeny' => false,
                        'wpautop' => false,
                        'editor_css' => '',
                        'reinit' => false,
                    ),
                )
            ),
            'tab-creator-box' => array(
                'title'   => __( 'BuddyPress tab creator', 'woffice' ),
                'type'    => 'box',
                'options' => array(
                    'buddypress-tabs-error' => array(
                        'label' => __( 'Not available', 'woffice' ),
                        'type'  => 'html',
                        'html' => '<span class="highlight">'. __('No pro account has been found, it\'s required for this feature.', 'woffice') .'</span>',
                    )
                )
            ),
        )
    )
);

if(Woffice_Pro::is_pro()) {
    $options['alka-pro']['options']['tab-creator-box'] = array(
        'title'   => __( 'BuddyPress tab creator', 'woffice' ),
        'type'    => 'box',
        'options' => array(
            'buddypress-tabs' => array(
                'type' => 'addable-popup',
                'popup-title' => null,
                'size' => 'small',
                'limit' => 0,
                'add-button-text' => __('Add', 'woffice'),
                'label' => __('Tabs', 'woffice'),
                'sortable' => true,
                'template' => 'Tab: {{- name }}',
                'popup-options' => array(
                    'name' => array(
                        'label' => __('Name', 'woffice'),
                        'type' => 'text',
                    ),
                    'content' => array(
                        'type'  => 'wp-editor',
                        'label' => __( 'Content', 'woffice' ),
                        'media_buttons' => false,
                        'teeny' => false,
                        'wpautop' => false,
                        'editor_css' => '',
                        'reinit' => false,
                    ),
                    'icon' => array(
                        'label' => __('Icon', 'woffice'),
                        'type' => 'icon',
                    ),
                    'action' => array(
                        'label' => __('PHP Action', 'woffice'),
                        'type' => 'text',
                        'value' => 'woffice_bp_tab_',
                        'desc' =>
                            __('Like: <b>woffice_bp_tab_my_tab</b>. Must be unique. This advanced option let you attach any PHP function to the tab content by calling:','woffice').
                            ' <span class="highlight">add_action("woffice_bp_tab_my_tab", "your_callback_function")</span> '.
                            __('Please see the official WordPress documentation for more details here:', 'woffice').
                            ' <a href="https://developer.wordpress.org/reference/functions/add_action/" target="_blank">developer.wordpress.org/reference/functions/add_action/</a>'
                    ),
                ),
            )
        )
    );
}