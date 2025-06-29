let chartInstance = null;

function renderChart(ctx, x, y, label = '') {
  if (chartInstance) {
    chartInstance.destroy();
  }
  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: x,
      datasets: [{
        label: label,
        data: y,
        borderWidth: 2,
        borderColor: 'rgba(33, 150, 243, 1)',
        backgroundColor: 'rgba(33, 150, 243, 0.15)',
        pointBackgroundColor: 'rgba(255,255,255,1)',
        pointBorderColor: 'rgba(33, 150, 243, 1)',
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: 0.3,
        fill: true,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          labels: {
            color: '#222',
            font: {
              size: 16,
              family: 'Segoe UI, Arial, sans-serif',
              weight: 'bold',
            },
            padding: 20,
          }
        },
        tooltip: {
          backgroundColor: 'rgba(33, 150, 243, 0.9)',
          titleColor: '#fff',
          bodyColor: '#fff',
          borderColor: '#fff',
          borderWidth: 1,
          padding: 12,
        }
      },
      scales: {
        x: {
          ticks: {
            color: '#333',
            font: {
              size: 13,
              family: 'Segoe UI, Arial, sans-serif',
            },
            autoSkip: true,
            maxTicksLimit: 10,
          },
          grid: {
            color: 'rgba(33, 150, 243, 0.08)',
            borderColor: 'rgba(33, 150, 243, 0.2)',
          }
        },
        y: {
          beginAtZero: false,
          ticks: {
            color: '#333',
            font: {
              size: 13,
              family: 'Segoe UI, Arial, sans-serif',
            }
          },
          grid: {
            color: 'rgba(33, 150, 243, 0.08)',
            borderColor: 'rgba(33, 150, 243, 0.2)',
          }
        }
      }
    }
  });
}