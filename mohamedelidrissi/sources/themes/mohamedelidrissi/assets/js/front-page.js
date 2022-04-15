/* ***************** welcome ****************/

const newsticker = document.querySelector(".newsticker");
const typedTextSpan = document.querySelector(".welcometxt");
const cursorSpan = document.querySelector(".cursor");

const textArray = ["مرحبا بكم في الموقع الرسمي للفنان محمد الإدريسي", "Welcome to the official website of Mohamed El Idrissi", "Bienvenue sur le site officiel de Mohamed El Idrissi"];
const typingDelay = 200;
const erasingDelay = 100;
const newTextDelay = 3000; // Delay between current and next text
let textArrayIndex = 0;
let charIndex = 0;


function type() {
   if(textArrayIndex == 0) {
      newsticker.style ="flex-direction: row;"    
   }
   else {
      newsticker.style ="flex-direction: row-reverse;"
   } 
  if (charIndex < textArray[textArrayIndex].length) {
    if(!cursorSpan.classList.contains("typing")) cursorSpan.classList.add("typing");
    typedTextSpan.textContent += textArray[textArrayIndex].charAt(charIndex);
    charIndex++;
    setTimeout(type, typingDelay);
  } 
  else {
    cursorSpan.classList.remove("typing");
     setTimeout(erase, newTextDelay);
  }
}

function erase() {
   if (charIndex > 0) {
    if(!cursorSpan.classList.contains("typing")) cursorSpan.classList.add("typing");
    typedTextSpan.textContent = textArray[textArrayIndex].substring(0, charIndex-1);
    charIndex--;
    setTimeout(erase, erasingDelay);
  } 
  else {
    cursorSpan.classList.remove("typing");
    textArrayIndex++;
    if(textArrayIndex>=textArray.length) textArrayIndex=0;
    setTimeout(type, typingDelay + 1100);
  }
}

document.addEventListener("DOMContentLoaded", function() { // On DOM Load initiate the effect
  if(textArray.length) setTimeout(type, newTextDelay + 250);
});

/* ***************** welcome ****************/

const aboutimg = document.querySelector('.about_img');
const about_text = document.querySelector('.about_text');

/* -------------------------------------------------------------------*/

// slideshow-galery

const slider = document.querySelector('.slider');
const slideimages = document.querySelectorAll('.slider img');

// buttons
const nextbtn = document.querySelector('.fa-chevron-double-right');
const prevbtn = document.querySelector('.fa-chevron-double-left');

let counter = 1;

/********** focus on the first image ************/

slider.style.transform = 'translateX(' + (100 * counter) + '%)';

/********** focus on the first image ************/


// Clone first and last slide
slider.appendChild(slideimages[0].cloneNode(true));
slider.insertBefore(slideimages[slideimages.length - 1].cloneNode(true), slideimages[0]);

slideshow = () => {
   if (counter >= slideimages.length + 1) return;
   counter++;
   slider.style.transition = 'transform .7s ease-in-out';
   slider.style.transform = 'translateX(' + (100 * counter) + '%)';
}

timer = setInterval(() => { slideshow() }, 6000);

/* *************** slideshow ********************/

nextbtn.addEventListener('click', () => {
   clearInterval(timer);
   timer = setInterval(() => { slideshow() }, 18000);
   if (counter >= slideimages.length + 1) return;
   counter++;
   slider.style.transition = 'transform .6s ease-in-out';
   slider.style.transform = 'translateX(' + (100 * counter) + '%)';
   setTimeout(() => {
      clearInterval(timer);
      timer = setInterval(() => { slideshow() }, 6000);
   }, 3000);
})

prevbtn.addEventListener('click', () => {
   clearInterval(timer);
   timer = setInterval(() => { slideshow() }, 18000);
   if (counter <= 0) return;
   counter--;
   slider.style.transition = 'transform .6s ease-in-out';
   slider.style.transform = 'translateX(' + (100 * counter) + '%)';
   setTimeout(() => {
      clearInterval(timer);
      timer = setInterval(() => { slideshow() }, 6000);
   }, 3000);
})

