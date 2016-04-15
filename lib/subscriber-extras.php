<?php

class RNS_Subscriber_Extras {
	private $import_version = 1;

	function __construct() {
		if(function_exists('rcp_is_active')){
			//make sure RCP is available before we do anything.
			//temporary removal of restrict filter on old posts, reenable once everyone si live
			add_action('wp', array($this, 'hide_old_posts'));

			//this guts the old way in favor of our new way without forcing plugin deactivation
			remove_filter( 'img_caption_shortcode', 'rns_include_image_archive_url');
			add_filter('img_caption_shortcode', array($this, 'add_caption_download_text'), 10, 3);
		}
		if(class_exists( 'Easy_Digital_Downloads' )){
			//check that EDD is enabled
			// creates the downloads posts
			add_action('transition_post_status', array($this, 'auto_create_downloads'), 10, 3);
			// updates the download posts on save
			add_action('save_post', array($this, 'update_downloads'), 10, 2);

			add_filter('rns-subscriber-enhancements-post-image-number', array($this, 'normalize_array_keys'));
			add_filter('rns-edd-mods-content-html', array( $this, 'cleanup_html'), 10, 1);
			add_filter('rns-edd-mods-content-text', array( $this, 'cleanup_text'), 10, 1);
			add_filter( 'edd_email_heading', array( $this, 'set_email_heading' ) );

		}
		if( ! is_admin() && function_exists('rcp_is_active') && class_exists( 'Easy_Digital_Downloads' )){
			// allows for old posts to get a download associated on pageview
			add_action('wp', array($this, 'check_dl_post'));

			add_filter('the_content', array($this, 'output_download_link'));
			add_filter('the_content', array($this, 'attachment_page_link'));
		}
	}

	/**
	 * attachment_page_link generates the download link for subscribers
	 *
	 * @param $content string the incoming content
	 * @return string the content with the download links
	 */
	function attachment_page_link($content){
		if(is_attachment()){
			$dl_post_id = get_post_meta(get_the_ID(), '_edd_download_post', true);
			if($dl_post_id){
				if(function_exists('rcp_is_active') && rcp_is_active()){
					$new = '<div id="rns-subscriber-purchase" class="well well-small alignleft">';
					$new .= __('This image is available for republication.', 'rns-subscriber-enhancements');
					$new .= sprintf('[purchase_link id="%d" text="%s" style="button"]', $dl_post_id, __('Download Now', 'rns-subscriber-enhancements'));
					$new .= '</div>';
					$content = $content . $new;
				}
			}
		}
		return $content;
	}


	/**
	 * output_download_link generates the download link for subscribers
	 *
	 * @param $content string the incoming content
	 * @return string the content with the download links
	 */
	function output_download_link( $content ) {
		
		// figure out if we're looking at a press release
		$pr_term = get_term_by( 'slug', 'press-releases', 'post-type' );
		$post_terms = wp_get_post_terms( get_the_id(), 'post-type' ); 
		$is_press_release = array_search( $pr_term, $post_terms ); // will be false if term is not found, 0 otherwise
		
		if( is_singular( 'post' ) && ( $is_press_release === false ) ) {
			$dl_post_id = get_post_meta( get_the_ID(), '_edd_download_post', true );
			$is_restricted = get_post_meta( get_the_ID(), '_rns_not_for_republication', true );
			
			if( $dl_post_id && ! $is_restricted ) {
				if( function_exists( 'rcp_is_active' ) && rcp_is_active() ) {

					$new = '<div id="rns-subscriber-purchase" class="well well-small alignleft">';
					$new .= sprintf( '<span>%s</span>', __( 'This story is available for republication.', 'rns-subscriber-enhancements' ) );
					$new .= sprintf( '[purchase_link id="%d" text="%s" style="button"]', $dl_post_id, __( 'Download Now', 'rns-subscriber-enhancements' ) );
					$new .= '</div>';
					$content = $content . $new;

				} else {
					//temp removal until all is active - this would show link to non active subs or if RCP is disabled...
					//do nothing for now
					//$new .= sprintf('[purchase_link id="%d" text="%s" style="button"]', $dl_post_id, __('Buy Now', 'rns-subscriber-enhancements'));
				}
			}
		}
		return $content;
	}
	

