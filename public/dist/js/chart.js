import ApexCharts from '../plugins/apexcharts/apexcharts.esm.js'
import id from '../plugins/apexcharts/locales/id.js';

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
        data: [1053 ,1233 ,1261 ,620 ,894 ,1167 ,1384 ,1393 ,1484 ,1404 ,922 ,1142 ,1414 ,1624 ,1690 ,1954 ,2057 ,1390 ,1760 ,2137 ,2557 ,2881 ,2720 ,3263 ,1932 ,2234 ,3385 ,3835 ,3145 ,3948]
    }],
    labels: [1634169600000 ,1634083200000 ,1633996800000 ,1633910400000 ,1633824000000 ,1633737600000 ,1633651200000 ,1633564800000 ,1633478400000 ,1633392000000 ,1633305600000 ,1633219200000 ,1633132800000 ,1633046400000 ,1632960000000 ,1632873600000 ,1632787200000 ,1632700800000 ,1632614400000 ,1632528000000 ,1632441600000 ,1632355200000 ,1632268800000 ,1632182400000 ,1632096000000 ,1632009600000 ,1631923200000 ,1631836800000 ,1631750400000 ,1631664000000],
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
       strokeDashArray: 4
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
