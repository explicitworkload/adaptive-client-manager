/*
 * "Invoice values per month" chart (Chart.js 4, MIT licence). Replaces flot.
 * Load after the Chart.js script (webroot/vendor/chartjs-VERSION/chart.umd.min.js).
 *
 *   acmInvoiceChart('placeholder', previousYear, currentYear, '#40c442');
 *
 * previousYear / currentYear: either [[month, total], ...] pairs or an object
 * keyed by month number (1-12).
 */
window.acmInvoiceChart = function (elementId, previous, current, color) {
    var months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    function byMonth(data) {
        var values = new Array(12).fill(0);
        var pairs = Array.isArray(data) ? data : Object.keys(data || {}).map(function (k) { return [k, data[k]]; });
        pairs.forEach(function (pair) {
            var month = parseInt(pair[0], 10);
            if (month >= 1 && month <= 12) {
                values[month - 1] = Number(pair[1]) || 0;
            }
        });
        return values;
    }

    var holder = document.getElementById(elementId);
    if (!holder || !window.Chart) {
        return null;
    }
    var canvas = document.createElement('canvas');
    holder.innerHTML = '';
    holder.appendChild(canvas);

    function series(label, values, lineColor) {
        return {
            label: label,
            data: values,
            borderColor: lineColor,
            backgroundColor: lineColor,
            pointBackgroundColor: '#fff',
            pointBorderColor: lineColor,
            pointRadius: 3,
            borderWidth: 2,
            tension: 0
        };
    }

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                series('Last Year', byMonth(previous), '#ddd'),
                series('Current Year', byMonth(current), color)
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 14, font: { size: 11 } } }
            },
            scales: {
                x: { ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { font: { size: 10 } } }
            }
        }
    });
};
