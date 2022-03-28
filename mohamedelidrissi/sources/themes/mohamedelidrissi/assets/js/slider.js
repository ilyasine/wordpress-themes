// slideshow-galery

const slider = document.querySelector('.slider');
const slideimages = document.querySelectorAll('.slider img');

// buttons
const nextbtn = document.querySelector('.fa-chevron-double-right');
const prevbtn = document.querySelector('.fa-chevron-double-left');

let counter = 1 ;


/**********  focus on the first image ************/

  slider.style.transform = 'translateX('+ (100 * counter)+'%)';

/**********  focus on the first image ************/


// Clone first and last slide
slider.appendChild(slideimages[0].cloneNode(true));  
slider.insertBefore(slideimages[slideimages.length - 1].cloneNode(true), slideimages[0]);



/* ***************  slideshow  ********************/

window.addEventListener('blur', ()=> {
  clearInterval(timer);
})
window.addEventListener('focus', ()=> {

  if (document.hidden) {
    clearInterval(timer);
    console.log('document is hidden');
  } 
  else {
    timer = setInterval(() => {slideshow() }, 6000);
    console.log('document is visible');
  }
})
  
 


    slideshow =()=> {
   if(counter >= slideimages.length +1 ) return ;
   counter++; 
   slider.style.transition = 'transform .7s ease-in-out';
   slider.style.transform = 'translateX('+ (100 * counter)+'%)';
    }
   
   timer = setInterval(() => {slideshow() }, 6000);

/* ***************  slideshow  ********************/

nextbtn.addEventListener('click',()=> { 
   clearInterval(timer);
  
   timer = setInterval(() => {slideshow() }, 18000);
if(counter >= slideimages.length +1 ) return ;
counter++; 
slider.style.transition = 'transform .6s ease-in-out';
slider.style.transform = 'translateX('+ (100 * counter)+'%)';

setTimeout(() => {
  clearInterval(timer);
  timer = setInterval(() => {slideshow() }, 6000);
}, 3000);
})

prevbtn.addEventListener('click',()=> {
  clearInterval(timer);
   timer = setInterval(() => {slideshow() }, 18000);
if( counter <= 0 )  return ;
counter--;
slider.style.transition = 'transform .6s ease-in-out';
slider.style.transform = 'translateX('+ (100 * counter)+'%)';
setTimeout(() => {
  clearInterval(timer);
  timer = setInterval(() => {slideshow() }, 6000);
}, 3000);
  
})

slider.addEventListener('transitionend', ()=> {
 
if( counter == 0 )  { 
  counter = slideimages.length ; 
  slider.style.transition = "none";
  slider.style.transform = 'translateX('+ (100 * counter)+'%)';
}

if(counter == slideimages.length +1)
 {
  slider.style.transition = "none";
  counter = 1 ;
  slider.style.transform = 'translateX('+ (100 * counter)+'%)';
}

})
