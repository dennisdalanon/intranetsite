<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Direct access forbidden.' ); }
/**
 * Include static files: javascript and css
 */
if ( is_admin() ) {
	return;
}
/*---------------------------------------------------------
**
** COMMENTS SCRIPTS FROM WP
**
----------------------------------------------------------*/
if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
    wp_enqueue_script( 'comment-reply' );
}
/*---------------------------------------------------------
**
** CSS FILES NEEDED FOR WOFFICE
**
----------------------------------------------------------*/
if(function_exists('woffice_fonts_url')) {
    wp_enqueue_style('theme-fonts', woffice_fonts_url(), array(), null);
    // Assets
    wp_enqueue_style(
        'assets-css',
        get_template_directory_uri() . '/css/assets.min.css',
        array(),
        '1.0'
    );
}

// Load our main stylesheet.
wp_enqueue_style(
    'woffice-theme-style',
    get_template_directory_uri() . '/style.css',
    '1.0'
);

// Load printed stylesheet.
wp_enqueue_style(
    'woffice-printed-style',
    get_template_directory_uri() . '/css/print.min.css',
    array(),
    '1.0',
    'print'
);
/*---------------------------------------------------------
**
** JS FILES NEEDED FOR WOFFICE
**
----------------------------------------------------------*/
// LOAD JS PLUGINS FOR THE THEME
wp_enqueue_script(
    'woffice-theme-plugins',
    get_template_directory_uri() . '/js/plugins.min.js',
    array( 'jquery' ),
    '1.0',
    true
);

// Vue.js
wp_enqueue_script(
    'woffice-vue',
    get_template_directory_uri() . '/js/vue.js'
);

//NAVIGATION FIXED
$header_fixed = woffice_get_settings_option('header_fixed');
if( $header_fixed == "yep" ) :
    wp_enqueue_script(
        'woffice-fixed-navigation',
        get_template_directory_uri() . '/js/fixed-nav.js',
        array( 'jquery' ),
        '1.0',
        true
    );
endif;


// LOAD JS FUNCTIONS FOR THE THEME
$header_fixed = woffice_get_settings_option('minified_js');
$scripts_file = ( $header_fixed != "no" ) ? 'scripts.min.js' : 'scripts.js';

wp_enqueue_script(
    'woffice-theme-script',
    get_template_directory_uri() . '/js/'.$scripts_file,
    array( 'jquery' ),
    '1.0',
    true
);

// We load the chat JS
if(Woffice_AlkaChat::isEnabled()) {


    $has_emojis = woffice_get_settings_option('alka_pro_chat_emojis_enabled');
    if( $has_emojis ) {
        // Emojis CSS
        wp_enqueue_style('woffice-css-emojis-picker', get_template_directory_uri() . '/css/emojis/jquery.emojipicker.css', '1.0');
        wp_enqueue_style('woffice-css-emojis-twitter', get_template_directory_uri() . '/css/emojis/jquery.emojipicker.tw.css', '1.0');
        // Emojis JS
        wp_enqueue_script('woffice-js-emojis-picker', get_template_directory_uri() . '/js/emojis/jquery.emojipicker.js', array('jquery'), '1.0', true);
        wp_enqueue_script('woffice-js-emojis', get_template_directory_uri() . '/js/emojis/jquery.emojis.js', array('jquery'), '1.0', true);
    }

    // Main JS
    wp_enqueue_script(
        'woffice-alka-chat-script',
        get_template_directory_uri() . '/js/alkaChat.vue.js',
        array( 'jquery', 'woffice-theme-plugins', 'woffice-theme-script' ),
        '1.0',
        true
    );

}

//Load scripts needed to attach image in the frontend editors
wp_enqueue_media();

{

    $data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'site_url' => get_site_url(),
        'user_id' => get_current_user_id()
    );

    /**
     * We give the possibility to hook new data for the Theme Script JS
     * It's basically used for all things related to the Ajax calls
     *
     * @param array $data
     */
    $data = apply_filters('woffice_js_exchanged_data', $data);

    // Force responsive
    $use_force_responsive = (defined('WOFFICE_DISABLE_FORCE_RESPONSIVE') && (true == WOFFICE_DISABLE_FORCE_RESPONSIVE));
    $data['use_force_responsive'] = $use_force_responsive;

    // Mobile menu threshold
    $data['menu_threshold'] = woffice_get_settings_option('menu_threshold');

    wp_localize_script('woffice-theme-script', 'WOFFICE', $data);

}

