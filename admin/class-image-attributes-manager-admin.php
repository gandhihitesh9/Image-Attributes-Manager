<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Image_Attributes_Manager
 * @subpackage Image_Attributes_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Image_Attributes_Manager
 * @subpackage Image_Attributes_Manager/admin
 * @author     abc <abc@gmail.com>
 */
class Image_Attributes_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Image_Attributes_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Image_Attributes_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/image-attributes-manager-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Image_Attributes_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Image_Attributes_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/image-attributes-manager-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function load_dependencies(){
		
	}

	public function add_hooks(){
		add_filter('manage_upload_columns', array($this, 'add_alt_text_column'));
		// add_filter('default_hidden_columns', array($this, 'hide_alt_text_column'), 10, 2);
		add_filter('manage_media_custom_column', array($this, 'add_alt_text_column_val'), 10, 2);
		// add_action('manage_media_custom_column', array($this, 'show_alt_text_column'), 10, 2);

		// Add bulk actions to media library
        add_filter('bulk_actions-upload', array($this, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-upload', array($this, 'handle_bulk_actions'), 10, 3);

		add_action('wp_ajax_bulk_update_individual_alt_tags', array($this, "bulk_update_individual_alt_tags_cb"));


	}



	
	

	public function add_alt_text_column($columns) {
		$columns['alt_text'] = esc_html__('Alt Text', 'image-attributes-manager');
		return $columns;
	}
	public function hide_alt_text_column($hidden, $screen) {
		if($screen->id == "upload") {
			$hidden[] = 'alt_text';
		}
		return $hidden;
	}
	public function add_alt_text_column_val($column_name, $post_id) {
		$alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
		if ($column_name === 'alt_text') {
			echo esc_html($alt);
		}
	}

	public function show_alt_text_column($column_name, $post_id) {
		if ($column_name === 'alt_text') {
			$post_id = intval($post_id);
			if (wp_attachment_is_image($post_id)) {
				$alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
				echo esc_html($alt);
			}
			
		}
	}

	/**
     * Add bulk actions to media library
     */
    public function add_bulk_actions($bulk_actions) {
        $bulk_actions['bulk_set_alt_filename'] = esc_html__('Generate Alt from Filename', 'image-attributes-manager');
        $bulk_actions['bulk_set_alt_title'] = esc_html__('Generate Alt from Title', 'image-attributes-manager');
        $bulk_actions['bulk_clear_alt'] = esc_html__('Clear Alt Tags', 'image-attributes-manager');
        return $bulk_actions;
    }

	/**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        switch ($doaction) {
    
                
            case 'bulk_set_alt_filename':
                $updated = $this->generate_alt_from_filename($post_ids);
                $redirect_to = add_query_arg('bulk_alt_updated', $updated, $redirect_to);
                break;
                
            case 'bulk_set_alt_title':
                $updated = $this->generate_alt_from_title($post_ids);
                $redirect_to = add_query_arg('bulk_alt_updated', $updated, $redirect_to);
                break;
                
            case 'bulk_clear_alt':
                $updated = $this->clear_alt_tags($post_ids);
                $redirect_to = add_query_arg('bulk_alt_cleared', $updated, $redirect_to);
                break;
        }
        $redirect_to = wp_nonce_url($redirect_to, 'iam-bulk-actions');
        return $redirect_to;
    }

	/**
     * Generate alt tags from filename
     */
    private function generate_alt_from_filename($post_ids) {
        $updated = 0;
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post && wp_attachment_is_image($post_id)) {
                $filename = pathinfo($post->post_title, PATHINFO_FILENAME);
                
                // Clean filename: replace dashes/underscores with spaces, capitalize words
                $alt_text = ucwords(str_replace(array('-', '_'), ' ', $filename));
                
                update_post_meta($post_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
                $updated++;
            }
        }
        
        return $updated;
    }

	/**
     * Generate alt tags from post title
     */
    private function generate_alt_from_title($post_ids) {
        $updated = 0;
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post && wp_attachment_is_image($post_id)) {
                $alt_text = sanitize_text_field($post->post_title);
                update_post_meta($post_id, '_wp_attachment_image_alt', $alt_text);
                $updated++;
            }
        }
        
        return $updated;
    }

	/**
     * Clear alt tags
     */
    private function clear_alt_tags($post_ids) {
        $updated = 0;
        
        foreach ($post_ids as $post_id) {
            if (wp_attachment_is_image($post_id)) {
                delete_post_meta($post_id, '_wp_attachment_image_alt');
                $updated++;
            }
        }
        
        return $updated;
    }

	/**
     * Admin notices
     */
    public function admin_notices() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'iam-bulk-actions' ) ) {
			wp_die( 'Security check failed' );
		}
        if (isset($_GET['bulk_alt_updated'])) {
            $updated = intval($_GET['bulk_alt_updated']);
            echo '<div class="notice notice-success is-dismissible">';
			// Translators: %d is the number of images whose ALT tag was updated.
            echo '<p>' . esc_html(sprintf(_n('%d image alt tag updated.', '%d image alt tags updated.', $updated, "image-attributes-manager"), $updated) ). '</p>';
            echo '</div>';
        }
        
        if (isset($_GET['bulk_alt_cleared'])) {
            $cleared = intval($_GET['bulk_alt_cleared']);
            echo '<div class="notice notice-success is-dismissible">';
			// Translators: %d is the number of images whose ALT tag was cleared.
            echo '<p>' . esc_html(sprintf(_n('%d image alt tag cleared.', '%d image alt tags cleared.', $cleared, "image-attributes-manager"), $cleared)) . '</p>';
            echo '</div>';
        }
    }


	public function bulk_update_individual_alt_tags_cb(){
		check_ajax_referer('bulk_alt_nonce', 'nonce');
		$send_response = array(
			"success" => false,
			"message" => esc_html__('Something went wrong', 'image-attributes-manager'),
		);
		if (!current_user_can('upload_files')) {
			wp_die('Insufficient permissions');
		}
		if(isset($_POST['alt_tags']) && !empty($_POST['alt_tags'])){
			$alt_tags = array_map( 'sanitize_text_field', wp_unslash( $_POST['alt_tags'] ) );
			$updated = 0;
			
			foreach ($alt_tags as $post_id => $alt_text) {
				$post_id = intval($post_id);
				if (wp_attachment_is_image($post_id)) {
					update_post_meta($post_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
					$updated++;
				}
			}
			$send_response = array(
				"success" => true,
				"message" => esc_html__('All selected images alt tags updated', 'image-attributes-manager'),
				"updated" => $updated
			);
			wp_send_json_success($send_response);
		}
		
		wp_send_json_success($send_response);
		die;
	}

}