	/**
	 * check_dl_post checks if a post has a download post and creates one if not
	 */
	function check_dl_post() {

		if( ! is_singular( 'post' ) ) {
			return;
		}

		if( ! function_exists( 'rcp_is_active' ) || ! rcp_is_active() ) {
			return;
		}

		$dl_post_id = get_post_meta( get_the_ID(), '_edd_download_post', true );

		if( !$dl_post_id ) {
			self::create_download_post( get_post( get_the_ID() ) );
		}

	}

	/**
	 * checks if the image has download post, creating if needed
	 */
	function check_img_attachments() {
		if(!is_attachment()){
			return;
		}
		$dl_post_id = get_post_meta(get_the_ID(), '_edd_download_post', true);
		if(!$dl_post_id){
			self::create_img_download_post(get_post(get_the_ID()));
		}

	}

	/**
	 * Creates the image downloads
	 */
	function create_img_download_post($post){
		$dl_post_id = get_post_meta($post->ID, '_edd_download_post', true);
		$dl_post = array(
			'post_type' => 'download',
			'post_title' => sprintf('%s "%s"', __('Download', 'rns-subscriber-enhancements'), $post->post_title),
			'post_status' => 'publish',
			'post_parent' => $post->ID, //set the parent item of the download to be this post, not sure how EDD will react
		);

		if(!$dl_post_id){
			$dl_post_id = wp_insert_post($dl_post);
			update_post_meta($post->ID, '_edd_download_post', $dl_post_id);
		} else {
			$dl_post['ID'] = $dl_post_id;
			wp_update_post($dl_post);
		}

		//set prices first
		self::set_image_prices($dl_post_id);
		$license_file = plugin_dir_url(__FILE__) . 'license.txt'; //for now.
		$image_license = self::create_image_credits( $post->ID );

		$files = array();
		$files[] = array('name' => __('License', 'rns-subscriber-enhancements'), 'file' => $license_file);
		$files[] = array('name' => __('Image Credit', 'rns-subscriber-enhancements'), 'file' => $image_license);
		$files[] = array('name' => __('Image', 'rns-subscriber-enhancements') , 'file' => wp_get_attachment_url($post->ID));


		update_post_meta($dl_post_id, 'edd_download_files', $files);
	}

	/**
	 * auto_create_downloads sets up the download post on initial creation
	 *
	 * @param $new_status string the old status
	 * @param $old_status string the new status
	 * @param $post object the post object
	 */
	function auto_create_downloads($new_status, $old_status, $post){

		if( $new_status == 'auto-draft' || $post->post_type != 'post' ){
			return;
		}
		self::create_download_post($post);

		return;

	}

