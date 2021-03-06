import { addFormInputMagnitudePrice, renderAlert, postData } from '../module.js';

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

// delete product price
magnitudePriceElement.addEventListener('click', async (e) => {
  const targetElement = e.target;
  if (targetElement.getAttribute('id') == 'remove-form-input-magnitude-price') {
    e.preventDefault();

    // if product price id exist in buttom, delete product price in db
    if (targetElement.dataset.productPriceId != undefined) {
      const loadingElement = document.querySelector('#loading');
      const csrfName = document.querySelector('main.main').dataset.csrfName;
      const csrfInputElement = document.querySelector(`input[name=${csrfName}]`);
      const csrfValue = csrfInputElement.value;
      const productPriceId = targetElement.dataset.productPriceId;
      const baseUrl = document.querySelector('html').dataset.baseUrl;

      // show loading
      loadingElement.classList.remove('d-none');
      
      try {
        const responseJson = await postData(
          `${baseUrl}/admin/product/delete-product-price`,
          `${csrfName}=${csrfValue}&product_price_id=${productPriceId}`
        );

        // set new csrf hash to csrf input
        if (responseJson.csrf_value != undefined) {
          csrfInputElement.value = responseJson.csrf_value;
        }

        // if success delete product price
        if (responseJson.status == 'success') {
          targetElement.parentElement.parentElement.remove();
        }
        // else if fail delete product price
        else if (responseJson.status == 'fail') {
          const parentElement = document.querySelector('main.main > div > div');
          const referenceElement = document.querySelector('div.main__box');
          renderAlert(parentElement, referenceElement, responseJson.message, [
            'alert--warning',
            'mb-3'
          ]);
        }
      } catch (error) {
        console.error(error);
      }

      // hide loading
      loadingElement.classList.add('d-none');
    } else {
      // just remove product price form
      targetElement.parentElement.parentElement.remove();
    }
  }
});
