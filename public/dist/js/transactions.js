import {
    renderAlert,
    numberFormatterToCurrency,
    showModal,
    hideModal,
    showPassword,
    postData
} from './module.js';
import Indonesian from '../plugins/flatpickr/id.js';

const tableElement = document.querySelector('#table');
const searchElement = document.querySelector('a#search');
const modalElement = document.querySelector('.modal');
const modalContentElement = modalElement.querySelector('.modal__content');
const exportExcelElement = document.querySelector('a#export-excel');

// flatpickr setting
flatpickr('input[name="date_range"]', {
    disableMobile: 'true',
    mode: 'range',
    altInput: true,
    altFormat: 'j M, Y',
    altInputClass: 'form-input form-input--rounded-left hover-cursor-pointer',
    locale: Indonesian
});

// search transactions
searchElement.addEventListener('click', async (e) => {
    e.preventDefault();
    
    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;
    const dateRange = document.querySelector('input[name="date_range"]').value;

    // if empty date range
    if (dateRange.trim() == '') {
        return false;
    }

    // show loading, disable button search, button export and dropdown toggle
    loadingElement.classList.remove('d-none');
    searchElement.classList.add('btn--disabled');
    exportExcelElement.classList.add('btn--disabled');
    exportExcelElement.nextElementSibling.classList.add('btn--disabled');
    
    try {
        const resultStatusElement = document.querySelector('span#result-status');

        const response = await fetch(`${baseUrl}/admin/transactions/search/${dateRange}`);
        const responseJson = await response.json();

        // if transactions exists
        if (responseJson.transactions.length > 0) {
            let tr = '';

            responseJson.transactions.forEach((t, i) => {
                // if i is odd number
                if ((i + 1) % 2 != 0) {
                    tr += '<tr class="table__row-odd">';
                } else {
                    tr += '<tr>';
                }
                tr += '<td width="10">';

                // if transaction is allowed to delete
                if (t.delete_permission == true) {
                    tr += `<div class="form-check">
                            <input type="checkbox" name="transaction_id" data-edited-at="${t.edited_at}" class="form-check-input" value="${t.transaction_id}">
                        </div>`;
                }

                tr += `</td>
                    <td width="10"><a href="#" id="show-transaction-details" data-transaction-id="${t.transaction_id}" title="Lihat detail transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                    <td>${t.total_product || 0}</td>
                    <td>${numberFormatterToCurrency(parseInt(t.total_payment || 0))}</td>`;

                if (t.transaction_status == 'selesai') {
                    tr += '<td><span class="text-green">Selesai</span></td>';
                } else {
                    tr += '<td><span class="text-red">Belum</span></td>';
                }

                tr += `<td>${t.full_name}</td><td>${t.created_at}</td><td>${t.indo_edited_at}</td></tr>`;
            });

            tableElement.querySelector('tbody').innerHTML = tr;

            // show result status
            resultStatusElement.innerText = `1 - ${responseJson.transactions.length} dari ${responseJson.total_transaction} Total transaksi hasil pencarian`;
        }
        // if transactions not exists
        else {
            // inner html message
            tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="8">Transaksi tidak ada.</td></tr>`;

            // show result status
            resultStatusElement.innerText = '0 Total transaksi hasil pencarian';
        }

        const limitMessageElement = document.querySelector('span#limit-message');
        // add limit message if total transaction search > transaction limit && limit message not exists
        if (responseJson.total_transaction > responseJson.transaction_limit && (limitMessageElement == null || tableElement.dataset.showType == undefined)) {
            if (limitMessageElement != null) {
                // delete old limit message
                limitMessageElement.remove();
            }

            const spanElement = document.createElement('span');
            spanElement.classList.add('text-muted');
            spanElement.classList.add('d-block');
            spanElement.classList.add('mt-3');
            spanElement.setAttribute('id', 'limit-message');
            spanElement.innerHTML = `
                Hanya ${responseJson.transaction_limit} transaksi yang ditampilkan,
                Pakai fitur <i>Pencarian</i> untuk hasil lebih spesifik!
            `;
            tableElement.after(spanElement);
        }
        // else if total transaction search <= transaction limit and limit message exists
        else if (responseJson.total_transaction <= responseJson.transaction_limit && limitMessageElement != null) {
            limitMessageElement.remove();
        }

        // add dataset show-type and dataset date-range
        tableElement.dataset.showType = 'date-range';
        tableElement.dataset.dateRange = dateRange;

    } catch (error) {
        console.error(error);
    }

    // hide loading, enable button search, button export and dropdown toggle
    loadingElement.classList.add('d-none');
    searchElement.classList.remove('btn--disabled');
    exportExcelElement.classList.remove('btn--disabled');
    exportExcelElement.nextElementSibling.classList.remove('btn--disabled');
});

// show hide transaction details
tableElement.querySelector('tbody').addEventListener('click', async (e) => {
    let targetElement = e.target;
    if(targetElement.getAttribute('id') != 'show-transaction-details') targetElement = targetElement.parentElement;
    if(targetElement.getAttribute('id') != 'show-transaction-details') targetElement = targetElement.parentElement;
    if(targetElement.getAttribute('id') == 'show-transaction-details') {
        e.preventDefault();

        /**
         * if next element sibling exist and next element sibling
         * is element with table__row-detail class, or is mean product detail exists in table
         */
        const tableRowDetailElement = targetElement.parentElement.parentElement.nextElementSibling;
        if(tableRowDetailElement !== null && tableRowDetailElement.classList.contains('table__row-detail')) {
            tableRowDetailElement.classList.toggle('table__row-detail--show');
        // else, is mean transaction detail not exists in table
        } else {
            const loadingElement = document.querySelector('#loading');
            const baseUrl = document.querySelector('html').dataset.baseUrl;
            const transactionId = targetElement.dataset.transactionId;

            // show loading and disable button search
            loadingElement.classList.remove('d-none');
            searchElement.classList.add('btn--disabled');

            try {
                const response = await fetch(`${baseUrl}/admin/transaction/show-details/${transactionId}`);
                const responseJson = await response.json();

                // if exists transaction details
                if (responseJson.transaction_details.length > 0) {
                    let li = '';
                    responseJson.transaction_details.forEach(val => {
                        li += `<li><span class="table__title">${val.product_name}</span>
                            <span class="table__information">Harga :</span><span class="table__data">
                                ${numberFormatterToCurrency(parseInt(val.product_price))} / ${val.product_magnitude}
                            </span>
                            <span class="table__information">Jumlah :</span><span class="table__data">${val.product_quantity}</span>
                            <span class="table__information">Bayaran :</span><span class="table__data">
                                ${numberFormatterToCurrency(parseInt(val.product_price * val.product_quantity))}
                            </span></li>`;
                    });

                    const trElement = document.createElement('tr');
                    trElement.classList.add('table__row-detail');
                    trElement.classList.add('table__row-detail--show');
                    trElement.innerHTML = `<td colspan="8"><ul>${li}</ul></td>`;
                    targetElement.parentElement.parentElement.after(trElement);
                }
            } catch (error) {
                console.error(error);
            }

            // hide loading and enable button search
            loadingElement.classList.add('d-none');
            searchElement.classList.remove('btn--disabled');
        }
    }
});

document.querySelector('a#show-modal-delete').addEventListener('click', (e) => {
    e.preventDefault();

    const checkboxsChecked = document.querySelectorAll('input[type="checkbox"][name="transaction_id"]:checked');
    // if not found checked checkbox
    if (checkboxsChecked.length == 0) {
        return false;
    }

    // show modal
    showModal(modalElement, modalContentElement);
});

// close modal
modalContentElement.querySelector('a#btn-close').addEventListener('click', (e) => {
    e.preventDefault();

    // hide modal
    hideModal(modalElement, modalContentElement);

    // reset modal
    modalContentElement.querySelector('input[name="password"]').value = '';
    const smallElement = modalContentElement.querySelector('small.form-message');
    if (smallElement != null) {
        smallElement.remove();
    }
});

// show password
document.querySelector('.modal a#show-password').addEventListener('click', showPassword);

// delete transactions and automatic delete transaction details
document.querySelector('a#delete').addEventListener('click', async (e) => {
    e.preventDefault();

    // reset form message
    const smallElement = modalContentElement.querySelector('small.form-message');
    if (smallElement != null) {
        smallElement.remove();
    }

    const loadingElement = document.querySelector('#delete-loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    // generate data
    let data = '';

    const csrfName = tableElement.dataset.csrfName;
    const csrfValue = tableElement.dataset.csrfValue;
    const userSignInPassword = modalContentElement.querySelector('input[name="user_sign_in_password"]').value;
    data += `${csrfName}=${csrfValue}&user_sign_in_password=${userSignInPassword}`;

    let transactionIds = '';
    const checkedCheckboxElements = document.querySelectorAll('input[type="checkbox"][name="transaction_id"]:checked');
    checkedCheckboxElements.forEach((val, index) => {
        // if last checkbox
        if (index == checkedCheckboxElements.length-1) {
            transactionIds += val.value;
        } else {
            transactionIds += val.value + ',';
        }
    });
    data += `&transaction_ids=${transactionIds}`;

    // get smallest edited at in table
    const allCheckboxElements = document.querySelectorAll('input[type="checkbox"][name="transaction_id"]');
    data += `&smallest_edited_at=${allCheckboxElements[allCheckboxElements.length-1].dataset.editedAt}`;

    // if dataset show-type and dataset date-range exists in table tag
    if (tableElement.dataset.showType != undefined && tableElement.dataset.dateRange != undefined) {
        data += `&date_range=${tableElement.dataset.dateRange}`;
    }

    // show loading
    loadingElement.classList.remove('d-none');

    try {
        const responseJson = await postData(`${baseUrl}/admin/transactions/delete`, data);

        // set new csrf hash to table tag
        if (responseJson.csrf_value != undefined) {
            tableElement.dataset.csrfValue = responseJson.csrf_value;
        }

        // if delete transaction success
        if (responseJson.status == 'success') {
            checkedCheckboxElements.forEach(val => {
                // if transaction detail exist in table
                const tableRowDetailElement = val.parentElement.parentElement.parentElement.nextElementSibling;
                if (tableRowDetailElement != null && tableRowDetailElement.classList.contains('table__row-detail')) {
                    // remove detail transaction
                    tableRowDetailElement.remove();
                }

                // remove transaction checked
                val.parentElement.parentElement.parentElement.remove();
            });

            // if longer transactions exist
            if (responseJson.longer_transactions.length > 0) {
                responseJson.longer_transactions.forEach((t, i) => {
                    const trElement = document.createElement('tr');

                    // if i is odd number
                    if ((i + 1) % 2 != 0) {
                        trElement.classList.add('table__row-odd');
                    }
                    let td = '<td width="10">';

                    // if transaction is allow for delete
                    if (t.delete_permission == true) {
                        td += `<div class="form-check">
                                <input type="checkbox" name="transaction_id" data-edited-at="${t.edited_at}" class="form-check-input" value="${t.transaction_id}">
                            </div>`;
                    }

                    td += `</td>
                        <td width="10"><a href="#" id="show-transaction-details" data-transaction-id="${t.transaction_id}" title="Lihat detail transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                        <td>${t.total_product || 0}</td>
                        <td>${numberFormatterToCurrency(parseInt(t.total_payment || 0))}</td>`;

                    if (t.transaction_status == 'selesai') {
                        td += '<td><span class="text-green">Selesai</span></td>';
                    } else {
                        td += '<td><span class="text-red">Belum</span></td>';
                    }

                    td += `<td>${t.full_name}</td><td>${t.created_at}</td><td>${t.indo_edited_at}</td></tr>`;

                    // inner td to tr
                    trElement.innerHTML = td;
                    // append tr to tbody
                    tableElement.querySelector('tbody').append(trElement);
                });
            }

            const resultStatusElement = document.querySelector('span#result-status');
            const countTransactionInTable = tableElement.querySelectorAll('tbody tr').length;
            // if total transaction = 0
            if (responseJson.total_transaction == 0) {
                // inner html message
                tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="7">Transaksi tidak ada.</td></tr>`;

                // if dataset show-type and dataset date-range exists in table tag
                if (tableElement.dataset.showType != undefined && tableElement.dataset.dateRange != undefined) {
                    // show result status
                    resultStatusElement.innerText = '0 Total transaksi hasil pencarian';
                } else {
                    // show result status
                    resultStatusElement.innerText = '0 Total transaksi';
                }

            } else {
                // if dataset show-type and dataset date-range exists in table tag
                if (tableElement.dataset.showType != undefined && tableElement.dataset.dateRange != undefined) {
                    // show result status
                    resultStatusElement.innerText = `1 - ${countTransactionInTable} dari ${responseJson.total_transaction} Total transaksi hasil pencarian`;
                } else {
                    // show result status
                    resultStatusElement.innerText = `1 - ${countTransactionInTable} dari ${responseJson.total_transaction} Total transaksi`;
                }
            }

            // if total transaction in table <= transaction limit and limit message exist
            const limitMessageElement = document.querySelector('span#limit-message');
            if (responseJson.total_transaction <= responseJson.transaction_limit && limitMessageElement != null) {
                limitMessageElement.remove();
            }
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
        // else if fail remove transaction
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

// export transactions to excel
exportExcelElement.addEventListener('click', async (e) => {
    e.preventDefault();

    const loadingElement = document.querySelector('#loading');
    const exportLoadingElement = document.querySelector('#export-loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;
    const type = e.target.dataset.type;

    // generate data
    let data = '';

    const csrfName = tableElement.dataset.csrfName;
    const csrfValue = tableElement.dataset.csrfValue;
    data += `${csrfName}=${csrfValue}`;

    // if dataset show-type and dataset date-range exists in table tag
    if (tableElement.dataset.showType != undefined && tableElement.dataset.dateRange != undefined) {
        data += `&date_range=${tableElement.dataset.dateRange}`;
    }

    // show loading, disable button search and disable action in table
    exportLoadingElement.classList.remove('d-none');
    searchElement.classList.add('btn--disabled');
    loadingElement.querySelector('.loading').classList.add('d-none');
    loadingElement.classList.remove('d-none');

    try {
        const responseJson = await postData(`${baseUrl}/admin/transactions/export/excel/${type}`, data);

        // set new csrf hash to table tag
        if (responseJson.csrf_value != undefined) {
            tableElement.dataset.csrfValue = responseJson.csrf_value;
        }
        
        let alertClass;
        // if export transactions success
        if (responseJson.status == 'success') {
            alertClass = 'alert--success';
        }
        // else if export transactions fail
        else if (responseJson.status == 'fail') {
            alertClass = 'alert--warning';
        }

        // show alert
        const parentElement = document.querySelector('main.main');
        const referenceElement = document.querySelector('div.main__box');
        renderAlert(parentElement, referenceElement, responseJson.message, [
            alertClass,
            'mb-3'
        ]);
    } catch (error) {
        console.error(error);
    }

    // hide loading, enable button search and enable action in table
    exportLoadingElement.classList.add('d-none');
    searchElement.classList.remove('btn--disabled');
    loadingElement.querySelector('.loading').classList.remove('d-none');
    loadingElement.classList.add('d-none');
});

// dropdown btn
document.querySelector('#dropdown-menu-options').addEventListener('click', (e) => {
    const targetElement = e.target;

    if (targetElement.getAttribute('id') == 'dropdown-menu-option') {
        e.preventDefault();
        
        const targetBtnId = targetElement.parentElement.parentElement.getAttribute('target');
        const type = targetElement.dataset.type;
        const title = targetElement.title;
        const text = targetElement.textContent;
        
        // change data in button target
        const targetBtnElement = document.querySelector(targetBtnId);
        targetBtnElement.dataset.type = type;
        targetBtnElement.title = title;
        targetBtnElement.textContent = text;
        
        // hide dropdown btn
        targetElement.parentElement.parentElement.classList.add('d-none');
    } 
});
