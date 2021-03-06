<?php
/**
 * Theme options component.
 *
 * @package    Michelle
 * @copyright  WebMan Design, Oliver Juhas
 *
 * @since  1.0.0
 */

namespace WebManDesign\Michelle\Customize;

use WebManDesign\Michelle\Component_Interface;
use WebManDesign\Michelle\Setup\Media;
use WebManDesign\Michelle\Tool\Google_Fonts;
use WP_Customize_Manager;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Options implements Component_Interface {

	/**
	 * Initialization.
	 *
	 * @since  1.0.0
	 *
	 * @return  void
	 */
	public static function init() {

		// Processing

			// Actions

				add_action( 'customize_register', __CLASS__ . '::modify', 100 );

			// Filters

				add_filter( 'michelle/customize/options/get', __CLASS__ . '::set', 5 );

	} // /init

	/**
	 * Get theme options setup array.
	 *
	 * @since  1.0.0
	 *
	 * @return  array
	 */
	public static function get(): array {

		// Output

			/**
			 * Filters customizer theme options setup array.
			 *
			 * @since  1.0.0
			 *
			 * @param  array $options
			 */
			return (array) apply_filters( 'michelle/customize/options/get', array() );

	} // /get

	/**
	 * Modify native WordPress options and setup partial refresh pointers.
	 *
	 * @since  1.0.0
	 *
	 * @param  WP_Customize_Manager $wp_customize
	 *
	 * @return  void
	 */
	public static function modify( WP_Customize_Manager $wp_customize ) {

		// Processing

			// Set live preview for predefined controls.
			$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
			$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
			$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

			// Remove obsolete custom header controls (only, not setting!).
			$wp_customize->remove_control( 'header_textcolor' );

			// Option pointers only:

				// Footer content.
				$wp_customize->selective_refresh->add_partial( 'block_area_site_footer', array(
					'selector' => '.site-footer-section:first-child .site-footer-content',
				) );

	} // /modify

	/**
	 * Sets theme options array.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $options
	 *
	 * @return  array
	 */
	public static function set( array $options = array() ): array {

		// Variables

			// Predefined font families.
			$font_families = array_filter( array_merge(
				Google_Fonts::get_suggested_fonts(),
				array(
					'system',
					'sans-serif',
					'serif',
				)
			) );

			// Image sizes.
			$image_sizes = array_map(
				function( $args ) {
					$output  = $args['name'] . ': ';
					$output .= $args['width'] . 'px &times; ';
					$output .= ( $args['height'] ) ? ( $args['height'] . 'px' ) : ( esc_html_x( 'variable height', 'Variable image height.', 'michelle' ) );
					$output .= ', ';
					$output .= ( $args['crop'] ) ? ( esc_html__( 'cropped', 'michelle' ) ) : ( esc_html__( 'scaled', 'michelle' ) );
					return $output;
				},
				Media::get_image_sizes()
			);

			// Reusable blocks.
			$blocks_reusable = get_posts( array(
				'post_type'   => 'wp_block',
				'numberposts' => 100,
			) );
			$blocks = array(
				0 => esc_html__( 'Select a block', 'michelle' ),
			);
			foreach ( $blocks_reusable as $block ) {
				$blocks[ $block->ID ] = $block->post_title;
			}


		// Processing

			$options = array(

				/**
				 * Site identity: Logo image size
				 */
				'0' . 10 . 'logo' . 10 => array(
					'id'          => 'custom_logo_dimenstions_info',
					'section'     => 'title_tagline',
					'priority'    => 100,
					'type'        => 'html',
					'content'     => '<h3>' . esc_html__( 'Logo image', 'michelle' ) . '</h3>',
					'description' => esc_html__( 'Do not forget to set the logo max height.', 'michelle' ) . ' ' . esc_html__( 'Upload twice as big image to make your logo ready for high DPI screens.', 'michelle' ),
				),

					'0' . 10 . 'logo' . 20 => array(
						'section'           => 'title_tagline',
						'priority'          => 101,
						'type'              => 'number',
						'id'                => 'custom_logo_height',
						'label'             => esc_html__( 'Max logo image height (px)', 'michelle' ),
						'default'           => 60,
						'sanitize_callback' => 'absint',
						'input_attrs'       => array(
							'size'     => 5,
							'maxwidth' => 3,
							'min'      => 20,
							'max'      => 500,
						),
						'css_var'           => __NAMESPACE__ . '\Sanitize::css_px',
						'preview_js'        => array(
							'css' => array(
								':root' => array(
									array(
										'property' => '--[[id]]',
										'suffix'   => 'px',
									),
								),
							),
						),
					),

				/**
				 * Theme credits.
				 */
				'0' . 90 . 'placeholder' => array(
					'id'                   => 'placeholder',
					'type'                 => 'section',
					'create_section'       => '',
					'in_panel'             => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
					'in_panel-description' => '<h3>' . esc_html__( 'Theme Credits', 'michelle' ) . '</h3>'
						. '<p class="description">'
						. sprintf(
							/* translators: 1: linked theme name, 2: theme author name. */
							esc_html__( '%1$s is a WordPress theme developed by %2$s.', 'michelle' ),
							'<a href="' . esc_url( wp_get_theme( 'michelle' )->get( 'ThemeURI' ) ) . '"><strong>' . esc_html( wp_get_theme( 'michelle' )->get( 'Name' ) ) . '</strong></a>',
							'<strong>' . esc_html( wp_get_theme( 'michelle' )->get( 'Author' ) ) . '</strong>'
						)
						. '</p>'
						. '<p class="description">'
						. sprintf(
							/* translators: %s: theme author link. */
							esc_html__( 'You can obtain other professional WordPress themes at %s.', 'michelle' ),
							'<strong><a href="' . esc_url( wp_get_theme( 'michelle' )->get( 'AuthorURI' ) ) . '">' . esc_html( str_replace( 'http://', '', untrailingslashit( wp_get_theme( 'michelle' )->get( 'AuthorURI' ) ) ) ) . '</a></strong>'
						)
						. '</p>'
						. '<p class="description">'
						. esc_html__( 'Thank you for using a theme by WebMan Design!', 'michelle' )
						. '</p>',
				),

				/**
				 * Colors: Accent colors.
				 */
				100 . 'colors' => array(
					'id'             => 'colors_accents',
					'type'           => 'section',
					'create_section' => sprintf(
						/* translators: Customizer section title. %s = section name. */
						esc_html__( 'Colors: %s', 'michelle' ),
						esc_html_x( 'Accents', 'Customizer color section title', 'michelle' )
					),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					100 . 'colors' . 100 => array(
						'type'       => 'color',
						'id'         => 'color_accent',
						'label'      => esc_html__( 'Accent color', 'michelle' ),
						'default'    => '#aa5a00',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),

					/**
					 * Button colors.
					 */
					100 . 'colors' . 200 => array(
						'type'    => 'html',
						'content' => '<h3>' . esc_html__( 'Button', 'michelle' ) . '</h3>',
					),

						100 . 'colors' . 210 => array(
							'type'       => 'color',
							'id'         => 'color_button_background',
							'label'      => esc_html__( 'Background color', 'michelle' ),
							'default'    => '#aa5a00',
							'css_var'    => 'maybe_hash_hex_color',
							'preview_js' => array(
								'css' => array(
									':root' => array(
										'--[[id]]',
									),
								),
							),
						),
						100 . 'colors' . 220 => array(
							'type'        => 'color',
							'id'          => 'color_button_text',
							'label'       => esc_html__( 'Text color', 'michelle' ),
							'default'     => '#ffffff',
							'css_var'     => 'maybe_hash_hex_color',
							'preview_js'  => array(
								'css' => array(
									':root' => array(
										'--[[id]]',
									),
								),
							),
						),
						100 . 'colors' . 230 => array(
							'type'       => 'color',
							'id'         => 'color_button_hover_background',
							'label'      => esc_html__( 'Hover background color', 'michelle' ),
							'default'    => '#9a4a00',
							'css_var'    => 'maybe_hash_hex_color',
							'preview_js' => array(
								'css' => array(
									':root' => array(
										'--[[id]]',
									),
								),
							),
						),

				/**
				 * Colors: Header.
				 */
				110 . 'colors' => array(
					'id'             => 'colors_header',
					'type'           => 'section',
					'create_section' => sprintf(
						/* translators: Customizer section title. %s = section name. */
						esc_html__( 'Colors: %s', 'michelle' ),
						esc_html_x( 'Header', 'Customizer color section title', 'michelle' )
					),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					110 . 'colors' . 100 => array(
						'type'       => 'color',
						'id'         => 'color_header_background',
						'label'      => esc_html__( 'Background color', 'michelle' ),
						'default'    => '#ffffff',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),
					110 . 'colors' . 110 => array(
						'type'       => 'color',
						'id'         => 'color_header_text',
						'label'      => esc_html__( 'Text color', 'michelle' ),
						'default'    => '#6a6a60',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),
					110 . 'colors' . 120 => array(
						'type'       => 'color',
						'id'         => 'color_header_link',
						'label'      => esc_html__( 'Link color', 'michelle' ),
						'default'    => '#0a0a00',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),

				/**
				 * Colors: Content.
				 */
				130 . 'colors' => array(
					'id'             => 'colors_content',
					'type'           => 'section',
					'create_section' => sprintf(
						/* translators: Customizer section title. %s = section name. */
						esc_html__( 'Colors: %s', 'michelle' ),
						esc_html_x( 'Content', 'Customizer color section title', 'michelle' )
					),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					/**
					 * Content colors.
					 */
					130 . 'colors' . 100 => array(
						'type'    => 'html',
						'content' => '<h3>' . esc_html__( 'Content', 'michelle' ) . '</h3>',
					),

						130 . 'colors' . 110 => array(
							'type'       => 'color',
							'id'         => 'color_content_background',
							'label'      => esc_html__( 'Background color', 'michelle' ),
							'default'    => '#fbf7f0',
							'css_var'    => 'maybe_hash_hex_color',
							'preview_js' => array(
								'css' => array(
									':root' => array(
										'--[[id]]',
									),
								),
							),
						),
						130 . 'colors' . 120 => array(
							'type'       => 'color',
							'id'         => 'color_content_text',
							'label'      => esc_html__( 'Text color', 'michelle' ),
							'default'    => '#6a6a60',
							'css_var'    => 'maybe_hash_hex_color',
							'preview_js' => array(
								'css' => array(
									':root' => array(
										'--[[id]]',
									),
								),
							),
						),
						130 . 'colors' . 130 => array(
							'type'       => 'color',
							'id'         => 'color_content_headings',
							'label'      => esc_html__( 'Headings color', 'michelle' ),
							'default'    => '#0a0a00',
							'css_var'    => 'maybe_hash_hex_color',
							'preview_js' => array(
								'css' => array(
									':root' => array(
										'--[[id]]',
									),
								),
							),
						),

				/**
				 * Colors: Footer.
				 */
				140 . 'colors' => array(
					'id'             => 'colors_footer',
					'type'           => 'section',
					'create_section' => sprintf(
						/* translators: Customizer section title. %s = section name. */
						esc_html__( 'Colors: %s', 'michelle' ),
						esc_html_x( 'Footer', 'Customizer color section title', 'michelle' )
					),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					140 . 'colors' . 100 => array(
						'type'       => 'color',
						'id'         => 'color_footer_background',
						'label'      => esc_html__( 'Background color', 'michelle' ),
						'default'    => '#ffffff',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),
					140 . 'colors' . 110 => array(
						'type'       => 'color',
						'id'         => 'color_footer_text',
						'label'      => esc_html__( 'Text color', 'michelle' ),
						'default'    => '#6a6a60',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),
					140 . 'colors' . 120 => array(
						'type'       => 'color',
						'id'         => 'color_footer_headings',
						'label'      => esc_html__( 'Headings color', 'michelle' ),
						'default'    => '#ffffff',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),
					140 . 'colors' . 130 => array(
						'type'       => 'color',
						'id'         => 'color_footer_link',
						'label'      => esc_html__( 'Link color', 'michelle' ),
						'default'    => '#0a0a00',
						'css_var'    => 'maybe_hash_hex_color',
						'preview_js' => array(
							'css' => array(
								':root' => array(
									'--[[id]]',
								),
							),
						),
					),

				/**
				 * Layout.
				 */
				300 . 'layout' => array(
					'id'             => 'layout',
					'type'           => 'section',
					'create_section' => esc_html_x( 'Layout', 'Customizer section title.', 'michelle' ),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					/**
					 * Site layout.
					 */
					300 . 'layout' . 100 => array(
						'type'    => 'html',
						'content' => '<h3>' . esc_html_x( 'Site Container', 'A website container.', 'michelle' ) . '</h3>',
					),

						300 . 'layout' . 110 => array(
							'type'              => 'range',
							'id'                => 'layout_width_content',
							'label'             => esc_html__( 'Content width', 'michelle' ),
							'description'       =>
								esc_html__( 'Default value:', 'michelle' ) . ' ' . 1280
								. '<br>'
								. esc_html__( 'This width is applied on archive pages, wide-aligned blocks&hellip;', 'michelle' ),
							'default'           => 1280,
							'min'               => 880,
							'max'               => 1920,
							'step'              => 1,
							'suffix'            => 'px',
							'sanitize_callback' => 'absint',
							'css_var'           => __NAMESPACE__ . '\Sanitize::css_px',
							'preview_js'        => array(
								'css' => array(
									':root' => array(
										array(
											'property' => '--[[id]]',
											'suffix'   => 'px',
										),
									),
								),
							),
						),
						300 . 'layout' . 120 => array(
							'type'              => 'range',
							'id'                => 'layout_width_entry_content',
							'label'             => esc_html__( 'Entry content width', 'michelle' ),
							'description'       =>
								esc_html__( 'Default value:', 'michelle' ) . ' ' . 640
								. '<br>'
								. esc_html__( 'This width is applied on post and page actual content elements.', 'michelle' )
								. ' '
								. esc_html__( 'Set this cautiously for the best readability.', 'michelle' ),
							'default'           => 640,
							'min'               => 400,
							'max'               => 1000,
							'step'              => 1,
							'suffix'            => 'px',
							'sanitize_callback' => 'absint',
							'css_var'           => __NAMESPACE__ . '\Sanitize::css_px',
							'preview_js'        => array(
								'css' => array(
									':root' => array(
										array(
											'property' => '--[[id]]',
											'suffix'   => 'px',
										),
									),
								),
							),
						),

				/**
				 * Typography.
				 */
				900 . 'typography' => array(
					'id'             => 'typography',
					'type'           => 'section',
					'create_section' => esc_html_x( 'Typography', 'Customizer section title.', 'michelle' ),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					900 . 'typography' . 100 => array(
						'type'              => 'range',
						'id'                => 'typography_size_html',
						'label'             => esc_html__( 'Basic font size in px', 'michelle' ),
						'description'       => esc_html__( 'All other font sizes are calculated automatically from this basic font size.', 'michelle' ),
						'default'           => 18,
						'min'               => 12,
						'max'               => 24,
						'step'              => 1,
						'suffix'            => 'px',
						'sanitize_callback' => 'absint',
						'css_var'           => __NAMESPACE__ . '\Sanitize::css_px',
						'preview_js'        => array(
							'css' => array(
								':root' => array(
									array(
										'property' => '--[[id]]',
										'suffix'   => 'px',
									),
								),
							),
						),
					),

					900 . 'typography' . 110 => array(
						'type'    => 'html',
						'content' =>
							'<h3>'
							. esc_html__( 'Font families setup', 'michelle' )
							. '</h3>'
							. '<p class="description">'
							. sprintf(
								/* translators: %s: customizer option values. */
								esc_html__( 'Values of %s set web safe system font families.', 'michelle' ),
								'<code>system</code>, <code>serif</code>, <code>sans-serif</code>'
							)
							. '</p>'
							. '<p class="description">'
							. esc_html__( 'You can use any Google Fonts with this theme.', 'michelle' )
							. ' '
							. esc_html__( 'Just input the Google Fonts font family name into the fields below, choose language, and you are done!', 'michelle' )
							. '</p>',
					),

					900 . 'typography' . 120 => array(
						'type'              => 'text',
						'id'                => 'typography_font_global',
						'label'             => esc_html__( 'Global font', 'michelle' ),
						'description'       => esc_html__( 'Default value:', 'michelle' ) . ' <code>system</code>',
						'default'           => 'system',
						'datalist'          => $font_families,
						'sanitize_callback' => __NAMESPACE__ . '\Sanitize::fonts',
						'css_var'           => __NAMESPACE__ . '\Sanitize::css_fonts',
						'input_attrs'       => array(
							'placeholder' => 'sans-serif',
						),
					),
					900 . 'typography' . 130 => array(
						'type'              => 'text',
						'id'                => 'typography_font_headings',
						'label'             => esc_html__( 'Headings font', 'michelle' ),
						'description'       => esc_html__( 'Default value:', 'michelle' ) . ' <code>system</code>',
						'default'           => 'system',
						'datalist'          => $font_families,
						'sanitize_callback' => __NAMESPACE__ . '\Sanitize::fonts',
						'css_var'           => __NAMESPACE__ . '\Sanitize::css_fonts',
						'input_attrs'       => array(
							'placeholder' => 'sans-serif',
						),
					),
					900 . 'typography' . 140 => array(
						'type'              => 'text',
						'id'                => 'typography_font_site_title',
						'label'             => esc_html__( 'Site title font', 'michelle' ),
						'description'       => esc_html__( 'Default value:', 'michelle' ) . ' <code>system</code>',
						'default'           => 'system',
						'datalist'          => $font_families,
						'sanitize_callback' => __NAMESPACE__ . '\Sanitize::fonts',
						'css_var'           => __NAMESPACE__ . '\Sanitize::css_fonts',
						'input_attrs'       => array(
							'placeholder' => 'serif',
						),
					),

					900 . 'typography' . 150 => array(
						'type'        => 'checkbox',
						'id'          => 'typography_google_fonts',
						'label'       => esc_html__( 'Enable theme Google Fonts loading', 'michelle' ),
						'description' => esc_html__( 'In case you are loading fonts via plugin, disable this option.', 'michelle' ),
						'default'     => true,
					),
					900 . 'typography' . 160 => array(
						'type'        => 'multicheckbox',
						'id'          => 'typography_font_language',
						'label'       => esc_html__( 'Languages', 'michelle' ),
						'description' =>
							esc_html__( 'Not all Google Fonts support all languages.', 'michelle' )
							. ' '
							. esc_html__( 'Please check on Google Fonts website to make sure.', 'michelle' ),
						'default'     => array( 'latin' ),
						'choices' 		=> array(
							'latin'        => esc_html__( 'Latin', 'michelle' ),
							'latin-ext'    => esc_html__( 'Latin Extended', 'michelle' ),
							'cyrillic'     => esc_html__( 'Cyrillic', 'michelle' ),
							'cyrillic-ext' => esc_html__( 'Cyrillic Extended', 'michelle' ),
							'greek'        => esc_html__( 'Greek', 'michelle' ),
							'greek-ext'    => esc_html__( 'Greek Extended', 'michelle' ),
							'vietnamese'   => esc_html__( 'Vietnamese', 'michelle' ),
						),
						'active_callback' => __NAMESPACE__ . '\Options_Conditional::is_typography_google_fonts',
					),

				/**
				 * Others.
				 */
				950 . 'others' => array(
					'id'             => 'others',
					'type'           => 'section',
					'create_section' => esc_html_x( 'Others', 'Customizer section title.', 'michelle' ),
					'in_panel'       => esc_html_x( 'Theme Options', 'Customizer panel title.', 'michelle' ),
				),

					950 . 'others' . 100 => array(
						'type'        => 'checkbox',
						'id'          => 'admin_welcome_page',
						'label'       => esc_html__( 'Show "Welcome" page', 'michelle' ),
						'description' => esc_html__( 'Under "Appearance" WordPress dashboard menu.', 'michelle' ),
						'default'     => true,
						'preview_js'  => false, // This is to prevent customizer preview reload.
					),

					950 . 'others' . 110 => array(
						'type'        => 'checkbox',
						'id'          => 'navigation_mobile',
						'label'       => esc_html__( 'Enable mobile navigation', 'michelle' ),
						'description' => esc_html__( 'If your website navigation is very simple and you do not want to use the mobile navigation functionality, you can disable it here.', 'michelle' ),
						'default'     => true,
					),

					950 . 'others' . 120 => array(
						'type'    => 'html',
						'content' =>
							'<h3>'
							. esc_html__( 'Footer', 'michelle' )
							. '</h3>',
					),

						950 . 'others' . 130 => array(
							'type'              => 'select',
							'id'                => 'block_area_site_footer',
							'label'             => esc_html__( 'Footer content', 'michelle' ),
							'description'       => esc_html__( 'Edit or create your footer content in the Reusable Blocks manager.', 'michelle' ) . ' <a href="' . esc_url_raw( admin_url( 'edit.php?post_type=wp_block' ) ) . '" target="_blank"  rel="noopener noreferrer">' . esc_html__( 'Open Reusable Blocks manager in a new window now &rarr;', 'michelle' ) . '</a>',
							'default'           => 0,
							'choices'           => $blocks,
							'sanitize_callback' => 'absint',
						),

			);


		// Output

			return (array) $options;

	} // /set

}
