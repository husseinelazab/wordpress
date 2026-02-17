(function () {
  const chartContainer = document.getElementById('rtaibah-chart');
  const chartList = document.getElementById('rtaibah-chart-list');

  function drawList(labels, values) {
    if (!chartList) {
      return;
    }

    chartList.innerHTML = '';
    const max = Math.max(...values, 1);

    labels.forEach((label, idx) => {
      const value = Number(values[idx] || 0);
      const li = document.createElement('li');
      li.className = 'chart-row';
      li.innerHTML = `
        <span class="label">${label}</span>
        <div class="bar-wrap">
          <span class="bar" style="width:${(value / max) * 100}%"></span>
        </div>
        <strong>${value}</strong>
      `;
      chartList.appendChild(li);
    });
  }

  function updateCards(totals) {
    document.querySelectorAll('[data-key]').forEach((el) => {
      const key = el.getAttribute('data-key');
      if (Object.prototype.hasOwnProperty.call(totals, key)) {
        el.textContent = totals[key];
      }
    });
  }

  function renderFromData(data) {
    drawList(data.chart.labels || [], data.chart.values || []);
    updateCards(data.totals || {});
  }

  if (chartContainer) {
    try {
      const labels = JSON.parse(chartContainer.dataset.labels || '[]');
      const values = JSON.parse(chartContainer.dataset.values || '[]');
      drawList(labels, values);
    } catch (e) {
      console.error(e);
    }
  }

  async function fetchMetrics() {
    if (!window.rtaibahDashboardData) {
      return;
    }

    const { endpoint, nonce } = window.rtaibahDashboardData;

    const response = await fetch(`${endpoint}&nonce=${encodeURIComponent(nonce)}`, {
      credentials: 'same-origin',
    });

    const result = await response.json();
    if (result.success) {
      renderFromData(result.data);
    }
  }

  fetchMetrics();
  window.setInterval(fetchMetrics, 30000);
})();
