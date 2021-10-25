import { renderAlert, postData } from './module.js';

// remove product category
const table = document.querySelector('table.table');
table.querySelector('tbody').addEventListener('click', async (e) => {
    console.log('test');
    let target = e.target;

    if (target.getAttribute('id') !== 'remove-product-category') target = target.parentElement;
    if (target.getAttribute('id') !== 'remove-product-category') target = target.parentElement;

    if (target.getAttribute('id') === 'remove-product-category') {
        e.preventDefault();

        const loadingElement = table.parentElement.nextElementSibling;

        // data for remove product category
        const productCategoryId = target.dataset.productCategoryId;
        const csrfName = table.dataset.csrfName;
        const csrfValue = table.dataset.csrfValue;

        // show loading
        loadingElement.classList.remove('d-none');

        try {
            const responseJson = await postData(
                '/admin/kategori-produk/menghapus',
                `${csrfName}=${csrfValue}&product_category_id=${productCategoryId}`
            );

            // set new csrf hash to table tag
            if (responseJson.csrf_value !== undefined) {
                table.dataset.csrfValue = responseJson.csrf_value;
            }

            // if success remove product category
            if (responseJson.status === 'success') {
                target.parentElement.parentElement.remove();
            }
            // else if fail remove product category
            else if (responseJson.status === 'fail') {
                const parentElement = document.querySelector('main.main');
                const referenceElement = document.querySelector('div.main__box');
                const message = `
                    ${responseJson.message}
                    <a href="https://github.com/rezafikkri/Point-Of-Sales-W/wiki/Kategori-Produk#gagal-menghapus-kategori-produk"
                    target="_blank" rel="noreferrer noopener">Pelajari lebih lanjut!</a>
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
