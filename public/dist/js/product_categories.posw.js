import { create_alert_node } from './module.posw.js';

// remove product category
const table = document.querySelector('table.table');
table.querySelector('tbody').addEventListener('click', (e) => {
    let target = e.target;

    if (target.getAttribute('id') !== 'remove-product-category') target = target.parentElement;
    if (target.getAttribute('id') !== 'remove-product-category') target = target.parentElement;

    if (target.getAttribute('id') === 'remove-product-category') {
        e.preventDefault();

        // data for remove product category
        const product_category_id = target.dataset.categoryProductId;
        const csrf_name = table.dataset.csrfName;
        const csrf_value = table.dataset.csrfValue;

        // loading
        table.parentElement.nextElementSibling.classList.remove('d-none');

        fetch('/admin/hapus_kategori_produk_di_db', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `${csrf_name}=${csrf_value}&product_category_id=${product_category_id}`
        })
        .finally(() => {
            // loading
            table.parentElement.nextElementSibling.classList.add('d-none');
        })
        .then(response => {
            return response.json();
        })
        .then(json => {
            // set new csrf hash to table tag
            if (json.csrf_value !== undefined) {
                table.dataset.csrfValue = json.csrf_value;
            }

            // if success remove product category
            if (json.status === 'success') {
                target.parentElement.parentElement.remove();
            }
            // else if fail remove product category
            else if (json.status === 'fail') {
                const alert = create_alert_node(['alert--warning', 'mb-3'], json.message);

                // append alert to before div.main__box element
                document.querySelector('main.main > div').insertBefore(alert, document.querySelector('div.main__box'));
            }
        })
        .catch(error => {
            console.error(error);
        });
    }
});
