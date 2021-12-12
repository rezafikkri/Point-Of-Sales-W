import {
    showModal,
    hideModal,
    changeInputTypeLockIcon,
    renderAlert,
    postData
} from '../module.js';

const tableElement  = document.querySelector('#table');

function openModal(targetElement, modalElement, modalContentElement)
{
    // show modal
    showModal(modalElement, modalContentElement);
    // append data for delete or restore user in modal
    modalContentElement.querySelector('input[name="user_id"]').value = targetElement.dataset.userId;
    modalContentElement.querySelector('.modal__body p strong').innerText = targetElement.dataset.fullName;
}

const tbodyElement = tableElement.querySelector('tbody');
tbodyElement.addEventListener('click', (e) => {
    // find true target
    let targetDeleteElement = e.target;
    if (targetDeleteElement.getAttribute('id') != 'delete-user') targetDeleteElement = targetDeleteElement.parentElement;
    if (targetDeleteElement.getAttribute('id') != 'delete-user') targetDeleteElement = targetDeleteElement.parentElement;

    let targetRestoreElement = e.target;
    if (targetRestoreElement.getAttribute('id') != 'restore-user') targetRestoreElement = targetRestoreElement.parentElement;
    if (targetRestoreElement.getAttribute('id') != 'restore-user') targetRestoreElement = targetRestoreElement.parentElement;

    // if delete user button clicked
    if (targetDeleteElement.getAttribute('id') == 'delete-user') {
        e.preventDefault();

        const modalElement = document.querySelector('#permanently-delete-user-modal');
        const modalContentElement = modalElement.querySelector('.modal__content');
        openModal(targetDeleteElement, modalElement, modalContentElement);
    }
    // if restore user button clicked
    else if (targetRestoreElement.getAttribute('id') == 'restore-user') {
        e.preventDefault();

        const modalElement = document.querySelector('#restore-user-modal');
        const modalContentElement = modalElement.querySelector('.modal__content');
        openModal(targetRestoreElement, modalElement, modalContentElement);
    }
});

function closeModal(modalElement, modalContentElement)
{
    // hide modal
    hideModal(modalElement, modalContentElement);

    // reset modal
    modalContentElement.querySelector('input[name="user_sign_in_password"]').value = '';
    const smallElement = modalContentElement.querySelector('small.form-message');
    if (smallElement != null) {
        smallElement.remove();
    }
}

async function RDUser(targetElement, path, action)
{
    const modalContentElement = targetElement.parentElement.parentElement;
    const modalElement = modalContentElement.parentElement;
    const baseUrl = document.querySelector('html').dataset.baseUrl;
    const userSignInPassword = modalContentElement.querySelector('input[name="user_sign_in_password"]').value;
    const userId = modalContentElement.querySelector('input[name="user_id"]').value;
    const csrfName = table.dataset.csrfName;
    const csrfValue = table.dataset.csrfValue;

    // reset message form
    const small = modalContentElement.querySelector('small.form-message');
    if (small != null) {
        small.remove();
    }

    // show loading
    targetElement.classList.add('btn--disabled');
    targetElement.nextElementSibling.classList.remove('d-none');
   
    try {
        const responseJson = await postData(
            baseUrl+path,
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
            const parentElement = document.querySelector('main.main > div');
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
    targetElement.classList.remove('btn--disabled');
    targetElement.nextElementSibling.classList.add('d-none');
}

// close modal
document.querySelector('#modals').addEventListener('click', (e) => {
    // find true target
    let targetCloseModalElement = e.target;
    if (targetCloseModalElement.getAttribute('id') != 'btn-close') targetCloseModalElement = targetCloseModalElement.parentElement;
    if (targetCloseModalElement.getAttribute('id') != 'btn-close') targetCloseModalElement = targetCloseModalElement.parentElement;

    let targetShowPasswordElement = e.target;
    if (targetShowPasswordElement.getAttribute('id') != 'show-password') targetShowPasswordElement = targetShowPasswordElement.parentElement;
    if (targetShowPasswordElement.getAttribute('id') != 'show-password') targetShowPasswordElement = targetShowPasswordElement.parentElement;

    let targetRestoreElement = e.target;
    if (targetRestoreElement.getAttribute('id') != 'restore-user') targetRestoreElement = targetRestoreElement.parentElement;
    if (targetRestoreElement.getAttribute('id') != 'restore-user') targetRestoreElement = targetRestoreElement.parentElement;
    
    // if btn close modal clicked
    if (targetCloseModalElement.getAttribute('id') == 'btn-close') {
        e.preventDefault();

        const modalElement = targetCloseModalElement.parentElement.parentElement;
        const modalContentElement = targetCloseModalElement.parentElement;
        closeModal(modalElement, modalContentElement);
    }
    // if btn show password clicked
    else if (targetShowPasswordElement.getAttribute('id') == 'show-password') {
        e.preventDefault();
        changeInputTypeLockIcon(targetShowPasswordElement);
    }
    // if btn restore user clicked
    else if (targetRestoreElement.getAttribute('id') == 'restore-user') {
        e.preventDefault();
        RDUser(targetRestoreElement, '/admin/user/restore', 'restore');
    }
});
