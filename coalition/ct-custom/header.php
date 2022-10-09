<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package CT_Custom
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'ct-custom' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="top-bar d-flex container-fluid ">
			<div class="container d-flex justify-content-between">
				<div class="call-us d-flex flex-sm-row flex-column align-items-center">		   
					<div class="call-txt">CALL US NOW! </div>
					<div class="call-number"> 385.154.11.28.35</div>
				</div>
				<div class="login-sign d-flex flex-sm-row flex-column align-items-center">
					<div class="login">LOGIN </div>
					<div class="sign">SIGNUP</div>
				</div>
				<!-- <div class="row">
					<div class="col-sm-6">col-sm-6</div>
					<div class="col-sm-6">col-sm-6</div>
					</div>
					<div class="row">
						<div class="col-sm-3">.col-sm-3</div>
						<div class="col-sm-6">.col-sm-6</div>
						<div class="col-sm-3">.col-sm-3</div>
					</div> -->
				</div>
		</div>
		<div class="logo-menu container-fluid">
			<div class="container py-5 d-flex justify-content-between">
				<div class="site-branding">
					<?php
					/* the_custom_logo();
					if ( is_front_page() && is_home() ) :
						?>
						<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
						<?php
					else :
						?>
						<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
						<?php
					endif;
					$ct_custom_description = get_bloginfo( 'description', 'display' );
					if ( $ct_custom_description || is_customize_preview() ) :
						?>
						<p class="site-description"><?php echo $ct_custom_description;  ?></p>
					<?php endif;  */
					echo '<a href="'. esc_url(home_url()) .'" rel="home"><img class="img-preview" src="'. get_option('ct_theme_logo_image_option') .'" alt="'. get_option('ct_theme_logo_image_option') .'"></a>'; ?>
				</div><!-- .site-branding -->

				<nav id="site-navigation" class="main-navigation">
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><span class="dashicons dashicons-menu"></span></button>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
					) );
					?>
				</nav><!-- #site-navigation -->
			</div>
		</div>
	</header><!-- #masthead -->	
		<div id="content" class="site-content">
			<div class="container">
		
