import { 
  renderAlert,
  numberFormatterToCurrency,
  showModal,
  hideModal,
  postData,
  getData
} from './module.js';

const mainElement = document.querySelector('main.main');
const searchElement = document.querySelector('a#search');
const showCartElement = document.querySelector('a#show-cart');
const cartTableElement = document.querySelector('aside.cart table.table');
const cancelTransactionElement = document.querySelector('a#cancel-transaction');
const finishTransactionElement = document.querySelector('a#finish-transaction');

// change product price info
mainElement.addEventListener('change', (e) => {
  let targetElement = e.target;
  // if magnitude in product item is changed
  if (targetElement.getAttribute('name') == 'magnitude') {
    const productPrice = targetElement.selectedOptions[0].dataset.productPrice;
    targetElement.previousElementSibling.previousElementSibling.innerText = numberFormatterToCurrency(parseInt(productPrice));
  }
});

// show and hide product image
mainElement.addEventListener('click', (e) => {
  // find true target
  const targetShowElement = e.target;
  let targetHideElement = e.target;
  if (targetHideElement.getAttribute('id') != 'product-image') targetHideElement = targetHideElement.parentElement;

  // if product name is clicked
  if (targetShowElement.getAttribute('id') == 'product-name') {
    e.preventDefault();

    const productImageElement = targetShowElement.parentElement.parentElement.previousElementSibling;
    productImageElement.classList.add('d-flex');
    setTimeout(() => {
      productImageElement.classList.add('product__image--fade-in');
    }, 50);

    setTimeout(() => {
      productImageElement.classList.remove('product__image--fade-in');
      productImageElement.classList.add('product__image--show');
    }, 250);
  }

  // if product image is clicked
  if (targetHideElement.getAttribute('id') == 'product-image') {
    targetHideElement.classList.add('product__image--fade-out');
    setTimeout(() => {
      targetHideElement.classList.remove('product__image--fade-out');
      targetHideElement.classList.remove('product__image--show');
      targetHideElement.classList.remove('d-flex');
    }, 100);
  }
});

// search product
searchElement.addEventListener('click', async (e) => {
  e.preventDefault();

  const containerElement = mainElement.querySelector('div.container-xl');
  const keyword = document.querySelector('input[name="product_name_search"]').value;
  const baseUrl = document.querySelector('html').dataset.baseUrl;

  // if empty keyword
  if (keyword.trim() == '') {
    return false;
  }

  // loading and disable button search
  containerElement.innerHTML = `
    <div id="search-loading" class="d-flex justify-content-center align-items-center mt-4">
      <div class="loading"><div></div></div>
    </div>
  `;
  searchElement.classList.add('btn--disabled');

  try {
    const response = await fetch(`${baseUrl}/cashier/search/products/${keyword}`);
    const responseJson = await response.json();
    
    let product = '';
    // if product exists
    if (responseJson.products.length > 0) {
      product += `<span class="text-muted me-1 d-block mb-3" id="result-status">
        1 - ${responseJson.products.length} dari ${responseJson.total_product} Total produk hasil pencarian</span>`;

      product += '<h5 class="mb-2 main__title">Produk</h5><div class="product mb-4">';

      responseJson.products.forEach((p) => {
        product += `
          <div class="product__item" data-product-id="${p.product_id}">
          <div class="product__image" id="product-image">
            <img src="${baseUrl}/dist/images/product-photos/${p.product_photo}" alt="${p.product_name}" loading="lazy">
          </div>
          <div class="product__info">
            <p class="product__name mb-0"><a href="#" id="product-name">${p.product_name}</a></p>

            <div class="product__price">
            <span class="me-2">${p.product_prices[0].product_price_formatted}</span><span>/</span>
            <select name="magnitude">
        `;
        
        p.product_prices.forEach((pp) => {
          product += ` 
            <option data-product-price="${pp.product_price}" value="${pp.product_price_id}">${pp.product_magnitude}</option>
          `;
        });

        product += `
            </select>
            </div>
          </div>
          <div class="product__action">
            <input type="number" class="form-input" name="product_qty" placeholder="Jumlah..." min="1">
            <a class="btn" href="#" id="buy-rollback" title="Tambah ke keranjang belanja">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>
            </a>
          </div>
          </div><!-- product__item -->
        `;
      });
    }
    // if product not exists
    else {
      product += `
        <span class="text-muted me-1 d-block mb-3" id="result-status">0 Total produk hasil pencarian</span>
        <h5 class="mb-2 main__title">Produk</h5>
        <p>Produk tidak ada.</p>
      `;
    }

    // inner html product to container
    containerElement.innerHTML = product;

    const limitMessageElement = document.querySelector('span#limit-message');
    // add limit message if total product search > product limit && limit message not exists
    if (responseJson.total_product > responseJson.product_limit && (limitMessageElement == null || tableElement.dataset.showType == undefined)) {
      if (limitMessageElement != null) {
        // delete old limit message
        limitMessageElement.remove();
      }

      const spanElement = document.createElement('span');
      spanElement.classList.add('text-muted');
      spanElement.classList.add('d-block');
      spanElement.classList.add('mb-5');
      spanElement.setAttribute('id', 'limit-message');
      spanElement.innerHTML = `
        Hanya ${responseJson.product_limit} Produk terbaru yang ditampilkan,
        Pakai fitur <i>Pencarian</i> untuk hasil lebih spesifik!
      `;
      document.querySelector('div.product').after(spanElement);
    }
    // else if total product search <= product limit and limit message exists
    else if (responseJson.total_product <= responseJson.product_limit && limitMessageElement != null) {
      limitMessage.remove();
    }
  } catch (error) {
    console.error(error);
  }
  
  // enable search button
  searchElement.classList.remove('btn--disabled');
});

