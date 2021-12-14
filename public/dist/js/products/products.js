import { renderAlert, numberFormatterToCurrency, postData } from '../module.js';

const tableElement = document.querySelector('#table');
const productSearchElement = document.querySelector('a#search-product');

// show hide product detail
tableElement.querySelector('tbody').addEventListener('click', async (e) => {
    let targetElement = e.target;
    if (targetElement.getAttribute('id') != 'show-product-detail') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') != 'show-product-detail') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') == 'show-product-detail') {
        e.preventDefault();
        
        /**
         * if next element sibling exist and next element sibling
         * is element with table__row-detail class, or is mean product detail exists in table
         */
        const tableRowDetailElement = targetElement.parentElement.parentElement.nextElementSibling;
        if (tableRowDetailElement != null && tableRowDetailElement.classList.contains('table__row-detail')) {
            tableRowDetailElement.classList.toggle('table__row-detail--show');
        // else, is mean product detail not exists in table
        } else {
            const loadingElement = document.querySelector('#loading');
            const baseUrl = document.querySelector('html').dataset.baseUrl;
            const productId = targetElement.dataset.productId;

            // hide loading and enable button search
            loadingElement.classList.remove('d-none');
            productSearchElement.classList.add('btn--disabled');

            try {
                const response = await fetch(`${baseUrl}/admin/product/show-details/${productId}`);
                const responseJson = await response.json();

                // set new csrf hash to table tag
                if (responseJson.csrf_value != undefined) {
                    tableElement.dataset.csrfValue = responseJson.csrf_value;
                }

                // if product price exists
                if (responseJson.product_prices.length > 0) {
                    let li = '';
                    responseJson.product_prices.forEach(val => {
                        li += `<li><span class="table__title">Harga Produk</span>
                            <span class="table__information">Besaran :</span><span class="table__data">${val.product_magnitude}</span>
                            <span class="table__information">Harga :</span><span class="table__data">
                                ${numberFormatterToCurrency(parseInt(val.product_price))}
                            </span></li>`;
                    });
                    const trElement = document.createElement('tr');
                    trElement.classList.add('table__row-detail');
                    trElement.classList.add('table__row-detail--show');
                    trElement.innerHTML = `<td colspan="6"><ul>${li}</ul></td>
                        <td colspan="2"><img src="${baseUrl}/dist/images/product-photos/${responseJson.product_photo}"></td>`;
                    targetElement.parentElement.parentElement.after(trElement);
                }
            } catch (error) {
                console.error(error);
            }

            // hide loading and enable button search
            loadingElement.classList.add('d-none');
            productSearchElement.classList.remove('btn--disabled');
        }
    }
});

