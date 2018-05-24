<?php 
/*
Plugin Name:  Galleria.io for Wordpress
Description:  Implements galleria.io image galery on Wordpress
Version:      1.0
Author:       Mario Ito
Author URI:   https://marioito.com.br
*/

class Galleriaio_wordpress {

	function __construct() {
		add_action('wp_enqueue_scripts', array(&$this, 'galleria_scripts'));

		add_action('wp_head', array(&$this, 'custom_galleria_css'));
		add_action('wp_footer', array(&$this, 'galleria_run'));

		remove_shortcode('gallery');
		add_shortcode('gallery', array(&$this, 'parse_gallery_shortcode'));
	}

	function galleria_run() {
		if (is_single()) {
			global $post;
			if( has_shortcode( $post->post_content, 'gallery' ) || has_post_format('gallery') ) {
				echo  "<script type='text/javascript'>Galleria.run('.galleria', {_toggleInfo: false});</script>";
			}
		}
	}

	function custom_galleria_css() {
		if (is_single()) {
			global $post;
			if( has_shortcode( $post->post_content, 'gallery' ) || has_post_format('gallery') ) {
				echo "<style type='text/css'>
				.galleria { height:500px; margin-bottom:20px; }
				.galleria .galleria-info { height:100%; margin:0; top:0; left:0; z-index:1; }
				.galleria-theme-classic .galleria-info-text { height:100%; background:transparent; margin:0; padding:0; top:0; }
				.galleria-theme-classic .galleria-info { position: relative; width:auto; height:100%; top:0; }
				.galleria-theme-classic .galleria-info-description { text-align: center; position: absolute; bottom: 68px; background-color: rgba(0,0,0,0.8); padding:4px 8px; width:80%; margin-left:10%; }
				.galleria-theme-classic .galleria-info-description span { font: normal 12px/15px 'Open Sans'; }
				.galleria-theme-classic .galleria-info-title { color:#bbbbbb; margin: 0 6px 0 0; background-color: #000000; padding: 4px 8px; width: auto; display: inline; float: right; font-weight:normal; }
				.galleria-image-nav { z-index:99; }

				@media screen and (max-width: 640px) {
				  .galleria { height: 320px; }
				  .galleria-theme-classic .galleria-info-description { width:100%; margin-left:0; }
				}
				</style>";
			}
		}
	}

	function galleria_scripts(){
		if (is_single()) {
			global $post;
			if( has_shortcode( $post->post_content, 'gallery' ) || has_post_format('gallery') ) {
				wp_enqueue_script( 'galleria', 'https://cdnjs.cloudflare.com/ajax/libs/galleria/1.5.7/galleria.min.js', array('jquery'), '1.5.7' );
				wp_enqueue_script( 'galleria-theme', 'https://cdnjs.cloudflare.com/ajax/libs/galleria/1.5.7/themes/classic/galleria.classic.min.js', array('jquery','galleria'), '1.5.7' );
			}
		}
	}

	function parse_gallery_shortcode($atts) {

	    global $post;

	    if ( ! empty( $atts['ids'] ) ) {
	        // 'ids' is explicitly ordered, unless you specify otherwise.
	        if ( empty( $atts['orderby'] ) )
	            $atts['orderby'] = 'post__in';
	        $atts['include'] = $atts['ids'];
	    }

	    extract(shortcode_atts(array(
	        'orderby' => 'menu_order ASC, ID ASC',
	        'include' => '',
	        'id' => $post->ID,
	        'itemtag' => 'dl',
	        'icontag' => 'dt',
	        'captiontag' => 'dd',
	        'columns' => 3,
	        'size' => 'medium',
	        'link' => 'file'
	    ), $atts));

	    $args = array(
	        'post_type' => 'attachment',
	        'post_status' => 'inherit',
	        'post_mime_type' => 'image',
	        'orderby' => $orderby
	    );

	    if ( !empty($include) )
	        $args['include'] = $include;
	    else {
	        $args['post_parent'] = $id;
	        $args['numberposts'] = -1;
	    }

	    $images = get_posts($args);
	    
	    $saida = '<div class="galleria" style="height:500px">';
	    foreach ( $images as $image ) {     
	    	
	        $caption = $image->post_excerpt;	

	        $description = $image->post_content;
	        if($description == '') $description = $image->post_title;

	        $image_alt = get_post_meta($image->ID,'_wp_attachment_image_alt', true);
	        $src_thumb = wp_get_attachment_image_src($image->ID, 'thumbnail');
	        $src_big = wp_get_attachment_image_src($image->ID, 'large');
	        // Better Image Credits Meta
	        if (get_post_meta( $image->ID, '_wp_attachment_source_name', true ) != '') {
	        	$credit = 'data-title="'.get_post_meta( $image->ID, '_wp_attachment_source_name', true ).'" ';
	        }
	        $saida .= '<a href="'.$src_big[0].'"><img src="'.$src_thumb[0].'" data-big="'.$src_big[1].'" '.$credit.'data-description="<span>'.$caption.'</span>" /></a>';
	    }
	    $saida .= '</div>';
	    return  $saida;
	}

}

$Galleriaio_wordpress = new Galleriaio_wordpress();