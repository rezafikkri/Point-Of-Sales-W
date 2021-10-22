import ApexCharts from '../plugins/apexcharts/apexcharts.esm.js'
import id from '../plugins/apexcharts/locales/id.js';

function renderChart(transactionsTwoMonthsAgo)
{
    const options = {
        chart: {
            locales: [id],
            defaultLocale: 'id',
            type: 'area',
            height: 300,
            toolbar: {
                offsetY: -5,
                tools: {
                    download: false
                }
            }
        },
        colors: ['#7874f7'],
        series: [{
            name: 'Transaksi',
            data: transactionsTwoMonthsAgo.amount
        }],
        labels: transactionsTwoMonthsAgo.updated_at,
        xaxis: {
            type: 'datetime',
            labels: {
                style: {
                    colors: '#78909c',
                    fontFamily: 'roboto-regular'
                },
                datetimeFormatter: {
                    year: 'yyyy',
                    month: 'MMM',
                    day: 'dd MMM'
                }
            },
            tooltip: {
                enabled: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#78909c',
                    fontFamily: 'roboto-regular'
                },
                formatter: (val) => new Intl.NumberFormat('id').format(val)
            }
        },
        dataLabels: {
            enabled: false
       },
       grid: {
           borderColor: '#e8e8e8',
           xaxis: {
               lines: {
                   show: true,
               },
           },
       },
       tooltip: {
            style: {
                fontSize: '14.4px',
                fontFamily: 'roboto-regular'
            },
            x: {
                format: 'dd MMMM yyyy'
            }
        }
    };

    const chart = new ApexCharts(document.querySelector('.chart .chart__body'), options);
    chart.render();
}

export default renderChart;
