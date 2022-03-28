const video = () => {

const categorytxt = document.querySelector('.cat-text');
const adanico = document.querySelector('.adan-ico');
const quranico = document.querySelector('.quran-ico');
const mawalico = document.querySelector('.mawal-ico');
const nachidico = document.querySelector('.nachid-ico');
const saharatico = document.querySelector('.saharat-ico');
const homeico = document.querySelector('.fa-home');
const mariyatico = document.querySelector('.mariyat-ico');

const grid = document.querySelectorAll('.video-grid');
const videocontainer = document.querySelector('.video-container');

if( body.classList.contains('post-type-archive-video') ){ 

  const quranbtn = document.querySelector('.categories').childNodes[7];
const anachidbtn = document.querySelector('.categories').childNodes[3];
const mawalbtn = document.querySelector('.categories').childNodes[9];
const adanbtn = document.querySelector('.categories').childNodes[1];
const saharatbtn = document.querySelector('.categories').childNodes[5];

saharatbtn.addEventListener("click", saharatfilter);
quranbtn.addEventListener("click", quranfilter);
mawalbtn.addEventListener("click", mawalfilter);
adanbtn.addEventListener("click", adanfilter);
anachidbtn.addEventListener("click", nachidfilter);
homeico.addEventListener("click", home);

function saharatfilter(e) {

  e.preventDefault();
  categorytxt.innerText = saharatbtn.textContent;
  saharatico.style = 'display : block !important ;';
  quranico.style = 'display : none !important ;';
  adanico.style = 'display : none !important ;';
  mariyatico.style = 'display : none !important ;';
  nachidico.style = 'display : none !important ;';
  mawalico.style = 'display : none !important ;';
}
function quranfilter(e) {

  e.preventDefault();
  categorytxt.innerText = quranbtn.textContent;
  quranico.style = 'display : block !important ;';
  adanico.style = 'display : none !important ;';
  mawalico.style = 'display : none !important ;';
  mariyatico.style = 'display : none !important ;';
  nachidico.style = 'display : none !important ;';
  saharatico.style = 'display : none !important ;';
}
function mawalfilter(e) {

  e.preventDefault();
  categorytxt.innerText = mawalbtn.textContent;
  mawalico.style = 'display : block !important ;';
  quranico.style = 'display : none !important ;';
  adanico.style = 'display : none !important ;';
  mariyatico.style = 'display : none !important ;';
  nachidico.style = 'display : none !important ;';
  saharatico.style = 'display : none !important ;';
}
function adanfilter(e) {

  e.preventDefault();
  categorytxt.innerText = adanbtn.textContent;
  adanico.style = 'display : block !important ;';
  quranico.style = 'display : none !important ;';
  mawalico.style = 'display : none !important ;';
  mariyatico.style = 'display : none !important ;';
  nachidico.style = 'display : none !important ;';
  saharatico.style = 'display : none !important ;';

}

function nachidfilter(e) {

  e.preventDefault();
  categorytxt.innerText = anachidbtn.textContent;
  nachidico.style = 'display : block !important ;';
  adanico.style = 'display : none !important ;';
  mawalico.style = 'display : none !important ;';
  mariyatico.style = 'display : none !important ;';
  quranico.style = 'display : none !important ;';
  saharatico.style = 'display : none !important ;';
}

function home() {
  categorytxt.innerText = 'مَرئِيّات';
  mariyatico.style = 'display : block !important ;';
  quranico.style = 'display : none !important ;';
  adanico.style = 'display : none !important ;';
  mawalico.style = 'display : none !important ;';
  nachidico.style = 'display : none !important ;';
  saharatico.style = 'display : none !important ;';
}

}


// video player

let box = document.querySelectorAll('.box');
let play = document.querySelectorAll('.icon-play');

for ( let i = 0; i < play.length; i++){
  play[i].addEventListener( "click", ()=> {
  
    var iframe = document.createElement( "iframe" );

        iframe.setAttribute( "width", "560" );
        iframe.setAttribute( "height", "315" );
        iframe.setAttribute( "frameborder", "0" );
        iframe.setAttribute( "allowfullscreen", "" );
        iframe.setAttribute( "allow", "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" );
        iframe.setAttribute( "src", 'https://www.youtube.com/embed/'+ box[i].dataset.embed +'?rel=0&color=white&autoplay=1' );

        box[i].innerHTML = "";
        box[i].appendChild( iframe );
    } );
  } 
}


video();