	/**
	 * create_download_post creates the download post if it does not exist
	 *
	 * @param $post
	 */
	function create_download_post($post){
		if( ! $post ){
			$post = get_post(get_the_ID());
		}

		$dl_post_id = get_post_meta($post->ID, '_edd_download_post', true);
		$dl_version = get_post_meta($post->ID, '_rns_edd_import_version', true);

		$dl_post = array(
			'post_type' => 'download',
			'post_title' => sprintf('%s "%s"', __('Media Assets for', 'rns-subscriber-enhancements'), $post->post_title),
			'post_status' => 'publish',
			'post_parent' => $post->ID, //set the parent item of the download to be this post, not sure how EDD will react
		);

		if(!$dl_post_id){
			$dl_post_id = wp_insert_post($dl_post);
			update_post_meta($post->ID, '_edd_download_post', $dl_post_id);
		} else {
			$dl_post['ID'] = $dl_post_id;
			wp_update_post($dl_post);
		}
		update_post_meta(get_the_ID(), '_rns_edd_import_version', $this->version);

		//set prices first
		self::set_default_prices($dl_post_id);

		//now work on the DL post a bit.
		$text_transcript = self::create_text_transcript($post);
		$html_transcript = self::create_html_transcript($post);

		$featured_image_id = get_post_thumbnail_id($post->ID);
		$post_image_ids = self::get_post_images($post);


		$license_file = plugin_dir_url(__FILE__) . 'license.txt'; //for now.

		$files = get_post_meta($dl_post_id, 'edd_download_files', true);
		//clean out previously generated files
		$files = self::remove_previous_files($files);

		$files[] = array('name' => __('License', 'rns-subscriber-enhancements'), 'file' => $license_file);
		$files[] = array('name' => __('Article Text', 'rns-subscriber-enhancements'), 'file' => $text_transcript);
		$files[] = array('name' => __('Article HTML', 'rns-subscriber-enhancements'), 'file' => $html_transcript);

		// $files[] = array('name' => __('Article Text', 'rns-subscriber-enhancements'), 'file' => $text_transcript, 'condition' => 2 );

		if( $featured_image_id ) {
			// _navis_media_can_distribute corresponds to the checkbox "Available for Republication on the Web Only"
			$is_only_available_for_web = !! get_post_meta($featured_image_id, '_navis_media_can_distribute', true);
			// rns_image_archives_url corresponds to the text field "Hi Resolution Print Quality URL"
			$is_available_for_print = !! get_post_meta($featured_image_id, 'rns_image_archives_url', true);

			// If either of these interface items is used, then it should be available. Only in the case where they are both
			// blank should it not be made available.
			if( $is_only_available_for_web || $is_available_for_print ) {
				$feat_image_url = wp_get_attachment_url( $featured_image_id );
				$image_license = self::create_image_credits( $featured_image_id );
				$files[] = array('name' => __('Featured Image', 'rns-subscriber-enhancements'), 'file' => $feat_image_url );
			}
		}

		if( $post_image_ids ) {
			$counter = 0;
			foreach( $post_image_ids as $key => $post_image_id ) {
				// _navis_media_can_distribute corresponds to the checkbox "Available for Republication on the Web Only"
				$is_only_available_for_web = !! get_post_meta($post_image_id, '_navis_media_can_distribute', true);
				// rns_image_archives_url corresponds to the text field "Hi Resolution Print Quality URL"
				$is_available_for_print = !! get_post_meta($post_image_id, 'rns_image_archives_url', true);

					/*
					Case 1: If "Available for Republication on the Web Only" is checked (_navis_media_can_distribute == true) AND "Hi Resolution Print Quality URL" is filled out:

					The low resolution Web Image link should be made available in the download package and the Print Image link should not be available.

					Case 2: If "Available for Republication on the Web Only" is checked (_navis_media_can_distribute == true) AND "Hi Resolution Print Quality URL" is NOT filled out:

					The low resolution Web Image link should be made available in the download package and the Print Image link should not be available.
(The results of Case 1 and Case 2 are the same.)

					Case 3: If "Available for Republication on the Web Only" is NOT checked (_navis_media_can_distribute == false) AND "Hi Resolution Print Quality URL" is filled out:

					Both the Web Image and Print Image links should be available in the download package, with the Web Image link set to the original low resolution version and the Print Image link set to the value of the "Hi Resolution Print Quality URL" field.

					Case 4: If "Available for Republication on the Web Only" is NOT checked (_navis_media_can_distribute == false) AND "Hi Resolution Print Quality URL" is NOT filled out:

					Neither the Web Image nor the Print Image links should be available in the download package.
					 */
				if( ! $is_only_available_for_web && ! $is_available_for_print ) {
					continue;
				}
				$image_license = self::create_image_credits( $post_image_id );
				$files[] = array('name' => sprintf('%s %s', __('Image Credit', 'rns-subscriber-enhancements') , apply_filters('rns-subscriber-enhancements-post-image-number', $counter)), 'file' => $image_license);
				$files[] = array('name' => sprintf('%s %s', __('Web Image', 'rns-subscriber-enhancements') , apply_filters('rns-subscriber-enhancements-post-image-number', $counter)), 'file' => wp_get_attachment_url($post_image_id));

				if( ! $is_only_available_for_web && $is_available_for_print ) {
					$print_url = get_post_meta($post_image_id, 'rns_image_archives_url', true);

					$imported_high_res = get_post_meta($post_image_id, 'rns_image_archive_imported', true);
					if(substr($print_url, 0 , 15) == 'http://archives' && ! $imported_high_res){

						update_post_meta($post_image_id, 'rns2_highres_image', true);

						//saves the legacy url, wish I had done this in the first version...
						update_post_meta($post_image_id, 'rns_image_archives_url_legacy', $print_url);
						$image_meta = get_post_meta($post_image_id, '_wp_attachment_metadata', true);
						$image_file = $image_meta['file'];
						$file_parts = explode('/', $image_file);
						$yr = ("2015" == $file_parts[0] ) ? "2015%20Photos" : $file_parts[0];
						$filename =str_replace('thumb', '', $file_parts[2]);
						// this actually grabs the image (raw) from archives. needs meta to work
						$hack_the_print_url = 'http://archives.religionnews.com/images/uploads/articles/' . $yr . '/' . $filename;

						$print_image_wp = self::import_archive_image($hack_the_print_url, $post_image_id, str_replace('thumb', '', $file_parts[2]));
						// $print_image_caption = self::import_archive_image_caption($print_url, $print_image_wp);

						if($print_image_wp){
							$files[] = array('name' => sprintf('%s %s', __('Print Image', 'rns-subscriber-enhancements') , apply_filters('rns-subscriber-enhancements-post-image-number', $counter)), 'file' => $print_image_wp);
							// $files[] = array('name' => sprintf('%s %s', __('High Resolution Post Image', 'rns-subscriber-enhancements'), apply_filters('rns-subscriber-enhancements-post-image-number', $counter)) , 'file' => $print_image_wp, 'condition' => 2);

							//skip updating the post metas for now...
							update_post_meta($post_image_id, 'rns_image_archives_url', $print_image_wp);
							update_post_meta($post_image_id, 'rns_image_archive_imported', $print_image_wp);
						}
					}elseif($print_url){
						$files[] = array('name' => sprintf('%s %s', __('Print Image', 'rns-subscriber-enhancements') , apply_filters('rns-subscriber-enhancements-post-image-number', $counter)), 'file' => get_post_meta($post_image_id, 'rns_image_archives_url', true));
					}
				}
				//end foreach loop
				$counter++;
			}
		}
		update_post_meta($dl_post_id, 'edd_download_files', $files);

	}

