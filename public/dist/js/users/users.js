import { showModal, hideModal, showPassword, renderAlert, postData } from '../module.js';

// delete user
const tableElement  = document.querySelector('#table');
const modalElement = document.querySelector('.modal');
const modalContentElement = modalElement.querySelector('.modal__content');

const tbodyElement = table.querySelector('tbody');
tbodyElement.addEventListener('click', (e) => {
    let targetElement = e.target;

    if (targetElement.getAttribute('id') !== 'delete-user') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') !== 'delete-user') targetElement = targetElement.parentElement;

    if (targetElement.getAttribute('id') == 'delete-user') {
        e.preventDefault();

        // show modal
        showModal(modalElement, modalContentElement);

        // append data for delete user in modal
        modalContentElement.querySelector('input[name="user_id"]').value = targetElement.dataset.userId;
        modalContentElement.querySelector('.modal__body p strong').innerText = targetElement.dataset.fullName;
    }
});

// close modal
modalContentElement.querySelector('#btn-close').addEventListener('click', (e) => {
    e.preventDefault();

    // hide modal
    hideModal(modalElement, modalContentElement);

    // reset modal
    modalContentElement.querySelector('input[name="user_sign_in_password"]').value = '';
    const smallElement = modalContentElement.querySelector('small.form-message')
    if (smallElement != null) {
        smallElement.remove();
    }
});

// delete user
modalContentElement.querySelector('#delete-user').addEventListener('click', async (e) => {
    e.preventDefault();

    const baseUrl = document.querySelector('html').dataset.baseUrl;
    const userSignInPassword = modalContentElement.querySelector('input[name="user_sign_in_password"]').value;
    const userId = modalContentElement.querySelector('input[name="user_id"]').value;
    const csrfName = table.dataset.csrfName;
    const csrfValue = table.dataset.csrfValue;

    // reset message form
    const small = modalContentElement.querySelector('small.form-message');
    if (small !== null) {
        small.remove();
    }

    // loading
    e.target.classList.add('btn--disabled');
    e.target.nextElementSibling.classList.remove('d-none');
    
    try {
        const responseJson = await postData(
            `${baseUrl}/admin/user/delete/soft`,
            `${csrfName}=${csrfValue}&user_id=${userId}&user_sign_in_password=${userSignInPassword}`
        );

        // set new csrf hash to table tag
        if (responseJson.csrf_value != undefined) {
            table.dataset.csrfValue = responseJson.csrf_value;
        }

        // if success remove user
        if (responseJson.status == 'success') {
            document.querySelector(`tr#user${userId}`).remove();
        }
        // else if password sign in user is wrong
        else if (responseJson.status == 'wrong_password') {
            const small = document.createElement('small');
            small.classList.add('form-message');
            small.classList.add('form-message--danger');
            small.innerText = responseJson.message;

            // append message to modal
            modalContentElement.querySelector('div.modal__body').append(small);
        }
        // else if fail remove user
        else if (responseJson.status == 'fail') {
            const parentElement = document.querySelector('main.main');
            const referenceElement = document.querySelector('div.main__box');
            renderAlert(parentElement, referenceElement, responseJson.message, [
                'alert--warning',
                'mb-3'
            ]);
        }

        if (responseJson.status == 'success' || responseJson.status == 'fail') {
            // hide modal
            hideModal(modalElement, modalContentElement);
            // reset modal
            modalContentElement.querySelector('input[name="user_sign_in_password"]').value = '';
        }
    } catch (error) {
        console.error(error);
    }

    // hide loading
    e.target.classList.remove('btn--disabled');
    e.target.nextElementSibling.classList.add('d-none');
});

// show password
document.querySelector('.modal #show-password').addEventListener('click', showPassword);
