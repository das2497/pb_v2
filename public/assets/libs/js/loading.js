window.addEventListener("load", function () {
    document.getElementById("preloader").style.display = "none";
});

window.addEventListener("beforeunload", function () {
    // Show loader immediately when page starts loading
    document.getElementById("preloader").style.display = "flex";
});