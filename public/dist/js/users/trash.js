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
  if (targetDeleteElement.getAttribute('id') != 'show-modal-delete') targetDeleteElement = targetDeleteElement.parentElement;
  if (targetDeleteElement.getAttribute('id') != 'show-modal-delete') targetDeleteElement = targetDeleteElement.parentElement;

  let targetRestoreElement = e.target;
  if (targetRestoreElement.getAttribute('id') != 'show-modal-restore') targetRestoreElement = targetRestoreElement.parentElement;
  if (targetRestoreElement.getAttribute('id') != 'show-modal-restore') targetRestoreElement = targetRestoreElement.parentElement;

  // if delete user button clicked
  if (targetDeleteElement.getAttribute('id') == 'show-modal-delete') {
    e.preventDefault();

    const modalElement = document.querySelector('#permanently-delete-modal');
    const modalContentElement = modalElement.querySelector('.modal__content');
    openModal(targetDeleteElement, modalElement, modalContentElement);
  }
  // if restore user button clicked
  else if (targetRestoreElement.getAttribute('id') == 'show-modal-restore') {
    e.preventDefault();

    const modalElement = document.querySelector('#restore-modal');
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
  let loadingElement = document.querySelector('#restore-loading');
  if (action == 'delete') {
    loadingElement = document.querySelector('#delete-loading');
  }
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
      baseUrl+path,
      `${csrfName}=${csrfValue}&user_id=${userId}&user_sign_in_password=${userSignInPassword}`
    );

    // set new csrf hash to table tag
    if (responseJson.csrf_value != undefined) {
      table.dataset.csrfValue = responseJson.csrf_value;
    }

    // if success remove user
    if (responseJson.status == 'success') {
      // remove user from table
      document.querySelector(`tr#user${userId}`).remove();
      // add description
      const usersTable = tbodyElement.querySelectorAll('tr');
      if (usersTable.length == 0) {
        tbodyElement.innerHTML = '<tr><td colspan="5">Pengguna tidak ada.</td></tr>';
      }
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

      let message = responseJson.message;
      if (action == 'delete') {
        message = `
          ${responseJson.message}
          <a href="https://github.com/rezafikkri/Point-Of-Sales-W/wiki/Pengguna#gagal-menghapus-permanen-pengguna"
          target="_blank" rel="noreferrer noopener">Pelajari lebih lanjut.</a>
        `;
      }

      renderAlert(parentElement, referenceElement, message, [
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
}

document.querySelector('#modals').addEventListener('click', (e) => {
  // find true target
  let targetCloseModalElement = e.target;
  if (targetCloseModalElement.getAttribute('id') != 'btn-close') targetCloseModalElement = targetCloseModalElement.parentElement;
  if (targetCloseModalElement.getAttribute('id') != 'btn-close') targetCloseModalElement = targetCloseModalElement.parentElement;

  let targetShowPasswordElement = e.target;
  if (targetShowPasswordElement.getAttribute('id') != 'show-password') targetShowPasswordElement = targetShowPasswordElement.parentElement;
  if (targetShowPasswordElement.getAttribute('id') != 'show-password') targetShowPasswordElement = targetShowPasswordElement.parentElement;

  let targetRestoreElement = e.target;
  if (targetRestoreElement.getAttribute('id') != 'restore') targetRestoreElement = targetRestoreElement.parentElement;
  if (targetRestoreElement.getAttribute('id') != 'restore') targetRestoreElement = targetRestoreElement.parentElement;

  let targetDeleteElement = e.target;
  if (targetDeleteElement.getAttribute('id') != 'delete') targetDeleteElement = targetDeleteElement.parentElement;
  if (targetDeleteElement.getAttribute('id') != 'delete') targetDeleteElement = targetDeleteElement.parentElement;
  
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
  else if (targetRestoreElement.getAttribute('id') == 'restore') {
    e.preventDefault();
    RDUser(targetRestoreElement, '/admin/user/restore', 'restore');
  }
  // if btn delete user clicked
  else if (targetDeleteElement.getAttribute('id') == 'delete') {
    e.preventDefault();
    RDUser(targetDeleteElement, '/admin/user/delete/hard', 'delete');
  }
});
