window.addEventListener('scroll', () => {
    let header = document.querySelector("header .navbar"),
        sticky = header.offsetTop;

    if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
})


