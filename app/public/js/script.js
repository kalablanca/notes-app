document.getElementById("toggleNavbar").addEventListener("click", function () {
    console.log("clicked");
    const navbar = document.getElementById("slideNavbar");
    if (navbar.classList.contains("open")) {
        navbar.classList.remove("open");
        document.querySelector(".main").style.marginLeft = "0";
    } else {
        navbar.classList.add("open");
        document.querySelector(".main").style.marginLeft = "250px";
    }
});

document.getElementById("toggleNavbar").addEventListener("click", function () {
    const navbar = document.getElementById("slideNavbar");
    navbar.classList.add("open");
});

document.getElementById("closeNavbar").addEventListener("click", function () {
    const navbar = document.getElementById("slideNavbar");
    navbar.classList.remove("open");
});

