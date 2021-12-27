// dropdown menu
document.querySelector('body').addEventListener('click', (e) => {
    const targetElement = e.target;

    if (targetElement.classList.contains('dropdown-toggle')) {
        e.preventDefault();

        const dropdownMenuElement = document.querySelector(targetElement.getAttribute('target'));
        dropdownMenuElement.classList.toggle('d-none');
    }
});

// navbar collapse
const navbarTogglerElement = document.querySelector('.navbar a.btn--toggler');
const navbarCollapseElement = document.querySelector('.navbar__right--collapse');
if (navbarTogglerElement != null) {
    navbarTogglerElement.addEventListener('click', (e) => {
        e.preventDefault();

        navbarCollapseElement.classList.toggle('navbar__right--collapse-show');
    });
}

// alert close
document.querySelector('main.main').addEventListener('click', (e) => {
    if (e.target.classList.contains('alert__close')) { e.preventDefault(); e.target.parentElement.remove(); }
});
