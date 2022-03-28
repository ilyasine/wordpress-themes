const share_post = () => {

  const sharelist = document.querySelectorAll('.share-list');
  const shareopen = document.querySelectorAll('.share-open');
  const sharebtn = document.querySelectorAll('.share-btn');
  const sharearrow = document.querySelectorAll('.share-arrow');

  for (let i = 0; i < sharebtn.length; i++) {
    sharebtn[i].addEventListener('click', () => {
      sharelist[i].classList.toggle('share-open');
      sharearrow[i].classList.toggle('share-open');
    })
  }

}

share_post();

loader.remove();

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
  slideimg.addEventListener('touchstart', touchStart(counter));
  slideimg.addEventListener('touchend', touchEnd);
  slideimg.addEventListener('touchmove', touchMove);

  //Mouse Events
  slideimg.addEventListener('mousedown', touchStart(counter));
  slideimg.addEventListener('mouseup', touchEnd);
  slideimg.addEventListener('mousemove', touchMove);


})