	function update_downloads($post_id, $post){
		if( $post->post_status != 'publish' || $post->post_type != 'post' ){
			return $post_id;
		}
		self::create_download_post( $post );
	}

	/**
	 * @param $url string the legacy archive url
	 * @param $post_id int the post to attach the post
	 * @param $desc string the image caption
	 * @return mixed false if error, url if success
	 */
	function import_archive_image($url, $post_id, $desc){
		if(!is_admin()){
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');
		}

		$doc = new DOMDocument();
		$desc = sprintf('%s %s', __('High resolution version of', 'rns-subscriber-enhancements'), $desc);
		$image = media_sideload_image($url, $post_id, $desc);

		if(is_wp_error($image)){
			return false;
		}

		$doc->loadHTML($image);
		$imageTags = $doc->getElementsByTagName('img');
		foreach($imageTags as $tag) {
			$file_url = $tag->getAttribute('src');
		}
		return $file_url;
	}

	function import_archive_image_caption($print_url, $photo_id){
		$image_post_id = self::get_attachment_id_from_url( $photo_id );
		$doc = wp_remote_get( $print_url );
		$rbody = $doc['body'];

		$doc = new DOMDocument();
		$doc->loadHTML($rbody);


		$captions = $doc->getElementsByTagName('figcaption');
		foreach($captions as $cap) {
			update_post_meta( $photo_id, 'rns_archive_imported_caption', $cap->nodeValue );
		}

		return $captions;
	}


	/**
	 * attempts to find the post id of an image by its url
	 */
	function get_attachment_id_from_url( $attachment_url = '' ) {

		global $wpdb;
		$attachment_id = false;

		// If there is no url, return.
		if ( '' == $attachment_url )
			return;

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}

	/**
	 * remove_previous_files strips the download post of the previously added files
	 *
	 * @todo this is a bit hacky at the moment and won't work if translated
	 * @param $files array the incoming files to clean up
	 * @return array the remaining files
	 */
	function remove_previous_files($files){
		$filenames = array('Article Text', 'Article HTML', 'Featured Image', 'Featured Image Credit', 'License');
		if(is_array($files)){
			foreach ( $files as $key => $file ){
				if ( in_array($file['name'], $filenames) || substr($file['name'], 0, 12) == 'Image Credit' || substr($file['name'], 0, 9) == 'Web Image' || substr($file['name'], 0, 11) == 'Print Image' ) {
					unset($files[$key]);
				}
			}
		}
		return $files;
	}

