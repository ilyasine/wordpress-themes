window.onload = function(){
    lineDraw();
    //repeat for demo purposes
    setInterval(lineDraw, 5000);
    
  };
  function lineDraw(){
  var path = document.querySelector('.line-animated path');
  var length = path.getTotalLength();
  // Clear any previous transition
  path.style.transition = path.style.WebkitTransition =
    'none';
  
  // Set up the starting positions
  path.style.strokeDasharray = length + ' ' + length; 
  path.style.strokeDashoffset = length;
  // Trigger a layout so styles are calculated & the browser 
  // picks up the starting position before animating
  path.getBoundingClientRect();
  // Define our transition
  path.style.transition = path.style.WebkitTransition =
    'stroke-dashoffset 7s ease-in-out';
  // Go!
  path.style.strokeDashoffset = '0';
    //0 is the image fully animated, 988.01 is the starting point.
  };
  
  
  
  