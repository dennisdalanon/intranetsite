<?php
/**
 * Let's init the component properly :
 * We load the classes :
 */
if(defined('Eonet')) {

    add_action('plugins_loaded', 'eonet_component_init_frontend_publisher');

    function eonet_component_init_frontend_publisher()
    {

        // Classes to load :
        $component_classes = array(
            'ComponentFrontendPublisher\EonetFrontendPublisher',
        );
        // Load them :
        foreach ($component_classes as $class) {
            eonet_autoload($class);
        }
        // Fire it !
	    eonet_frontend_publisher();

        // Hook it
        do_action('eonet_component_after_init_frontend_publisher');

    }


	if(!function_exists('eonet_frontend_publisher')) {
		/**
		 * Return the static instance of the class, in this way the class is instanced only one time and ae avoided actions doubled
		 *
		 * @return \ComponentManualUserApprove\EonetManualUserApprove
		 */
		function eonet_frontend_publisher() {
			return \ComponentFrontendPublisher\EonetFrontendPublisher::instance();
		}
	}
}
