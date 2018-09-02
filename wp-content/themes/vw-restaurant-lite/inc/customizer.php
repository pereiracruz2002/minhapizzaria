<?php
/**
 * VW Restaurant Lite Theme Customizer
 *
 * @package VW Restaurant Lite
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function vw_restaurant_lite_customize_register( $wp_customize ) {	

	//add home page setting pannel
	$wp_customize->add_panel( 'vw_restaurant_lite_panel_id', array(
	    'priority' => 10,
	    'capability' => 'edit_theme_options',
	    'theme_supports' => '',
	    'title' => __( 'VW Settings', 'vw-restaurant-lite' ),
	    'description' => __( 'Description of what this panel does.', 'vw-restaurant-lite' ),
	) );

	//theme Layouts
	$wp_customize->add_section( 'vw_restaurant_lite_left_right', array(
    	'title'      => __( 'Theme Layout Settings', 'vw-restaurant-lite' ),
		'priority'   => 30,
		'panel' => 'vw_restaurant_lite_panel_id'
	) );

	// Add Settings and Controls for Layout
	$wp_customize->add_setting('vw_restaurant_lite_theme_options',array(
	        'default' => '',
	        'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	) );
	$wp_customize->add_control('vw_restaurant_lite_theme_options',
	    array(
	        'type' => 'radio',
	        'label' => __('Do you want this section','vw-restaurant-lite'),
	        'section' => 'vw_restaurant_lite_left_right',
	        'choices' => array(
	            'Left Sidebar' => __('Left Sidebar','vw-restaurant-lite'),
	            'Right Sidebar' => __('Right Sidebar','vw-restaurant-lite'),
	            'One Column' => __('One Column','vw-restaurant-lite'),
	            'Three Columns' => __('Three Columns','vw-restaurant-lite'),
	            'Four Columns' => __('Four Columns','vw-restaurant-lite'),
	            'Grid Layout' => __('Grid Layout','vw-restaurant-lite')
	        ),
	    )
    );

    $font_array = array(
        '' => __( 'No Fonts', 'vw-restaurant-lite' ),
        'Abril Fatface' => __( 'Abril Fatface', 'vw-restaurant-lite' ),
        'Acme' => __( 'Acme', 'vw-restaurant-lite' ),
        'Anton' => __( 'Anton', 'vw-restaurant-lite' ),
        'Architects Daughter' => __( 'Architects Daughter', 'vw-restaurant-lite' ),
        'Arimo' => __( 'Arimo', 'vw-restaurant-lite' ),
        'Arsenal' => __( 'Arsenal', 'vw-restaurant-lite' ),
        'Arvo' => __( 'Arvo', 'vw-restaurant-lite' ),
        'Alegreya' => __( 'Alegreya', 'vw-restaurant-lite' ),
        'Alfa Slab One' => __( 'Alfa Slab One', 'vw-restaurant-lite' ),
        'Averia Serif Libre' => __( 'Averia Serif Libre', 'vw-restaurant-lite' ),
        'Bangers' => __( 'Bangers', 'vw-restaurant-lite' ),
        'Boogaloo' => __( 'Boogaloo', 'vw-restaurant-lite' ),
        'Bad Script' => __( 'Bad Script', 'vw-restaurant-lite' ),
        'Bitter' => __( 'Bitter', 'vw-restaurant-lite' ),
        'Bree Serif' => __( 'Bree Serif', 'vw-restaurant-lite' ),
        'BenchNine' => __( 'BenchNine', 'vw-restaurant-lite' ),
        'Cabin' => __( 'Cabin', 'vw-restaurant-lite' ),
        'Cardo' => __( 'Cardo', 'vw-restaurant-lite' ),
        'Courgette' => __( 'Courgette', 'vw-restaurant-lite' ),
        'Cherry Swash' => __( 'Cherry Swash', 'vw-restaurant-lite' ),
        'Cormorant Garamond' => __( 'Cormorant Garamond', 'vw-restaurant-lite' ),
        'Crimson Text' => __( 'Crimson Text', 'vw-restaurant-lite' ),
        'Cuprum' => __( 'Cuprum', 'vw-restaurant-lite' ),
        'Cookie' => __( 'Cookie', 'vw-restaurant-lite' ),
        'Chewy' => __( 'Chewy', 'vw-restaurant-lite' ),
        'Days One' => __( 'Days One', 'vw-restaurant-lite' ),
        'Dosis' => __( 'Dosis', 'vw-restaurant-lite' ),
        'Droid Sans' => __( 'Droid Sans', 'vw-restaurant-lite' ),
        'Economica' => __( 'Economica', 'vw-restaurant-lite' ),
        'Fredoka One' => __( 'Fredoka One', 'vw-restaurant-lite' ),
        'Fjalla One' => __( 'Fjalla One', 'vw-restaurant-lite' ),
        'Francois One' => __( 'Francois One', 'vw-restaurant-lite' ),
        'Frank Ruhl Libre' => __( 'Frank Ruhl Libre', 'vw-restaurant-lite' ),
        'Gloria Hallelujah' => __( 'Gloria Hallelujah', 'vw-restaurant-lite' ),
        'Great Vibes' => __( 'Great Vibes', 'vw-restaurant-lite' ),
        'Handlee' => __( 'Handlee', 'vw-restaurant-lite' ),
        'Hammersmith One' => __( 'Hammersmith One', 'vw-restaurant-lite' ),
        'Inconsolata' => __( 'Inconsolata', 'vw-restaurant-lite' ),
        'Indie Flower' => __( 'Indie Flower', 'vw-restaurant-lite' ),
        'IM Fell English SC' => __( 'IM Fell English SC', 'vw-restaurant-lite' ),
        'Julius Sans One' => __( 'Julius Sans One', 'vw-restaurant-lite' ),
        'Josefin Slab' => __( 'Josefin Slab', 'vw-restaurant-lite' ),
        'Josefin Sans' => __( 'Josefin Sans', 'vw-restaurant-lite' ),
        'Kanit' => __( 'Kanit', 'vw-restaurant-lite' ),
        'Lobster' => __( 'Lobster', 'vw-restaurant-lite' ),
        'Lato' => __( 'Lato', 'vw-restaurant-lite' ),
        'Lora' => __( 'Lora', 'vw-restaurant-lite' ),
        'Libre Baskerville' => __( 'Libre Baskerville', 'vw-restaurant-lite' ),
        'Lobster Two' => __( 'Lobster Two', 'vw-restaurant-lite' ),
        'Merriweather' => __( 'Merriweather', 'vw-restaurant-lite' ),
        'Monda' => __( 'Monda', 'vw-restaurant-lite' ),
        'Montserrat' => __( 'Montserrat', 'vw-restaurant-lite' ),
        'Muli' => __( 'Muli', 'vw-restaurant-lite' ),
        'Marck Script' => __( 'Marck Script', 'vw-restaurant-lite' ),
        'Noto Serif' => __( 'Noto Serif', 'vw-restaurant-lite' ),
        'Open Sans' => __( 'Open Sans', 'vw-restaurant-lite' ),
        'Overpass' => __( 'Overpass', 'vw-restaurant-lite' ),
        'Overpass Mono' => __( 'Overpass Mono', 'vw-restaurant-lite' ),
        'Oxygen' => __( 'Oxygen', 'vw-restaurant-lite' ),
        'Orbitron' => __( 'Orbitron', 'vw-restaurant-lite' ),
        'Patua One' => __( 'Patua One', 'vw-restaurant-lite' ),
        'Pacifico' => __( 'Pacifico', 'vw-restaurant-lite' ),
        'Padauk' => __( 'Padauk', 'vw-restaurant-lite' ),
        'Playball' => __( 'Playball', 'vw-restaurant-lite' ),
        'Playfair Display' => __( 'Playfair Display', 'vw-restaurant-lite' ),
        'PT Sans' => __( 'PT Sans', 'vw-restaurant-lite' ),
        'Philosopher' => __( 'Philosopher', 'vw-restaurant-lite' ),
        'Permanent Marker' => __( 'Permanent Marker', 'vw-restaurant-lite' ),
        'Poiret One' => __( 'Poiret One', 'vw-restaurant-lite' ),
        'Quicksand' => __( 'Quicksand', 'vw-restaurant-lite' ),
        'Quattrocento Sans' => __( 'Quattrocento Sans', 'vw-restaurant-lite' ),
        'Raleway' => __( 'Raleway', 'vw-restaurant-lite' ),
        'Rubik' => __( 'Rubik', 'vw-restaurant-lite' ),
        'Rokkitt' => __( 'Rokkitt', 'vw-restaurant-lite' ),
        'Russo One' => __( 'Russo One', 'vw-restaurant-lite' ),
        'Righteous' => __( 'Righteous', 'vw-restaurant-lite' ),
        'Slabo' => __( 'Slabo', 'vw-restaurant-lite' ),
        'Source Sans Pro' => __( 'Source Sans Pro', 'vw-restaurant-lite' ),
        'Shadows Into Light Two' => __( 'Shadows Into Light Two', 'vw-restaurant-lite'),
        'Shadows Into Light' => __( 'Shadows Into Light', 'vw-restaurant-lite' ),
        'Sacramento' => __( 'Sacramento', 'vw-restaurant-lite' ),
        'Shrikhand' => __( 'Shrikhand', 'vw-restaurant-lite' ),
        'Tangerine' => __( 'Tangerine', 'vw-restaurant-lite' ),
        'Ubuntu' => __( 'Ubuntu', 'vw-restaurant-lite' ),
        'VT323' => __( 'VT323', 'vw-restaurant-lite' ),
        'Varela Round' => __( 'Varela Round', 'vw-restaurant-lite' ),
        'Vampiro One' => __( 'Vampiro One', 'vw-restaurant-lite' ),
        'Vollkorn' => __( 'Vollkorn', 'vw-restaurant-lite' ),
        'Volkhov' => __( 'Volkhov', 'vw-restaurant-lite' ),
        'Yanone Kaffeesatz' => __( 'Yanone Kaffeesatz', 'vw-restaurant-lite' )
    );

	//Typography
	$wp_customize->add_section( 'vw_restaurant_lite_typography', array(
    	'title'      => __( 'Typography', 'vw-restaurant-lite' ),
		'priority'   => 30,
		'panel' => 'vw_restaurant_lite_panel_id'
	) );
	
	// This is Paragraph Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_paragraph_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_paragraph_color', array(
		'label' => __('Paragraph Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_paragraph_color',
	)));

	//This is Paragraph FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_paragraph_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_paragraph_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'Paragraph Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	$wp_customize->add_setting('vw_restaurant_lite_paragraph_font_size',array(
		'default'	=> '12px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_paragraph_font_size',array(
		'label'	=> __('Paragraph Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_paragraph_font_size',
		'type'	=> 'text'
	));

	// This is "a" Tag Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_atag_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_atag_color', array(
		'label' => __('"a" Tag Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_atag_color',
	)));

	//This is "a" Tag FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_atag_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_atag_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( '"a" Tag Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	// This is "a" Tag Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_li_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_li_color', array(
		'label' => __('"li" Tag Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_li_color',
	)));

	//This is "li" Tag FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_li_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_li_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( '"li" Tag Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	// This is H1 Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_h1_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_h1_color', array(
		'label' => __('H1 Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_h1_color',
	)));

	//This is H1 FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_h1_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_h1_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'H1 Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	//This is H1 FontSize setting
	$wp_customize->add_setting('vw_restaurant_lite_h1_font_size',array(
		'default'	=> '50px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_h1_font_size',array(
		'label'	=> __('H1 Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_h1_font_size',
		'type'	=> 'text'
	));

	// This is H2 Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_h2_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_h2_color', array(
		'label' => __('h2 Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_h2_color',
	)));

	//This is H2 FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_h2_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_h2_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'h2 Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	//This is H2 FontSize setting
	$wp_customize->add_setting('vw_restaurant_lite_h2_font_size',array(
		'default'	=> '45px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_h2_font_size',array(
		'label'	=> __('h2 Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_h2_font_size',
		'type'	=> 'text'
	));

	// This is H3 Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_h3_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_h3_color', array(
		'label' => __('h3 Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_h3_color',
	)));

	//This is H3 FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_h3_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_h3_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'h3 Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	//This is H3 FontSize setting
	$wp_customize->add_setting('vw_restaurant_lite_h3_font_size',array(
		'default'	=> '36px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_h3_font_size',array(
		'label'	=> __('h3 Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_h3_font_size',
		'type'	=> 'text'
	));

	// This is H4 Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_h4_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_h4_color', array(
		'label' => __('h4 Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_h4_color',
	)));

	//This is H4 FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_h4_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_h4_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'h4 Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	//This is H4 FontSize setting
	$wp_customize->add_setting('vw_restaurant_lite_h4_font_size',array(
		'default'	=> '30px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_h4_font_size',array(
		'label'	=> __('h4 Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_h4_font_size',
		'type'	=> 'text'
	));

	// This is H5 Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_h5_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_h5_color', array(
		'label' => __('h5 Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_h5_color',
	)));

	//This is H5 FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_h5_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_h5_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'h5 Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	//This is H5 FontSize setting
	$wp_customize->add_setting('vw_restaurant_lite_h5_font_size',array(
		'default'	=> '25px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_h5_font_size',array(
		'label'	=> __('h5 Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_h5_font_size',
		'type'	=> 'text'
	));

	// This is H6 Color picker setting
	$wp_customize->add_setting( 'vw_restaurant_lite_h6_color', array(
		'default' => '',
		'sanitize_callback'	=> 'sanitize_hex_color'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vw_restaurant_lite_h6_color', array(
		'label' => __('h6 Color', 'vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_typography',
		'settings' => 'vw_restaurant_lite_h6_color',
	)));

	//This is H6 FontFamily picker setting
	$wp_customize->add_setting('vw_restaurant_lite_h6_font_family',array(
	  'default' => '',
	  'capability' => 'edit_theme_options',
	  'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices'
	));
	$wp_customize->add_control(
	    'vw_restaurant_lite_h6_font_family', array(
	    'section'  => 'vw_restaurant_lite_typography',
	    'label'    => __( 'h6 Fonts','vw-restaurant-lite'),
	    'type'     => 'select',
	    'choices'  => $font_array,
	));

	//This is H6 FontSize setting
	$wp_customize->add_setting('vw_restaurant_lite_h6_font_size',array(
		'default'	=> '18px',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_h6_font_size',array(
		'label'	=> __('h6 Font Size','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_typography',
		'setting'	=> 'vw_restaurant_lite_h6_font_size',
		'type'	=> 'text'
	));
	
	//home page slider
	$wp_customize->add_section( 'vw_restaurant_lite_slidersettings' , array(
    	'title'      => __( 'Slider Settings', 'vw-restaurant-lite' ),
		'priority'   => 30,
		'panel' => 'vw_restaurant_lite_panel_id'
	) );

	for ( $count = 1; $count <= 4; $count++ ) {

		// Add color scheme setting and control.
		$wp_customize->add_setting( 'vw_restaurant_lite_slidersettings-page-' . $count, array(
				'default'           => '',
				'sanitize_callback' => 'absint'
		) );

		$wp_customize->add_control( 'vw_restaurant_lite_slidersettings-page-' . $count, array(
			'label'    => __( 'Select Slide Image Page', 'vw-restaurant-lite' ),
			'section'  => 'vw_restaurant_lite_slidersettings',
			'type'     => 'dropdown-pages'
		) );

	}

	//Topbar section
	$wp_customize->add_section('vw_restaurant_lite_topbar_icon',array(
		'title'	=> __('Topbar Section','vw-restaurant-lite'),
		'description'	=> __('Add Top Header Content here','vw-restaurant-lite'),
		'priority'	=> null,
		'panel' => 'vw_restaurant_lite_panel_id',
	));

	$wp_customize->add_setting('vw_restaurant_lite_contact',array(
		'default'	=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_contact',array(
		'label'	=> __('Add Phone Number','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_icon',
		'setting'	=> 'vw_restaurant_lite_contact',
		'type'		=> 'text'
	));

	$wp_customize->add_setting('vw_restaurant_lite_email',array(
		'default'	=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_email',array(
		'label'	=> __('Add Email','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_icon',
		'setting'	=> 'vw_restaurant_lite_email',
		'type'		=> 'text'
	));

	//Social Icons(topbar)
	$wp_customize->add_section('vw_restaurant_lite_topbar_header',array(
		'title'	=> __('Social Icon Section','vw-restaurant-lite'),
		'description'	=> __('Add Header Content here','vw-restaurant-lite'),
		'priority'	=> null,
		'panel' => 'vw_restaurant_lite_panel_id',
	));

	$wp_customize->add_setting('vw_restaurant_lite_headertwitter',array(
		'default'	=> '',
		'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_headertwitter',array(
		'label'	=> __('Add twitter link','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_header',
		'setting'	=> 'vw_restaurant_lite_headertwitter',
		'type'		=> 'url'
	));

	$wp_customize->add_setting('vw_restaurant_lite_headerpinterest',array(
		'default'	=> '',
		'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_headerpinterest',array(
		'label'	=> __('Add pinterest link','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_header',
		'setting'	=> 'vw_restaurant_lite_headerpinterest',
		'type'	=> 'url'
	));

	$wp_customize->add_setting('vw_restaurant_lite_headerfacebook',array(
		'default'	=> '',
		'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_headerfacebook',array(
		'label'	=> __('Add facebook link','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_header',
		'setting'	=> 'vw_restaurant_lite_headerfacebook',
		'type'	=> 'url'
	));

	$wp_customize->add_setting('vw_restaurant_lite_headeryoutube',array(
		'default'	=> '',
		'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_headeryoutube',array(
		'label'	=> __('Add Youtube link','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_header',
		'setting'	=> 'vw_restaurant_lite_headeryoutube',
		'type'	=> 'url'
	));

	$wp_customize->add_setting('vw_restaurant_lite_headerinstagram',array(
		'default'	=> '',
		'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_headerinstagram',array(
		'label'	=> __('Add Instagram link','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_topbar_header',
		'setting'	=> 'vw_restaurant_lite_headerinstagram',
		'type'	=> 'url'
	));
	
	//we Belive
	$wp_customize->add_section('vw_restaurant_lite_belive',array(
		'title'	=> __('We Belive Section','vw-restaurant-lite'),
		'description'	=> __('Add We Belive sections below.','vw-restaurant-lite'),
		'panel' => 'vw_restaurant_lite_panel_id',
	));

	$post_list = get_posts();
	$i = 0;
	foreach($post_list as $post){
		$posts[$post->post_title] = $post->post_title;
	}

	$wp_customize->add_setting('vw_restaurant_lite_belive_post_setting',array(
		'sanitize_callback' => 'vw_restaurant_lite_sanitize_choices',
	));
	$wp_customize->add_control('vw_restaurant_lite_belive_post_setting',array(
		'type'    => 'select',
		'choices' => $posts,
		'label' => __('Select post','vw-restaurant-lite'),
		'section' => 'vw_restaurant_lite_belive',
	));

	//footer text
	$wp_customize->add_section('vw_restaurant_lite_footer_section',array(
		'title'	=> __('Footer Text','vw-restaurant-lite'),
		'description'	=> __('Add some text for footer like copyright etc.','vw-restaurant-lite'),
		'panel' => 'vw_restaurant_lite_panel_id'
	));
	
	$wp_customize->add_setting('vw_restaurant_lite_footer_copy',array(
		'default'	=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('vw_restaurant_lite_footer_copy',array(
		'label'	=> __('Copyright Text','vw-restaurant-lite'),
		'section'	=> 'vw_restaurant_lite_footer_section',
		'type'		=> 'text'
	));
	
}
add_action( 'customize_register', 'vw_restaurant_lite_customize_register' );	

load_template( trailingslashit( get_template_directory() ) . '/inc/logo-resizer.php' );

/**
 * Singleton class for handling the theme's customizer integration.
 *
 * @since  1.0.0
 * @access public
 */