	/**
	 * get_post_images parses the post content for images, and puts the id's into an array
	 *
	 * @param $post
	 * @return bool
	 */
	function get_post_images($post){
		$images = false;
		if(preg_match_all('~wp-image-(\d+)~', $post->post_content, $found_images)){

			$images = $found_images[1];
		}
		return $images;

	}

	/**
	 * normalize_array_keys returns the word version of a number
	 *
	 * @param $key int the number
	 * @return string|void
	 */
	function normalize_array_keys($key){
		switch ($key){
		case 0:
			$word_value = __('One', 'rns-subscriber-enhancements');
			break;
		case 1:
			$word_value = __('Two', 'rns-subscriber-enhancements');
			break;
		case 2:
			$word_value = __('Three', 'rns-subscriber-enhancements');
			break;
		case 3:
			$word_value = __('Four', 'rns-subscriber-enhancements');
			break;
		case 4:
			$word_value = __('Five', 'rns-subscriber-enhancements');
			break;
		case 5:
			$word_value = __('Six', 'rns-subscriber-enhancements');
			break;
		case 6:
			$word_value = __('Seven', 'rns-subscriber-enhancements');
			break;
		case 7:
			$word_value = __('Eight', 'rns-subscriber-enhancements');
			break;
		case 8:
			$word_value = __('Nine', 'rns-subscriber-enhancements');
			break;
		case 9:
			$word_value = __('Ten', 'rns-subscriber-enhancements');
			break;
		default:
			$word_value = '';
			break;
		}
		return $word_value;
	}

	/**
	 * set_default_prices sets the prices for download types
	 *
	 * @param $dl_post_id int the id of the download
	 */
	function set_default_prices($dl_post_id){
		$default_price = apply_filters('rns-subscriber-enhancements-default-price', '0.00');
		update_post_meta($dl_post_id, 'edd_price', $default_price);
	}

	/**
	 * set_image_prices sets the prices for image downloads
	 *
	 * @param $dl_post_id int the id of the download
	 */
	function set_image_prices($dl_post_id){
		$default_price = apply_filters('rns-subscriber-enhancements-default-image-price', '0.00');

		update_post_meta($dl_post_id, 'edd_price', $default_price);
	}

	/**
	 * create_image_credits sets up the credits for an image
	 *
	 * @param $image string the attachment id
	 * @return string the image credit file url
	 */
	function create_image_credits($image){
		$image_post = get_post($image);
		$upload_dir = wp_upload_dir();
		$image_guid = explode('/', $image_post->guid);
		$filename = end($image_guid);

		$file = $upload_dir['path'] . '/' . $image . '-credit.txt';
		$credit_text = sprintf('%s: %s', __('Image Filename', 'rns-subscriber-enhancements'), $filename);
		$credit_text .= ($image_post->post_content) ? "\n\n\n" . sprintf('%s: %s', __('Caption', 'rns-subscriber-enhancements'), $image_post->post_content) : '';
		$rns_credit_text = get_post_meta($image, '_media_credit', true);
		$credit_text .= ($rns_credit_text) ? "\n\n\n" . sprintf('%s: %s', __('Photo Credit', 'rns-subscriber-enhancements'), $rns_credit_text) : '';
		$rns_credit_url = get_post_meta($image, '_media_credit_url', true);
		$credit_text .= ($rns_credit_url) ? "\n\n\n" . sprintf('\n\n%s: %s', __('Credit Url', 'rns-subscriber-enhancements'), $rns_credit_url) : '';

		if(is_file($file)){
			$open = fopen($file, 'w');
			if($open){
				fwrite($open, $credit_text);
				fclose($open);
			}
		} else {
			$open = fopen($file, 'c');
			if($open){
				fwrite($open, $credit_text);
				fclose($open);
			}
		}
		return $upload_dir['url'] . '/' . $image . '-credit.txt';
	}