// update qty total and payment total in cart table
function updateTotalQtyPayment(cartTableElement, totalQty, totalPayment)
{
  cartTableElement.querySelector('td#total-qty').innerText = totalQty;
  cartTableElement.querySelector('td#total-qty').dataset.totalQty = totalQty;
  cartTableElement.querySelector('td#total-payment').innerText = numberFormatterToCurrency(totalPayment);
  cartTableElement.querySelector('td#total-payment').dataset.totalPayment = totalPayment;
}

// show transaction detail in cart table
function showTransactionDetails(cartTableElement, transactionDetails)
{
  let tr = '';
  let totalPayment = 0;
  let totalQty = 0;
  transactionDetails.forEach (td => {
    const payment = parseInt(td.product_price) * parseInt(td.product_quantity);
    tr += `<tr data-product-id="${td.product_id}" data-transaction-detail-id="${td.transaction_detail_id}">
      <td width="10"><a href="#" title="Hapus produk" id="delete-product"  class="text-hover-red">
        <svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg>
      </a></td>
      <td width="10"><a href="#" title="Tambah jumlah produk" id="add-product-qty">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm6.5 11.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/></svg>
      </a></td>
      <td width="10"><a href="#" title="Kurangi jumlah produk" id="reduce-product-qty">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm6.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/></svg>
      </a></td>
      <td>${td.product_name}</td>
      <td id="price" data-price="${td.product_price}" data-magnitude="${td.product_magnitude}">
        ${numberFormatterToCurrency(parseInt(td.product_price))} / ${td.product_magnitude}
      </td>
      <td id="qty" data-qty="${td.product_quantity}">${td.product_quantity}</td>
      <td id="payment" data-payment="${payment}">${numberFormatterToCurrency(payment)}</td>
    </tr>`;
    totalPayment += payment;
    totalQty += parseInt(td.product_quantity);
  });

  // inner html transaction detail to cart table tbody
  cartTableElement.querySelector('tbody').innerHTML = tr;

  // update total qty and total payment in cart table
  updateTotalQtyPayment(cartTableElement, totalQty, totalPayment);
}

