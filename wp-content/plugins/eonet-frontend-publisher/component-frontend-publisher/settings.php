<?php
/**
 * Get array of all post types :
 */
$post_types = Eonet\Core\EonetOptions::getPostTypesArray();

/**
 * Get array of all user roles :
 */
$roles = Eonet\Core\EonetOptions::getRolesArray();

/**
 * Setup HTML Code :
 */
$setup_html = '';
$setup_html .= '<ol>';
$setup_html .= '<li>'.__('Enable the frontend publisher button on any edit page by choosing its post type in the options below.','eonet-frontend-publisher').'</li>';
$setup_html .= '<li>'.__('You can use the following shortcode on any page to create any new post :','eonet-frontend-publisher');
$setup_html .= '<pre>[eonet_frontend_create type="post"]</pre>';
$setup_html .= '<strong>type</strong> : '.__('can take any WordPress post type such as post, page...','eonet-frontend-publisher').'<br>';
$setup_html .= '<strong>wrapper</strong> : '.__('is an additional parameter to disable the button\'s wrapper tags, if you want it to be inline for instance.','eonet-frontend-publisher');
$setup_html .= '</li>';
$setup_html .= '</ol>';

/**
 * Component settings, used in the Eonet admin pages
 */
$settings = array(
    array(
        'name'      => 'frontent_setup',
        'type'      => 'html',
        'label'     => __('Setup', 'eonet-frontend-publisher'),
        'desc'      => __('Feel free to get in touch with us if you need more informations.', 'eonet-frontend-publisher'),
        'content'   => $setup_html
    ),
    array(
        'name'      => 'frontend_status',
        'type'      => 'select',
        'label'     => __('Status on creation', 'eonet-frontend-publisher'),
        'desc'      => __('This is the status of the Wordpress post when it\'ll be created.', 'eonet-frontend-publisher'),
        'val'       => 'publish',
        'choices'   => array(
            'publish'      => __('Publish', 'eonet-frontend-publisher'),
            'pending'      => __('Pending', 'eonet-frontend-publisher'),
            'draft'      => __('Draft', 'eonet-frontend-publisher'),
            'private'      => __('Private', 'eonet-frontend-publisher'),
        )
    ),
    array(
        'name'      => 'frontend_roles_manage',
        'type'      => 'select',
        'label'     => __('Managing users', 'eonet-frontend-publisher'),
        'desc'      => __('These users will be able to manage (edit / delete) posts of any user.', 'eonet-frontend-publisher'),
        'multiple'  => true,
        'choices'   => $roles,
        'val'       => array('administrator', 'editor')
    ),
    array(
        'name'      => 'frontend_roles_create',
        'type'      => 'select',
        'label'     => __('Creation users', 'eonet-frontend-publisher'),
        'desc'      => __('These users will be able to create posts and edit / delete their own posts.', 'eonet-frontend-publisher'),
        'multiple'  => true,
        'choices'   => $roles,
        'val'       => array('administrator', 'editor', 'author')
    ),
    array(
        'name'      => 'frontend_post_types',
        'type'      => 'select',
        'label'     => __('Post types', 'eonet-frontend-publisher'),
        'desc'      => __('Select the post types which will have the creation / edit / delete buttons.', 'eonet-frontend-publisher'),
        'multiple'  => true,
        'choices'   => $post_types,
        'val'       => array('post', 'page')
    ),
    array(
        'name'      => 'frontend_note',
        'type'      => 'textarea',
        'label'     => __('Additional note', 'eonet-frontend-publisher'),
        'desc'      => __('This note will be added at the bottom of the form in the frontend.', 'eonet-frontend-publisher'),
        'val'       => ''
    ),
);