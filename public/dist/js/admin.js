import renderChart from './chart.js';

document.addEventListener('DOMContentLoaded', async () => {
    const loadingElement = document.querySelector('#loading');
    const baseUrl = document.querySelector('html').dataset.baseUrl;

    const response = await fetch(`${baseUrl}/admin/tampilkan-transaksi-dua-bulan-yang-lalu`);
    const responseJson = await response.json();
    
    // if transaction two months ago exist
    if (responseJson.length > 0) {
        renderChart(responseJson);
    } else {
        document.querySelector('#chart-body').innerHTML = '<p class="text-muted mb-0">Transaksi tidak ada.</p>';
    }

    // hide loading
    loadingElement.classList.add('d-none');
});
