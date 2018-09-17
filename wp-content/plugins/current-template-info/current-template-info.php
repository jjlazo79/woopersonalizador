<?php
/*
Plugin Name: Current Template Info 
Plugin URI: https://cms3.ru/cti/
Author URI: https://cms3.ru/
Author: spoot1986
Version: 1.0.0
Description: Show the current template Information in the admin bar.
Text Domain: spcti
Domain Path: /languages/
*/

//languages
add_action('plugins_loaded','spcti_languages');
function spcti_languages() {
	load_plugin_textdomain('spcti', false, dirname( plugin_basename( __FILE__ ) ).'/languages/');
}

//show current template information
add_action('admin_bar_menu', 'spcti_admin_bar_menu', 9999);
function spcti_admin_bar_menu( $wp_admin_bar ) {
	global $template;
	
	$template_file_name  = wp_basename($template);
	$current_ID          = get_queried_object_id();
	$post_type           = get_post_type($current_ID);
	$current_theme		 = wp_get_theme();
	$current_theme_name	 = $current_theme->Name;

	$taxonomies = get_object_taxonomies($post_type);

	$sp_taxonomies = array();
	$sp_terms = array();

	if(!empty($taxonomies)){

		$sp_taxonomies = array();
		$sp_terms = array();

		foreach ($taxonomies as $taxonomy) {
			$terms = get_the_terms($current_ID, $taxonomy);
			$sp_taxonomies[] = $taxonomy;
			if (is_array($terms) || is_object($terms)){
				foreach ($terms as $term) {
					$sp_terms[] = $term->name; 
				}
			}	
		}
		
		$sp_taxonomies = implode(', ', $sp_taxonomies);
		$sp_terms = implode(', ', $sp_terms);
	}	

	$sp_count_queries = get_num_queries();
	$sp_time_queries = timer_stop();
	
	$wp_admin_bar->add_menu(array(
		'id'    => 'spcti_id',
		'title' => $template_file_name,
	));

	if(is_front_page()) {
		$wp_admin_bar->add_menu(array(
			'parent' => 'spcti_id', 
			'id'     => 'is_front_page',
			'title'  => '<span>'.__('Front page','spcti').'</span>',
		));
	}

	if(is_home()){
	   	$wp_admin_bar->add_menu(array(
			'parent' => 'spcti_id', 
			'id'     => 'is_posts_page',
			'title'  => '<span>'.__('Posts page','spcti').'</span>',
		));
	}

	if(!empty($current_ID) && $current_ID !='0'){
		$wp_admin_bar->add_menu( array(
			'parent' => 'spcti_id', 
			'id'     => 'current_ID',
			'title'  => '<span>'.__('ID','spcti').':</span> '.$current_ID,
		));
	}	

	$wp_admin_bar->add_menu(array(
		'parent' => 'spcti_id', 
		'id'     => 'post_type',
		'title'  => '<span>'.__('Post Type','spcti').':</span> '.$post_type,
	));

	if(!empty($sp_taxonomies)){
		$wp_admin_bar->add_menu(array(
			'parent' => 'spcti_id', 
			'id'     => 'taxonomies',
			'title'  => '<span>'.__('Taxonomies','spcti').':</span> '.$sp_taxonomies,
		));
	}	
	
	if(!empty($sp_terms)){	
		$wp_admin_bar->add_menu(array(
			'parent' => 'spcti_id', 
			'id'     => 'term',
			'title'  => '<span>'.__('Terms','spcti').':</span> '.$sp_terms,
		));
	}

	$wp_admin_bar->add_menu(array(
		'parent' => 'spcti_id', 
		'id'     => 'count_queries',
		'title'  => '<span>'.__('Total Queries','spcti').':</span> '.$sp_count_queries,
	));

	$wp_admin_bar->add_menu(array(
		'parent' => 'spcti_id', 
		'id'     => 'time_queries',
		'title'  => '<span>'.__('Total Queries Time','spcti').':</span> '.$sp_time_queries.' s',
	));

	$wp_admin_bar->add_menu(array(
		'parent' => 'spcti_id', 
		'id'     => 'theme_name',
		'title'  => '<span>'.__('Theme Name','spcti').':</span> '.$current_theme_name,
	));
}

?>