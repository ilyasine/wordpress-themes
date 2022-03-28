<?php 

if(post_password_required()){
    return ;
}
?>

<div class="comments">
 <?php
  comment_form(
	array(
		// 'logged_in_as' => null,
        'title_reply' => __( 'اترك تعليقا' ),
        'author' => __( 'الإسم ' ),
        'email' => __( 'البريد الإلكتروني ' ),
        'label_submit' => __( 'إرســال التعليق' ),
        'comment_notes_before' => __( 'لن يتم نشر عنوان بريدك الإلكتروني .' ),
        'comment_field' => __('<p class="comment-form-comment"><label for="comment">Comment</label> <textarea id="comment" name="comment" cols="45" rows="5" maxlength="65525" required="required"></textarea></p>'),
        'title_reply_to' => __( 'اترك ردا ل %s  ' ),
        'cancel_reply_link' => __( 'إلغاء ' ),
 
        
         
        add_filter('comment_form_default_fields', function ($fields)  {
                                                   unset($fields['url']);  
                                                  return $fields;
                 }
                ),

                add_filter( 'comment_form_default_fields', function ( $fields ) {
                   
                    $fields['author'] = '<table dir="rtl"> <p class="comment-form-author"><tr><td><label for="author">الإسم <span class="required">*</span></label></td> <td><input id="author" name="author" type="text" value="" size="30" maxlength="245" required="required"></p></td></tr>';
                    $fields['email'] = '<tr><td> <p class="comment-form-email"><label for="email">البريد الإلكتروني <span class="required">*</span></label></td> <td><input id="email" name="email" type="text" value="" size="30" maxlength="100" required="required"></p></td></tr></table>';
                    $fields['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' />' . '<label for="wp-comment-cookies-consent">احفظ اسمي والبريد الإلكتروني في هذا المتصفح للمرة القادمة التي أعلق فيها</label></p>';
                    return $fields;
                

              }),

    
        add_filter('comment_form_logged_in', function( $logged_in_as, $commenter, $user_identity) {
            return sprintf(
                      '<p class="logged-in-as">%s</p>',
                      sprintf(
                          /* translators: 1: Edit user link, 2: Accessibility text, 3: User name, 4: Logout URL. */
                          __( 'متّصل كـ <a href="%1$s" aria-label="%2$s">%3$s</a>. <a class="log-out-link" href="%4$s">تسجيل الخروج؟</a>' ),
                          get_edit_user_link(),
                          /* translators: %s: User name. */
                          esc_attr( sprintf( __( 'متّصل كـ %s. Edit your profile.' ), $user_identity ) ),
                          $user_identity,
                          /** This filter is documented in wp-includes/link-template.php */
                          wp_logout_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ), get_the_ID() ) )
                      )
                  );
          }, 10, 3 ),
          
    
             
    )
    
   
    
 ); ?>
  <div class="hr-line"></div>

 <?php
  if (have_comments()) : ?>
    <div class="post-comments">
        
<?php wp_list_comments( 'type=comment&callback=mytheme_comment' ); ?>

    </div>
 <?php 
 endif;
 ?>

</div>






