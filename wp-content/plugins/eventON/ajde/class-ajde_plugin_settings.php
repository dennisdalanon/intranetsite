<?php
/**
 * Generalized AJDE plugin backend settings
 * @version 0.1
 */

class ajde_settings{
	public $focus_tab;
	public $current_section;

	public function __construct($defailt_tab){
		$this->focus_tab = (isset($_GET['tab']) )? sanitize_text_field( urldecode($_GET['tab'])):$defailt_tab;
		$this->current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';
	}

	function get_current_tab_values($options_pre){
		if(empty($options_pre)) return false;

		$current_tab_number = substr($this->focus_tab, -1);
		// if the tab last character is not numeric then get the whole tab name as the variable name for the options 		
		if(!is_numeric($current_tab_number)){ 
			$current_tab_number = $this->focus_tab;
		}
	
		return array($current_tab_number=> get_option($options_pre.$this->focus_tab));
	}

	function header_wraps($args){
		?>
		<div class="wrap ajde_settings" id='<?php echo $args['tab_id'];?>'>
			<h2><?php echo $args['title'];?> (ver <?php echo $args['version'];?>)</h2>
			<h2 class='nav-tab-wrapper' id='meta_tabs'>
				<?php					
					foreach($args['tabs'] as $key=>$val){
						
						echo "<a href='{$args['tab_page']}".$key."' class='nav-tab ".( ($this->focus_tab == $key)? 'nav-tab-active':null)." {$key}' ". 
							( (!empty($args['tab_attr_field']) && !empty($args['tab_attr_pre']))? 
								$args['tab_attr_field'] . "='{$args['tab_attr_pre']}{$key}'":'') . ">".$val."</a>";
					}			
				?>		
			</h2>
		<?php
	}

	function settings_tab_start($args){
		?>
		<form method="post" action="">
			<?php settings_fields($args['field_group']); ?>
			<?php wp_nonce_field( $args['nonce_key'], $args['nonce_field'] );?>
		<div id="<?php echo $args['tab_id'];?>" class="<?php implode(' ', $args['classes']);?>">
			<div class="<?php implode(' ', $args['inside_classes']);?>">
				<?php
	}
	function settings_tab_end(){
		?></div></div><?php
	}

	function save_settings($nonce_key, $nonce_field, $options_pre){
		if( isset($_POST[$nonce_field]) && isset( $_POST ) ){
			if ( wp_verify_nonce( $_POST[$nonce_field], $nonce_key ) ){
				foreach($_POST as $pf=>$pv){
					$pv = (is_array($pv))? $pv: addslashes(esc_html(stripslashes(($pv)))) ;
					$options[$pf] = $pv;
				}

				update_option($options_pre.$this->focus_tab, $options);
			}
		}
	}
}