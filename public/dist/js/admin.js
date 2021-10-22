import renderChart from './chart.js';

document.addEventListener('DOMContentLoaded', async function() {
    const loadingElement = document.querySelector('#loading');
    const baseUrl = this.querySelector('html').dataset.baseUrl;

    const response = await fetch(`${baseUrl}/admin/transactions-two-months-ago`);
    const transactionsTwoMonthsAgo = await response.json();

    renderChart(transactionsTwoMonthsAgo);

    // hide loading
    loadingElement.classList.add('d-none');
});
