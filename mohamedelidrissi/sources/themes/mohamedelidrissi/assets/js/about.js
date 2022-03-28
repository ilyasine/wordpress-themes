window.onload = function () {

    fload();
    
}

function fload() {
    // animated fadeInLeft slow
    const golden3 = document.querySelector('.golden3') ;
    golden3.classList.add('animate__animated', 'animate__fadeInLeft','animate__slower');
    const box = document.querySelector('.box') ;
    box.classList.add('animate__animated', 'animate__fadeInRight','animate__slower');
    const golden3_2 = document.querySelector('.golden3-2') ;
    golden3_2.classList.add('animate__animated', 'animate__fadeInLeft','slow');

}

window.onscroll = function () {
    scrol();
 }
function scrol() {
 const golden3 = document.querySelector('.golden3') ;
 golden3.classList.add('animate__animated', 'animate__fadeInLeft','animate__slower');
 const golden3_2 = document.querySelector('.golden3-2') ;
 golden3_2.classList.add('animate__animated', 'animate__fadeInLeft','animate__slower');
 const golden4 = document.querySelector('.golden4') ;
 golden4.classList.add('animate__animated', 'animate__fadeInRight','animate__slower');
 const rissala = document.querySelector('.rissalacontain') ;
 rissala.classList.add('animate__animated', 'animate__fadeInLeft','animate__slower');
}






