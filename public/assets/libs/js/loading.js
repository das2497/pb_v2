window.addEventListener("load", function () {
    document.getElementById("preloader").style.display = "none";
});

window.addEventListener("beforeunload", function () {
    // Show loader immediately when page starts loading
    document.getElementById("preloader").style.display = "flex";
});

// Browser back/forward restores the page from bfcache instead of reloading
// it, so "load" never fires again - only "pageshow" with persisted=true does.
// Without this, the preloader stays stuck at display:flex (set by the
// beforeunload above, right before the page was cached) forever after a
// back/forward navigation.
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        document.getElementById("preloader").style.display = "none";
    }
});