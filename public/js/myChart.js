let chartInstance = null;

function renderChart(ctx, labels, data, labelText) {
  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: labelText,
          data: data,
          borderColor: "blue",
          borderWidth: 2,
          backgroundColor: "rgba(0, 0, 255, 0.1)",
          tension: 0.3,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        x: {
          ticks: { autoSkip: true, maxTicksLimit: 10 },
        },
        y: {
          beginAtZero: false,
        },
      },
    },
  });
}

function setTimeframe(range) {
  const ctx = document.getElementById("myChart").getContext('2d');
  const filteredLabels = [];
  const filteredData = [];
  const now = new Date();

  if (range === "day") {
    const dateInput = document.getElementById('datePicker').value;
    if (!dateInput) {
      alert('Пожалуйста, выберите дату!');
      return;
    }
    const dayStart = new Date(dateInput + "T00:00:00");
    const dayEnd = new Date(dateInput + "T23:59:59");
    for (let i = 0; i < originalLabels.length; i++) {
      const pointDate = new Date(originalLabels[i]);
      if (pointDate >= dayStart && pointDate <= dayEnd) {
        filteredLabels.push(originalLabels[i]);
        filteredData.push(originalData[i]);
      }
    }
  } else {
    for (let i = 0; i < originalLabels.length; i++) {
      const date = new Date(originalLabels[i]);
      const diff = (now - date) / (1000 * 60 * 60 * 24); // разница в днях
      if (
        (range === "week" && diff <= 7) ||
        (range === "month" && diff <= 30) ||
        range === "all"
      ) {
        filteredLabels.push(originalLabels[i]);
        filteredData.push(originalData[i]);
      }
    }
  }

  renderChart(ctx, filteredLabels, filteredData, "Stocks price change");
} 