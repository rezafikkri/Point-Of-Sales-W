// dropdown menu
const navElement = document.querySelector("nav.navbar");
navElement.addEventListener('click', e => {
    const target = e.target;

    if(target.classList.contains('dropdown-toggle')) {
        e.preventDefault();

        const dropdownMenuElement = document.querySelector(target.getAttribute('target'));
        dropdownMenuElement.classList.toggle('d-none');
        dropdownMenuElement.previousElementSibling.classList.toggle('navbar__link--active');

    }
});

// navbar collapse
const navbarTogglerElement = document.querySelector('.navbar a.btn--toggler');
const navbarCollapseElement = document.querySelector('.navbar__right--collapse');
if(navbarTogglerElement !== null) {
    navbarTogglerElement.addEventListener('click', e => {
        e.preventDefault();

        navbarCollapseElement.classList.toggle('navbar__right--collapse-show');
    });
}

// alert close
document.querySelector('main.main').addEventListener('click', e => {
    if (e.target.classList.contains('alert__close')) { e.preventDefault(); e.target.parentElement.remove(); }
});
