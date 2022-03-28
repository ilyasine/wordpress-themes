<?php

function ajax_contact_form(){


  $name = $_POST['name'] ;
  $mail = $_POST['mail'] ;
  $tel = $_POST['tel'] ;
  $msg = $_POST['msg'] ;

  // if (isset($_POST['email'])) {

   
  //  $email_to = "contact@mohamedelidrissi.com";
    $email_to = "contact@mohamedelidrissi.com";
    $email_subject =  " رسالة تواصل من الموقع , من طرف " . $name  ;
  
    // $fname = $_POST['visitor_name']; // required
    // $email_from = $_POST['visitor_email']; // required
    // $telephone = $_POST['visitor_telephone']; // required
    // $comments = $_POST['visitor_message']; //not required
  
    

  
    function clean_string($string)
    {
      $bad = array("content-type", "bcc:", "to:", "cc:", "href");
      return str_replace($bad, "", $string);
    }
  
    // $email_message .= "Name: " . clean_string($name) . "\n";
    // $email_message .= "Email: " . clean_string($mail) . "\n";
    // $email_message .= "Telephone: " . clean_string($tel) . "\n";
    // $email_message .= "Comments: " . clean_string($msg) . "\n";

    $email_message = '<html>
    <head>
       <meta charset="utf-8">
       <meta http-equiv="X-UA-Compatible" content="IE=edge">
       <title>رسالة تواصل من الموقع</title>
       <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
       <div class="rps_ff3f">
          <div dir="rtl" style="color:#ffffff; font-size:0">
             <table dir="rtl" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="min-width:100%; width:100%">
                <tbody>
                   <tr dir="rtl">
                      <td dir="rtl" align="center">
                         <table dir="rtl" align="center" class="x_mobile-width-full" border="0" cellspacing="0" cellpadding="0" style="width: 80%;
                         margin: auto;">
                            <tbody>
                            
                               <tr dir="rtl">
                                  <td dir="rtl" align="right" class="x_mobile-padding-LR12" border="0" style="padding:15px 25px 25px"><a href="https://mohamedelidrissi.com/" target="_blank" rel="noopener noreferrer" data-auth="NotApplicable" data-linkindex="0">
                                  </a>
                                  </td>
                               </tr>
                               <tr dir="rtl">
                                  <td dir="rtl" align="left" class="x_mobile-padding-LR12" style="padding:0 25px 5px">
                                     <table dir="rtl" cellspacing="0" cellpadding="0" border="0" style="width:100%">
                                        <tbody>
                                           <tr dir="rtl">
                                              <td dir="rtl" class="x_mobile-padding-LR15" style="background-color: #0c8601; text-align:center; padding:30px 20px; font-family: arabswell_1;font-weight:normal; line-height:33px;border-radius: 50px; font-size:24px; color:#ffffff">رسالة تواصل من الموقع </td>
                                           </tr>
                                           <tr dir="rtl">
                                              <td dir="rtl" style="padding:25px 0 8px; font-family:Segoe UI,Segoe UI Regular,SUWR,Arial,sans-serif; font-weight:normal; line-height:18px; font-size:12px; color:#000000">مرحبًا،<br><br>لقد تلقيت رسالة من طرف أحد زوار موقعك الرسمي</td>
                                           </tr>
                                           <tr dir="rtl">
                                              <td dir="rtl" style="padding:10px 0 8px; font-family:Segoe UI,Segoe UI Regular,SUWR,Arial,sans-serif; font-weight:normal; line-height:18px; font-size:12px; color:#000000">معلومات المرسل : </td>
                                           </tr>
                                         
                                           <tr dir="rtl">
                                              <td dir="rtl" style="padding:10px 0 8px; font-family:Segoe UI,Segoe UI Regular,SUWR,Arial,sans-serif; font-weight:normal; line-height:18px; font-size:16px; color:#000000">
                                                 <table class="comicGreen" style="  font-family: Comic Sans MS, cursive, sans-serif;
                                                 border: 2px solid #4F7849;
                                                 background-color: #EEEEEE;
                                                 width: 100%;
                                                 text-align: center;
                                                 border-collapse: collapse; font-family: El Messiri, sans-serif;">
                                                 <tbody>
                                                 <tr>
                                                 <td style="border: 1px solid #4F7849;
                                                 padding: 12px 2px;font-size: 16px;font-weight: bold;color: #4F7849;background: #CEE0CC;">الإسم :</td>
                                                 <td style="border: 1px solid #4F7849;
                                                 padding: 12px 2px;font-size: 16px;font-weight: bold;color: #4F7849;">'. $name .'</td>
                                                 </tr>
                                                 <tr>
                                                 <td style="border: 1px solid #4F7849;
                                                 padding: 12px 2px;font-size: 16px;font-weight: bold;color: #4F7849;background: #CEE0CC;">الهاتف :</td>
                                                 <td style="border: 1px solid #4F7849;
                                                 padding: 12px 2px;font-size: 16px;font-weight: bold;color: #4F7849;">'. $tel .'</td>
                                                 </tr>
                                                 <tr>
                                                 <td style="border: 1px solid #4F7849;
                                                 padding: 12px 2px;font-size: 16px;font-weight: bold;color: #4F7849;background: #CEE0CC;">البريد الإلكتروني :</td>
                                                 <td style="border: 1px solid #4F7849;
                                                 padding: 12px 2px;font-size: 16px;font-weight: bold;color: #4F7849;">'. $mail .'</td>
                                                 </tr>
                                                 </tbody>
                                                 </table> </td>
                                           </tr>
                                           <tr dir="rtl">
                                              <td dir="rtl" style="padding:10px 0 8px; font-family:Segoe UI,Segoe UI Regular,SUWR,Arial,sans-serif; font-weight:normal; line-height:18px; font-size:12px; color:#000000">نص الرسالة :<br><br>'. $msg .' </td>
                                           </tr>
                                        </tbody>
                                     </table>
                                  </td>
                               </tr>
                          
                               <tr dir="rtl">
                                  <td dir="rtl" class="x_mobile-padding-LR12" bgcolor="#f0f0f0" style="padding:15px 25px 25px">
                                     <table dir="rtl" border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100%">
                                        <tbody>
                                           <tr dir="rtl">
                                              <td dir="rtl" align="right" style="padding:10px 0 0; font-family:Segoe UI,Segoe UI Regular,SUWR,Arial,sans-serif; font-size:10px; line-height:14px; color:#6b6b6b">تم إرسال هذا البريد الإلكتروني من علبة البريد الخاصة بموقعك.<br><a href="https://mohamedelidrissi.com/privacy-policy/" target="_blank" rel="noopener noreferrer" data-auth="NotApplicable" style="color:#6b6b6b; text-decoration:underline; font-weight:normal; white-space:nowrap" data-linkindex="6"><strong style="color:#6b6b6b; text-decoration:underline; font-weight:normal; white-space:nowrap">بيان الخصوصية</strong></a><br><br>كل الحقوق محفوظة للموقع الرسمي للفنان محمد الإدريسي</td>
                                           </tr>
                                           <tr dir="rtl">
                                              <td dir="rtl" align="right" style="padding:15px 0 0"><a href="https://mohamedelidrissi.com/" target="_blank" rel="noopener noreferrer" data-auth="NotApplicable" data-linkindex="7"></a></td>
                                           </tr>
                                        </tbody>
                                     </table>
                                  </td>
                               </tr>
                            </tbody>
                         </table>
                      </td>
                   </tr>
                </tbody>
             </table>   
          </div>
       </div>
    </body>
 </html>';
  
    // create email headers

     //email headers

  $headers[] = 'Content-Type : text/html ; charset=UTF-8'; 
  $headers[] = "From : Mohamed El idrissi's Website <" . $email_to . ">"; 
  $headers[] = 'Reply-To : ' . $mail . "\r\n"; 
  $headers[] = 'BCC : ' . $mail . "\r\n"; 

    // $headers = 'From: ' . $email_from . "\r\n" .
    //   'Reply-To: ' . $email_from . "\r\n" .
    //   'X-Mailer: PHP/' . phpversion();
    // @mail($email_to, $email_subject, $email_message, $headers);

    wp_mail($email_to, $email_subject, $email_message, $headers);




  // wp_die();

}







