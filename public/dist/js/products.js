import { renderAlert, numberFormatterToCurrency, postData } from './module.js';

const tableElement = document.querySelector('table.table');
const searchProductElement = document.querySelector('a#search-product');
const resultStatusElement = document.querySelector('span#result-status');
const baseUrl = document.querySelector('html').dataset.baseUrl;

// show hide product detail
tableElement.querySelector('tbody').addEventListener('click', async (e) => {
    let targetElement = e.target;
    if (targetElement.getAttribute('id') !== 'show-product-detail') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') !== 'show-product-detail') targetElement = targetElement.parentElement;
    if (targetElement.getAttribute('id') === 'show-product-detail') {
        e.preventDefault();

        // if next element sibling exists and next element sibling is tr.table__row-detail, or is mean product detail exists in table
        const tableRowDetailElement = targetElement.parentElement.parentElement.nextElementSibling;
        if (tableRowDetailElement !== null && tableRowDetailElement.classList.contains('table__row-detail')) {
            tableRowDetailElement.classList.toggle('table__row-detail--show');
        // else, is mean product detail not exists in table
        } else {
            const productId = targetElement.dataset.productId;

            // show loading
            tableElement.parentElement.nextElementSibling.classList.remove('d-none');
            // disabled button search
            searchProductElement.classList.add('btn--disabled');

            try {
                const response = await fetch(`${baseUrl}/admin/produk/tampilkan-detail/${productId}`);
                const responseJson = await response.json();

                // set new csrf hash to table tag
                if (responseJson.csrf_value !== undefined) {
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

            // hide loading
            tableElement.parentElement.nextElementSibling.classList.add('d-none');
            // enabled button search
            searchProductElement.classList.remove('btn--disabled');
        }
    }
});

// search product
searchProductElement.addEventListener('click', e => {
    e.preventDefault();

    const keyword = document.querySelector('input[name="product_name_search"]').value;
    const csrf_name = tableElement.dataset.csrfName;
    const csrf_value = tableElement.dataset.csrfValue;

    // if empty keyword
    if (keyword.trim() === '') {
        return false;
    }

    // loading
    tableElement.parentElement.nextElementSibling.classList.remove('d-none');
    // disabled button search
    searchProductElement.classList.add('btn--disabled');

    fetch('/admin/cari_produk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `keyword=${keyword}&${csrf_name}=${csrf_value}`
    })
    .finally(() => {
        // loading
        tableElement.parentElement.nextElementSibling.classList.add('d-none');
        // enabled button search
        searchProductElement.classList.remove('btn--disabled');
    })
    .then(response => {
        return response.json();
    })
    .then(json => {
        // set new csrf hash to table tag
        if (json.csrf_value !== undefined) {
            tableElement.dataset.csrfValue = json.csrf_value;
        }

        // if product exists
        if (json.products_db.length > 0) {
            let tr = '';

            json.products_db.forEach((p, i) => {
                // if i is odd number
                if ((i+1)%2 !== 0) {
                    tr += '<tr class="table__row-odd">';
                } else {
                    tr += '<tr>';
                }
                tr += `<td width="10">
                        <div class="form-check">
                            <input type="checkbox" name="product_id" data-create-time="${p.waktu_buat}" class="form-check-input" value="${p.produk_id}">
                        </div>
                    </td>
                    <td width="10"><a href="/admin/perbaharui_produk/${p.produk_id}" title="Ubah Produk"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></a></td>
                    <td width="10"><a href="#" id="show-product-detail" data-product-id="${p.produk_id}" title="Lihat detail produk"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                    <td>${p.nama_produk}</td>
                    <td>${p.nama_kategori_produk}</td>`;
                if (p.status_produk === 'ada') {
                     tr += `<td><span class="text-green">Ada</span></td>`;
                } else {
                     tr += `<td><span class="text-red">Tidak Ada</span></td>`;
                }
                tr += `<td>${p.indo_create_time}</td></tr>`;
            });

            tableElement.querySelector('tbody').innerHTML = tr;

            // show result status
            result_status.innerText = `1 - ${json.products_db.length} dari ${json.product_search_total} Total produk hasil pencarian`;

            // add dataset type show and dataset keyword
            tableElement.dataset.typeShow = 'search';
            tableElement.dataset.keyword = keyword;
        }
        // if product not exists
        else {
            // inner html message
            tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="7">Produk tidak ada.</td></tr>`;

            // show result status
            result_status.innerText = '0 Total produk hasil pencarian';
        }

        const limit_message = document.querySelector('span#limit-message');
        // add limit message if product search total = product limit && limit message not exists
        if (json.products_db.length === json.product_limit && limit_message === null) {
            const span = document.createElement('span');
            span.classList.add('text-muted');
            span.classList.add('d-block');
            span.classList.add('mt-3');
            span.setAttribute('id', 'limit-message');
            span.innerHTML = `Hanya ${json.product_limit} Produk terbaru yang ditampilkan, Pakai fitur <i>Pencarian</i> untuk hasil lebih spesifik!`;
            tableElement.after(span);
        }
        // else if product search total != product limit and limit message exists
        else if (json.products_db.length !== json.product_limit && limit_message !== null) {
            limit_message.remove();
        }
    })
    .catch(error => {
        console.error(error);
    });
});

// remove product and automatic remove product price
document.querySelector('a#remove-product').addEventListener('click', e => {
    e.preventDefault();

    const checkboxs_checked = document.querySelectorAll('input[type="checkbox"][name="product_id"]:checked');
    // if not found input checkbox checklist
    if (checkboxs_checked.length === 0) {
        return false;
    }

    // generate data
    let data = '';

    const csrf_name = tableElement.dataset.csrfName;
    const csrf_value = tableElement.dataset.csrfValue;
    data += `${csrf_name}=${csrf_value}`;

    let product_ids = '';
    checkboxs_checked.forEach((val, index) => {
        // if last checkbox
        if (index === checkboxs_checked.length-1) {
            product_ids += val.value;
        } else {
            product_ids += val.value+',';
        }
    });
    data += `&product_ids=${product_ids}`;

    // get smallest create time in table
    const all_checkboxs = document.querySelectorAll('input[type="checkbox"][name="product_id"]');
    data += `&smallest_create_time=${all_checkboxs[all_checkboxs.length-1].dataset.createTime}`;

    // if dataset type-show and dataset keyword exists in table tag
    if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.keyword !== undefined) {
        data += `&keyword=${tableElement.dataset.keyword}`;
    }

    // loading
    tableElement.parentElement.nextElementSibling.classList.remove('d-none');
    // disabled button search
    searchProductElement.classList.add('btn--disabled');

    fetch('/admin/hapus_produk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .finally(() => {
        // loading
        tableElement.parentElement.nextElementSibling.classList.add('d-none');
        // enabled button search
        searchProductElement.classList.remove('btn--disabled');
    })
    .then(response => {
        return response.json();
    })
    .then(json => {
        // set new csrf hash to table tag
        if (json.csrf_value !== undefined) {
            tableElement.dataset.csrfValue = json.csrf_value;
        }

        // if remove product success
        if (json.status === 'success') {
            checkboxs_checked.forEach(val => {
                // if exists detail product in table
                const table_row_detail = val.parentElement.parentElement.parentElement.nextElementSibling;
                if (table_row_detail !== null && table_row_detail.classList.contains('table__row-detail')) {
                    // remove detail product
                    table_row_detail.remove();
                }

                // remove product checklist
                val.parentElement.parentElement.parentElement.remove();
            });

            // if longer product exists
            if (json.longer_products.length > 0) {
                json.longer_products.forEach((p, i) => {
                    const tr = document.createElement('tr');

                    // if i is odd number
                    if ((i+1)%2 !== 0) {
                        tr.classList.add('table__row-odd');
                    }

                    let td = `<td width="10">
                            <div class="form-check">
                                <input type="checkbox" name="product_id" data-create-time="${p.waktu_buat}" class="form-check-input" value="${p.produk_id}">
                            </div>
                        </td>
                        <td width="10"><a href="/admin/perbaharui_produk/${p.produk_id}" title="Ubah Produk"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></a></td>
                        <td width="10"><a href="#" id="show-product-detail" data-product-id="${p.produk_id}" title="Lihat detail produk"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                        <td>${p.nama_produk}</td>
                        <td>${p.nama_kategori_produk}</td>`;

                    if (p.status_produk === 'ada') {
                         td += `<td><span class="text-green">Ada</span></td>`;
                    } else {
                         td += `<td><span class="text-red">Tidak Ada</span></td>`;
                    }
                    td += `<td>${p.indo_create_time}</td>`;

                    // inner td to tr
                    tr.innerHTML = td;
                    // append tr to tbody
                    tableElement.querySelector('tbody').append(tr);
                });
            }

            const count_product_in_table = tableElement.querySelectorAll('tbody tr').length;
            // if product total = 0
            if (json.product_total === 0) {
                // inner html message
                tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="6">Produk tidak ada.</td></tr>`;

                // if dataset type-show and dataset keyword exists in table tag
                if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.keyword !== undefined) {
                    // show result status
                    result_status.innerText = '0 Total produk hasil pencarian';
                } else {
                    // show result status
                    result_status.innerText = '0 Total produk';
                }

            } else {
                // if dataset type-show and dataset keyword exists in table tag
                if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.keyword !== undefined) {
                    // show result status
                    result_status.innerText = `1 - ${count_product_in_table} dari ${json.product_total} Total produk hasil pencarian`;
                } else {
                    // show result status
                    result_status.innerText = `1 - ${count_product_in_table} dari ${json.product_total} Total produk`;
                }
            }

            // if total product in table < product limit and limit message exists
            const limit_message = document.querySelector('span#limit-message');
            if (count_product_in_table < json.product_limit && limit_message !== null) {
                limit_message.remove();
            }
        }
        // else if fail remove product
        else if (json.status === 'fail') {
            const alert = create_alert_node(['alert--warning', 'mb-3'], json.message);

            // append alert to before div.main__box element
            document.querySelector('main.main > div').insertBefore(alert, document.querySelector('div.main__box'));

            // reset input checkboxs checked
            checkboxs_checked.forEach(val => {
                val.checked = false;
            });
        }
    })
    .catch(error => {
        console.error(error);
    })
});
