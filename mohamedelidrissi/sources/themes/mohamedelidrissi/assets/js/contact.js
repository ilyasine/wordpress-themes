

const h5tawasal = document.querySelector(".h5tawasal");

h5tawasal.classList.add("animate__animated", "animate__slideInUp", 'animate__duration-1s', "animate__slow");


const contact_main = document.querySelector('.contact-main');
contact_main.classList.add('animate__animated', 'animate__slideInRight');


const form = document.querySelector('form');
const name = document.querySelector('#fname');
const email = document.querySelector('#mail');
const tel = document.querySelector('#tel');
const input = document.querySelectorAll('.contact-form .input');
const clear = document.querySelector('.clear');

const telefone = document.querySelector('.telefone');
const divname = document.querySelector('.name');
const telmessage = document.querySelector('.telmessage');
const namemessage = document.querySelector('.namemessage');
const mail = document.querySelector('.mail');
const mailmessage = document.querySelector('.mailmessage');
const send = document.querySelector('.send');
const sent = document.querySelector('.sent');
const sending = document.querySelector('.sending');
const submitform = document.querySelector('.submitform');
const phone_number = document.querySelector('.phone_number');

let invalid = false;


function formsending() {

  send.classList.add('animate__animated', 'animate__fadeOut', 'animate__faster');

  setTimeout(() => {
    send.style = 'display:none;pointer-events:none;';
    sending.style = 'display:block !important';

  }, 100);

  setTimeout(() => {
    sending.style = 'display:none !important';
    sent.classList.remove('animate__animated', 'animate__fadeOut', 'animate__slow');
    sent.classList.add('animate__animated', 'animate__fadeIn', 'animate__faster');
    sent.style = 'display:block !important';
  }, 2000);

  setTimeout(() => {

    sent.classList.add('animate__animated', 'animate__fadeOut', 'animate__slow');

  }, 10000);

  setTimeout(() => {
    send.classList.remove('animate__animated', 'animate__fadeOut', 'animate__faster');
    send.classList.add('animate__animated', 'animate__fadeIn', 'animate__faster');
    send.style = "display:block !important ;"

  }, 12500);

}

//  phonenumber


phone_number.addEventListener('click', () => {

  phone_number.innerHTML = '50 68 14 06 6 212+';
  phone_number.style = 'font-size: 24px;';
  setTimeout(() => {
    phone_number.setAttribute('href', 'tel:+212 606-146850');
  }, 1000);
  setTimeout(() => {
    phone_number.innerHTML = 'ٱضغط لإظهار رقم الهاتف';
    phone_number.style = 'font-size: 23px;';
    phone_number.removeAttribute('href');
  }, 20000);

})

fname.addEventListener('input', nameverification);
email.addEventListener('input', emailverification);
tel.addEventListener('input', phoneverification);

fname.addEventListener('focusout', ()=>{
  namemessage.style = "display:none !important;";
  divname.style = "margin-top: 0px;";
  fname.classList.remove('invalid');
  divname.querySelector('.icon').classList.remove('invalid');
});
email.addEventListener('focusout', ()=>{
  mailmessage.style = "display:none !important;";
  mail.style = "margin-top: 0px;"
  email.classList.remove('invalid');
  mail.querySelector('.icon').classList.remove('invalid');
});
tel.addEventListener('focusout', ()=>{
  telmessage.style = "display:none !important;";
  telefone.style = "margin-top: 0px;";
  tel.classList.remove('invalid');
  telefone.querySelector('.icon').classList.remove('invalid');
});

form.addEventListener('submit', (e) => {
  // e.preventDefault();
  // if (invalid) {

  //   submitbtn.value = "إرسال";
  //   submitbtn.style = " border: 1.4px solid gold; color: gold; ";
  // } else {
  //   formsending();
  //   setTimeout(() => {
  //     input.forEach(input => input.value = '');
  //     document.querySelector('.msg-text').value = '' ;
  //   }, 8500);

  // }

})

function nameverification() {
  setTimeout(() => {
    if ( fname.value.length == 0 || fname == null || fname == "") {
       // console.log(" please verify you phone number");
       namemessage.style = "display:block;";
       divname.style = "margin-top: 10px;";
       fname.classList.add('invalid');
       divname.querySelector('.icon').classList.add('invalid');
       namemessage.classList.add("animate__animated", "animate__headShake");
       invalid = true;
    }
    else {
        // console.log(" your phone numbe is valid !");
        namemessage.style = "display:none !important;";
        divname.style = "margin-top: 0px;";
        fname.classList.remove('invalid');
        divname.querySelector('.icon').classList.remove('invalid');
    }
  }, 2000);

}


function emailverification() {

  if (email.value.match(/^[^ ]+@[^ ]+\.[a-z]{2,3}$/) || email.value.length == 0) {
    // console.log(" your email is valid !");
    mailmessage.style = "display:none !important;";
    mail.style = "margin-top: 0px;"
    email.classList.remove('invalid');
    mail.querySelector('.icon').classList.remove('invalid');
  }
  else {
    // console.log(" please verify you email");
    mailmessage.style = "display:block !important;";
    mail.style = "margin-top: 20px;";
    email.classList.add('invalid');
    mail.querySelector('.icon').classList.add('invalid');
    mailmessage.classList.add("animate__animated", "animate__headShake");
    invalid = true;
  }

}
function phoneverification() {

  if (tel.value.match(/^\d{10}$/) || tel.value.match(/^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/g) || tel.value.length == 0) {
    // console.log(" your phone numbe is valid !");
    telmessage.style = "display:none !important;";
    telefone.style = "margin-top: 0px;";
    telefone.querySelector('.icon').classList.remove('invalid');
    tel.classList.remove('invalid');
  }
  else {
    // console.log(" please verify you phone number");
    telmessage.style = "display:block;";
    telefone.style = "margin-top: 10px;";
    tel.classList.add('invalid');
    telefone.querySelector('.icon').classList.add('invalid');
    telmessage.classList.add("animate__animated", "animate__headShake");
    invalid = true;
  }

}

function get_action(form) 
{
    var v = grecaptcha.getResponse();
    if(v.length == 0)
    {
        document.getElementById('captcha').innerHTML="You can't leave Captcha Code empty";
        return false;
    }
    else
    {
         document.getElementById('captcha').innerHTML="Captcha completed";
        return true; 
    }
}