// show cart
const cartElement = document.querySelector('aside.cart');
showCartElement.addEventListener('click', async (e) => {
  e.preventDefault();

  cartElement.classList.add('cart--animate-show');
  setTimeout(() => {
    cartElement.classList.remove('cart--animate-show');
    cartElement.classList.add('cart--show');

    // if window less than 991.98px add overflow hidden to body tag
    if(window.screen.width <= 991.98) {
      document.querySelector('body').classList.add('overflow-hidden');
    }
  }, 500);

  // if not exists dataset type-show, then show transaction details in cart
  if (!cartTableElement.dataset.typeShow) {
    const loadingElement = document.querySelector('div#cart-loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    // show loading
    loadingElement.classList.remove('d-none');
    // disabled button show cart
    showCartElement.classList.add('btn--disabled');
    
    try {    
      const response = await fetch(`${baseUrl}/cashier/show-transaction-details`);
      const responseJson = await response.json();

      /* if transaction detail is not null, this is mean transaction details not exists but
       * transaction is exists
      */
      if (responseJson.transaction_details != null) {
        // if exists transaction detail
        if (responseJson.transaction_details.length > 0) {
          // show transaction detail in cart table
          showTransactionDetails(cartTableElement, responseJson.transaction_details);
        }

        // if type = rollback-transaction
        if (responseJson.type == 'rollback-transaction') {
          // show customer money
          const customerMoney = parseInt(responseJson.customer_money);
          document.querySelector('input[name="customer_money"]').value = customerMoney;

          // calculate change money
          const paymentTotal = parseInt(cartTableElement.querySelector('td#total-payment').dataset.totalPayment);
          calculateChangeMoney(customerMoney, paymentTotal);
        }
        
        // if transaction id is not null
        if (responseJson.transaction_id != null) {
          // add dataset type-show
          cartTableElement.dataset.typeShow = responseJson.type;
        }
      }
    } catch (error) {
      console.error(error);
    }

    // show loading
    loadingElement.classList.add('d-none');
    // disabled button show cart
    showCartElement.classList.remove('btn--disabled');
  }
});

// hide cart
const closeCartElement = cartElement.querySelector('#btn-close');
closeCartElement.addEventListener('click', (e) => {
  e.preventDefault();

  cartElement.classList.replace('cart--show', 'cart--animate-hide');
  setTimeout(() => {
    cartElement.classList.remove('cart--animate-hide');
  }, 450);

  // remove class overflow hidden in tag body
  document.querySelector('body').classList.remove('overflow-hidden');
});

function resetShoppingCart(cartTableElement)
{
  // empty cart
  cartTableElement.querySelector('tbody').innerHTML = '<tr id="empty-cart-table"><td colspan="7"></td></tr>';
  cartTableElement.querySelector('td#total-qty').innerText = 0;
  cartTableElement.querySelector('td#total-qty').dataset.totalQty = 0;
  cartTableElement.querySelector('td#total-payment').innerText = 'Rp 0';
  cartTableElement.querySelector('td#total-payment').dataset.totalPayment = 0;
  document.querySelector('input[name="customer_money"]').value = '';
  document.querySelector('input[name="change_money"]').value = ''; 
  // remove dataset type-show in cart table
  delete cartTableElement.dataset.typeShow;
}

async function cancelTransaction(csrfName, csrfValue, cartTableElement, mainElement, baseUrl)
{
  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  try { 
    const responseJson = await postData(`${baseUrl}/cashier/cancel-transaction`, `${csrfName}=${csrfValue}`);

    // set new csrf hash to main tag
    if (responseJson.csrf_value != undefined) {
      mainElement.dataset.csrfValue = responseJson.csrf_value;
    }

    // if success
    if (responseJson.status == 'success') {
      // reset shopping cart
      resetShoppingCart(cartTableElement);
    }
  } catch (error) {
    console.error(error)
  }

  // loading
  document.querySelector('div#cart-loading').classList.add('d-none');
}

function cancel_rollback_transaction(csrfName, csrfValue, cart_table, main)
{
  const transaction_details_cart_table = cartTableElement.querySelectorAll('tbody tr[data-product-id]');
  // generate data transaction detail ids for remove transaction detail not exists in backup file
  const transaction_detail_ids = generate_transaction_detail_ids(transaction_details_cart_table);

  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  fetch('/kasir/rollback_transaksi_batal', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: `transaction_detail_ids=${transaction_detail_ids}&${csrfName}=${csrfValue}`
  })
  .finally(() => {
    // loading
    document.querySelector('div#cart-loading').classList.add('d-none');
  })
  .then(response => {
    return response.json();
  })
  .then(json => {
    // set new csrf hash to table tag
    if (json.csrfValue !== undefined) {
      main.dataset.csrfValue = json.csrfValue;
    }

    // if success
    if (json.status === 'success') {
      // generate data transaction detail for update product sales
      const transaction_details = generate_transaction_details_for_update_product_sale(
        json.transaction_details,
        transaction_details_cart_table
      );

      // update product sales in product items
      transactionDetails.forEach (tdcb => {
        const product_sale_el = document.querySelector(`div.product__item[data-product-id="${tdcb.product_id}"] p.product__sale`);
        // if exists product sales el
        if (product_sale_el !== null) {
          // product sale new = product sales old - product qty
          const product_sale_new = parseInt(product_sale_el.dataset.productSale) - tdcb.product_qty;
          product_sale_el.dataset.productSale = product_sale_new;
          product_sale_el.innerText = `Terjual ${product_sale_new}`;
        }
      });

      // reset shopping cart
      reset_shopping_cart(cart_table);
    }
  })
  .catch(error => {
    console.error(error);
  });
}

// cancel transaction
cancelTransactionElement.addEventListener('click', (e) => {
  e.preventDefault();

  const csrfName = mainElement.dataset.csrfName;
  const csrfValue = mainElement.dataset.csrfValue;
  const baseUrl = document.querySelector('html').dataset.baseUrl;

  const typeShow = cartTableElement.dataset.typeShow;
  // if exists dataset type-show = transaction or rollback-transaction
  if (typeShow == 'transaction' || typeShow == 'rollback-transaction') {
    // remove all form message
    const allFormMessage = document.querySelectorAll('aside.cart small.form-message');
    if (allFormMessage.length > 0) {
      allFormMessage.forEach(el => el.remove());
    }

    // if alert exists
    const alertElement = cartElement.querySelector('.alert');
    if (alertElement) {
      // remove alert
      alertElement.remove();
    }
  }

  // if exists dataset type-show = transaction
  if (typeShow == 'transaction') {
    cancelTransaction(csrfName, csrfValue, cartTableElement, mainElement, baseUrl);
  }
  // else if exists dataset = rollback-transaction in cart table
  else if (typeShow === 'rollback-transaction') {
    cancel_rollback_transaction(csrfName, csrfValue, cart_table, main);
  }
});

