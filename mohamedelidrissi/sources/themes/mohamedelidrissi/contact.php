<?php /* Template Name: contact */ 

 get_header();

?>

    <!-- main -->
    <div class="main">
    <div class="head">
      <div class="headtext">تواصل مع محمد الإدريسي</div>
    </div>
     <h5 class='h5tawasal '>نحن مستعدون دائماً للإجابة عن أي أسئلة أو سماع أفكارك </h5></div>
     <div class="line"></div>
   <div class="container">
    <div class="contact-main">
      <p>لا تتردد في الاتصال بنا عبر الهاتف أو الواتساب ، كما يمكنك التواصل معنا للطلب و الإستفسار عن طريق ملء النموذج أدناه أو مراسلتنا عبر البريد الإلكتروني. سيتم قطع اتصال هواتفنا خلال موسم الإجازات ولكن يمكننا الاتصال بك مرة أخرى إذا أرسلت لنا رقم هاتفك. يمكنك أيضًا مراسلتنا على :
      </p>
       <div class="contact-s-wrapper">
        <ul>
          <li class="facebook"><a href="https://www.facebook.com/medeldrissi" target="_blank"  aria-label="Facebook Page" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a></li>
          <li class="twitter"><a href="https://twitter.com/medeldrissi" target="_blank"  aria-label="Twitter" rel="noopener noreferrer"><i class="fab fa-twitter" ></i></a></li>
          <li class="instagram"><a href="https://www.instagram.com/mohamed_el.idrissi/" target="_blank"  aria-label="Instagram" rel="noopener noreferrer"><i class="fab fa-instagram" ></i></a></li>
          <li class="google"><a href="mailto:elidrissi.madi7@gmail.com" target="_blank"  aria-label="Google" rel="noopener noreferrer"><i class="fab fa-google "></i></a></li>
          <li class="whatsapp"><a href="https://wa.me/212606146850" target="_blank"  aria-label="Whatsapp" rel="noopener noreferrer"><i class="fab fa-whatsapp" ></i></a></li>
        </ul>
       </div>
       <div class="line"></div>
       <div class="adress">حي الدومة ، سيدي مومن  20400
        الدارالبيضاء
       المغرب</div>
      <div class="phone"><span class="label-phone"> الهاتف :</span>
        <a  class="phone_number"> ٱضغط لإظهار رقم الهاتف </a>
      </div>
      <div class="email"><span class="label-email">البريد الإلكتروني :</span> 
        <a href="mailto:contact@mohamedelidrissi.com">contact@mohamedelidrissi.com</a>
      </div> 
         
     </div>
        
     <div class="contact-map">
      <div class="mapouter"><div class="gmap_canvas"><iframe title="الموقع الجغرافي على الخريطة" width="533" height="569" id="gmap_canvas" src="https://maps.google.com/maps?q=sidi%20moumen&t=k&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe></div></div>
    </div>
    

   </div>
   <div class="contact-form-outer">
    <div class="contact-form">
     <div class="cordonnes">

      
       <form id="form" method="post" action="send_form.php">  
        <table dir="rtl">
          <tr><td><label for="fname" >الإسم الكامل</label>
          <td class="name"><div class="input-icons"> 
            <i class="fa fa-user icon"> 
          </i> <input class="input" autocapitalize="words" type="text" id="fname" name="visitor_name" required placeholder=" name">
          <div class="namemessage"><span>&#9888;</span> المرجو إدخال إسم صحيح</div></td></tr> </td></tr>
  
          <tr><td> <label for="email">البريد الإلكتروني </label></td>
          <td class="mail"><div class="input-icons"> 
            <i class="fa fa-envelope icon"> 
          </i> <input class="input" type="email" id="mail" name="visitor_email" required placeholder="ex. name@email.com"></div><div class="mailmessage"><span>&#9888;</span> المرجو إدخال بريد إلكتروني صحيح</div></td></tr> 

          <tr><td> <label for="tel">الهاتف </label></td>
          <td class="telefone"><div class="input-icons"> 
            <i class="fa fa-phone icon"> 
          </i> <input class="input" type="tel" id="tel" name="visitor_telephone" placeholder="ex. 06-00-00-00-00">
        <div class="telmessage"><span>&#9888;</span> المرجو إدخال رقم هاتف صحيح</div></td></tr> 
        </table>

     </div>
        
    <div class="msg"><textarea class="msg-text" name="visitor_message" form="form" cols="40" rows="14" placeholder="اُكتب رسالتك هنا ..." required></textarea>
    </div>
    <input type="hidden" id="ak_js" name="ak_js" value="1632009065674">
    </form>
  
   </div>

  <div class="submitform">    
  <button class="send" form="form" >إرسال<i class="fad fa-paper-plane"></i></button>
  <div class="sending"><i class="fad fa-spinner fa-2x"></i></div>
  <button class="sent" form="form" ><i class="fas fa-shield-check"></i> تم الإرسال بنجاح</button>
   <div class="message-sent"><i class="fad fa-bell-on"></i> شكرا على تواصلك معنا ! سيتم الرد على رسالتك في أقرب وقت ممكن .</div>
 </div> 
</div>

<?php  get_footer(); ?>

