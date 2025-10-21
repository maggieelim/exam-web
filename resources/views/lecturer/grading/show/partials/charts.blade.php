@php
    $charts = [
        [
            'id' => 'scoreDistributionChart',
            'title' => 'Score Distribution',
            'type' => 'bar',
            'data' => $chartData['scores'] ?? [],
        ],
        [
            'id' => 'difficultyChart',
            'title' => 'Question Difficulty Level',
            'type' => 'doughnut',
            'data' => $chartData['difficulty'] ?? [],
        ],
        [
            'id' => 'discriminationChart',
            'title' => 'Discrimination Index',
            'type' => 'pie',
            'data' => $chartData['discrimination'] ?? [],
        ],
    ];
@endphp

@foreach ($charts as $chart)
    <div class="col-md-4">
        <div class="card">
            <div class="card-header mb-0 pb-0">
                <h6 class="card-title mb-0">{{ $chart['title'] }}</h6>
            </div>
            <div class="card-body">
                <canvas id="{{ $chart['id'] }}" height="300"></canvas>
            </div>
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Score Distribution Chart
        const scoreCtx = document.getElementById('scoreDistributionChart');
        if (scoreCtx) {
            new Chart(scoreCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(@json($chartData['scores'] ?? [])),
                    datasets: [{
                        label: 'Number of Students',
                        data: Object.values(@json($chartData['scores'] ?? [])),
                        backgroundColor: [
                            '#e63946', // merah lembut
                            '#f77f00', // oranye terang
                            '#ffba08', // kuning
                            '#90be6d', // hijau muda
                            '#4eaf04ff' // hijau tua
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Students'
                            },
                            ticks: {
                                stepSize: 1
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
            });
        }

        // Difficulty Chart
        const difficultyCtx = document.getElementById('difficultyChart');
        if (difficultyCtx) {
            new Chart(difficultyCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(@json($chartData['difficulty'] ?? [])),
                    datasets: [{
                        data: Object.values(@json($chartData['difficulty'] ?? [])),
                        backgroundColor: ['#4eaf04ff', '#90be6d', '#ffba08', '#e63946'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                    }
                }
            });
        }

        // Discrimination Chart
        const discriminationCtx = document.getElementById('discriminationChart');
        if (discriminationCtx) {
            new Chart(discriminationCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(@json($chartData['discrimination'] ?? [])),
                    datasets: [{
                        data: Object.values(@json($chartData['discrimination'] ?? [])),
                        backgroundColor: ['#4eaf04ff', '#90be6d', '#ffba08', '#f77f00',
                            '#e63946'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },

                    }
                }
            });
        }
    });
</script>