// show form message in cart
function showFormErrorMessageCustomerMoney(message)
{
  const smallElement = document.createElement('small');
  smallElement.classList.add('form-message');
  smallElement.classList.add(`form-message--danger`);
  smallElement.innerText = message;
  // add form message to after customer money input
  document.querySelector('aside.cart div#customer-money').append(smallElement);
}

async function finishTransaction(csrfName, csrfValue, cartTableElement, mainElement, closeCartElement, customerMoney, productHistories, baseUrl)
{
  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  try { 
    const responseJson = await postData(
      `${baseUrl}/cashier/finish-transaction`,
      `${csrfName}=${csrfValue}&customer_money=${customerMoney}&product_histories=${JSON.stringify(productHistories)}`
    );

    // set new csrf hash to main tag
    if (responseJson.csrf_value != undefined) {
      mainElement.dataset.csrfValue = responseJson.csrf_value;
    }

    // if success
    if (responseJson.status == 'success') {
      // close cart
      closeCartElement.click();
      // reset shopping cart
      resetShoppingCart(cartTableElement);
    }
    // if not success
    else if (responseJson.status == 'fail') {
      showFormErrorMessageCustomerMoney(responseJson.message);
    }
  } catch (error) {
    console.error(error)
  }

  // loading
  document.querySelector('div#cart-loading').classList.add('d-none');
}

async function finishRollbackTransaction(csrfName, csrfValue, cartTableElement, mainElement, closeCartElement, customerMoney, productHistories, baseUrl)
{
  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  try {
    const responseJson = await postData(
      `${baseUrl}/cashier/finish-rollback-transaction`,
      `${csrfName}=${csrfValue}&customer_money=${customerMoney}&product_histories=${JSON.stringify(productHistories)}`
    );

    // set new csrf hash to main tag
    if (responseJson.csrf_value != undefined) {
      mainElement.dataset.csrfValue = responseJson.csrf_value;
    }

    // if success
    if (responseJson.status == 'success') {
      // close cart
      closeCartElement.click();
      // reset shopping cart
      resetShoppingCart(cartTableElement);
    }
    // if not success
    else if (responseJson.status == 'fail') {
      showFormErrorMessageCustomerMoney(responseJson.message);
    }
  } catch (error) {
    console.error(error)
  }

  // loading
  document.querySelector('div#cart-loading').classList.add('d-none');
}

// finish transaction
finishTransactionElement.addEventListener('click', (e) => {
  e.preventDefault();

  const csrfName = mainElement.dataset.csrfName;
  const csrfValue = mainElement.dataset.csrfValue;
  const customerMoney = document.querySelector('input[name="customer_money"]').value;
  const baseUrl = document.querySelector('html').dataset.baseUrl;
  
  let productHistories = [];
  const trElements = cartTableElement.querySelectorAll('tbody tr:not(#empty-cart-table)');
  trElements.forEach((trElement, i) => {
    const tdPriceElement = trElement.querySelector('td#price');
    productHistories[i] = {
      transactionDetailId: trElement.dataset.transactionDetailId,
      productName: tdPriceElement.previousElementSibling.textContent,
      productPrice: tdPriceElement.dataset.price,
      productMagnitude: tdPriceElement.dataset.magnitude
    };
  });
  
  const typeShow = cartTableElement.dataset.typeShow;
  // if exists dataset type-show = transaction or rollback-transaction
  if (typeShow == 'transaction' || typeShow == 'rollback-transaction') {
    // remove all form message
    const allFormMessage = document.querySelectorAll('aside.cart small.form-message');
    if (allFormMessage.length > 0) {
      allFormMessage.forEach(el => el.remove());
    }

    // if alert exists
    const alertElement = cartElement.querySelector('.alert');
    if (alertElement) {
      // remove alert
      alertElement.remove();
    }
  }

  // if exists dataset type-show = transaction
  if (typeShow == 'transaction') {
    finishTransaction(csrfName, csrfValue, cartTableElement, mainElement, closeCartElement, customerMoney, productHistories, baseUrl);
  }

  // else if exists dataset type-show = rollback-transaction
  else if (typeShow == 'rollback-transaction') {
    finishRollbackTransaction(csrfName, csrfValue, cartTableElement, mainElement, closeCartElement, customerMoney, productHistories, baseUrl);
  }
});

// calculate change money
function calculateChangeMoney(customerMoney, paymentTotal)
{
  const changeMoneyElement = document.querySelector('input[name="change_money"]');
  // if customer money >= payment total
  if (customerMoney >= paymentTotal) {
    changeMoneyElement.value = numberFormatterToCurrency(customerMoney - paymentTotal);
  }
  // else if change money exists
  else if (changeMoneyElement.value != '') {
    // reset input change money
    changeMoneyElement.value = '';
  }
}

