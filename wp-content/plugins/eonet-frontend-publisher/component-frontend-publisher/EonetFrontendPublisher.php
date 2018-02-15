<?php
/**
 * Class Eonet Live Search Component
 */
namespace ComponentFrontendPublisher;

if ( ! defined('ABSPATH') ) die('Forbidden');

use Eonet\Core\EonetComponents;
use Eonet\Core\EonetOptions;
use Exception;

if(!class_exists('ComponentFrontendPublisher\EonetFrontendPublisher')) {
    class EonetFrontendPublisher extends EonetComponents
    {

        /**
         * Slug of the component so we can get its details
         * @var string
         */
        public $slug = "frontend-publisher";

        /**
         * Construct the component :
         */
        public function __construct()
        {
            // Filter :
            add_filter('the_content', array($this, 'filterManageButton'));
            // Actions :
            add_action('wp_enqueue_scripts', array($this,'loadScripts'));
            add_action('wp_ajax_eonet_fetch_form', array($this, 'ajaxGetForm'));
            add_action('wp_ajax_nopriv_eonet_fetch_form', array($this, 'ajaxGetForm'));
            add_action('wp_ajax_eonet_process_form', array($this, 'ajaxProcessForm'));
            add_action('wp_ajax_nopriv_eonet_process_form', array($this, 'ajaxProcessForm'));
            add_action('wp_ajax_eonet_delete_form', array($this, 'ajaxProcessDeleteForm'));
            add_action('wp_ajax_nopriv_eonet_delete_form', array($this, 'ajaxDeleteForm'));
            // Shortcode :
            add_shortcode( 'eonet_frontend_create', array($this,'shortcodeCreate') );
            // Actions :
            add_action('eonet_before_frontend_button', array($this, 'editorWorkaround'));
            // Parent Instance :
            parent::__construct($this->slug);
            // Fire actions :
            do_action('eonet_frontend_construct');
        }

        /**
         * Add the scripts used by the extension :
         */
        public function loadScripts()
        {
            // JS object :
            $action = 'eonet_fetch_form';
            $data = array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'frontend_nonce' => wp_create_nonce( $action . '_nonce' ),
                'frontend_action' => $action,
            );
            wp_enqueue_script( $this->slug.'-script', $this->getUrl($this->slug) . '/assets/js/eonet_frontend_publisher.js', array('jquery'), 1.0, true );
            wp_localize_script( $this->slug.'-script', 'EONET_FRONTEND', $data);
            // WP Editor assets
            wp_enqueue_style($this->slug .'-dashicons',includes_url('css/dashicons.min.css'));
            wp_enqueue_style($this->slug .'-editor-buttons',includes_url('css/editor.min.css'));
            wp_enqueue_script($this->slug.'-tinymce-js', includes_url('js/tinymce/') . 'wp-tinymce.php', array( 'jquery' ), false, true );
        }

        /**
         * Helper to return post type labels
         * @param $type : the post type
         * @return array
         */
        public function getLabels($type)
        {
            $type_obj = get_post_type_object( $type );
            if(empty($type_obj)) {
                return null;
            }
            $labels = $type_obj->labels;
            return $labels;
        }

        /**
         * Helper function to returns the post type taxonomy used in the frontend forms :
         *
         * @param $type : the post type
         * @param $position : the position of the tax in the $taxonomies array
         * @return string : taxonomy slug
         */
        public function getTaxonomy($type, $position = 0)
        {

            $taxonomies = get_object_taxonomies($type);
            if(!empty($taxonomies) && is_array($taxonomies)) {
                /**
                 * By default, we'll grab the first attached taxonomy
                 * but we create a new filter : eonet_front_taxonomy:$type
                 * IE : eonet_front_taxonomy:post
                 * So it's easy to override from any PHP script
                 */
                $taxonomy = apply_filters('eonet_front_taxonomy:' . $type, $taxonomies[$position]);

            } else {
                $taxonomy = '';
            }

            return $taxonomy;

        }

        /**
         * Returns the form title
         *
         * @param $type : post type
         * @param $id : the post's id
         * @return string
         */
        public function getFormTitle($type, $id) {

            if(!empty($id)) {
                $post = get_post($id);
                $title = __('Edit', 'eonet-frontend-publisher') . ' ' . $post->post_title;
            } else {
                $labels = $this->getLabels($type);
                $title = __('Create a new', 'eonet-frontend-publisher') . ' ' . $labels->singular_name;;
            }

            return $title;

        }

        /**
         * Returns the form buttons
         *
         * @param $type : the post type
         * @param $id : depends on the method,
         * @return string
         */
        public function getFormButtons($type, $id) {

            $btns = '<div class="eo_btn_left">';
            // Cancel btn :
            $btns .= '<a href="javascript:void(0);" id="eo_modal_cancel" class="eo_btn eo_btn_info eo_close">'. __('Cancel', 'eonet-frontend-publisher') .'</a>';
            // Delete btn :
            if(!empty($id)) {
                $btns .= '<a href="javascript:void(0);" id="eo_modal_delete" data-post-id="'.$id.'">'. __('Delete', 'eonet-frontend-publisher') .'</a>';
            }
            $btns .= '</div>';

            $btns .= '<a href="javascript:void(0);" id="eo_modal_kickstart" class="eo_btn eo_btn_success">'. __('Save', 'eonet-frontend-publisher') .'</a>';

            return $btns;

        }

        /**
         * Returns the forms field as an array ready for the EonetOptions class :
         *
         * @param $type : the post type
         * @param $id : the post id
         * @return array
         */
        public function getFields($type, $id) {

            if(empty($type)) {
                return null;
            }

            // If there is a post ID :
            if(!empty($id)) {
                $post = get_post($id);
            }

            // Getting field labels :
            $labels = $this->getLabels($type);

            $fields = array();

            // Title :
            $fields[] = array(
                'name'      => 'post_title',
                'type'      => 'text',
                'label'     => __('Title', 'eonet-frontend-publisher'),
                'desc'      => __('REQUIRED, this is the', 'eonet-frontend-publisher').' '.$labels->singular_name.' '. __('title','eonet-frontend-publisher'). '.',
                'val'       => (isset($post)) ? $post->post_title : ''
            );

            // Content :
            $fields[] = array(
                'name'      => 'post_content',
                'type'      => 'editor',
                'label'     => __('Content', 'eonet-frontend-publisher'),
                'desc'      => __('This is the', 'eonet-frontend-publisher').' '.$labels->singular_name.' '. __('content','eonet-frontend-publisher'). '.',
                'val'       => (isset($post)) ? $post->post_content : ''
            );

            // Featured Image :
            $fields[] = array(
                'name'      => 'featured_image',
                'type'      => 'upload',
                'label'     => __('Featured Image', 'eonet-frontend-publisher'),
                // We pass an attachment ID
                'val'       => ( has_post_thumbnail($id) ) ? get_post_thumbnail_id( $id ) : ''
            );

            // Taxonomy :
            $taxonomy = $this->getTaxonomy($type);
            if(!empty($taxonomy)) {

                // tax's object :
                $taxonomy_object = get_taxonomy( $taxonomy );

                // current terms :
                $post_terms = (!empty($id)) ? wp_get_post_terms( $id, $taxonomy, array("fields" => "all") ) : '';
                $post_terms_ready = array();
                if(is_array($post_terms) && !empty($post_terms)) {
                    foreach ($post_terms as $term) {
                        $post_terms_ready[$term->term_id] = $term->name;
                    }
                }

                // taxonomy's terms :
                $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false ) );
                $terms_ready = array();
                if(is_array($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        $terms_ready[$term->term_id] = $term->name;
                    }
                }

                // the field :
                if(!empty($terms_ready)) {
                    $fields[] = array(
                        'name'      => 'post_terms',
                        'type'      => 'select',
                        'multiple'  => true,
                        'label'     => $taxonomy_object->labels->name,
                        'val'       => (!empty($post_terms_ready)) ? $post_terms_ready : '',
                        'choices'   => $terms_ready,
                    );
                }

            }

            // Tags:
            $tag_name = ($type == 'post') ? $this->getTaxonomy($type, 1) : '';
            if(!empty($tag_name)) {
                // tax's object :
                $taxonomy_object = get_taxonomy( $tag_name );
                // Current tags :
                $post_tags = (!empty($id)) ? wp_get_post_terms( $id, $tag_name, array("fields" => "all") ) : '';
                $post_tags_ready = array();
                if(is_array($post_tags) && !empty($post_tags)) {
                    foreach ($post_tags as $term) {
                        $post_tags_ready[$term->term_id] = $term->name;
                    }
                }
                // the field :
                $fields[] = array(
                    'name'      => 'post_tags',
                    'type'      => 'tag',
                    'label'     => $taxonomy_object->labels->name,
                    'val'       => (!empty($post_tags_ready)) ? $post_tags_ready : array(),
                    'taxonomy'  => $tag_name
                );
            }

            /**
             * Custom fields filter 'eonet_front_custom_fields'
             *
             * @param $fields array the current fields
             * @param $id int the id of the post
             */
            $fields = apply_filters( 'eonet_front_custom_fields', $fields, $id);

            return $fields;

        }

        /**
         * Handle ajax query to delete a post :
         * We return a JSON response to create an alert later in Jquery
         */
        public function ajaxProcessDeleteForm()
        {

            // We check whether it's from our page or not.
            check_ajax_referer( 'eonet_delete_form' );

            if(isset($_POST['eo_post_id']) && !empty($_POST['eo_post_id']) && isset($_POST['eo_method']) && $_POST['eo_method'] == 'delete') {

                // We delete :
                wp_delete_post($_POST['eo_post_id'], true);

                // We return :
                $response['status'] = 'success';
                $response['permalink'] = get_site_url();
                $response['title'] = __('Delete !', 'eonet-frontend-publisher');
                $response['content'] = __('We\'ll redirect you in a moment.', 'eonet-frontend-publisher');

            }

        }

        /**
         * Handle ajax query to process the form :
         * We return a JSON response to create an alert later in Jquery
         */
        public function ajaxProcessForm()
        {
            // We check whether it's from our page or not.
            check_ajax_referer( 'eonet_process_form' );

            // Validation watcher :
            $errors = array();

            // Response array :
            $response = array();

            /**
             * We start the validation and we use some actions for later custom process
             * By third-party apps
             */
            // Title is required :
            if(!isset($_POST['eo_field_post_title']) || empty($_POST['eo_field_post_title'])) {
                $errors[] = __('The title is required.', 'eonet-frontend-publisher');
            }
            // We must have the method AND the post type :
            if(!isset($_POST['eo_method']) || empty($_POST['eo_method']) || !isset($_POST['eo_post_type']) || empty($_POST['eo_post_type'])) {
                $errors[] = __('Something is missing...', 'eonet-frontend-publisher');
            }
            // More validation here ...
            do_action('eonet_frontend_custom_validation', $_POST);

            // If there is an error :
            if(!empty($errors)) {
                $response = array(
                    'status' => 'error',
                    'title' => __('Please try again...', 'eonet-frontend-publisher'),
                    'content' => implode(" ",$errors),
                );
            }

            // We process the form :
            if(empty($response)) {

                /**
                 * Basic fields required so we can create / edit the post
                 */

                // Main details of the post :
                $post_details = array();
                // If there is an ID :
                if(isset($_POST['eo_post_id']) && !empty($_POST['eo_post_id'])) {
                    $post_details['ID'] = $_POST['eo_post_id'];
                }

                //Status :
                $status = eonet_get_option('frontend_status', 'publish');

                // Post details :
                $post_details['post_title'] = wp_strip_all_tags($_POST['eo_field_post_title']);

                /**
                 * eo_field_post_title post content is the value in the textarea
                 * wp_editor_content is the value from the WP editor
                 */
                $post_details['post_content'] = (isset($_POST['wp_editor_content'])) ? $_POST['wp_editor_content'] : '';
                $post_details['post_type'] = esc_html($_POST['eo_post_type']);
                $post_details['post_status'] = ($_POST['eo_method'] == 'create') ? $status : 'publish';

                // We either create it or update it :
                if($_POST['eo_method'] == 'manage') {
                    $post_id = wp_update_post($post_details);
                } else {
                    $post_id = wp_insert_post($post_details);
                }

                // If the creation failed ...
                if($post_id === 0) {
                    $response = array(
                        'status' => 'error',
                        'title' => __('Please try again...', 'eonet-frontend-publisher'),
                        'content' => __('We\'ve been able to process the action using Wordpress functions.', 'eonet-frontend-publisher'),
                    );
                }

                /**
                 * Action `eonet_frontend_custom_process`
                 * Best hook to save custom post metas and taxonomies
                 *
                 * @param $post_id
                 * @param $_POST
                 */
                do_action('eonet_frontend_custom_process', $post_id, $_POST);

                // If we still don't have any error :
                if(empty($response)) {

                    /**
                     * Main Taxonomy : i.e category
                     */
                    if(isset($_POST['eo_field_post_terms']) && !empty($_POST['eo_field_post_terms']) && isset($_POST['eo_post_taxonomy'])) {
                        $terms = $_POST['eo_field_post_terms'];
                        $taxonomy = $_POST['eo_post_taxonomy'];
                    } else {
                        $terms = array();
                        $taxonomy = '';
                    }
                    $this->setTerms($post_id, $terms, $taxonomy);

                    /**
                     * Tags :
                     */
                    if(isset($_POST['eo_field_post_tags']) && !empty($_POST['eo_field_post_tags'])) {
                        $tags = $_POST['eo_field_post_tags'];
                    } else {
                        $tags = array();
                    }
                    $this->setTerms($post_id, $tags, 'post_tag');

                    /**
                     * Featured image :
                     */
                    if(isset($_POST['eo_field_featured_image'])) {
                        update_post_meta( $post_id, '_thumbnail_id', $_POST['eo_field_featured_image'] );
                    }

                    // Return success :
                    $response = array(
                        'status' => 'success',
                        'method' => $_POST['eo_method'],
                        'permalink' => get_the_permalink($post_id),
                        'post_title' => get_the_title($post_id),
                    );
                    if($_POST['eo_method'] == 'manage') {
                        $response['title'] = __('Updated !', 'eonet-frontend-publisher');
                        $response['content'] = __('Your post has been updated successfully. You might refresh your page.', 'eonet-frontend-publisher');
                    } else {
                        $response['title'] = __('Created !', 'eonet-frontend-publisher');
                        $response['content'] = __('We\'ll redirect you in a moment.', 'eonet-frontend-publisher');
                    }

                }

            }

            /**
             * We return here the response in JSON format
             */

            echo json_encode($response);

            // We stop it :
            wp_die();

        }

        /**
         * @param $post_id int : the post ID
         * @param $terms array : array of terms labels or IDs
         * @param $taxonomy string : the taxonomy
         * @return bool|\WP_Error|string|array
         */
        public function setTerms($post_id, $terms, $taxonomy)
        {
            // If something is empty :
            if(empty($terms) || empty($taxonomy) || empty($post_id))
                return false;

            // We remove all existing terms :
            wp_delete_object_term_relationships( $post_id, $taxonomy );

            // We create a valid array
            $terms_ready = array();
            foreach ($terms as $term) {
                // If it's already a term ID :
                if(is_numeric($term)) {
                    $terms_ready[] = (int) $term;
                } else {
                    $term_object = get_term_by( 'name', $term, $taxonomy );
                    if($term_object != false){
                        $terms_ready[] = (int) $term_object->term_id;
                    } else {
                        $new_term_array = wp_insert_term( $term, $taxonomy);
                        if(is_array($new_term_array)) {
                            $terms_ready[] = (int) $new_term_array['term_id'];
                        }
                    }
                }

            }

            if(empty($terms_ready))
                return false;

            // We set the terms :
            $set_terms = wp_set_object_terms( $post_id, $terms_ready, $taxonomy );

            return $set_terms;

        }

        /**
         * Handle ajax query to get the form :
         * We return directly the HTML that'll be outputed under the modal
         */
        public function ajaxGetForm()
        {
            // We check whether it's from our page or not.
            check_ajax_referer( 'eonet_fetch_form_nonce', 'security' );

            $method = (isset($_POST['method'])) ? sanitize_text_field($_POST['method']) : '';
            $id = (isset($_POST['id']) && !empty($_POST['id'])) ? sanitize_text_field($_POST['id']) : '';
            $type = (isset($_POST['type']) && !empty($_POST['type'])) ? sanitize_text_field($_POST['type']) : '';

            if(empty($type) && !empty($id))
                $type = get_post_type($id);

            $data = array(
                'method' => $method,
                'type' => $type,
                'id' => $id
            );

            echo eonet_render_view($this->getPath($this->slug).'/views/form.php', $data);

            // We stop it :
            wp_die();

        }

        /**
         * Whether the user is allowed to make changes on the frontend AND SO if the buttons should be displayed :
         * So we check the role and post type here
         * @param $post_id : the post to check
         * @param $user_id : by default we take the current one
         * @return bool
         */
        static public function isManageAllowed($post_id, $user_id = '')
        {

            if(empty($user_id)) {
                $user_id = get_current_user_id();
            }

            // If not logged, not allowed or post empty
            if(empty($user_id) || is_bool($user_id) || !is_int($post_id)) {
                return false;
            }

            // We get the post's details :
            $post = get_post($post_id);
            // If it's the author he's allowed
            if($post->post_author == $user_id) {
                return true;
            }

            // We check whether the post type is available :
            $post_types = eonet_get_option('frontend_post_types', array('post', 'page'));
            if(!in_array($post->post_type, $post_types)) {
                return false;
            }

            // We check whether the user is allowed or not :
            $role_manage = eonet_get_option('frontend_roles_manage', array('administrator', 'editor'));
            $user = get_userdata($user_id);
            $the_user_role = (array) $user->roles;
            $role_intersect = array_intersect( $the_user_role, $role_manage );
            if(!empty($role_intersect) && is_array($role_intersect)){
                return true;
            }

            return false;

        }

        /**
         * Whether the user is allowed to make create from the frontend :
         * @param $type : the post type
         * @param $user_id : by default we take the current one
         * @return bool
         */
        static public function isCreationAllowed($type, $user_id)
        {

            if(!isset($user_id)) {
                $user_id = get_current_user_id();
            }

            // If not logged, not allowed or post empty
            if(empty($user_id) || is_bool($user_id)) {
                return false;
            }

            // We check whether the post type is available :
            $post_types = eonet_get_option('frontend_post_types', array('post', 'page'));
            if(!in_array($type, $post_types)) {
                return false;
            }

            // We check whether the user is allowed or not :
            $role_create = eonet_get_option('frontend_roles_create', array('administrator', 'editor', 'author'));
            $user = get_userdata($user_id);
            $the_user_role = (array) $user->roles;
            $role_intersect = array_intersect( $the_user_role, $role_create );
            if(!empty($role_intersect) && is_array($role_intersect)){
                return true;
            }

            return false;

        }

        /**
         * Returns the button markup for the post edit
         * @param $content string content of the post
         * @return string
         */
        public function filterManageButton($content)
        {

            /**
             * Filter 'eonet_front_edit_btn_deactivated'
             * If you don't want to display the button for some post types, or change its location
             * You can remove it from this filter
             *
             * @param array of post types
             */
            $post_types_deactivated = apply_filters('eonet_front_edit_btn_deactivated', array('post_type_that_needs_to_be_excluded'));

            // If we're on a single page AND current user is allowed :
            if(is_singular() && !is_singular($post_types_deactivated) && self::isManageAllowed(get_the_ID())) {

                // Buttons class :
                $btn_classes = apply_filters('eonet_front_btn_classes', 'eo_btn eo_btn_default');

                // We build the markup :
                $btn_html = '<div id="eo_manage_btns">';
                    do_action('eonet_before_frontend_button');
                    $btn_html .= '<a href="javascript:void(0);" data-eo-post-id="'.get_the_ID().'" id="eo_edit_btn" class="'.$btn_classes.'">';
                        $btn_html .= '<i class="fa fa-pencil-square"></i>'. __('Edit', 'eonet-frontend-publisher') . ' ' . get_the_title();
                    $btn_html .= '</a>';
                    do_action('eonet_after_frontend_button');
                $btn_html .= '</div>';

                // we add the markup to the content
                $content = $content . $btn_html;
            }

            // We return the content
            return $content;
        }

        /**
         * Shortcode to generate the Create button of any post type :
         * @param $atts : shortcode attributes
         * @return string
         */
        public function shortcodeCreate($atts) {

            // We extract the settings :
            $settings = shortcode_atts( array(
                'type' => 'post',
                'wrapper' => 'true',
            ), $atts );

            // If not allowed we don't show it :
            if(!self::isCreationAllowed($settings['type'], get_current_user_id())) {
                return '';
            }

            // Getting field labels :
            $labels = $this->getLabels($settings['type']);

            // Buttons class :
            $btn_classes = apply_filters('eonet_front_btn_classes', 'eo_btn eo_btn_default');

            // We build the HTML to return :
            $btn_html = '';
            $btn_html .= ($settings['wrapper'] == true) ? '<div id="eo_create_btns">' : '';
                do_action('eonet_before_frontend_button');
                $btn_html .= '<a href="javascript:void(0);" data-eo-post-type="'.$settings['type'].'" id="eo_create_btn" class="'.$btn_classes.'">';
                    $btn_html .= '<i class="fa-pencil-square-o"></i>'. __('New', 'eonet-frontend-publisher') . ' ' . $labels->singular_name;
                $btn_html .= '</a>';
                do_action('eonet_after_frontend_button');
            $btn_html .= ($settings['wrapper'] == true) ? '</div>' : '';

            return $btn_html;

        }

        /**
         * This is a workaround to init the WP editor in the frontend
         * We hide it by default, yet by calling it here we load all the scripts
         * And we can easily ReInit it from JS after
         */
        public function editorWorkaround() {
            wp_editor('', 'eo-front-blank-editor', EonetOptions::getEditorSettings('eo-front-blank-editor'));
        }

    }
}