	/**
	 * create_text_transcript dumps the content into a text file
	 *
	 * @param $post object the post object
	 * @return string the url to the transcript
	 */
	function create_text_transcript($post){
		$upload_dir = wp_upload_dir();
		$file = $upload_dir['path'] . '/' . $post->ID . '-text.txt';

		$title = $post->post_title;
		$byline = apply_filters('rns-edd-mods-byline', __('by: ', 'rns-subscriber-enhancements') . get_the_author_meta('display_name', $post->post_author) );
		$top = apply_filters('rns-edd-mods-top-text' , $title . "\n\n" . $byline . "\n" . date('M j, Y', strtotime($post->post_date) ) . "\n\n\n" );
		$content = apply_filters('rns-edd-mods-content-text', $post->post_content);
		$bottom = apply_filters('rns-edd-mods-content-text', '');

		if(is_file($file)){
			//exists, so update it.
			$open = fopen($file, 'w');
			if($open){
				fwrite($open, $top . $content . $bottom);
				fclose($open);
			}

		} else {
			//file doesn't exist, create it.
			$open = fopen($file, 'c');
			if($open){
				fwrite($open, $top . $content . $bottom);
				fclose($open);
			}
		}

		return $upload_dir['url'] . '/' . $post->ID . '-text.txt';
	}

	/**
	 * create_html_transcript dumps the content into a html file
	 *
	 * @param $post object the post object
	 * @return string the url to the transcript
	 */
	function create_html_transcript($post){
		$upload_dir = wp_upload_dir();
		$file = $upload_dir['path'] . '/' . $post->ID . '-html.txt';

		$title = $post->post_title;
		$byline = apply_filters('rns-edd-mods-byline', __('by: ', 'rns-subscriber-enhancements') . get_the_author_meta('display_name', $post->post_author) );
		// I hate that I am putting this markup here like this
		// but last minute addons make it possible
		$top_markup = sprintf('<h1 class="title">%s</h1>', $title);
		$top_markup .= "\n\n" . sprintf('<p class="author">%s</p>', $byline);
		$top_markup .= "\n\n" . sprintf('<p class="date">%s</p>', date('M j, Y', strtotime($post->post_date) ) );

		$top = apply_filters('rns-edd-mods-top-text' , $top_markup);
		$content = apply_filters('rns-edd-mods-content-html', $post->post_content);
		$bottom = apply_filters('rns-edd-mods-content-text', '');


		if(is_file($file)){
			//exists, so update it.
			$open = fopen($file, 'w');
			if($open){
				fwrite($open, $top . "\n\n" . $content . "\n\n" . $bottom);
				fclose($open);
			}
		} else {
			//file doesn't exist, create it.
			$open = fopen($file, 'c');
			if($open){
				fwrite($open, $top . "\n\n" . $content . "\n\n" .$bottom);
				fclose($open);
			}
		}
		return $upload_dir['url'] . '/' . $post->ID . '-html.txt';
	}

