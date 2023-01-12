<?php // phpcs:ignore -- This file name is determined by WordPress

/**
 * Plugin Name:       Custom Shortcodes
 * Description:       Create your own custom shortcodes to keep contact information consistent across your site.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4.0
 * Author:            Web Results Direct
 * Author URI:        https://wrd.studio
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       customshortcodes
 * Domain Path:       /languages
 */

namespace customshortcodes;

use PO;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Plugin_Manager' ) ) :
	/**
	 * Plugin_Manager
	 * Handles the plugin's operation.
	 *
	 * @since 1.0.0
	 */
	class Plugin_Manager {
		const DIR     = __DIR__;
		const FILE    = __FILE__;
		const VERSION = '1.0.0';

		const POST_TYPE = 'wrd_shortcode';

		/**
		 * Creates an instance of the plugin.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->add_hooks();
		}

		/**
		 * Attaches the plugins functionality to WordPress core.
		 *
		 * @since 1.0.0
		 */
		public function add_hooks() {
			add_action( 'init', array( $this, 'register_post_type' ) );
			add_action( 'init', array( $this, 'add_shortcodes' ) );

			add_filter( 'use_block_editor_for_post', array( $this, 'use_block_editor' ), 10, 2 );
			add_filter( 'wp_insert_post_data', array( $this, 'slugifiy_shortcode_title' ), 10, 2 );

			add_action( 'admin_head-edit.php', array( $this, 'add_brackets_to_list_table' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );

			add_filter( 'mce_buttons', array( $this, 'add_mce_editor_button' ) );
			add_filter( 'mce_external_plugins', array( $this, 'add_mce_editor_plugin' ) );
			add_filter( 'admin_init', array( $this, 'add_editor_style' ) );
		}

		/**
		 * Registers the shortcodes post type with WordPress.
		 *
		 * Runs on the 'init' action.
		 *
		 * @since 1.0.0
		 */
		public function register_post_type() {
			register_post_type(
				static::POST_TYPE,
				array(
					'labels'              => array(
						'name'                     => __( 'Shortcodes', 'customshortcodes' ),
						'singular_name'            => __( 'Shortcode', 'customshortcodes' ),
						'add_new_item'             => __( 'Add New Shortcode', 'customshortcodes' ),
						'edit_item'                => __( 'Edit Shortcode', 'customshortcodes' ),
						'new_item'                 => __( 'New Shortcode', 'customshortcodes' ),
						'view_item'                => __( 'View Shortcode', 'customshortcodes' ),
						'view_items'               => __( 'View Shortcodes', 'customshortcodes' ),
						'search_items'             => __( 'Search Shortcodes', 'customshortcodes' ),
						'not_found'                => __( 'No shortcode found', 'customshortcodes' ),
						'not_found_in_trash'       => __( 'No shortcodes found in Trash', 'customshortcodes' ),
						'all_items'                => __( 'Custom Shortcodes', 'customshortcodes' ),
						'archives'                 => __( 'Shortcode Archives', 'customshortcodes' ),
						'attributes'               => __( 'Shortcode Attributes', 'customshortcodes' ),
						'insert_into_item'         => __( 'Insert into shortcode', 'customshortcodes' ),
						'uploaded_to_this_item'    => __( 'Uploaded to this shortcode', 'customshortcodes' ),
						'filter_items_list'        => __( 'Filter shortcodes list', 'customshortcodes' ),
						'items_list_navigation'    => __( 'Shortcode list navigation', 'customshortcodes' ),
						'items_list'               => __( 'Shortcodes list', 'customshortcodes' ),
						'item_published'           => __( 'Shortcode published', 'customshortcodes' ),
						'item_published_privately' => __( 'Shortcode published privately', 'customshortcodes' ),
						'item_reverted_to_draft'   => __( 'Shortcode reverted to draft', 'customshortcodes' ),
						'item_scheduled'           => __( 'Shortcode scheduled', 'customshortcodes' ),
						'item_updated'             => __( 'Shortcode updated', 'customshortcodes' ),
						'item_link'                => __( 'Shortcode Link', 'customshortcodes' ),
						'item_link_description'    => __( 'A link to a shortcode', 'customshortcodes' ),
					),
					'description'         => __( 'Create your own custom shortcodes to add across your site . ', 'customshortcodes' ),

					'public'              => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'show_ui'             => true,
					'show_in_menu'        => 'tools.php',
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => true,

					'capability_type'     => 'post',
					'supports'            => array( 'title', 'editor' ),

					'has_archive'         => false,

					'can_export'          => true,
					'delete_with_user'    => false,

					'show_in_rest'        => true,
				)
			);
		}

		/**
		 * Registers a shortcode with WordPress for each shortcode post.
		 *
		 * Runs on the 'init' action.
		 *
		 * @since 1.0.0
		 */
		public function add_shortcodes() {
			$posts = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => static::POST_TYPE,
					'post_status' => 'publish',
				)
			);

			foreach ( $posts as $post ) {
				$name    = sanitize_title( $post->post_title );
				$content = $post->post_content;

				add_shortcode(
					$name,
					function() use ( $content ) {
						return wp_kses_post( $content );
					}
				);
			}
		}

		/**
		 * Disables the block editor for the shortcode post type.
		 *
		 * Runs on the 'use_block_editor_for_post' filter.
		 *
		 * @param bool    $use_block_editor Result of previous filters.
		 *
		 * @param WP_Post $post The post to use the editor for.
		 *
		 * @return bool Whether or not to use the block editor.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/use_block_editor_for_post/
		 *
		 * @since 1.0.0
		 */
		public function use_block_editor( bool $use_block_editor, WP_Post $post ) {
			if ( get_post_type( $post ) === static::POST_TYPE ) {
				return $use_block_editor;
			}

			return false;
		}

		/**
		 * Filters the shortcode post type's title to be slugified.
		 *
		 * Runs on the 'wp_insert_post_data' action.
		 *
		 * @param array $data An array of slashed, sanitized, and processed post data.
		 *
		 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
		 *
		 * @return string The slugified title.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
		 *
		 * @since 1.0.0
		 */
		public function slugifiy_shortcode_title( array $data, array $postarr ) {
			$post_id = $postarr['ID'];

			if ( get_post_type( $post_id ) === static::POST_TYPE ) {
				$data['post_title'] = sanitize_title( $data['post_title'] );
			}

			return $data;
		}

		/**
		 * Adds square brackets around the titles of custom shortcode posts.
		 *
		 * Run on the 'admin_head-edit.php' action.
		 *
		 * @since 1.0.0
		 */
		public function add_brackets_to_list_table() {
			add_filter(
				'the_title',
				function ( string $post_title, int $post_id ) {
					if ( get_post_type( $post_id ) === static::POST_TYPE ) {
						return "[$post_title]";
					}

					return $post_title;
				},
				10,
				2
			);
		}

		/**
		 * Register the meta box for the shortcodes post type.
		 *
		 * Run on the 'add_meta_boxes' action.
		 *
		 * @since 1.0.0
		 */
		public function add_metabox() {
			add_meta_box( 'wrd_shortcode_metabox', __( 'Shortcode', 'customshortcodes' ), array( $this, 'render_metabox' ), static::POST_TYPE, 'side' );
		}

		/**
		 * Renders the shortcode copy to clipboard metabox.
		 *
		 * @param WP_Post $post The post being edited.
		 *
		 * @since 1.0.0
		 */
		public function render_metabox( WP_Post $post ) {
			$title     = sanitize_title( get_the_title( $post ) );
			$shortcode = "[$title]";

			wp_enqueue_style( 'customshortcodes_styles', plugins_url( 'assets/styles/metabox.css', static::FILE ), array(), static::VERSION );
			wp_enqueue_script( 'customshortcodes_scripts', plugins_url( 'assets/scripts/metabox.js', static::FILE ), array(), static::VERSION, true );

			?>

			<div class="customshortcodes__metabox">
				<input data-customshortcodes-input class="customshortcodes__metabox__input" readonly value="<?php echo esc_attr( $shortcode ); ?>">

				<button data-customshortcodes-copy class="customshortcodes__metabox__btn" type="button" title="<?php esc_attr_e( 'Copy to Clipboard', 'customshortcodes' ); ?>">
					<span class="dashicons dashicons-clipboard"></span>
					<span class="screen-reader-text">
						<?php esc_html_e( 'Copy to Clipboard', 'customshortcodes' ); ?>
					</span>
				</button>
			</div>

			<?php
		}

		/**
		 * Adds the custom shortcodes button to the TinyMCE editor.
		 *
		 * @param array $mce_buttons First-row list of buttons.
		 *
		 * @return array The filtered array of buttons
		 *
		 * @since 1.0.0
		 */
		public function add_mce_editor_button( array $mce_buttons ) {
			array_push( $mce_buttons, 'customshortcodes' );
			return $mce_buttons;
		}

		/**
		 * Adds the custom shortcodes script to the TinyMCE editor.
		 *
		 * @param array $external_plugins An array of external TinyMCE plugins.
		 *
		 * @return array The filtered list of plugins.
		 *
		 * @since 1.0.0
		 */
		public function add_mce_editor_plugin( array $external_plugins ) {
			$external_plugins['customshortcodes'] = plugins_url( 'assets/scripts/tinymce.js', static::FILE );
			return $external_plugins;
		}

		/**
		 * Adds the editor styling for the TinyMCE custom shortcodes button.
		 *
		 * @since 1.0.0
		 */
		public function add_editor_style() {
			wp_enqueue_style( 'customshortcodes_editor_css', plugins_url( 'assets/styles/editor.css', __FILE__ ), array(), static::VERSION );
		}
	}

endif;

if ( ! function_exists( 'customshortcodes' ) ) :
	/**
	 * Returns the singleton instance of the Custom Shortcodes plugin manager.
	 *
	 * @since 1.0.0
	 */
	function customshortcodes() {
		global $customshortcodes;

		if ( ! isset( $customshortcodes ) ) {
			$customshortcodes = new Plugin_Manager();
		}

		return $customshortcodes;
	}
endif;

customshortcodes();
