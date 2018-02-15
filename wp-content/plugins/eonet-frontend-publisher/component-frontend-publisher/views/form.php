<?php
/**
 * Form Modal template 
 */
// Load the class object :
$frontendPublisher = new ComponentFrontendPublisher\EonetFrontendPublisher();

// Modal Title :
$title = apply_filters('eonet_front_modal_title',$frontendPublisher->getFormTitle($type, $id));

// Modal Buttons :
$buttons = $frontendPublisher->getFormButtons($type, $id);

// Get the fields :
$fields = $frontendPublisher->getFields($type, $id);
?>
<div class="eo_modal_content">
    <div class="eo_modal_header">
        <button type="button" class="eo_close"><span aria-hidden="true">&times;</span></button>
        <h4 class="eo_modal_title"><?php echo $title; ?></h4>
    </div>
    <div class="eo_modal_body">
        <form id="eo_frontend_form" method="post" action="#" class="eo_form eo_screen_1">
            <?php wp_nonce_field( 'eonet_process_form' ); ?>
            <input type="hidden" name="action" value="eonet_process_form">
            <input type="hidden" name="eo_method" value="<?php echo (!empty($id)) ? 'manage' : 'create'; ?>">
            <input type="hidden" name="eo_post_id" value="<?php echo (!empty($id)) ? $id : ''; ?>">
            <input type="hidden" name="eo_post_type" value="<?php echo (!empty($type)) ? $type : ''; ?>">
            <?php
            /**
             * If there is a taxonomy option, we need to also pass its slug in the form processing function
             * so we can handle it properly and more easily
             */
            $has_tax = 0;
            foreach ($fields as $field) :
                $has_tax = (isset($field['name']) && $field['name'] == 'post_terms') ? $has_tax + 1 : $has_tax;
            endforeach;
            if($has_tax > 0) : ?>
                <input type="hidden" name="eo_post_taxonomy" value="<?php echo $frontendPublisher->getTaxonomy($type); ?>">
            <?php endif; ?>
            <?php // We render the fields :
            echo Eonet\Core\EonetOptions::renderForm($fields); ?>
        </form>
        <?php
        // Delete screen :
        if(!empty($id)) : ?>
            <form id="eo_frontend_delete" method="post" action="#" class="eo_screen_2">
                <?php wp_nonce_field( 'eonet_delete_form' ); ?>
                <input type="hidden" name="action" value="eonet_delete_form">
                <input type="hidden" name="eo_method" value="delete">
                <input type="hidden" name="eo_post_id" value="<?php echo (!empty($id)) ? $id : ''; ?>">
                <div class="eo_confirm text-center">
                    <h2><?php _e('Are you sure ?', 'eonet-frontend-publisher'); ?></h2>
                    <p><?php _e('This can\'t be undone...') ?></p>
                    <div class="eo_btns">
                        <a href="javascript:void(0);" class="eo_btn eo_btn_default" id="eo_confirm_back">
                            <i class="fa fa-arrow-left"></i> <?php _e('Go back') ?>
                        </a>
                        <a href="javascript:void(0);" class="eo_btn eo_btn_danger" id="eo_confirm_go">
                            <i class="fa fa-trash-o "></i> <?php echo __('Delete') . ' ' . get_the_title($id); ?>
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
        <?php // Additional notes :
        $notes = eonet_get_option('frontend_note', '');
        if(!empty($notes)) : ?>
            <div class="eo_modal_notes">
                <p><?php echo $notes; ?></p>
            </div>
        <?php endif; ?>
    </div>
    <div class="eo_modal_footer">
        <?php echo $buttons; ?>
    </div>
</div>