final class VW_Restaurant_Lite_Customize {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Sets up initial actions.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Register panels, sections, settings, controls, and partials.
		add_action( 'customize_register', array( $this, 'sections' ) );

		// Register scripts and styles for the controls.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ), 0 );
	}

	/**
	 * Sets up the customizer sections.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  object  $manager
	 * @return void
	 */
	public function sections( $manager ) {

		// Load custom sections.
		load_template( trailingslashit( get_template_directory() ) . '/inc/section-pro.php' );

		// Register custom section types.
		$manager->register_section_type( 'VW_Restaurant_Lite_Customize_Section_Pro' );

		// Register sections.
		$manager->add_section(
			new VW_Restaurant_Lite_Customize_Section_Pro(
				$manager,
				'example_1',
				array(
					'priority'   => 9,
					'title'    => esc_html__( 'VW Restaurant Pro', 'vw-restaurant-lite' ),
					'pro_text' => esc_html__( 'Upgrade Pro',         'vw-restaurant-lite' ),
					'pro_url'  => esc_url('https://www.vwthemes.com/premium/food-restaurant-wordpress-theme/')
				)
			)
		);

		// Register sections.
		$manager->add_section(
			new VW_Restaurant_Lite_Customize_Section_Pro(
				$manager,
				'example_2',
				array(
					'priority'   => 9,
					'title'    => esc_html__( 'Documentation', 'vw-restaurant-lite' ),
					'pro_text' => esc_html__( 'Docs', 'vw-restaurant-lite' ),
					'pro_url'  => admin_url( 'themes.php?page=vw_restaurant_lite_guide' )
				)
			)
		);

	}

	/**
	 * Loads theme customizer CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue_control_scripts() {

		wp_enqueue_script( 'vw-restaurant-lite-customize-controls', trailingslashit( get_template_directory_uri() ) . '/js/customize-controls.js', array( 'customize-controls' ) );

		wp_enqueue_style( 'vw-restaurant-lite-customize-controls', trailingslashit( get_template_directory_uri() ) . '/css/customize-controls.css' );
	}
}

// Doing this customizer thang!
VW_Restaurant_Lite_Customize::get_instance();