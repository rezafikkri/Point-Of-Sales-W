// show modal
function showModal(modal, modalContent)
{
    modal.classList.add('d-block');
    setTimeout(() => {
        modal.classList.add('modal--fade-in');
    }, 50);

    setTimeout(() => {
        modal.classList.remove('modal--fade-in');
        modal.classList.add('modal--show');
        modalContent.classList.add('modal__content--animate-show');
    }, 200);
}

// hide modal
function hideModal(modal, modalContent)
{
    modalContent.classList.replace('modal__content--animate-show', 'modal__content--animate-hide');
    setTimeout(() => {
        modalContent.classList.remove('modal__content--animate-hide');
        modal.classList.add('modal--fade-out');
    }, 100);

    setTimeout(() => {
        modal.classList.remove('modal--fade-out');
        modal.classList.remove('modal--show');
        modal.classList.remove('d-block');;
    }, 200);
}

// add form input magnitude and price
function addFormInputMagnitudePrice(targetElement)
{
    const formMagnitudePriceElement = document.createElement('div');
    formMagnitudePriceElement.classList.add('mt-3');
    formMagnitudePriceElement.innerHTML = `<div class="input-group">
        <input class="form-input" type="text" placeholder="Besaran..." name="product_magnitudes[]">
        <input class="form-input" type="number" placeholder="Harga..." name="product_prices[]">
        <a class="btn btn--gray-outline" id="remove-form-input-magnitude-price" href="#">Hapus</a>
    </div>`;

    // append new form magnitude price to targetElement
    targetElement.append(formMagnitudePriceElement);
}

// change password input type and lock icon for show password
function changeInputTypeLockIcon(targetElement)
{
    const inputElement = targetElement.previousElementSibling;
    if (inputElement.getAttribute('type') == 'password') {
        inputElement.setAttribute('type', 'text');
        targetElement.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2z"/></svg>`;
    } else {
        inputElement.setAttribute('type', 'password');
        targetElement.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg>`;
    }
}

function showPassword(e)
{
	let targetElement = e.target;

    if (targetElement.getAttribute('id') != 'show-password') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') != 'show-password') targetElement = targetElement.parentElement;
    
    if (targetElement.getAttribute('id') == 'show-password') {
	    e.preventDefault();
        changeInputTypeLockIcon(targetElement);
    }
}

function renderAlert(parentElement, referenceElement, message, alertClasses)
{
    const alertElement = document.createElement('div');
    alertElement.classList.add('alert');
    for (const ac of alertClasses) {
        alertElement.classList.add(ac);
    }

    alertElement.innerHTML = `<span class="alert__icon"></span>
    <p>${message}</p>
    <a class="alert__close" href="#"></a>`;

    parentElement.insertBefore(alertElement, referenceElement);
}

// number formatter currency
function numberFormatterToCurrency(number)
{
    return number.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2});
}

async function postData(url = '', data = '')
{
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    });
    return response.json();
}

// format number like 100k
function abbreviateNumber(number)
{
    // if number greater than 0 and smaller than 999
    if (number >= 0 && number <= 999) return number;

    const SISymbol = ['', 'k', 'M', 'G', 'T', 'P'];

    // tier is determine SI Symbol
    const tier = Math.floor(Math.log10(Math.abs(number)) / 3);

    // get suffix and determine scale
    const suffix = SISymbol[tier];
    const scale = Math.pow(10, tier * 3);

    // scale the number
    const scaled = number / scale;

    // format number and add suffix
    return Math.abs(scaled.toFixed(1)) + suffix;
}

export {
    showModal,
    hideModal,
    addFormInputMagnitudePrice,
    changeInputTypeLockIcon,
    showPassword,
    renderAlert,
    numberFormatterToCurrency,
    postData,
    abbreviateNumber
};