// calculate change money
let calculate = true;
document.querySelector('aside.cart input[name="customer_money"]').addEventListener('input', (e) => {
  if (calculate) {
    // set calculate = false
    calculate = false;

    // calculate change money after 300ms
    setTimeout(() => {
      const customerMoney = parseInt(e.target.value);
      const paymentTotal = parseInt(document.querySelector('aside.cart td#total-payment').dataset.totalPayment);

      calculateChangeMoney(customerMoney, paymentTotal);

      calculate = true;
    }, 300);
  }
});

// buy product
mainElement.querySelector('div.container-xl').addEventListener('click', async (e) => {
  let targetElement = e.target;

  if (targetElement.getAttribute('id') != 'buy-rollback') targetElement = targetElement.parentElement;
  if (targetElement.getAttribute('id') != 'buy-rollback') targetElement = targetElement.parentElement;

  if (targetElement.getAttribute('id') == 'buy-rollback') {
    e.preventDefault();

    const productPriceId = targetElement.parentElement.previousElementSibling.querySelector('select[name="magnitude"]').value;
    const productQty = targetElement.previousElementSibling.value;
    const csrfName = mainElement.dataset.csrfName;
    const csrfValue = mainElement.dataset.csrfValue;
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    // if empty product qty
    if (productQty.trim() == '') {
      return false;
    }

    // loading
    document.querySelector('div#transaction-loading').classList.remove('d-none');
    // disabled button search, cancel and finish transaction
    searchElement.classList.add('btn--disabled');
    cancelTransactionElement.classList.add('btn--disabled');
    finishTransactionElement.classList.add('btn--disabled');

    try {
      const responseJson = await postData(
        `${baseUrl}/cashier/buy-product`,
        `product_price_id=${productPriceId}&product_qty=${productQty}&${csrfName}=${csrfValue}`
      );

      // set new csrf hash to main tag
      if (responseJson.csrf_value != undefined) {
        mainElement.dataset.csrfValue = responseJson.csrf_value;
      }

      // reset form number of product
      targetElement.previousElementSibling.value = '';

      // if buy product success
      if (responseJson.status == 'success') {
        // if dataset type-show exists in cart table
        if (cartTableElement.dataset.typeShow != undefined) {
          // if not exists product in cart table
          if (cartTableElement.querySelector('tr#empty-cart-table') != null) {
            cartTableElement.querySelector('tr#empty-cart-table').remove();
          }

          const productId = targetElement.parentElement.parentElement.dataset.productId;
          const productInfoElement = targetElement.parentElement.previousElementSibling;
          const productName = productInfoElement.querySelector('p.product__name').textContent;
          const productPrice = productInfoElement.querySelector('select[name="magnitude"]').selectedOptions[0].dataset.productPrice;
          const productMagnitude = productInfoElement.querySelector('select[name="magnitude"]').selectedOptions[0].text;
          const payment = parseInt(productPrice) * parseInt(productQty);

          // add product to cart table
          const trElement = document.createElement('tr');
          trElement.setAttribute('data-product-id', productId);
          trElement.setAttribute('data-transaction-detail-id', responseJson.transaction_detail_id);

          trElement.innerHTML = `<td width="10"><a href="#" title="Hapus produk" id="delete-product" class="text-hover-red">
              <svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg>
            </a></td>
            <td width="10"><a href="#" title="Tambah jumlah produk" id="add-product-qty">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm6.5 11.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/></svg>
            </a></td>
            <td width="10"><a href="#" title="Kurangi jumlah produk" id="reduce-product-qty">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm6.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/></svg>
            </a></td>

            <td>${productName}</td>
            <td id="price" data-price="${productPrice}" data-magnitude="${productMagnitude}">
              ${numberFormatterToCurrency(parseInt(productPrice))} / ${productMagnitude}
            </td>
            <td id="qty" data-qty="${productQty}">${productQty}</td>
            <td id="payment" data-payment="${payment}">${numberFormatterToCurrency(payment)}</td>`;

          // append tr to cart table
          cartTableElement.querySelector('tbody').append(trElement);

          const oldTotalPayment = cartTableElement.querySelector('td#total-payment').dataset.totalPayment;
          const oldTotalQty = cartTableElement.querySelector('td#total-qty').dataset.totalQty;
          const newTotalPayment = payment + parseInt(oldTotalPayment);
          const newTotalQty = parseInt(productQty) + parseInt(oldTotalQty);

          // update qty total and payment total in cart table
          updateTotalQtyPayment(cartTableElement, newTotalQty, newTotalPayment);

          // calculate change money
          const customerMoney = parseInt(document.querySelector('input[name="customer_money"]').value);
          calculateChangeMoney(customerMoney, newTotalPayment);
        }
      } else if (responseJson.status == 'fail') {
        const parentElement = document.querySelector('main.main');
        const referenceElement = document.querySelector('div.container-xl');
        renderAlert(parentElement, referenceElement, responseJson.message, [
          'alert--warning',
          'alert--fixed-rb',
          'mb-3'
        ]);
      }
    } catch (error) {
      console.error(error)
    }

    // loading
    document.querySelector('div#transaction-loading').classList.add('d-none');
    // enabled button search, cancel and finish transaction
    searchElement.classList.remove('btn--disabled');
    cancelTransactionElement.classList.remove('btn--disabled');
    finishTransactionElement.classList.remove('btn--disabled');
  }
});

