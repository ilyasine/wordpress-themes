let imgs = document.getElementsByTagName('img');

Array.from(imgs, img => img.setAttribute("loading", 'lazy'));

document.addEventListener('dragstart', (e) => {
  e.preventDefault();
});

/*     ************   scroll top   ****************  */

const body = document.body,
 scrollTop = document.querySelector('.scrolltop');

window.addEventListener('scroll', () => {
  scrollTop.classList.toggle('activescrol', window.scrollY > 100)
})

if (window.scrollY > 100) scrollTop.classList.add('activescrol');

scrollTop.addEventListener('click', () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth"
  })
})

/*     ************   scroll top   ****************  */

/*     ************   menu  slider down   ****************  */

const videomenu = document.querySelector('.video-menu'),
      audiomenu = document.querySelector('.audio-menu'),
      blogmenu = document.querySelector('.blog-menu'),
      dropdown = document.querySelector('.dropdown'),
      dropdown1 = document.querySelector('.dropdown1'),
      dropdown2 = document.querySelector('.dropdown2');

dropdown.addEventListener('mouseover', () => {
  audiomenu.classList.add("slideaudio");
})

dropdown.addEventListener('mouseleave', () => {
  audiomenu.classList.remove("slideaudio");
})

dropdown1.addEventListener('mouseover', () => {
  videomenu.classList.add("slidevideo");
})
dropdown1.addEventListener('mouseleave', () => {
  videomenu.classList.remove("slidevideo");
})

dropdown2.addEventListener('mouseover', () => {
  blogmenu.classList.add("slideblog");
})
dropdown2.addEventListener('mouseleave', () => {
  blogmenu.classList.remove("slideblog");
})

/*     ************   menu  slider down   ****************  */
const music = document.querySelector('i.fas.fa-music'),
 music_slash = document.querySelector('i.fas.fa-music-slash'),
 hasbi = document.querySelector(".hasbi"),
 bgmusic = document.querySelector(".bg-music"),
 loader = document.querySelector(".loader"),
 html = document.documentElement;

/*  *************  Background Music  ************** */
window.addEventListener("load", () => {

  loader.classList.add("animate__animated", "animate__fadeOut");

  loader.addEventListener("animationend", function () {
    loader.remove();
  });

  document.querySelector('span#year').textContent = new Date().getFullYear();

  const date = document.querySelector('.Date');

  let hijri = new Intl
    .DateTimeFormat('ar-u-ca-islamic', {
      day: 'numeric',
      month: 'long',
      weekday: 'long',
      year: 'numeric'
    })
    .format(Date.now());

  let miladi = new Intl
    .DateTimeFormat('ar', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
    .format(Date.now());

  date.textContent = hijri + '\xa0::\xa0' + miladi + '\xa0م';

})

bgmusic.classList.add("animate__animated", "animate__bounceInRight", "animate__duration-1s", "animate__slow");
hasbi.volume = 0.04;

const audio_remove = () => {
  if (window.innerWidth <= 480) {
    hasbi.pause();
  }
}

window.addEventListener('load', audio_remove);
window.addEventListener('resize', audio_remove);

hasbi.addEventListener('playing', () => {

  music.addEventListener('click', () => {
    music_slash.style = 'display : block !important';
    music.replaceWith(music_slash);
    hasbi.pause();
    hasbi.currentTime = 0;
  })
})

if (hasbi.paused) {

  music.addEventListener('click', () => {
    music_slash.style = 'display : block !important';
    music_slash.replaceWith(music);
    hasbi.play();
  })
  music_slash.addEventListener('click', () => {
    music_slash.style = 'display : block !important';
    music_slash.replaceWith(music);
    hasbi.play();
  })
}

/*  *************  Background Music  ************** */

/*     ************  Highlight nab buttons   ****************  */

const aboutbtn = document.querySelector('#aboutbtn'),
      audiobtn = document.querySelector('#audiobtn'),
      videobtn = document.querySelector('#videobtn'),
      newsbtn = document.querySelector('#newsbtn'),
      contactbtn = document.querySelector('#contactbtn');

if (document.title === 'عن محمد الإدريسي') {
  aboutbtn.classList.add('active');
}
if (document.title.includes('صوتيات')) {
  audiobtn.classList.add('active');
}
if (document.title.includes('مرئيات')) {
  videobtn.classList.add('active');
}
if (document.title.includes('أخبار و مواعيد')) {
  newsbtn.classList.add('active');
}
if (document.title === 'كن على تواصل') {
  contactbtn.classList.add('active');
}

/*     ************  Highlight nab buttons   ****************  */

/*  *************  Whatsapp  ************** */


window.addEventListener("load", () => {

  const Whatsapp = document.querySelector(".scrolltop + i.fab.fa-whatsapp");

  setTimeout(() => {

    Whatsapp.style = " display: block !important";
    Whatsapp.classList.add("animate__animated", "animate__backInUp", "animate__duration-1s", "animate__slow");

  }, 4000);

  Whatsapp.addEventListener('click', () => {
    window.open("https://wa.me/212606146850");
  })
})

/*  *************  Whatsapp  ************** */

const hamburger = document.querySelector(".hamburger"),
      menu_lines = hamburger.querySelector('.menu-lines'),
      parentmenu = document.querySelector(".menu"),
      menu = parentmenu.querySelector(".menu > div");

hamburger.addEventListener('click', (e) => {
  e.stopPropagation();
  hamburger.classList.toggle('checked');
  menu_lines.classList.toggle('checked');
  document.addEventListener('click', (e) => {
    if (menu != e.target && hamburger.classList.contains('checked') && hamburger != e.target) {
      hamburger.classList.remove('checked');
      menu_lines.classList.remove('checked');
    }
  })
  if (hamburger.classList.contains('checked')) {
    const hamburgertouchStart = (e) => {
      hamburgerxstart = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
      parentmenu.classList.add('grabbing');
    }
    const hamburgertouchMove = (e) => {
      hamburgerxmove = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }
    const hamburgertouchEnd = () => {
      isDragging = false;
      //going to right
      if (hamburgerxstart - 100 > hamburgerxmove) {
        hamburger.classList.remove('checked');
        menu_lines.classList.remove('checked');
      }
      parentmenu.classList.remove('grabbing');
    }

    //Touch Events
    parentmenu.addEventListener('touchstart', hamburgertouchStart);
    parentmenu.addEventListener('touchend', hamburgertouchEnd);
    parentmenu.addEventListener('touchmove', hamburgertouchMove);

    //Mouse Events
    parentmenu.addEventListener('mousedown', hamburgertouchStart);
    parentmenu.addEventListener('mouseup', hamburgertouchEnd);
    parentmenu.addEventListener('mousemove', hamburgertouchMove);

  }
});


if (document.getElementById("fb-root") != null) {
  let TIMEOUT = null;
  window.onresize = () => {
    if (TIMEOUT === null) {
      TIMEOUT = window.setTimeout(() => {
        TIMEOUT = null;
        //fb_iframe_widget class is added after first FB.FXBML.parse()
        //fb_iframe_widget_fluid is added in same situation, but only for mobile devices (tablets, phones)
        //By removing those classes FB.XFBML.parse() will reset the plugin widths.

        document.querySelector('.fb-page').classList.remove('fb_iframe_widget');
        document.querySelector('.fb-page').classList.remove('fb_iframe_widget_fluid')
        FB.XFBML.parse();
      }, 300);
    }
  }
}
