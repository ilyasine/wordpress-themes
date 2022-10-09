<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package CT_Custom
 */

?>
		</div><!-- #container -->
	</div><!-- #content -->
	<div class="container">
		<footer id="colophon" class="site-footer">
			<?php if( ! is_page('homepage')) :?>
			<div class="site-info">
				<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'ct-custom' ) ); ?>">
					<?php
					/* translators: %s: CMS name, i.e. WordPress. */
					printf( esc_html__( 'Proudly powered by %s', 'ct-custom' ), 'WordPress' );
					?>
				</a>
				<span class="sep"> | </span>
					<?php
					/* translators: 1: Theme name, 2: Theme author. */
					printf( esc_html__( 'Theme: %1$s by %2$s.', 'ct-custom' ), 'ct-custom', '<a href="https://coalitiontechnologies.com/">Coalition Technologies</a>' );
					?>
			</div><!-- .site-info -->
			<?php endif; ?>
		</footer><!-- #colophon -->
	</div>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
