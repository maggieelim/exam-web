<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData ?? []);

    // Chart configurations
    const chartConfigs = {
      scoreDistributionChart: {
        type: 'bar',
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: true,
              text: 'Score Distribution'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of Students'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Score Range (%)'
              }
            }
          }
        }
      },
      difficultyChart: {
        type: 'doughnut',
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            title: {
              display: true,
              text: 'Difficulty Level Distribution'
            }
          }
        }
      },
      discriminationChart: {
        type: 'pie',
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            title: {
              display: true,
              text: 'Discrimination Index Quality'
            }
          }
        }
      }
    };

    // Initialize charts
    initializeCharts(chartData, chartConfigs);
  });

  function initializeCharts(chartData, configs) {
    Object.keys(configs).forEach(chartId => {
      const config = configs[chartId];
      const canvas = document.getElementById(chartId);

      if (!canvas || !chartData[getDataKey(chartId)]) {
        return;
      }

      const ctx = canvas.getContext('2d');
      const dataKey = getDataKey(chartId);

      new Chart(ctx, {
        type: config.type,
        data: {
          labels: Object.keys(chartData[dataKey]),
          datasets: [{
            label: config.type === 'bar' ? 'Number of Students' : '',
            data: Object.values(chartData[dataKey]),
            backgroundColor: getChartColors(chartId),
            borderWidth: 1
          }]
        },
        options: config.options
      });
    });
  }

  function getDataKey(chartId) {
    const keyMap = {
      'scoreDistributionChart': 'scores',
      'difficultyChart': 'difficulty',
      'discriminationChart': 'discrimination'
    };
    return keyMap[chartId] || chartId;
  }

  function getChartColors(chartId) {
    const colorMap = {
      'scoreDistributionChart': ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'],
      'difficultyChart': ['#198754', '#20c997', '#ffc107', '#dc3545'],
      'discriminationChart': ['#198754', '#20c997', '#ffc107', '#fd7e14', '#dc3545']
    };
    return colorMap[chartId] || ['#007bff'];
  }
</script>