<?php 

 /* Template Name: Homepage */

 get_header(); ?>
 <div class="breadcumb py-5">Home/ Who we are/ <b>Contact</b></div>
<div class="contact-head row">
  <h1>Contact</h1>
  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam posuere ipsum nec velit mattis elementum. Cum sociis natoque 
penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas eu placerat metus, eget placerat libero. </p>
</div>

<div class="row">
    <div class="col-sm-7 py-4">
        <h1 class="contact-main-title">CONTACT US</h1>
        <!-- contact form -->   
        <?php echo apply_shortcodes( '[contact-form-7 id="28" title="Contact form 1"]' ); ?>
        <!-- contact form -->
    </div>
    <div class="col-sm-5 py-4">
        <h1 class="contact-main-title">REACH US</h1>

        <div class="contact-info">
            <p class="skills-test">Contac Infos<p>

            <p class="adress-info">
                <?= get_option('ct_theme_adress_information_option'); ?>
            </p>
            <p class="phone">
                Phone: <?= get_option('ct_theme_phone_number_option'); ?>
            </p>
            <p class="fax">
                Fax: <?= get_option('ct_theme_fax_number_option'); ?>
            </p>
        </div>

        <div class="social-media">
            <a target="_blank" href="<?= get_option('ct_theme_facebook_link_option'); ?>"><i class="fa-brands fa-facebook-square"></i></a>
            <a target="_blank" href="<?= get_option('ct_theme_twitter_link_option'); ?>"><i class="fa-brands fa-twitter-square"></i></a>
            <a target="_blank" href="<?= get_option('ct_theme_linkedIn_link_option'); ?>"><i class="fa-brands fa-linkedin"></i></a>
            <a target="_blank" href="<?= get_option('ct_theme_pinterest_link_option'); ?>"><i class="fa-brands fa-pinterest-square"></i></a>
        </div>
    </div>
</div>
 
<?php
 get_footer();