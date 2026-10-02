/**
*
*
*/

/* Sticky Header */
const THRESHOLD = 10;
const HIDE_AFTER = 400;

let lastScrollTop = window.scrollY || 0;

window.addEventListener('scroll', () => {
    const currentScrollTop = window.scrollY || 0;
    const diff = currentScrollTop - lastScrollTop;

    // ignore tiny movements (jitter)
    if (Math.abs(diff) < THRESHOLD) return;

    const header = document.querySelector('.header-main');
    const nav = document.querySelector('.nav-main');
    if (!header || !nav) return;

    // scrolling down + past 400 => hide (add class)
    if (diff > 0 && currentScrollTop > HIDE_AFTER) {
        header.classList.add('not-sticky');
        nav.classList.add('not-sticky');
    }

    // scrolling up => show (remove class)
    if (diff < 0) {
        header.classList.remove('not-sticky');
        nav.classList.remove('not-sticky');
    }

    lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
}, { passive: true });
/* ==================== */

/* Cookies setting button */
document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.querySelector('.js-cookie-trigger');

    if (!trigger) return;

    trigger.addEventListener('click', function (e) {
        e.preventDefault();

        const cookiesSettingButton = document.querySelector('.js-cookie-configuration-button button');
        if (!cookiesSettingButton) return;

        cookiesSettingButton.click();
    });
});
