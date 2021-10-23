// show modal
function showModal(modal, modalContent)
{
    modal.classList.add('d-block');
    setTimeout(() => {
        modal.classList.add('modal--fade-in');
    }, 50);

    setTimeout(() => {
        modalContent.classList.add('modal__content--animate-show');
    }, 200);
}

// hide modal
function hideModal(modal, modalContent)
{
    modalContent.classList.replace('modal__content--animate-show', 'modal__content--animate-hide');
    setTimeout(() => {
        modalContent.classList.remove('modal__content--animate-hide');
        modal.classList.replace('modal--fade-in', 'modal--fade-out');
    }, 100);

    setTimeout(() => {
        modal.classList.remove('modal--fade-out');
        modal.classList.remove('modal--show');
        modal.classList.remove('d-block');;
    }, 200);
}

// add form input magnitude and price
function addFormInputMagnitudePrice(targetAppend)
{
    const formMagnitudePriceElement = document.createElement('div');
    formMagnitudePriceElement.classList.add('mt-3');
    formMagnitudePriceElement.innerHTML = `<div class="input-group">
        <input class="form-input" type="text" placeholder="Besaran..." name="product_magnitudes[]">
        <input class="form-input" type="number" placeholder="Harga..." name="product_prices[]">
        <a class="btn btn--gray-outline" id="remove-form-input-magnitude-price" href="#">Hapus</a>
    </div>`;

    // append new form magnitude price to targetAppend
    targetAppend.append(formMagnitudePriceElement);
}

function showPassword(e)
{
    e.preventDefault();

    let target = e.target;
    if(!/^show-password.*/.test(target.getAttribute('id'))) target = target.parentElement;
    if(!/^show-password.*/.test(target.getAttribute('id'))) target = target.parentElement;

    if(/^show-password.*/.test(target.getAttribute('id'))) {
        const input = target.previousElementSibling;
        if(input.getAttribute('type') === 'password') {
            input.setAttribute('type','text');
            target.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2z"/></svg>`;
        } else {
            input.setAttribute('type','password');
            target.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg>`;
        }
    }
}

function createAlertNode(alertClasses, message)
{
    const alertElement = document.createElement('div');
    alertElement.classList.add('alert');
    for (const ac of alertClasses) {
        alertElement.classList.add(ac);
    }

    alertElement.innerHTML = `<span class="alert__icon"></span>
    <p>${message}</p>
    <a class="alert__close" href="#"></a>`;

    return alertElement;
}

// number formatter currency
function numberFormatterToCurrency(number)
{
    return number.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0});
}

export {
    showModal,
    hideModal,
    addFormInputMagnitudePrice,
    showPassword,
    createAlertNode,
    numberFormatterToCurrency
};
