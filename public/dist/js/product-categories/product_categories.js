import { renderAlert, postData } from '../module.js';

// delete product category
const tableElement = document.querySelector('#table');
const tbodyElement = tableElement.querySelector('tbody');
tbodyElement.addEventListener('click', async (e) => {
  let targetElement = e.target;

  if (targetElement.getAttribute('id') != 'delete') targetElement = targetElement.parentElement;
  if (targetElement.getAttribute('id') != 'delete') targetElement = targetElement.parentElement;

  if (targetElement.getAttribute('id') == 'delete') {
    e.preventDefault();

    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    // data for delete product category
    const productCategoryId = targetElement.dataset.productCategoryId;
    const csrfName = tableElement.dataset.csrfName;
    const csrfValue = tableElement.dataset.csrfValue;

    // show loading
    loadingElement.classList.remove('d-none');

    try {
      const responseJson = await postData(
        `${baseUrl}/admin/product-category/delete`,
        `${csrfName}=${csrfValue}&product_category_id=${productCategoryId}`
      );

      // set new csrf hash to table tag
      if (responseJson.csrf_value != undefined) {
        tableElement.dataset.csrfValue = responseJson.csrf_value;
      }

      // if success delete product category
      if (responseJson.status == 'success') {
        // remove product category from table
        targetElement.parentElement.parentElement.remove();
        // add description
        const productCategoriesTable = tbodyElement.querySelectorAll('tr');
        if (productCategoriesTable.length == 0) {
          tbodyElement.innerHTML = '<tr><td colspan="5">Pengguna tidak ada.</td></tr>';
        }
      }
      // else if fail delete product category
      else if (responseJson.status == 'fail') {
        const parentElement = document.querySelector('main.main');
        const referenceElement = document.querySelector('div.main__box');
        const message = `
          ${responseJson.message}
          <a href="https://github.com/rezafikkri/Point-Of-Sales-W/wiki/Kategori-Produk#gagal-menghapus-kategori-produk"
          target="_blank" rel="noreferrer noopener">Pelajari lebih lanjut.</a>
        `;
        renderAlert(parentElement, referenceElement, message, [
          'alert--warning',
          'mb-3'
        ]);
      }
    } catch (error) {
      console.error(error);
    }

    // hide loading
    loadingElement.classList.add('d-none');
  }
});