// update qty and payment product in cart table
function updateQtyPayment(trElement, newProductQty, newPayment)
{
  trElement.querySelector('td#qty').innerText = newProductQty;
  trElement.querySelector('td#qty').dataset.qty = newProductQty;
  trElement.querySelector('td#payment').innerText = numberFormatterToCurrency(newPayment);
  trElement.querySelector('td#payment').dataset.payment = newPayment;
}

// update product quantity
async function updateProductQty(
  targetElement,
  cartTableElement,
  newProductQty,
  newTotalQty,
  newPayment,
  newTotalPayment,
  transactionDetailId,
  csrfName,
  csrfValue,
  mainElement,
  baseUrl
) {
  if (newProductQty <= 0) {
    return false;
  }

  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  try {
    const responseJson = await postData(
      `${baseUrl}/cashier/update-product-qty`,
      `new_product_qty=${newProductQty}&transaction_detail_id=${transactionDetailId}&${csrfName}=${csrfValue}`
    );

    // set new csrf hash to main tag
    if (responseJson.csrf_value != undefined) {
      mainElement.dataset.csrfValue = responseJson.csrf_value;
    }

    // if update product qty success
    if (responseJson.status == 'success') {
      const trElement = targetElement.parentElement.parentElement;

      // update product qty and payment in cart table
      updateQtyPayment(trElement, newProductQty, newPayment);
      // update total qty and total payment in cart table
      updateTotalQtyPayment(cartTableElement, newTotalQty, newTotalPayment);

      // calculate change money
      const customerMoney = parseInt(document.querySelector('input[name="customer_money"]').value);
      calculateChangeMoney(customerMoney, newTotalPayment);
    }
  } catch (error) {
    console.error(error)
  }

  // loading
  document.querySelector('div#cart-loading').classList.add('d-none');
}

// delete product from shopping cart
async function deleteProduct(
  targetElement,
  cartTableElement,
  newTotalQty,
  newTotalPayment,
  transactionDetailId,
  csrfName,
  csrfValue,
  mainElement,
  baseUrl
) {
  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  try {
    const responseJson = await postData(
      `${baseUrl}/cashier/delete-product`,
      `transaction_detail_id=${transactionDetailId}&${csrfName}=${csrfValue}`
    );

    // set new csrf hash to main tag
    if (responseJson.csrf_value != undefined) {
      mainElement.dataset.csrfValue = responseJson.csrf_value;
    }

    // if delete product success
    if (responseJson.status == 'success') {
      // update qty total and payment total in cart table
      updateTotalQtyPayment(cartTableElement, newTotalQty, newTotalPayment);

      const trElement = targetElement.parentElement.parentElement;
      // delete product in cart table
      trElement.remove();

      // calculate change money
      const customerMoney = parseInt(document.querySelector('input[name="customer_money"]').value);
      calculateChangeMoney(customerMoney, newTotalPayment);

      // if not exists product in cart table
      if (cartTableElement.querySelector('tbody tr') == null) {
        cartTableElement.querySelector('tbody').innerHTML = '<tr id="empty-cart-table"><td colspan="7"></td></tr>';
      }
    }
  } catch (error) {
    console.error(error)
  }

  // loading
  document.querySelector('div#cart-loading').classList.add('d-none');
}

