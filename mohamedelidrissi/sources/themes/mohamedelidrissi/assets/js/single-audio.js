

const playbtn = document.querySelector(".fa-play");
const stopbtn = document.querySelector(".fa-stop");
const pausebtn = document.querySelector(".fa-pause");
const repeatbtn = document.querySelector(".fa-repeat-alt");
const volumemute = document.querySelector(".fa-volume-slash");
const music_player_container = document.querySelector(
  ".music-player-container"
);
const cdpause = document.querySelector(".vinyl");
const song = document.querySelector(".song");
const seekbar = document.querySelector(".seek-bar");
const fillbar = document.querySelector(".fill");
const handle = document.querySelector(".handle");
const startime = document.querySelector(".start-time");
const endtime = document.querySelector(".end-time");
const volumeslider = document.querySelector("input[type='range']");
const volumebtn = document.querySelector(".fa-volume-up");


const categorytxt = document.querySelector('.cat-text');


const titleico = document.createElement("i");

const quranico = document.querySelector('.quran-ico');


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
        quranico.style.display = 'block';
        console.log('quran detected');

      }
      else {
        titleico.classList.add('fad');
        titleico.classList.add('fa-music-alt');
        category_title.appendChild(titleico);

      }




seekbar.addEventListener("mousedown", e => {


  if (seekbar == e.target) {

    song.setAttribute("preload", "true");

    song.addEventListener("loadedmetadata", () => {

      function clamp(min, val, max) {
        return Math.min(Math.max(min, val), max);
      }

      function getP(e) {
        let p = (e.clientX - seekbar.offsetLeft) / seekbar.clientWidth;
        p = clamp(0, p, 1);
        return p;
      }


      let p = getP(e);

      fillbar.style.width = p * 100 + "%";

      song.currentTime = p * song.duration;


      function convertTime(seconds) {
        let min = Math.floor(seconds / 60);
        let sec = seconds % 60;

        min = min < 10 ? "0" + min : min;
        sec = sec < 10 ? "0" + sec : sec;
        // startime[i].textContent = min + ":" + sec;

        totalTime(Math.round(song.duration));
      }



      function totalTime(seconds) {
        let min = Math.floor(seconds / 60);
        let sec = seconds % 60;

        min = min < 10 ? "0" + min : min;
        sec = sec < 10 ? "0" + sec : sec;

        endtime.textContent = min + ":" + sec;

        totalTime(Math.round(song.duration));
      }

      song.addEventListener("timeupdate", function () {

        convertTime(Math.round(song.currentTime));

        convertTime(Math.round(song.duration));

        if (mouseDown) return;

        let p = song.currentTime / song.duration;


        fillbar.style.width = p * 100 + "%";

        if (song.currentTime == song.duration) {
          console.log('song duration reatched !');
          setTimeout(() => {
            fillbar.style.width = 0;
            fillbar.style.transition = '.2s ease-in-out';
            startime.textContent = "00:00";

          }, 2000);
        }


      });

      stopbtn.addEventListener("click", () => {

        fillbar.style.width = 0;
        fillbar.style.transition = '.5s ease-in-out';
        startime.textContent = "00:00";


      });

    });

  }

});

let mouseDown = false;

music_player_container.addEventListener('contextmenu', e =>{
  e.preventDefault();
})

playbtn.addEventListener("click", () => {
  music_player_container.classList.add("is-playing");
  pausebtn.style = "display :block !important";
  cdpause.style = "animation-play-state:running;";
  playbtn.replaceWith(pausebtn);
  song.play();
});

pausebtn.addEventListener("click", () => {
  music_player_container.classList.add("is-playing");
  pausebtn.replaceWith(playbtn);
  cdpause.style = "animation-play-state: paused; ";
  song.pause();
});

stopbtn.addEventListener("click", () => {
  music_player_container.classList.remove("is-playing");
  pausebtn.style = "display :block !important";
  pausebtn.replaceWith(playbtn);
  song.pause();
  song.currentTime = 0;
});

song.addEventListener("timeupdate", function () {
  convertTime(Math.round(song.currentTime));

  convertTime(Math.round(song.duration));
});

