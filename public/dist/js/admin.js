import renderChart from './chart.js';

document.addEventListener('DOMContentLoaded', async () => {
    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    const response = await fetch(`${baseUrl}/admin/tampilkan-transaksi-dua-bulan-yang-lalu`);
    const transactionsTwoMonthsAgo = await response.json();

    renderChart(transactionsTwoMonthsAgo);

    // hide loading
    loadingElement.classList.add('d-none');
});