slider.addEventListener('transitionend', () => {

   if (counter <= 0) {
      counter = slideimages.length;
      slider.style.transition = "none";
      slider.style.transform = 'translateX(' + (100 * counter) + '%)';
   }

   if (counter == slideimages.length + 1) {
      slider.style.transition = "none";
      counter = 1;
      slider.style.transform = 'translateX(' + (100 * counter) + '%)';
   }

})

// Drage & Drop

let isDragging = false;

Array.from(slideimages, (slideimg) => {

   const touchStart = () => {
      return (e) => {
         isDragging = true;

         xstart = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

         slider.classList.add('grabbing');
      }
   }
   const touchMove = (e) => {

      xmove = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

   }
   const touchEnd = () => {
      isDragging = false;

      if (xstart + 100 < xmove) {

         //going to right
         clearInterval(timer);
         timer = setInterval(() => { slideshow() }, 18000);
         if (counter >= slideimages.length + 1) return;
         counter++;
         slider.style.transition = 'transform .6s ease-in-out';
         slider.style.transform = 'translateX(' + (100 * counter) + '%)';

         setTimeout(() => {
            clearInterval(timer);
            timer = setInterval(() => { slideshow() }, 6000);
         }, 3000);

      }

      else if (xstart - 100 > xmove) {

         //going to left
         clearInterval(timer);
         timer = setInterval(() => { slideshow() }, 18000);
         if (counter <= 0) return;
         counter--;
         slider.style.transition = 'transform .6s ease-in-out';
         slider.style.transform = 'translateX(' + (100 * counter) + '%)';
         setTimeout(() => {
            clearInterval(timer);
            timer = setInterval(() => { slideshow() }, 6000);
         }, 3000);

      }
      slider.classList.remove('grabbing');

   }

   //Touch Events
   slideimg.addEventListener('touchstart', touchStart(counter), {passive: true});
   slideimg.addEventListener('touchend', touchEnd, {passive: true});
   slideimg.addEventListener('touchmove', touchMove, {passive: true});

   //Mouse Events
   slideimg.addEventListener('mousedown', touchStart(counter));
   slideimg.addEventListener('mouseup', touchEnd);
   slideimg.addEventListener('mousemove', touchMove);


})


/* -------------------------------------------------------------------*/



let main_front_page = document.querySelector('.section');

// slideaudio

//audio
const audio_container = document.querySelector(".thumbnail-audio-container");
let audio_allBox = audio_container.children;
let audio_containerWidth = audio_container.offsetWidth;
let audio_counter = 0;

/*  ********** */
const margin = 50;
let items = 0;
let totalItems = 0;

// item setup per slide

responsive = [
   { breakPoint: { width: 0, item: 1 } }, //if width greater than 0 (1 item will show) 
   { breakPoint: { width: 600, item: 2 } }, //if width greater than 600 (2  item will show) 
   { breakPoint: { width: 800, item: 3 } }, //if width greater than 800 (3  item will show) 
   { breakPoint: { width: 1000, item: 4 } }, //if width greater than 1000 (4 item will show) 
   { breakPoint: { width: 1800, item: 5 } }, //if width greater than 1800 (5 item will show) 
   { breakPoint: { width: 2000, item: 6 } }, //if width greater than 2000 (6 item will show) 
   { breakPoint: { width: 2500, item: 7 } }, //if width greater than 2500 (7 item will show) 
]

function audio_adapt() {
   for (let i = 0; i < responsive.length; i++) {
      if (window.innerWidth > responsive[i].breakPoint.width) {
         items = responsive[i].breakPoint.item;
         audio_containerWidth = main_front_page.offsetWidth;
      }
   }
   audio_start();
}

window.addEventListener('load', audio_adapt);
window.addEventListener('resize', audio_adapt);

function audio_start() {
   var totalItemsWidth = 0
   for (let i = 0; i < audio_allBox.length; i++) {
      //  setup width and margin of items
      audio_allBox[i].style.width = (audio_containerWidth / items) - margin + "px";
      audio_allBox[i].style.margin = (margin / 2) + "px";
      totalItemsWidth += audio_containerWidth / items;
      totalItems++;
   }

   audio_container.style.width = totalItemsWidth + "px";

}

