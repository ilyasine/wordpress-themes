


 const categorytxt = document.querySelector('.cat-text');
 

 const titleico = document.createElement("i");

 const quranico = document.querySelector('.quran-ico');
//  const mawalico = document.querySelector('.mawal-ico');
//  const nachidico = document.querySelector('.nachid-ico');
//  const sawtiyatico = document.querySelector('.sawtiyat-ico');

 const homeico = document.querySelector('.fa-home');
 
 const category_title = document.querySelector('.categories-title');
 
 const grid = document.querySelector('.grid');
 const audiocontainer = document.querySelector('.audio-container');

 
//  Categories titles 

 if (category_title.classList.contains('أناشيد')) {
   titleico.classList.add('fad');
   titleico.classList.add('fa-list-music');
  category_title.appendChild(titleico);
  
 } else

 if (category_title.classList.contains('موال')) {
  titleico.classList.add('fad');
  titleico.classList.add('fa-microphone-stand');
 category_title.appendChild(titleico);
 
}
  else

 if (category_title.classList.contains('أذان')) {
  titleico.classList.add('fad');
  titleico.classList.add('fa-mosque');
 category_title.appendChild(titleico);
 
}
  else

 if (category_title.classList.contains('قرآنية')) {
  quranico.style.display='block';
  console.log('quran detected');
  
}
  else

{
  titleico.classList.add('fad');
  titleico.classList.add('fa-music-alt');
 category_title.appendChild(titleico);
 
}

let box = document.querySelector('.box'),
play = document.querySelector('.box .icon-play');

 
play.addEventListener( "click", ()=> {
 
    var iframe = document.createElement( "iframe" );

        iframe.setAttribute( "width", "560" );
        iframe.setAttribute( "height", "315" );
        iframe.setAttribute( "frameborder", "0" );
        iframe.setAttribute( "allowfullscreen", "" );
        iframe.setAttribute( "allow", "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" );
        iframe.setAttribute( "src", 'https://www.youtube.com/embed/'+ box.dataset.embed +'?rel=0&color=white&autoplay=1' );

        box.innerHTML = "";
        box.appendChild( iframe );
} );

 
 