function convertTime(seconds) {
  let min = Math.floor(seconds / 60);
  let sec = seconds % 60;

  min = min < 10 ? "0" + min : min;
  sec = sec < 10 ? "0" + sec : sec;
  startime.textContent = min + ":" + sec;

  totalTime(Math.round(song.duration));
}

function totalTime(seconds) {
  let min = Math.floor(seconds / 60);
  let sec = seconds % 60;

  min = min < 10 ? "0" + min : min;
  sec = sec < 10 ? "0" + sec : sec;

  endtime.textContent = min + ":" + sec;

  totalTime(Math.round(song.duration));
}

repeatbtn.addEventListener("click", () => {
  switch (song.loop) {
    case true:
      repeatbtn.style = "color :#2e850c !important";
      song.loop = false;
      break;

    default:
      song.loop = true;
      repeatbtn.style =
        "color: rgb(241, 197, 2) !important ; filter: brightness(1) !important;";
      break;
  }
});

var c;

// Volume bar

volumeslider.addEventListener("input", () => {
  c = volumeslider.value;
  song.volume = volumeslider.value;
  if (volumeslider.value == 0) {
    volumemute.style =
      "filter: brightness(1) !important;display :block !important";
    volumebtn.replaceWith(volumemute);
  } else volumemute.replaceWith(volumebtn);
  song.muted = false;
});

volumebtn.addEventListener("click", () => {
  volumemute.style =
    "filter: brightness(1) !important;display :block !important";
  song.muted = true;
  volumebtn.replaceWith(volumemute);
  volumeslider.value = 0;
});

volumemute.addEventListener("click", () => {
  song.muted = false;
  volumemute.replaceWith(volumebtn);
  volumeslider.value = c;
});

setTimeout(() => {
  volumebtn.addEventListener("mouseover", () => {
    volumeslider.style = " opacity:1 ";
  });
  volumemute.addEventListener("mouseover", () => {
    volumeslider.style = "opacity:1 ";
  });

  volumebtn.addEventListener("mouseleave", () => {
    volumeslider.style = "  opacity:0 ; transition: all .8s ease-in-out ";
  });

  volumemute.addEventListener("mouseleave", () => {
    volumeslider.style = "  opacity:0 ; transition: all .8s ease-in-out ";
  });

  volumeslider.addEventListener("mouseover", () => {
    volumeslider.style = "  opacity:1;  ";
  });
  volumeslider.addEventListener("mouseleave", () => {
    volumeslider.style = "  opacity:0; transition: all .8s ease-in-out ";
  });
}, 600);

song.addEventListener("timeupdate", function () {
  if (mouseDown) return;

  let p = song.currentTime / song.duration;

  fillbar.style.width = p * 100 + "%";

  if (song.currentTime == song.duration) {
    console.log('song duration reatched !');
    setTimeout(() => {
      fillbar.style.width = 0;
      fillbar.style.transition = '.5s ease-in-out';
      startime.textContent = "00:00";
      endtime.textContent = "00:00";
    }, 5000);
  }


});

function clamp(min, val, max) {
  return Math.min(Math.max(min, val), max);
}

function getP(e) {
  let p = (e.clientX - seekbar.offsetLeft) / seekbar.clientWidth;
  p = clamp(0, p, 1);
  return p;
}

seekbar.addEventListener("mousedown", function (e) {
  mouseDown = true;

  let p = getP(e);

  fillbar.style.width = p * 100 + "%";
});

seekbar.addEventListener("mousemove", function (e) {
  if (!mouseDown) return;

  let p = getP(e);

  fillbar.style.width = p * 100 + "%";
});

seekbar.addEventListener("mouseup", function (e) {
  if (!mouseDown) return;

  mouseDown = false;

  let p = getP(e);

  fillbar.style.width = p * 100 + "%";

  song.currentTime = p * song.duration;
});

song.addEventListener("ended", function () {
  music_player_container.classList.remove("is-playing");
  pausebtn.style = "display :block !important";
  pausebtn.replaceWith(playbtn);
});


