// search product
productSearchElement.addEventListener('click', async (e) => {
    e.preventDefault();

    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;
    const keyword = document.querySelector('input[name="product_name_search"]').value;

    // if empty keyword
    if (keyword.trim() == '') {
        return false;
    }

    // show loading and disable search button
    loadingElement.classList.remove('d-none');
    productSearchElement.classList.add('btn--disabled');

    try {
        const resultStatusElement = document.querySelector('span#result-status');

        const response = await fetch(`${baseUrl}/admin/product/search/${keyword}`);
        const responseJson = await response.json();

        // if product exists
        if (responseJson.products.length > 0) {
            let tr = '';
            responseJson.products.forEach((p, i) => {
                // if i is odd number
                if ((i+1)%2 !== 0) {
                    tr += '<tr class="table__row-odd">';
                } else {
                    tr += '<tr>';
                }
                tr += `
                    <td width="10">
                        <div class="form-check">
                            <input type="checkbox" name="product_id" data-edited-at="${p.edited_at}"
                            class="form-check-input" value="${p.product_id}">
                        </div>
                    </td>
                    <td width="10"><a href="/admin/product/edit/${p.product_id}" title="Ubah Produk"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></a></td>
                    <td width="10"><a href="#" id="show-product-detail" data-product-id="${p.product_id}" title="Lihat detail produk"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                    <td>${p.product_name}</td>
                    <td>${p.product_category_name}</td>
                `;
                if (p.product_status == 'ada') {
                     tr += '<td><span class="text-green">Ada</span></td>';
                } else {
                     tr += '<td><span class="text-red">Tidak Ada</span></td>';
                }
                tr += `
                    <td>${p.created_at}</td>
                    <td>${p.indo_edited_at}</td></tr>
                `;
            });

            tableElement.querySelector('tbody').innerHTML = tr;

            // show result status
            resultStatusElement.innerText = `1 - ${responseJson.products.length} dari ${responseJson.total_product} Total produk hasil pencarian`;

            /**
             * add dataset show-type and dataset keyword
             * showType used for show longer product when remove product
             */
            tableElement.dataset.showType = 'search';
            tableElement.dataset.keyword = keyword;
        }
        // if product not exists
        else {
            // inner html message
            tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="8">Produk tidak ada.</td></tr>`;
            // show result status
            resultStatusElement.innerText = '0 Total produk hasil pencarian';
        }

        const limitMessageElement = document.querySelector('#limit-message');
        // add limit message if total product search = product limit && limit message not exists
        if (responseJson.total_product > responseJson.product_limit && limitMessageElement == null) {
            const spanElement = document.createElement('span');
            spanElement.classList.add('text-muted');
            spanElement.classList.add('d-block');
            spanElement.classList.add('mt-3');
            spanElement.setAttribute('id', 'limit-message');
            spanElement.innerHTML = `
                Hanya ${responseJson.product_limit} Produk terbaru yang ditampilkan,
                Pakai fitur <i>Pencarian</i> untuk hasil lebih spesifik!
            `;
            tableElement.after(spanElement);
        }
        // else if total product search != product limit and limit message exists
        else if (responseJson.total_product <= responseJson.product_limit && limitMessageElement != null) {
            limitMessageElement.remove();
        }
    } catch (error) {
        console.error(error);
    }

    // hide loading and disable search button
    loadingElement.classList.add('d-none');
    productSearchElement.classList.remove('btn--disabled');
});

// delete product and automatic delete product price
document.querySelector('a#delete-product').addEventListener('click', async (e) => {
    e.preventDefault();

    const checkedCheckboxElements = document.querySelectorAll('input[type="checkbox"][name="product_id"]:checked');
    // if not found checked checkbox
    if (checkedCheckboxElements.length == 0) {
        return false;
    }

    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    // generate data
    let data = '';

    const csrfName = tableElement.dataset.csrfName;
    const csrfValue = tableElement.dataset.csrfValue;
    data += `${csrfName}=${csrfValue}`;

    let productIds = '';
    checkedCheckboxElements.forEach((val, index) => {
        // if last checkbox
        if (index == checkedCheckboxElements.length-1) {
            productIds += val.value;
        } else {
            productIds += val.value+',';
        }
    });
    data += `&product_ids=${productIds}`;

    // get smallest edited at in table
    const allCheckboxElements = document.querySelectorAll('input[type="checkbox"][name="product_id"]');
    data += `&smallest_edited_at=${allCheckboxElements[allCheckboxElements.length-1].dataset.editedAt}`;

    // if dataset show-type and dataset keyword exist in table tag
    if (tableElement.dataset.showType != undefined && tableElement.dataset.keyword != undefined) {
        data += `&keyword=${tableElement.dataset.keyword}`;
    }

    // show loading
    loadingElement.classList.remove('d-none');
    // disable button search
    productSearchElement.classList.add('btn--disabled');
    
    try {
        const responseJson = await postData(`${baseUrl}/admin/product/deletes`, data);

        // set new csrf hash to table tag
        if (responseJson.csrf_value != undefined) {
            tableElement.dataset.csrfValue = responseJson.csrf_value;
        }

        // if delete product success
        if (responseJson.status == 'success') {
            checkedCheckboxElements.forEach(val => {
                // if product detail exist in table
                const tableRowDetailElement = val.parentElement.parentElement.parentElement.nextElementSibling;
                if (tableRowDetailElement != null && tableRowDetailElement.classList.contains('table__row-detail')) {
                    // remove product detail
                    tableRowDetailElement.remove();
                }

                // remove product checked
                val.parentElement.parentElement.parentElement.remove();
            });

            // if longer product exist
            if (responseJson.longer_products.length > 0) {
                responseJson.longer_products.forEach((p, i) => {
                    const trElement = document.createElement('tr');

                    // if i is odd number
                    if ((i+1)%2 != 0) {
                        trElement.classList.add('table__row-odd');
                    }

                    let td = `
                        <td width="10">
                            <div class="form-check">
                                <input type="checkbox" name="product_id" data-edited-at="${p.edited_at}" class="form-check-input" value="${p.product_id}">
                            </div>
                        </td>
                        <td width="10"><a href="/admin/product/edit/${p.product_id}" title="Edit Produk"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></a></td>
                        <td width="10"><a href="#" id="show-product-detail" data-product-id="${p.product_id}" title="Tampilkan detail produk"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                        <td>${p.product_name}</td>
                        <td>${p.product_category_name}</td>
                    `;

                    if (p.product_status == 'ada') {
                         td += '<td><span class="text-green">Ada</span></td>';
                    } else {
                         td += '<td><span class="text-red">Tidak Ada</span></td>';
                    }
                    td += `
                        <td>${p.created_at}</td>
                        <td>${p.indo_edited_at}</td>
                    `;

                    // inner td to tr
                    trElement.innerHTML = td;
                    // append tr to tbody
                    tableElement.querySelector('tbody').append(trElement);
                });
            }

            const resultStatusElement = document.querySelector('span#result-status');
            const countProductInTable = tableElement.querySelectorAll('tbody tr').length;
            // if total product = 0
            if (responseJson.total_product == 0) {
                // inner html message
                tableElement.querySelector('tbody').innerHTML = '<tr class="table__row-odd"><td colspan="8">Produk tidak ada.</td></tr>';

                // if dataset show-type and dataset keyword exist in table tag
                if (tableElement.dataset.showType != undefined && tableElement.dataset.keyword != undefined) {
                    // show result status
                    resultStatusElement.innerText = '0 Total produk hasil pencarian';
                } else {
                    // show result status
                    resultStatusElement.innerText = '0 Total produk';
                }

            } else {
                // if dataset show-type and dataset keyword exist in table tag
                if (tableElement.dataset.showType != undefined && tableElement.dataset.keyword != undefined) {
                    // show result status
                    resultStatusElement.innerText = `1 - ${countProductInTable} dari ${responseJson.total_product} Total produk hasil pencarian`;
                } else {
                    // show result status
                    resultStatusElement.innerText = `1 - ${countProductInTable} dari ${responseJson.total_product} Total produk`;
                }
            }

            // if total product in table <= product limit and limit message exist
            const limitMessageElement = document.querySelector('span#limit-message');
            if (responseJson.total_product <= responseJson.product_limit && limitMessageElement != null) {
                limitMessageElement.remove();
            }
        }
        // else if delete product fail
        else if (responseJson.status == 'fail') {
            const parentElement = document.querySelector('main.main');
            const referenceElement = document.querySelector('div.main__box');
            renderAlert(parentElement, referenceElement, responseJson.message, [
                'alert--warning',
                'mb-3'
            ]);

            // reset checked checkboxs
            checkedCheckboxElements.forEach(val => val.checked = false);
        }
    } catch (error) {
        console.error(error);
    }

    // hide loading
    loadingElement.classList.add('d-none');
    // enable button search
    productSearchElement.classList.remove('btn--disabled');
});