// add and reduce product qty and delete product from cart
document.querySelector('aside.cart table.table tbody').addEventListener('click', (e) => {
  const csrfName = mainElement.dataset.csrfName;
  const csrfValue = mainElement.dataset.csrfValue;
  const baseUrl = document.querySelector('html').dataset.baseUrl;

  // find true target add, because may be variabel e containing not element a, but element path or svg
  let targetAddElement = e.target;
  if (targetAddElement.getAttribute('id') != 'add-product-qty') targetAddElement = targetAddElement.parentElement;
  if (targetAddElement.getAttribute('id') != 'add-product-qty') targetAddElement = targetAddElement.parentElement;

  // find true target reduce, because may be variabel e containing not element a, but element path or svg
  let targetReduceElement = e.target;
  if (targetReduceElement.getAttribute('id') != 'reduce-product-qty') targetReduceElement = targetReduceElement.parentElement;
  if (targetReduceElement.getAttribute('id') != 'reduce-product-qty') targetReduceElement = targetReduceElement.parentElement;

  // find true target delete, because may be variabel e containing not element a, but element path or svg
  let targetDeleteElement = e.target;
  if (targetDeleteElement.getAttribute('id') != 'delete-product') targetDeleteElement = targetDeleteElement.parentElement;
  if (targetDeleteElement.getAttribute('id') != 'delete-product') targetDeleteElement = targetDeleteElement.parentElement;

  // if user click link for add product qty
  if (targetAddElement.getAttribute('id') == 'add-product-qty') {
    e.preventDefault();

    // get transaction detail id
    const transactionDetailId = targetAddElement.parentElement.parentElement.dataset.transactionDetailId;

    // generate new product qty, new total qty, new payment and new total payment
    const productPrice = parseInt(targetAddElement.parentElement.parentElement.querySelector('td#price').dataset.price);
    const oldTotalPayment = parseInt(cartTableElement.querySelector('td#total-payment').dataset.totalPayment);
    const newProductQty = parseInt(targetAddElement.parentElement.parentElement.querySelector('td#qty').dataset.qty) + 1;
    const newTotalQty = parseInt(cartTableElement.querySelector('td#total-qty').dataset.totalQty) + 1;
    const newPayment = newProductQty * productPrice;
    const newTotalPayment = oldTotalPayment + productPrice;

    updateProductQty(
      targetAddElement,
      cartTableElement,
      newProductQty,
      newTotalQty,
      newPayment,
      newTotalPayment,
      transactionDetailId,
      csrfName,
      csrfValue,
      mainElement,
      baseUrl
    );
  }

  // if user click link for reduce product qty
  else if (targetReduceElement.getAttribute('id') == 'reduce-product-qty') {
    e.preventDefault();

    // get transaction detail id
    const transactionDetailId = targetReduceElement.parentElement.parentElement.dataset.transactionDetailId;

    // generate new product qty, new total qty, new payment and new total payment
    const productPrice = parseInt(targetReduceElement.parentElement.parentElement.querySelector('td#price').dataset.price);
    const oldTotalPayment = parseInt(cartTableElement.querySelector('td#total-payment').dataset.totalPayment);
    const newProductQty = parseInt(targetReduceElement.parentElement.parentElement.querySelector('td#qty').dataset.qty) - 1;
    const newTotalQty = parseInt(cartTableElement.querySelector('td#total-qty').dataset.totalQty) - 1;
    const newPayment = newProductQty * productPrice;
    const newTotalPayment = oldTotalPayment - productPrice;

    updateProductQty(
      targetReduceElement,
      cartTableElement,
      newProductQty,
      newTotalQty,
      newPayment,
      newTotalPayment,
      transactionDetailId,
      csrfName,
      csrfValue,
      mainElement,
      baseUrl
    );
  }

  // if user click link for remove product from cart
  else if (targetDeleteElement.getAttribute('id') == 'delete-product') {
    e.preventDefault();

    // get transaction detail id
    const transactionDetailId = targetDeleteElement.parentElement.parentElement.dataset.transactionDetailId;

    // generate qty total new and payment total new
    const payment = parseInt(targetDeleteElement.parentElement.parentElement.querySelector('td#payment').dataset.payment);
    const oldTotalPayment = parseInt(cartTableElement.querySelector('td#total-payment').dataset.totalPayment);
    const productQty = parseInt(targetDeleteElement.parentElement.parentElement.querySelector('td#qty').dataset.qty);

    const newTotalQty = parseInt(cartTableElement.querySelector('td#total-qty').dataset.totalQty) - productQty;
    const newTotalPayment = oldTotalPayment - payment;

    deleteProduct(
      targetDeleteElement,
      cartTableElement,
      newTotalQty,
      newTotalPayment,
      transactionDetailId,
      csrfName,
      csrfValue,
      mainElement,
      baseUrl
    );
  }
});


function generate_transaction_details_for_update_product_sale(transaction_details_backup, transaction_details_cart_table)
{
  let transaction_details = [];
  let i = 0;
  // get product id and product qty from transaction detail exists in cart table but not exists in backup
  for (const el of transaction_details_cart_table) {
    let exists = false;
    for (const tdb of transaction_details_backup) {
      // if exists in backup
      if (el.dataset.productId === tdb.product_id) {
        exists = true;
        break;
      }
    }

    if (exists === false) {
      transaction_details[i] = {product_id: el.dataset.productId, product_qty: parseInt(el.querySelector('td#qty').dataset.qty)};
      i++;
    }
  }

  // get product id and product qty from transaction detail backup
  for (const tdb of transaction_details_backup) {
    // find right product qty
    let product_qty_cart_table = 0;
    for (const el of transaction_details_cart_table) {
      if (tdb.product_id === el.dataset.productId) {
        product_qty_cart_table = el.querySelector('td#qty').dataset.qty;
        break;
      }
    }

    let product_qty = 0;
    // if product qty cart table != 0, this mean product not remove yet
    if (product_qty_cart_table !== 0) {
      product_qty = parseInt(product_qty_cart_table) - tdb.product_quantity;
    } else {
      product_qty = 0 - tdb.product_quantity;
    }

    transaction_details[i] = {product_id: tdb.product_id, product_qty: product_qty};
    i++;
  }

  return transaction_details;
}

