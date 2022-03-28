const audio = () => {

  const playbtn = document.querySelectorAll(".fa-play"),
  stopbtn = document.querySelectorAll(".fa-stop"),
  pausebtn = document.querySelectorAll(".fa-pause"),
  repeatbtn = document.querySelectorAll(".fa-repeat-alt"),
  volumemute = document.querySelectorAll(".fa-volume-slash"),
  music_player_container = document.querySelectorAll(
    ".music-player-container"
  ),
  cdpause = document.querySelectorAll(".vinyl"),
  song = document.querySelectorAll(".song"),
  seekbar = document.querySelectorAll(".seek-bar"),
  fillbar = document.querySelectorAll(".fill"),
  handle = document.querySelectorAll(".handle"),
  startime = document.querySelectorAll(".start-time"),
  endtime = document.querySelectorAll(".end-time"),
  volumeslider = document.querySelectorAll("input[type='range']"),
  volumebtn = document.querySelectorAll(".fa-volume-up"),
  categorytxt = document.querySelector('.cat-text'),
  adanico = document.querySelector('.adan-ico'),
  quranico = document.querySelector('.quran-ico'),
  mawalico = document.querySelector('.mawal-ico'),
  nachidico = document.querySelector('.nachid-ico'),
  homeico = document.querySelector('.fa-home'),
  sawtiyatico = document.querySelector('.sawtiyat-ico'),
  grid = document.querySelectorAll('.grid'),
  audiocontainer = document.querySelector('.audio-container'),
  gridcontainer = document.querySelectorAll('.audio-container > .grid');

  // categories-section

  if( body.classList.contains('post-type-archive-audio') ){ 
    
  const   quranbtn = document.querySelector('.categories').childNodes[5],
  anachidbtn = document.querySelector('.categories').childNodes[3],
  mawalbtn = document.querySelector('.categories').childNodes[7],
  adanbtn = document.querySelector('.categories').childNodes[1];

  homeico.addEventListener("click", home);

  quranbtn.addEventListener("click", e => {
    e.preventDefault();
    categorytxt.innerText = quranbtn.textContent;
    quranico.style = 'display : block !important ;';
    adanico.style = 'display : none !important ;';
    mawalico.style = 'display : none !important ;';
    sawtiyatico.style = 'display : none !important ;';
    nachidico.style = 'display : none !important ;';
  });

  mawalbtn.addEventListener("click", e => {
    e.preventDefault();
    categorytxt.innerText = mawalbtn.textContent;
    mawalico.style = 'display : block !important ;';
    quranico.style = 'display : none !important ;';
    adanico.style = 'display : none !important ;';
    sawtiyatico.style = 'display : none !important ;';
    nachidico.style = 'display : none !important ;';
  })

  adanbtn.addEventListener("click", e => {
    e.preventDefault();
    categorytxt.innerText = adanbtn.textContent;
    adanico.style = 'display : block !important ;';
    quranico.style = 'display : none !important ;';
    mawalico.style = 'display : none !important ;';
    sawtiyatico.style = 'display : none !important ;';
    nachidico.style = 'display : none !important ;';
  });

  anachidbtn.addEventListener("click", e => {
    e.preventDefault();
    categorytxt.innerText = anachidbtn.textContent;
    nachidico.style = 'display : block !important ;';
    adanico.style = 'display : none !important ;';
    mawalico.style = 'display : none !important ;';
    sawtiyatico.style = 'display : none !important ;';
    quranico.style = 'display : none !important ;';
  });

  function home() {
    categorytxt.innerText = 'صَوتِيات';
    sawtiyatico.style = 'display : block !important ;';
    quranico.style = 'display : none !important ;';
    adanico.style = 'display : none !important ;';
    mawalico.style = 'display : none !important ;';
    nachidico.style = 'display : none !important ;';
    //  for (let i = 0; i < grid.length; i++) {
    //   audiocontainer.appendChild(grid[i]); 
    //  }
  }
  }

  

  let mouseDown = false;

  let arr = [
    playbtn,
    stopbtn,
    pausebtn,
    repeatbtn,
    volumebtn,
    volumemute,
    volumeslider,
    music_player_container,
    cdpause,
    song,
    fillbar,
    handle,
    startime,
    endtime,
    seekbar,
  ];

  for (let i = 0; i < song.length; i++) {

    seekbar[i].addEventListener("mousedown", e => {

      if (seekbar[i] == e.target) {

        song[i].setAttribute("preload", "true");

        song[i].addEventListener("loadedmetadata", () => {

          function clamp(min, val, max) {
            return Math.min(Math.max(min, val), max);
          }

          function getP(e) {
            let p = (e.clientX - seekbar[i].offsetLeft) / seekbar[i].clientWidth;
            p = clamp(0, p, 1);
            return p;
          }

          let p = getP(e);

          fillbar[i].style.width = p * 100 + "%";

          song[i].currentTime = p * song[i].duration;

          function convertTime(seconds) {
            let min = Math.floor(seconds / 60);
            let sec = seconds % 60;

            min = min < 10 ? "0" + min : min;
            sec = sec < 10 ? "0" + sec : sec;
            // startime[i].textContent = min + ":" + sec;

            totalTime(Math.round(song[i].duration));
          }

          function totalTime(seconds) {
            let min = Math.floor(seconds / 60);
            let sec = seconds % 60;

            min = min < 10 ? "0" + min : min;
            sec = sec < 10 ? "0" + sec : sec;

            endtime[i].textContent = min + ":" + sec;

            totalTime(Math.round(song[i].duration));
          }

          song[i].addEventListener("timeupdate", function () {

            convertTime(Math.round(song[i].currentTime));

            convertTime(Math.round(song[i].duration));

            if (mouseDown) return;

            let p = song[i].currentTime / song[i].duration;

            fillbar[i].style.width = p * 100 + "%";

            if (song[i].currentTime == song[i].duration) {
              console.log('song duration reatched !');
              setTimeout(() => {
                fillbar[i].style.width = 0;
                fillbar[i].style.transition = '.2s ease-in-out';
                startime[i].textContent = "00:00";
              }, 2000);
            }
          });

          stopbtn[i].addEventListener("click", () => {
            fillbar[i].style.width = 0;
            fillbar[i].style.transition = '.5s ease-in-out';
            startime[i].textContent = "00:00";
          });

          song[i].addEventListener("timeupdate", function () {
          });
        });
      }
    });
  }


  for (let i = 0; i < arr.length; i++) {

    music_player_container[i].addEventListener('contextmenu', e =>{
      e.preventDefault();
    });

    cdpause[i].style = "animation-play-state: paused;";

    playbtn[i].addEventListener("click", () => {
      music_player_container[i].classList.add("is-playing");
      pausebtn[i].style = "display :block !important";
      cdpause[i].style = "animation-play-state:running;";
      playbtn[i].replaceWith(pausebtn[i]);
      song[i].play();
    });

    pausebtn[i].addEventListener("click", () => {
      music_player_container[i].classList.add("is-playing");
      pausebtn[i].replaceWith(playbtn[i]);
      cdpause[i].style = "animation-play-state: paused;";
      song[i].pause();
    });

    stopbtn[i].addEventListener("click", () => {

      music_player_container[i].classList.remove("is-playing");
      pausebtn[i].style = "display :block !important";
      pausebtn[i].replaceWith(playbtn[i]);
      song[i].pause();
      cdpause[i].style = "animation: none;";
      song[i].currentTime = 0;
      endtime[i].textContent = "00:00";
    });


    song[i].addEventListener("timeupdate", function () {
      convertTime(Math.round(song[i].currentTime));
      convertTime(Math.round(song[i].duration));
    });

    function convertTime(seconds) {
      let min = Math.floor(seconds / 60);
      let sec = seconds % 60;

      min = min < 10 ? "0" + min : min;
      sec = sec < 10 ? "0" + sec : sec;
      startime[i].textContent = min + ":" + sec;

      totalTime(Math.round(song[i].duration));
    }

    function totalTime(seconds) {
      let min = Math.floor(seconds / 60);
      let sec = seconds % 60;

      min = min < 10 ? "0" + min : min;
      sec = sec < 10 ? "0" + sec : sec;

      endtime[i].textContent = min + ":" + sec;

      totalTime(Math.round(song[i].duration));
    }


    repeatbtn[i].addEventListener("click", () => {
      switch (song[i].loop) {
        case true:
          repeatbtn[i].style = "color :#2e850c !important";
          song[i].loop = false;
          break;

        default:
          song[i].loop = true;
          repeatbtn[i].style =
            "color: rgb(241, 197, 2) !important ; filter: brightness(1) !important;";
          break;
      }
    });


    var c;

    // Volume bar

    volumeslider[i].addEventListener("input", () => {
      c = volumeslider[i].value;
      song[i].volume = volumeslider[i].value;
      if (volumeslider[i].value == 0) {
        volumemute[i].style =
          "filter: brightness(1) !important;display :block !important";
        volumebtn[i].replaceWith(volumemute[i]);
      } else volumemute[i].replaceWith(volumebtn[i]);
      song[i].muted = false;
    });

    volumebtn[i].addEventListener("click", () => {
      volumemute[i].style =
        "filter: brightness(1) !important;display :block !important";
      song[i].muted = true;
      volumebtn[i].replaceWith(volumemute[i]);
      volumeslider[i].value = 0;
    });

    volumemute[i].addEventListener("click", () => {
      song[i].muted = false;
      volumemute[i].replaceWith(volumebtn[i]);
      volumeslider[i].value = c;
    });

    setTimeout(() => {
      volumebtn[i].addEventListener("mouseover", () => {
        volumeslider[i].style = " opacity:1 ";
      });
      volumemute[i].addEventListener("mouseover", () => {
        volumeslider[i].style = "opacity:1 ";
      });

      volumebtn[i].addEventListener("mouseleave", () => {
        volumeslider[i].style = "  opacity:0 ; transition: all .8s ease-in-out ";
      });

      volumemute[i].addEventListener("mouseleave", () => {
        volumeslider[i].style = "  opacity:0 ; transition: all .8s ease-in-out ";
      });

      volumeslider[i].addEventListener("mouseover", () => {
        volumeslider[i].style = "  opacity:1;  ";
      });
      volumeslider[i].addEventListener("mouseleave", () => {
        volumeslider[i].style = "  opacity:0; transition: all .8s ease-in-out ";
      });
    }, 600);

    song[i].addEventListener("timeupdate", function () {
      if (mouseDown) return;

      let p = song[i].currentTime / song[i].duration;

      fillbar[i].style.width = p * 100 + "%";

      if (song[i].currentTime == song[i].duration) {
        console.log('song duration reatched !');
        setTimeout(() => {
          fillbar[i].style.width = 0;
          fillbar[i].style.transition = '.5s ease-in-out';
          startime[i].textContent = "00:00";

        }, 5000);
      }
    });

    function clamp(min, val, max) {
      return Math.min(Math.max(min, val), max);
    }

    function getP(e) {
      let p = (e.clientX - seekbar[i].offsetLeft) / seekbar[i].clientWidth;
      p = clamp(0, p, 1);
      return p;
    }

    document.addEventListener('play', function (e) {

      if (song[i] != e.target) {

        pausebtn[i].replaceWith(playbtn[i]);
        cdpause[i].style = "animation-play-state: paused;";
        song[i].pause();

      }
    }, true);

    seekbar[i].addEventListener("mousedown", function (e) {
      mouseDown = true;

      let p = getP(e);

      fillbar[i].style.width = p * 100 + "%";

      song[i].currentTime = p * song[i].duration;

      console.log(e.type);

    });

    seekbar[i].addEventListener("touchstart", function (e) {

      console.log(e.type)
      console.log('touchstart')

    });
    seekbar[i].addEventListener("touchend", function (e) {

      console.log(e.type)
      console.log('touchend')

    });
    seekbar[i].addEventListener("touchmove", function (e) {

      console.log(e.type)
      console.log('touchmove')
      

    });

    seekbar[i].addEventListener("mousemove", function (e) {
      if (!mouseDown) return;

      let p = getP(e);

      fillbar[i].style.width = p * 100 + "%";
      console.log(e.type);
    });



    seekbar[i].addEventListener("mouseup", function (e) {
      if (!mouseDown) return;

      mouseDown = false;

      let p = getP(e);

      fillbar[i].style.width = p * 100 + "%";

      song[i].currentTime = p * song[i].duration;

      console.log(e.type);

    });

    song[i].addEventListener("ended", function () {
      music_player_container[i].classList.remove("is-playing");
      pausebtn[i].style = "display :block !important";
      pausebtn[i].replaceWith(playbtn[i]);
    });
  }
}

audio();








