import ApexCharts from '../plugins/apexcharts/apexcharts.esm.js'
import id from '../plugins/apexcharts/locales/id.js';
import { abbreviateNumber } from './module.js';

function renderChart(transactionsTwoMonthsAgo)
{
    const options = {
        chart: {
            locales: [id],
            defaultLocale: 'id',
            type: 'area',
            height: 300,
            toolbar: {
                tools: {
                    download: false
                }
            },
            zoom: {
                enabled: false
            }
        },
        colors: ['#7874f7'],
        series: [{
            name: 'Transaksi',
            data: transactionsTwoMonthsAgo.amount
        }],
        labels: transactionsTwoMonthsAgo.edited_at,
        xaxis: {
            type: 'datetime',
            labels: {
                style: {
                    colors: '#78909c',
                    fontFamily: 'roboto-regular'
                },
                datetimeFormatter: {
                    year: 'y',
                    month: 'MMM',
                    day: 'dd MMM',
                    hour: ''
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#78909c',
                    fontFamily: 'roboto-regular'
                },
                formatter: (val) => abbreviateNumber(val)
            }
        },
        dataLabels: {
            enabled: false
       },
       grid: {
           borderColor: '#e8e8e8',
       },
       tooltip: {
            style: {
                fontSize: '14.4px',
                fontFamily: 'roboto-regular'
            },
            x: {
                format: 'dd MMMM y'
            }
        }
    };

    const chart = new ApexCharts(document.querySelector('#chart-body'), options);
    chart.render();
}

export default renderChart;