function generate_transaction_detail_ids(transaction_details_cart_table)
{
  let transaction_detail_ids = [];
  transaction_details_cartTableElement.forEach((el, i) => {
    transaction_detail_ids[i] = el.dataset.transactionDetailId;
  });

  return transaction_detail_ids;
}

const modalElement = document.querySelector('.modal');
const modalContentElement = modalElement.querySelector('.modal__content');
// show transaction five hours ago, in input select
document.querySelector('a#rollback-transaction').addEventListener('click', async (e) => {
  e.preventDefault();

  // if exists dataset type-show = transaction in cart table
  if (cartTableElement.dataset.typeShow == 'transaction') {
    const parentElement = e.target.parentElement;
    const referenceElement = e.target;
    const message = 'Tidak bisa melakukan rollback transaksi, karena kamu masih melakukan transaksi. Selesaikan atau batalkan transaksi, lalu coba kembali!';
    renderAlert(parentElement, referenceElement, message, [
      'alert--warning',
      'mb-3'
    ]);

    return false;
  }

  // if exists dataset type-show = rollback-transaction in cart table
  if (cartTableElement.dataset.typeShow == 'rollback-transaction') {
    const parentElement = e.target.parentElement;
    const referenceElement = e.target;
    const message = `Tidak bisa melakukan rollback transaksi lagi, karena kamu masih melakukan rollback transaksi. Selesaikan atau batalkan rollback transkasi, lalu coba kembali!`;
    renderAlert(parentElement, referenceElement, message, [
      'alert--warning',
      'mb-3'
    ]);

    return false;
  }

  const baseUrl = document.querySelector('html').dataset.baseUrl;

  // loading
  document.querySelector('div#cart-loading').classList.remove('d-none');

  try {
    const responseJson = await getData(`${baseUrl}/cashier/show-transactions-five-hours-ago`);

    // if exists transaction
    if (responseJson.transactions_five_hours_ago.length > 0) {
      // show modal
      showModal(modalElement, modalContentElement);

      // show data in select input
      let options = '<option>Transaksi</option>';
      responseJson.transactions_five_hours_ago.forEach((t) => {
        // if created at not equal to edited at
        if (t.created_at != t.edited_at) {
          options += `<option value="${t.transaction_id}">Dibuat - ${t.created_at}, Diedit - ${t.edited_at}</option>`;
        } else {
          options += `<option value="${t.transaction_id}">Dibuat - ${t.created_at}`;
        }
      });

      // inner html to select
      modalContentElement.querySelector('select[name="transactions_five_hours_ago"]').innerHTML = options;
    } else {
      const parentElement = e.target.parentElement;
      const referenceElement = e.target;
      const message = `Tidak ada transaksi dari 5 jam yang lalu.`;
      renderAlert(parentElement, referenceElement, message, [
        'alert--warning',
        'mb-3'
      ]);
    }
  } catch (error) {
    console.error(error)
  }

  // loading
  document.querySelector('div#cart-loading').classList.add('d-none');
});

// close modal
modalContentElement.querySelector('a#btn-close').addEventListener('click', (e) => {
  e.preventDefault();

  // hide modal
  hideModal(modalElement, modalContentElement);
  // reset modal
  modalContentElement.querySelector('select[name="transactions_five_hours_ago"]').innerHTML = '';
});

// show transaction details based on selected transaction in modal
document.querySelector('div.modal a#show-transaction-detail').addEventListener('click', async (e) => {
  e.preventDefault();

  const transactionId = modalContentElement.querySelector('select[name="transactions_five_hours_ago"]').value;
  const baseUrl = document.querySelector('html').dataset.baseUrl;

  // if transaction not selected
  if (transactionId.toLowerCase() == 'transaksi') {
    return false;
  }

  // loading
  e.target.nextElementSibling.classList.remove('d-none');

  try { 
    const responseJson = await getData(`${baseUrl}/cashier/show-transaction-details-five-hours-ago?transaction_id=${transactionId}`);

    // hide and reset modal
    hideModal(modalElement, modalContentElement);
    modalContentElement.querySelector('select[name="transactions_five_hours_ago"]').innerHTML = '';

    // if exists transaction detail
    if (responseJson.transaction_details.length > 0) {
      // show transaction detail in cart table
      showTransactionDetails(cartTableElement, responseJson.transaction_details);
    }

    // show customer money
    const customerMoney = parseInt(responseJson.customer_money);
    document.querySelector('input[name="customer_money"]').value = customerMoney;

    // calculate change money
    const totalPayment = parseInt(cartTableElement.querySelector('td#total-payment').dataset.totalPayment);
    calculateChangeMoney(customerMoney, totalPayment);

    // add dataset type-show = rollback-transaction
    cartTableElement.dataset.typeShow = 'rollback-transaction';
  } catch (error) {
    console.error(error)
  }

  // loading
  e.target.nextElementSibling.classList.add('d-none');
});