slideaudio = () => {

   audio_container.addEventListener('transitionend', () => {

      if (audio_counter <= 0) audio_counter = audio_allBox.length;

      switch (items) {
         case 1:
            if (audio_counter >= 10) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;
         case 2:
            if (audio_counter >= 9) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;
         case 3:
            if (audio_counter >= 8) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;
         case 4:
            if (audio_counter >= 7) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;
         case 5:
            if (audio_counter >= 6) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;
         case 6:
            if (audio_counter >= 5) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;
         case 7:
            if (audio_counter >= 4) {
               audio_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
            }
            break;

      }

   })

   audio_counter++;
   audio_container.style.transition = 'transform .5s ease-in-out';
   audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
}

audiotimer = setInterval(() => { slideaudio() }, 6000);

//Drag audio slider

const atouchStart = () => {
   return (e) => {
      isDragging = true;

      axstart = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

      audio_container.classList.add('grabbing');
   }
}
const atouchMove = (e) => {

   axmove = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

}
const atouchEnd = () => {
   isDragging = false;

   //clearInterval(audiotimer);

   if (axstart + 100 < axmove) {

      //going to right
      clearInterval(audiotimer);
      audiotimer = setInterval(() => { slideaudio() }, 18000);

      audio_counter--;
      audio_container.style.transition = 'transform .5s ease-in-out';
      audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';

      setTimeout(() => {
         clearInterval(audiotimer);
         audiotimer = setInterval(() => { slideaudio() }, 6000);
      }, 3000);

   }

   else if (axstart - 100 > axmove) {

      //going to left
      clearInterval(audiotimer);
      audiotimer = setInterval(() => { slideaudio() }, 18000);

      audio_counter++;
      audio_container.style.transition = 'transform .5s ease-in-out';
      audio_container.style.transform = 'translateX(' + (-(audio_containerWidth / items) * audio_counter) + 'px)';
      setTimeout(() => {
         clearInterval(audiotimer);
         audiotimer = setInterval(() => { slideaudio() }, 6000);
      }, 3000);


   }
   audio_container.classList.remove('grabbing');

}


//Touch Events
audio_container.addEventListener('touchstart', atouchStart(audio_counter), {passive: true});
audio_container.addEventListener('touchend', atouchEnd, {passive: true});
audio_container.addEventListener('touchmove', atouchMove, {passive: true});

//Mouse Events
audio_container.addEventListener('mousedown', atouchStart(audio_counter));
audio_container.addEventListener('mouseup', atouchEnd);
audio_container.addEventListener('mousemove', atouchMove);


