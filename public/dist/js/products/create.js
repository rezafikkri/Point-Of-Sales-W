import { addFormInputMagnitudePrice } from '../module.js';

// get file name and replace text in label with it
const formFileElement = document.querySelector('div.form-file input[type="file"]');
formFileElement.addEventListener('change', (e) => {
    e.target.nextElementSibling.innerText = e.target.files[0].name;
});

// add form input magnitude and price
const magnitudePriceElement = document.querySelector('div#magnitude-price');
document.querySelector('a#add-form-input-magnitude-price').addEventListener('click', (e) => {
    e.preventDefault();
    addFormInputMagnitudePrice(magnitudePriceElement);
});

// remove form input magnitude and price
magnitudePriceElement.addEventListener('click', (e) => {
    if(e.target.getAttribute('id') === 'remove-form-input-magnitude-price') {
        e.preventDefault();
        e.target.parentElement.parentElement.remove();
    }
});

