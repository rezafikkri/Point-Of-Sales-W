import { showPassword } from './module.js';

function generatePassword(inputElement)
{
    const chr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let password = '';

    for(let i = 0; i < 8; i++) {
        password += chr[Math.floor(Math.random()*chr.length)];
    }

    inputElement.value = password;
}

// generate passsword when document loaded
const inputElement = document.querySelector('input[name="password"]')
generatePassword(inputElement);

// generate password when button clicked
document.querySelector('#generate-password').addEventListener('click', (e) => {
    e.preventDefault();

    generatePassword(inputElement);
});

// show password
document.querySelector('form').addEventListener('click', (e) => {
    let targetElement = e.target;

    if (targetElement.getAttribute('id') != 'show-password') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') != 'show-password') targetElement = targetElement.parentElement;
    
    if (targetElement.getAttribute('id') == 'show-password') {
        e.preventDefault();
        showPassword(targetElement);
    }
});
