<?php

class Themify_Popup {

	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'i18n' ), 5 );
		add_action( 'init', array( $this, 'register_post_type' ) );
		if ( is_admin() ) {
			add_filter( 'themify_exclude_cpt_post_options', array( $this, 'exclude_post_options' ) );
			add_filter( 'themify_do_metaboxes', array( $this, 'meta_box' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		} else {
			add_filter( 'wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ) );
			add_filter( 'template_include', array( $this, 'template_include' ), 100 );
			add_action( 'template_redirect', array( $this, 'hooks' ) );
			add_shortcode( 'tf_popup', array( $this, 'shortcode' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 13 );
		}
		add_filter( 'themify_builder_layout_providers', array( $this, 'themify_builder_layout_providers' ) );

		include THEMIFY_POPUP_DIR . 'includes/tinymce.php';
		Themify_Popup_TinyMCE::init();
	}

	public function hooks() {
		if( ! is_singular( 'themify_popup' ) ) {
			add_action( 'wp_footer', array( $this, 'render' ), 1 );
		} else {
			if( ! current_user_can( 'manage_options' ) ) {
				wp_redirect( home_url() );
				exit;
			}
		}
	}

	public function i18n() {
		load_plugin_textdomain( 'themify-popup', false, THEMIFY_POPUP_DIR . 'languages/' );
	}

	function register_post_type() {
		$labels = array(
			'name'               => _x( 'Popups', 'post type general name', 'themify-popup' ),
			'singular_name'      => _x( 'Popup', 'post type singular name', 'themify-popup' ),
			'menu_name'          => _x( 'Themify Popups', 'admin menu', 'themify-popup' ),
			'name_admin_bar'     => _x( 'Popup', 'add new on admin bar', 'themify-popup' ),
			'add_new'            => _x( 'Add New', 'book', 'themify-popup' ),
			'add_new_item'       => __( 'Add New Popup', 'themify-popup' ),
			'new_item'           => __( 'New Popup', 'themify-popup' ),
			'edit_item'          => __( 'Edit Popup', 'themify-popup' ),
			'all_items'          => __( 'Manage Popups', 'themify-popup' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'themify_popup' ),
			'capability_type'    => 'post',
			'menu_position'      => 80, /* below Settings */
			'has_archive'        => false,
			'supports'           => array( 'title', 'editor' ),
		);

		register_post_type( 'themify_popup', $args );
	}

	function meta_box( $panels ) {
		$options = include( $this->get_view_path( 'config.php' ) );
		$panels[] = array(
			'name' => __( 'Popup Settings', 'themify' ),
			'id' => 'themify-popup',
			'options' => $options,
			'pages' => 'themify_popup'
		);
		$panels[] = array(
			'name' => __( 'Custom CSS', 'themify-popup' ),
			'id' => 'themify-popup-css',
			'options' => array(
				array(
					'name' => 'custom_css',
					'title' => __( 'Custom CSS', 'themify-popup' ),
					'type' => 'textarea',
					'size' => 55,
					'rows' => 25,
					'description' => __( 'You can use <code>%POPUP%</code> to reference this popup.', 'themify-popup' ),
				),
			),
			'pages' => 'themify_popup'
		);

		return $panels;
	}

	function is_admin_screen() {
		return get_current_screen()->post_type === 'themify_popup';
	}

	public function admin_enqueue() {
		if( ! $this->is_admin_screen() )
			return;

		wp_enqueue_script( 'themify-popup', THEMIFY_POPUP_URI . 'assets/admin.js', array( 'jquery' ), THEMIFY_POPUP_VERSION, true );
	}

	function get_popups() {
		$datenow = date_i18n('Y-m-d H:i:s');
		$args = array(
			'post_type' => 'themify_popup',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'popup_start_at',
					'value' => $datenow,
					'compare' => '<=',
					'type' => 'datetime'
				),
				array(
					'key' => 'popup_end_at',
					'value' => $datenow,
					'type' => 'datetime',
					'compare' => '>='
				)
			)
		);
		if (class_exists('SitePress')) {
			/*
			* For some unknown reason WPML 4.0.2 will not render posts for other languages if suppress_filters or posts_per_page value is not a string type.
			*/
			$args['suppress_filters'] = '0';
		}
		$the_query = new WP_Query();
		$posts = $the_query->query( apply_filters( 'themify_popup_query_args', $args ) );

		return $posts;
	}

	public function get_view_path( $name ) {
		if( locate_template( 'themify-popup/' . $name ) ) {
			return locate_template( 'themify-popup/' . $name );
		} elseif( file_exists( THEMIFY_POPUP_DIR . 'views/' . $name ) ) {
			return THEMIFY_POPUP_DIR . 'views/' . $name;
		}

		return false;
	}

	public function load_view( $name, $data = array() ) {
		extract( $data );
		if( $view = $this->get_view_path( $name ) ) {
			ob_start();
			include( $view );
			return ob_get_clean();
		}

		return '';
	}

	function render() {
		global $ThemifyBuilder;

		/* disable popups on these post types */
		if ( is_singular( array(
			'tbp_template', // Themify Builder Pro: Template
			'tbuilder_layout_part', // Themify Builder: Layout Part
			'tglobal_style', // Themify themes: Global Style
		) ) ) {
			return;
		}

		/* disable popups when Themify Builder editor is on */
		if ( method_exists( 'Themify_Builder_Model', 'is_front_builder_activate' ) && Themify_Builder_Model::is_front_builder_activate() ) {
			return;
		}

		do_action( 'themify_popup_before_render' );

		$popups = $this->get_popups();

		echo $this->load_view( 'render.php', array(
			'popups' => $popups,
		) );

		/* add the page view counter cookie? */
		foreach ( $popups as $popup ) {
			$popup_page_view = get_post_meta( $popup->ID, 'popup_page_view', true );
			if ( ! empty( $popup_page_view ) ) {
				wp_localize_script( 'themify-popup', 'themifyPopupCountViews', '1' );
				break;
			}
		}

		do_action( 'themify_popup_after_render' );
	}

	/**
	 * Displays the contents of the popup
	 *
	 * Themify Builder content is manually added, this is to avoid
	 * issues with WooCommerce.
	 *
	 * @return void
	 */
	public function the_content() {
		global $ThemifyBuilder;

		if ( isset( $ThemifyBuilder ) ) {
			add_filter( 'themify_builder_display', '__return_false' ); // disable default Builder output
			$ThemifyBuilder->in_the_loop = true;

			/* disable Row Width options: rows inside the popup cannot be displayed as fullwidth */
			add_filter( 'themify_builder_row_classes', array( $this, 'themify_builder_row_classes' ), 10, 3 );
		}

		/**
		 * do the_content() but return the result instead */
		$content = get_the_content();
		/** This filter is documented in wp-includes/post-template.php */
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		if ( isset( $ThemifyBuilder ) ) {
			remove_filter( 'themify_builder_display', '__return_false' );
			$content = $ThemifyBuilder->get_builder_output( get_the_id(), $content );
			$ThemifyBuilder->in_the_loop = false;
			remove_filter( 'themify_builder_row_classes', array( $this, 'themify_builder_row_classes' ), 10, 3 );
		}

		echo $content;
	}

	function themify_builder_row_classes( $row_classes, $row, $builder_id ) {
		return str_replace( array( 'fullwidth_row_container', 'fullwidth' ), '', $row_classes );
	}

	public function enqueue() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'themify-popup', THEMIFY_POPUP_URI . "assets/scripts{$min}.js", array( 'jquery' ), THEMIFY_POPUP_VERSION, true );
		wp_localize_script( 'themify-popup', 'themifyPopup', array(
			'assets' => THEMIFY_POPUP_URI . 'assets',
		) );
		wp_register_style( 'themify-builder-animate', THEMIFY_POPUP_URI . "assets/animate.min.css" );
		wp_register_style( 'magnific', THEMIFY_POPUP_URI . "assets/lightbox{$min}.css" );
		wp_register_style( 'themify-popup', THEMIFY_POPUP_URI . "assets/styles{$min}.css", array( 'themify-builder-animate', 'magnific' ), THEMIFY_POPUP_VERSION );
	}

	public function get_element_attributes( $props ) {
		$out = '';
		foreach( $props as $atts => $val ) { 
			if( ! in_array( $atts, array( 'id', 'class', 'style' ), true ) && substr( $atts, 0, 5 ) !== 'data-' ) {
				$atts = 'data-' . $atts;
			}
			$out .= ' '. $atts . '="' . esc_attr( $val ) . '"'; 
		}
		return $out;
	}

	/**
	 * Fix URLs in menu items pointing to an inline popup
	 */
	function wp_nav_menu_objects( $items ) {
		foreach( $items as $item ) {
			if( $item->type === 'post_type' && $item->object === 'themify_popup' ) {
				$item->url = '#themify-popup-' . $item->object_id;
				$item->classes[] = 'tf-popup';
			}
		}

		return $items;
	}

	function shortcode( $atts, $content = null ) {
		if( is_singular( 'themify_popup' ) ) {
			return;
		}
		extract( shortcode_atts( array(
			'color' => '',
			'size' 	=> '',
			'style'	=> '',
			'link' 	=> 0,
			'target'=> '',
			'text'	=> ''
		), $atts, 'tf_popup' ) );

		// WPML compatibility
		$link = apply_filters( 'wpml_object_id', $link, 'post', true );
		if( ! $post = get_post( $link ) ) {
			return;
		}

		if ( $color ) {
			$color = "background-color: $color;";
		}
		if ( $text ) {
			$text = "color: $text;";	
		}
		$html = '<a href="#themify-popup-' . $link . '" class="tf_popup '. esc_attr( $style.' '.$size ) . '"';
		if ( $color || $text ) {
			$html.=' style="'.esc_attr( $color.$text ).'"';
		}
		if ( $target ) {
			$html.=' target="'.esc_attr( $target ).'"';
		}
		$html.= '>' . do_shortcode( $content ) . '</a>';

		return $html;
	}

	/**
	 * Use custom template file on popup single pages
	 *
	 * @since 1.0
	 */
	function template_include( $template ) {
		if( is_singular( 'themify_popup' ) ) {
			$template = $this->get_view_path( 'single-popup.php' );
		}

		return $template;
	}

	/**
	 * Checks whether a popup should be displayed or not
	 *
	 * @since 1.0
	 * @return bool
	 */
	function is_popup_visible( $id ) {
		// popup is disabled for mobile
		$visible = !(themify_popup_check( 'popup_mobile_disable' ) && wp_is_mobile());

		// has user seen this popup before?
		/**
		 * Migration routine: previsouly used "show_once" checkbox is converted to "limit_count" (number).
		 */
		if ( themify_popup_check( 'popup_show_once' ) ) {
			delete_post_meta( $id, 'popup_show_once' );
			add_post_meta( $id, 'popup_limit_count', 1 );
		}
		if ($visible===true && isset( $_COOKIE["themify-popup-{$id}"] ) && themify_popup_check( 'popup_limit_count' ) &&  $_COOKIE["themify-popup-{$id}"] >= themify_popup_get( 'popup_limit_count' ) ) {
			$visible = false;
		}

		// check if popup has a page view limit
		if($visible===true && $view_count = themify_popup_get( 'popup_page_view', 0 ) ) {
			if( ! ( isset( $_COOKIE['themify_popup_page_view'] ) && $_COOKIE['themify_popup_page_view'] >= $view_count ) ) {
				$visible = false;
			}
		}

		if($visible===true && themify_popup_get( 'popup_show_on_toggle', 'all-pages' ) === 'specific-pages' && themify_popup_check( 'popup_show' ) && !themify_verify_assignments( themify_popup_get( 'popup_show' ) ) ) {
			$visible = false;
		}
		if($visible===true){
		    $showTo = themify_popup_get( 'popup_show_to' );
		    $visible = !(($showTo==='guest' && is_user_logged_in()) || ($showTo === 'user' && ! is_user_logged_in()));
		}

		return $visible;
	}

	/**
	 * Add sample layouts bundled with Popup plugin to Themify Builder
	 *
	 * @since 1.0.0
	 */
	function themify_builder_layout_providers( $providers ) {
		include THEMIFY_POPUP_DIR . 'includes/themify-builder-popup-layout-provider.php';
		$providers[] = 'Themify_Builder_Layouts_Provider_Themify_Popup';
		return $providers;
	}

	public function exclude_post_options($types){
	    $types[]='themify_popup';
	    return $types;
    }
}

/**
 * Check if option is set for the current popup in the loop
 *
 * @since 1.0
 */
function themify_popup_check( $var ) {
	global $post;
	return is_object( $post ) && get_post_meta( $post->ID, $var, true );
}

/**
 * Get an option for the current popup in the loop
 *
 * @since 1.0
 */
function themify_popup_get( $var, $default = null ) {
	global $post;
	$postmeta = is_object( $post ) ?get_post_meta( $post->ID, $var, true ):'';
	return $postmeta !== '' ?$postmeta:$default;
}

/**
 * Return the custom CSS codes for current popup (in the loop)
 *
 * @return string
 */
function themify_popup_get_custom_css() {
	return str_replace( '%POPUP%', '#themify-popup-' . get_the_id(), themify_popup_get( 'custom_css' ) );
}
