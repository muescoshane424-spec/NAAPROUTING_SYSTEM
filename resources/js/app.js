// resources/js/app.js
import './bootstrap';
console.log('App JS loaded');

import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Make sure your Blade has hidden inputs:
// <input type="hidden" id="inTransit" value="{{ $stats['in_transit'] }}">
// <input type="hidden" id="completed" value="{{ $stats['completed'] }}">
// <input type="hidden" id="pending" value="{{ $stats['pending'] }}">

const ctx = document.getElementById('documentFlowChart').getContext('2d');

const documentFlowChart = new Chart(ctx, {
    type: 'bar', // chart type
    data: {
        labels: ['In Transit', 'Completed', 'Pending Approvals'],
        datasets: [{
            label: 'Documents',
            data: [
                parseInt(document.getElementById('inTransit').value),
                parseInt(document.getElementById('completed').value),
                parseInt(document.getElementById('pending').value)
            ],
            backgroundColor: ['#FF99CC','#66CC99','#FFCC66'],
        }]
    },
    options: { // <-- moved inside the Chart constructor
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            },
            title: {
                display: true,
                text: 'Document Flow by Status'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});