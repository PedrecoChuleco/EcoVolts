// Replaces the React useScrollOpacity() hook: fades in a translucent
// backdrop behind the fixed header as the page scrolls down.
(function () {
    const headerBg = document.getElementById('header-bg');
    if (!headerBg) return;

    const FADE_DISTANCE = 120; // px of scroll to reach full opacity

    function updateHeaderOpacity() {
        const scrolled = window.scrollY || document.documentElement.scrollTop;
        const opacity = Math.min(scrolled / FADE_DISTANCE, 1);
        headerBg.style.opacity = opacity.toFixed(2);
    }

    window.addEventListener('scroll', updateHeaderOpacity, { passive: true });
    updateHeaderOpacity();
})();