	/**
	 * Filter for cleansing html before saving it to the filesystem
	 *
	 * @since 0.2
	 * @author Russell Fair
	 */
	function cleanup_html($content){

		//strips some html except the very basicas
		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array(),
			),
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array(),
			'h7' => array(),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'ul' => array(
				'li' => array(),
			),
			'blockquote' => array(),
			'cite' => array(),
			'p' => array(),
		);
		$content = wp_kses( $content, $allowed_html, array('http') );

		//finally, remove shortcodes
		$content = strip_shortcodes( $content );

		$content = wpautop($content);

		return $content;
	}

	/**
	 * Filter for cleansing text before saving it to the filesystem
	 *
	 * @since 0.2
	 * @author Russell Fair
	 */
	function cleanup_text($content){
		//this strips paragraphs and replaces with newlines first.
		$content = preg_replace("~<p(.*?)>~", "\n", $content);
		$content = str_replace("</p>", "\n", $content);

		//then strips all remaining html
		$allowed_html = array();
		$content = wp_kses( $content, $allowed_html );

		//and shortcodes
		$content = strip_shortcodes( $content );

		return $content;
	}

	/**
	 * Overrides the built in EDD purchase reciept heading
	 *
	 * @param $heading string the existing heading
	 */
	function set_email_heading( $heading ) {
		return __('Download Confirmation', 'rns_subscriber_extras');
	}

	/**
	 * add_caption_download_text adds the subscriber download text to the image captions
	 *
	 * @param $html string the existing html
	 * @param $attr string the incoming attributes
	 * @param $content string the content within the shortcode
	 * @return string the new text of the caption
	 */
	function add_caption_download_text($html, $attr, $content){
		//this is a replacement for the archives image url plugin
		if( ! is_singular() ){
			return $content;
		}

		extract(shortcode_atts(array(
			'id'    => '',
			'align' => 'alignnone',
			'width' => '',
			'caption' => ''
		), $attr));

		if ( 1 > (int) $width || empty($caption) ){
			return $content;
		}

		/**
		 * Get the attachment ID
		 *
		 * The version of  we get with this function is "attachment_XXX," so
		 * trim the "attachment_" bit to get just the number
		 */
		$id_number = trim( $id, 'attachment_' );
		$credit = ( function_exists('rns_get_image_credit_content')) ? rns_get_image_credit_content( $id_number ) : '';

		$caption = sprintf('<p class="wp-caption-text edd-enabled"><span class="caption">%s</span><span class="credit">%s</span></p>', $caption, $credit);

		/* this is wonkey ass stuff here, so let me explain what we did here */
		/* first of all the meta key "_navis_media_can_distribute" means not downloadable for print purposes, but IS available for web */
		$web_dl = !! get_post_meta($id_number, '_navis_media_can_distribute', true);
		/* the "rns_image_archives_url" is a legacy hold over from when we linked WP to EE */
		/* It is now just a link to another media library item, presumably */
		$print_dl = get_post_meta($id_number, 'rns_image_archives_url', true);

		/* so first and foremost, if there is a print_dl and the web_dl isn't checked, assume that the image is redistributable everywhere */
		if ( $print_dl && ! $web_dl ) {
			// do print and web
			$caption .= '<hr class="hr-small" /><p class="wp-caption-text"><i class="icon-picture">&nbsp;</i>';
			$caption .= __('This image is available for web and print publication. ', 'rns-subscriber-enhancements');
			$caption .= sprintf('%s <a href="mailto:%s">%s</a>.', __('For questions, contact ','rns-subscriber-enhancements'), antispambot( 'sally.morrow@religionnews.com' ), __('Sally Morrow', 'rns-subscriber-enhancements'));

		}
		/* otherwise if the web dl is checked do the web only link */
		else if ( $web_dl ) {
			$caption .= '<hr class="hr-small" /><p class="wp-caption-text"><i class="icon-picture">&nbsp;</i>';
			$caption .=__('This image is available for web publication. ', 'rns-subscriber-enhancements');
			$caption .= sprintf('%s <a href="mailto:%s">%s</a>.', __('For questions, contact ','rns-subscriber-enhancements'), antispambot( 'sally.morrow@religionnews.com' ), __('Sally Morrow', 'rns-subscriber-enhancements'));
		}

		return sprintf('<div id="%s" class="wp-caption %s" style="width: %dpx;">%s %s</div>', esc_attr($id),  esc_attr($align), esc_attr($width), do_shortcode( $content ), $caption );


		return $content; //just in case

	}

	/**
	 * hide_old_posts adds the restrict content filter if the post is more than a set age
	 */
	function hide_old_posts(){
		if(is_single('post')) {
			global $posts;
			//compare the date (timestamp) now against the old post
			$date = new DateTime();
			if ( $date->getTimestamp() - strtotime($posts[0]->post_date) >= self::default_date_threshold() ) {
				add_filter('the_content', array($this, 'restrict_content'), 1, 10);
			}
		}
	}

	/**
	 * @param $content string the incoming content
	 * @return string the content wrapped with [restrict] shortcode
	 */
	function restrict_content($content){
		return '[restrict]' . $content . '[/restrict]';
	}

	/**
	 * default_date_threshold sets the date threshold for restricting posts
	 *
	 * @todo set this back to a year
	 * @return int
	 */
	function default_date_threshold(){
		return 60*60*24;
	}

	/**
	 * unsets the related post (since presumably it has been deleted)
	 */
	function unset_relate_edd_meta( $post_id ){
		delete_post_meta( $post_id, '_edd_download_post' );
	}

	/**
	 * Gets the post id of the download (should have been the post parent)
	 */
	function get_related_post_id( $dl_id ){
		$related_post_args = array(
			'post_type' => 'post',
			'posts_per_page' => 1,
			'meta_key' => '_edd_download_post',
			'meta_value_num' => $dl_id
		);
		$related_post = new WP_Query($related_post_args);
		if( $related_post->have_posts()){

			while ( $related_post->have_posts() ){
				$related_post->the_post();
				return get_the_ID();
			}
		}

	}
}
