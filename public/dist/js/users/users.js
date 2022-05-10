import { showModal, hideModal, showPassword, renderAlert, postData } from '../module.js';

// delete user
const tableElement  = document.querySelector('#table');
const modalElement = document.querySelector('.modal');
const modalContentElement = modalElement.querySelector('.modal__content');

const tbodyElement = table.querySelector('tbody');
tbodyElement.addEventListener('click', (e) => {
  let targetElement = e.target;

  if (targetElement.getAttribute('id') != 'show-modal-delete') targetElement = targetElement.parentElement;
  if (targetElement.getAttribute('id') != 'show-modal-delete') targetElement = targetElement.parentElement;

  if (targetElement.getAttribute('id') == 'show-modal-delete') {
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
  const smallElement = modalContentElement.querySelector('small.form-message');
  if (smallElement != null) {
    smallElement.remove();
  }
});

// delete user
modalContentElement.querySelector('#delete').addEventListener('click', async (e) => {
  e.preventDefault();

  const loadingElement = document.querySelector('#loading');
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
  loadingElement.classList.remove('d-none');
  
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
      const smallElement = document.createElement('small');
      smallElement.classList.add('form-message');
      smallElement.classList.add('form-message--danger');
      smallElement.innerText = responseJson.message;

      // append message to modal
      modalContentElement.querySelector('div.modal__body').append(smallElement);
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
  loadingElement.classList.add('d-none');
});

// show password
document.querySelector('.modal #show-password').addEventListener('click', showPassword);