// ------------------   slidevideo   ------------------------
//video
const video_container = document.querySelector(".thumbnail-video-container");
let video_allBox = video_container.children;
let video_containerWidth = video_container.offsetWidth;
let video_counter = 0;
// item setup per slide
responsive = [{
   breakPoint: {
      width: 0,
      item: 1
   }
}, //if width greater than 0 (1 item will show) 
{
   breakPoint: {
      width: 600,
      item: 2
   }
}, //if width greater than 600 (2  item will show) 
{
   breakPoint: {
      width: 800,
      item: 3
   }
}, //if width greater than 800 (3  item will show) 
{
   breakPoint: {
      width: 1000,
      item: 4
   }
}, //if width greater than 1000 (4 item will show) 
{
   breakPoint: {
      width: 1800,
      item: 5
   }
}, //if width greater than 1800 (5 item will show) 
{
   breakPoint: {
      width: 2000,
      item: 6
   }
}, //if width greater than 2000 (6 item will show) 
{
   breakPoint: {
      width: 2500,
      item: 7
   }
}, //if width greater than 2500 (7 item will show) 
]
function video_adapt() {
   for (let i = 0; i < responsive.length; i++) {
      if (window.innerWidth > responsive[i].breakPoint.width) {
         items = responsive[i].breakPoint.item;
         video_containerWidth = main_front_page.offsetWidth;
      }
   }
   video_start();
}
window.addEventListener('load', video_adapt);
window.addEventListener('resize', video_adapt);
function video_start() {
   var totalItemsWidth = 0
   for (let i = 0; i < video_allBox.length; i++) {
      //  setup width and margin of items
      video_allBox[i].style.width = (video_containerWidth / items) - margin + "px";
      video_allBox[i].style.margin = (margin / 2) + "px";
      totalItemsWidth += video_containerWidth / items;
      totalItems++;
   }
   video_container.style.width = totalItemsWidth + "px";
}
slidevideo = () => {
   video_container.addEventListener('transitionend', () => {
      if (video_counter <= 0) video_counter = video_allBox.length;
      switch (items) {
         case 1:
            if (video_counter >= 10) {
               video_counter = 0;
               video_container.style.transition = 'none';
               video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
         case 2:
            if (video_counter >= 9) {
               video_counter = 0;
               video_container.style.transition = 'none';
               video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
         case 3:
            if (video_counter >= 8) {
               video_counter = 0;
               video_container.style.transition = 'none';
               video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
         case 4:
            if (video_counter >= 7) {
               video_counter = 0;
               video_container.style.transition = 'none';
               video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
         case 5:
            if (video_counter >= 6) {
               video_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
         case 6:
            if (video_counter >= 5) {
               video_counter = 0;
               audio_container.style.transition = 'none';
               audio_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
         case 7:
            if (video_counter >= 4) {
               video_counter = 0;
               video_container.style.transition = 'none';
               video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
            }
            break;
      }
   })
   video_counter++;
   video_container.style.transition = 'transform .5s ease-in-out';
   video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
}
setTimeout(() => {
   videotimer = setInterval(() => {
      slidevideo()
   }, 6000);
}, 500);

//Drag video slider

const vtouchStart = () => {
   return (e) => {
      isDragging = true;

      vxstart = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

      video_container.classList.add('grabbing');
   }
}
const vtouchMove = (e) => {

   vxmove = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

}
const vtouchEnd = () => {
   isDragging = false;

   //clearInterval(audiotimer);

   if (vxstart + 100 < vxmove) {

      //going to right
      clearInterval(videotimer);
      videotimer = setInterval(() => { slidevideo() }, 18000);

      video_counter--;
      video_container.style.transition = 'transform .5s ease-in-out';
      video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';

      setTimeout(() => {
         clearInterval(videotimer);
         videotimer = setInterval(() => { slidevideo() }, 6000);
      }, 3000);

   }

   else if (vxstart - 100 > vxmove) {

      //going to left
      clearInterval(videotimer);
      videotimer = setInterval(() => { slidevideo() }, 18000);

      video_counter++;
      video_container.style.transition = 'transform .5s ease-in-out';
      video_container.style.transform = 'translateX(' + (-(video_containerWidth / items) * video_counter) + 'px)';
      setTimeout(() => {
         clearInterval(videotimer);
         videotimer = setInterval(() => { slidevideo() }, 6000);
      }, 3000);


   }
   video_container.classList.remove('grabbing');

}


//Touch Events
video_container.addEventListener('touchstart', vtouchStart(video_counter), {passive: true});
video_container.addEventListener('touchend', vtouchEnd, {passive: true});
video_container.addEventListener('touchmove', vtouchMove, {passive: true});

//Mouse Events
video_container.addEventListener('mousedown', vtouchStart(video_counter));
video_container.addEventListener('mouseup', vtouchEnd);
video_container.addEventListener('mousemove', vtouchMove);


// ------------------   slideblog   ------------------------
//blog
const blog_container = document.querySelector(".thumbnail-blog-container");
let blog_allBox = blog_container.children;
let blog_containerWidth = blog_container.offsetWidth;
let blog_counter = 0;
responsive = [{
   breakPoint: {
      width: 0,
      item: 1
   }
}, //if width greater than 0 (1 item will show) 
{
   breakPoint: {
      width: 600,
      item: 2
   }
}, //if width greater than 600 (2  item will show) 
{
   breakPoint: {
      width: 800,
      item: 3
   }
}, //if width greater than 800 (3  item will show) 
{
   breakPoint: {
      width: 1000,
      item: 4
   }
}, //if width greater than 1000 (4 item will show) 
{
   breakPoint: {
      width: 1800,
      item: 5
   }
}, //if width greater than 1800 (5 item will show) 
{
   breakPoint: {
      width: 2000,
      item: 6
   }
}, //if width greater than 2000 (6 item will show) 
{
   breakPoint: {
      width: 2500,
      item: 7
   }
}, //if width greater than 2500 (7 item will show) 
]
function blog_adapt() {
   for (let i = 0; i < responsive.length; i++) {
      if (window.innerWidth > responsive[i].breakPoint.width) {
         items = responsive[i].breakPoint.item;
         blog_containerWidth = main_front_page.offsetWidth;
      }
   }
   blog_start();
}
window.addEventListener('load', blog_adapt);
window.addEventListener('resize', blog_adapt);
function blog_start() {
   var totalItemsWidth = 0
   for (let i = 0; i < blog_allBox.length; i++) {
      //  setup width and margin of items
      blog_allBox[i].style.width = (blog_containerWidth / items) - margin + "px";
      blog_allBox[i].style.margin = (margin / 2) + "px";
      totalItemsWidth += blog_containerWidth / items;
      totalItems++;
   }
   blog_container.style.width = totalItemsWidth + "px";
}
slideblog = () => {
   blog_container.addEventListener('transitionend', () => {
      if (blog_counter <= 0) blog_counter = blog_allBox.length;
      switch (items) {
         case 1:
            if (blog_counter >= 10) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
         case 2:
            if (blog_counter >= 9) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
         case 3:
            if (blog_counter >= 8) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
         case 4:
            if (blog_counter >= 7) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
         case 5:
            if (blog_counter >= 6) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
         case 6:
            if (blog_counter >= 5) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
         case 7:
            if (blog_counter >= 4) {
               blog_counter = 0;
               blog_container.style.transition = 'none';
               blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
            }
            break;
      }
   })
   blog_counter++;
   blog_container.style.transition = 'transform .5s ease-in-out';
   blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
}
setTimeout(() => {
   blogtimer = setInterval(() => {
      slideblog()
   }, 6000);
}, 1000);

//Drag blog slider

const btouchStart = () => {
   return (e) => {
      isDragging = true;

      bxstart = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

      blog_container.classList.add('grabbing');
   }
}
const btouchMove = (e) => {

   bxmove = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;

}
const btouchEnd = () => {
   isDragging = false;

   //clearInterval(audiotimer);

   if (bxstart + 100 < bxmove) {

      //going to right
      clearInterval(blogtimer);
      blogtimer = setInterval(() => { slideblog() }, 18000);

      blog_counter--;
      blog_container.style.transition = 'transform .5s ease-in-out';
      blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';

      setTimeout(() => {
         clearInterval(blogtimer);
         blogtimer = setInterval(() => { slideblog() }, 6000);
      }, 3000);

   }

   else if (bxstart - 100 > bxmove) {

      //going to left
      clearInterval(blogtimer);
      blogtimer = setInterval(() => { slideblog() }, 18000);

      blog_counter++;
      blog_container.style.transition = 'transform .5s ease-in-out';
      blog_container.style.transform = 'translateX(' + (-(blog_containerWidth / items) * blog_counter) + 'px)';
      setTimeout(() => {
         clearInterval(blogtimer);
         blogtimer = setInterval(() => { slideblog() }, 6000);
      }, 3000);


   }
   blog_container.classList.remove('grabbing');

}


//Touch Events
blog_container.addEventListener('touchstart', btouchStart(blog_counter), {passive: true});
blog_container.addEventListener('touchend', btouchEnd, {passive: true});
blog_container.addEventListener('touchmove', btouchMove, {passive: true});

//Mouse Events
blog_container.addEventListener('mousedown', btouchStart(blog_counter));
blog_container.addEventListener('mouseup', btouchEnd);
blog_container.addEventListener('mousemove', btouchMove);