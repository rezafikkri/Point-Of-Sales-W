import { renderAlert, numberFormatterToCurrency, showModal, hideModal, showPassword } from './module.js';
import Indonesian from '../plugins/flatpickr/id.js';

const tableElement = document.querySelector('#table');
const searchTransactionElement = document.querySelector('a#search-transactions');
const export_transaction_excel = document.querySelector('a#export-transaction-excel');
const modal = document.querySelector('.modal');
const modal_content = modal.querySelector('.modal__content');


// flatpickr setting
flatpickr('input[name="date_range"]', {
    disableMobile: 'true',
    mode: 'range',
    altInput: true,
    altFormat: 'j M, Y',
    altInputClass: 'form-input form-input--rounded-left hover-cursor-pointer',
    locale: Indonesian
});

// search transaction
searchTransactionElement.addEventListener('click', async (e) => {
    e.preventDefault();
    
    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;
    const dateRange = document.querySelector('input[name="date_range"]').value;

    // if empty date range
    if (dateRange.trim() == '') {
        return false;
    }

    // show loading and disable button search
    loadingElement.classList.remove('d-none');
    searchTransactionElement.classList.add('btn--disabled');
    
    try {
        const resultStatusElement = document.querySelector('span#result-status');

        const response = await fetch(`${baseUrl}/admin/transactions/search/${dateRange}`);
        const responseJson = await response.json();

        // if transaction exists
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
                    <td width="10"><a href="#" id="show-transaction-detail" data-transaction-id="${t.transaction_id}" title="Lihat detail transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

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

            // add dataset type-show and dataset date-range
            tableElement.dataset.typeShow = 'date-range';
            tableElement.dataset.dateRange = dateRange;
        }
        // if transaction not exists
        else {
            // inner html message
            tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="8">Transaksi tidak ada.</td></tr>`;

            // show result status
            resultStatusElement.innerText = '0 Total transaksi hasil pencarian';
        }

        const limitMessageElement = document.querySelector('span#limit-message');
        // add limit message if total transaction search > product limit && limit message not exists
        if (responseJson.total_transaction > responseJson.transaction_limit && limitMessageElement == null) {
            const spanElement = document.createElement('span');
            spanElement.classList.add('text-muted');
            spanElement.classList.add('d-block');
            spanElement.classList.add('mt-3');
            spanElement.setAttribute('id', 'limit-message');
            spanElement.innerHTML = `
                Hanya ${responseJson.transaction_limit} Transaksi terbaru yang ditampilkan,
                Pakai fitur <i>Pencarian</i> untuk hasil lebih spesifik!
            `;
            tableElement.after(spanElement);
        }
        // else if total transaction search <= product limit and limit message exists
        else if (responseJson.total_transaction <= responseJson.transaction_limit && limitMessageElement != null) {
            limitMessageElement.remove();
        }
    } catch (error) {
        console.error(error);
    }

    // hide loading and enable button search
    loadingElement.classList.add('d-none');
    searchTransactionElement.classList.remove('btn--disabled');
});

// show hide transaction details
tableElement.querySelector('tbody').addEventListener('click', e => {
    let target = e.target;
    if(target.getAttribute('id') !== 'show-transaction-detail') target = target.parentElement;
    if(target.getAttribute('id') !== 'show-transaction-detail') target = target.parentElement;
    if(target.getAttribute('id') === 'show-transaction-detail') {
        e.preventDefault();

        // if next element sibling exists and next element sibling is tr.table__row-detail, or is mean transaction detail exists in table
        const table_row_detail = target.parentElement.parentElement.nextElementSibling;
        if(table_row_detail !== null && table_row_detail.classList.contains('table__row-detail')) {
            table_row_detail.classList.toggle('table__row-detail--show');

        // else, is mean transaction detail not exists in table
        } else {
            const transaction_id = target.dataset.transactionId;
            const csrfName = tableElement.dataset.csrfName;
            const csrfValue = tableElement.dataset.csrfValue;

            // loading
            loadingElement.classList.remove('d-none');
            // disabled button search
            searchTransactionElement.classList.add('btn--disabled');

            fetch('/admin/tampil_transaksi_detail', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `${csrfName}=${csrfValue}&transaction_id=${transaction_id}`
            })
            .finally(() => {
                // loading
                loadingElement.classList.add('d-none');
                // enabled button search
                searchTransactionElement.classList.remove('btn--disabled');
            })
            .then(response => {
                return response.json();
            })
            .then(json => {
                // set new csrf hash to table tag
                if (responseJson.csrfValue !== undefined) {
                    tableElement.dataset.csrfValue = responseJson.csrfValue;
                }

                // if exists transaction details
                if (responseJson.transaction_details.length > 0) {
                    let li = '';
                    responseJson.transaction_details.forEach(val => {
                        li += `<li><span class="table__title">${val.nama_produk}</span>
                            <span class="table__information">Harga :</span><span class="table__data">
                                ${number_formatter_to_currency(parseInt(val.harga_produk))} / ${val.besaran_produk}
                            </span>
                            <span class="table__information">Jumlah :</span><span class="table__data">${val.jumlah_produk}</span>
                            <span class="table__information">Bayaran :</span><span class="table__data">
                                ${number_formatter_to_currency(parseInt(val.harga_produk*val.jumlah_produk))}
                            </span></li>`;
                    });

                    const tr = document.createElement('tr');
                    tr.classList.add('table__row-detail');
                    tr.classList.add('table__row-detail--show');
                    tr.innerHTML = `<td colspan="7"><ul>${li}</ul></td>`;
                    target.parentElement.parentElement.after(tr);
                }
            })
            .catch(error => {
                console.error(error);
            });
        }
    }
});

document.querySelector('a#remove-transaction').addEventListener('click', e => {
    e.preventDefault();

    const checkboxs_checked = document.querySelectorAll('input[type="checkbox"][name="transaction_id"]:checked');
    // if not found input checkbox checklist
    if (checkboxs_checked.length === 0) {
        return false;
    }

    // show modal
    show_modal(modal, modal_content);
});

// close modal
modal_content.querySelector('a#btn-close').addEventListener('click', e => {
    e.preventDefault();

    // hide modal
    hide_modal(modal, modal_content);

    // reset modal
    modal_content.querySelector('input[name="password"]').value = '';
    const small = modal_content.querySelector('small.form-message')
    if (small !== null) {
        small.remove();
    }
});

// show password
document.querySelector('.modal a#show-password').addEventListener('click', showPassword);

// remove transaction
document.querySelector('a#remove-transaction-in-db').addEventListener('click', e => {
    e.preventDefault();

    // reset form message
    const small = modal_content.querySelector('small.form-message');
    if (small !== null) {
        small.remove();
    }

    // generate data
    let data = '';

    const csrf_name = tableElement.dataset.csrfName;
    const csrfValue = tableElement.dataset.csrfValue;
    const password = modal_content.querySelector('input[name="password"]').value;
    data += `${csrf_name}=${csrfValue}&password=${password}`;

    let transaction_ids = '';
    const checkboxs_checked = document.querySelectorAll('input[type="checkbox"][name="transaction_id"]:checked');
    checkboxs_checked.forEach((val, index) => {
        // if last checkbox
        if (index === checkboxs_checked.length-1) {
            transaction_ids += val.value;
        } else {
            transaction_ids += val.value+',';
        }
    });
    data += `&transaction_ids=${transaction_ids}`;

    // get smallest create time in table
    const all_checkboxs = document.querySelectorAll('input[type="checkbox"][name="transaction_id"]');
    data += `&smallest_create_time=${all_checkboxs[all_checkboxs.length-1].dataset.createTime}`;

    // if dataset type-show and dataset date-range exists in table tag
    if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.dateRange !== undefined) {
        data += `&date_range=${tableElement.dataset.dateRange}`;
    }

    // loading
    e.target.classList.add('btn--disabled');
    e.target.nextElementSibling.classList.remove('d-none');

    fetch('/admin/hapus_transaksi', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .finally(() => {
        // loading
        e.target.classList.remove('btn--disabled');
        e.target.nextElementSibling.classList.add('d-none');
    })
    .then(response => {
        return response.json();
    })
    .then(json => {
        // set new csrf hash to table tag
        if (responseJson.csrfValue !== undefined) {
            tableElement.dataset.csrfValue = responseJson.csrfValue;
        }

        // if remove transaction success
        if (responseJson.status === 'success') {
            checkboxs_checked.forEach(val => {
                // if exists detail transaction in table
                const table_row_detail = val.parentElement.parentElement.parentElement.nextElementSibling;
                if (table_row_detail !== null && table_row_detail.classList.contains('table__row-detail')) {
                    // remove detail transaction
                    table_row_detail.remove();
                }

                // remove transaction checklist
                val.parentElement.parentElement.parentElement.remove();
            });

            // if longer transaction exists
            if (responseJson.longer_transactions.length > 0) {
                responseJson.longer_transactions.forEach((t, i) => {
                    const tr = document.createElement('tr');

                    // if i is odd number
                    if ((i+1)%2 !== 0) {
                        tr.classList.add('table__row-odd');
                    }
                    let td = '<td width="10">';

                    // if transaction is allow for delete
                    if (t.permission_delete === true) {
                        td += `<div class="form-check">
                                <input type="checkbox" name="transaction_id" data-create-time="${t.waktu_buat}" class="form-check-input" value="${t.transaksi_id}">
                            </div>`;
                    }

                    td += `</td>
                        <td width="10"><a href="#" id="show-transaction-detail" data-transaction-id="${t.transaksi_id}" title="Lihat detail transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                        <td>${t.product_total||0}</td>
                        <td>${number_formatter_to_currency(parseInt(t.payment_total||0))}</td>`;

                    if (t.status_transaksi === 'selesai') {
                        td += '<td><span class="text-green">Selesai</span></td>';
                    } else {
                        td += '<td><span class="text-red">Belum</span></td>';
                    }

                    td += `<td>${t.nama_lengkap}</td><td>${t.indo_create_time}</td></tr>`;

                    // inner td to tr
                    tr.innerHTML = td;
                    // append tr to tbody
                    tableElement.querySelector('tbody').append(tr);
                });
            }

            const count_transaction_in_table = tableElement.querySelectorAll('tbody tr').length;
            // if transaction total = 0
            if (responseJson.transaction_total === 0) {
                // inner html message
                tableElement.querySelector('tbody').innerHTML = `<tr class="table__row-odd"><td colspan="7">Transaksi tidak ada.</td></tr>`;

                // if dataset type-show and dataset date-range exists in table tag
                if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.dateRange !== undefined) {
                    // show result status
                    result_status.innerText = '0 Total transaksi hasil pencarian';
                } else {
                    // show result status
                    result_status.innerText = '0 Total transaksi';
                }

            } else {
                // if dataset type-show and dataset date-range exists in table tag
                if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.dateRange !== undefined) {
                    // show result status
                    result_status.innerText = `1 - ${count_transaction_in_table} dari ${responseJson.transaction_total} Total transaksi hasil pencarian`;
                } else {
                    // show result status
                    result_status.innerText = `1 - ${count_transaction_in_table} dari ${responseJson.transaction_total} Total transaksi`;
                }
            }

            // if total transaction in table < transaction limit and limit message exists
            const limitMessageElement = document.querySelector('span#limit-message');
            if (count_transaction_in_table < responseJson.transaction_limit && limitMessageElement !== null) {
                limitMessageElement.remove();
            }
        }
        // else if password sign in user is wrong
        else if (responseJson.status === 'wrong_password') {
            const small = document.createElement('small');
            small.classList.add('form-message');
            small.classList.add('form-message--danger');
            small.innerText = responseJson.message;

            // append message to modal
            modal_content.querySelector('div.modal__body').append(small);
        }
        // else if fail remove transaction
        else if (responseJson.status === 'fail') {
            const alert = create_alert_node(['alert--warning', 'mb-3'], responseJson.message);
            // append alert to before div.main__box element
            document.querySelector('main.main > div').insertBefore(alert, document.querySelector('div.main__box'));

            // reset input checkboxs checked
            checkboxs_checked.forEach(val => {
                val.checked = false;
            });
        }

        if (responseJson.status === 'success' || responseJson.status === 'fail') {
            // hide modal
            hide_modal(modal, modal_content);
            // reset modal
            modal_content.querySelector('input[name="password"]').value = '';
        }
    })
    .catch(error => {
        console.error(error);
    });
});

// export transactions to excel
export_transaction_excel.addEventListener('click', e => {
    e.preventDefault();

    // generate data
    let data = '';

    const csrf_name = tableElement.dataset.csrfName;
    const csrfValue = tableElement.dataset.csrfValue;
    data += `${csrf_name}=${csrfValue}`;

    // if dataset type-show and dataset date-range exists in table tag
    if (tableElement.dataset.typeShow !== undefined && tableElement.dataset.dateRange !== undefined) {
        data += `&date_range=${tableElement.dataset.dateRange}`;
    }

    // loading
    export_transaction_excel.nextElementSibling.classList.remove('d-none');
    // disabled button search
    searchTransactionElement.classList.add('btn--disabled');
    // disabled action in table
    const table_loading = tableElement.parentElement.nextElementSibling;
    table_loading.querySelector('.loading').classList.add('d-none');
    table_loading.classList.remove('d-none');

    fetch('/admin/ekspor_transaksi_ke_excel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .finally(() => {
        // loading
        export_transaction_excel.nextElementSibling.classList.add('d-none');
        // disabled button search
        searchTransactionElement.classList.remove('btn--disabled');
        // disabled action in table
        const table_loading = tableElement.parentElement.nextElementSibling;
        table_loading.querySelector('.loading').classList.remove('d-none');
        table_loading.classList.add('d-none');
    })
    .then(response => {
        return response.json();
    })
    .then(json => {
        // set new csrf hash to table tag
        if (responseJson.csrfValue !== undefined) {
            tableElement.dataset.csrfValue = responseJson.csrfValue;
        }

        // if export transactions success
        if (responseJson.status === 'success') {
             const alert = create_alert_node(['alert--success', 'mb-3'], responseJson.message);
            // append alert to before div.main__box element
            document.querySelector('main.main > div').insertBefore(alert, document.querySelector('div.main__box'));
        }
        // else if export transactions fail
        else if (responseJson.status === 'fail') {
            const alert = create_alert_node(['alert--warning', 'mb-3'], responseJson.message);
            // append alert to before div.main__box element
            document.querySelector('main.main > div').insertBefore(alert, document.querySelector('div.main__box'));
        }
    })
    .catch(error => {
        console.error(error);
    });
});
