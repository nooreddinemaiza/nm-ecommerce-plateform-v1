
<!-- Chart Modal -->
<div class="modal fade" id="ordersChart" tabindex="-1" aria-labelledby="ordersChartTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ordersChartTitle">
                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                    Les produits les plus commandés
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="miniChart"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('miniChart').getContext('2d');
        let chart = null;
        let compteur = <?= json_encode(array_column($data['productsChart'], 'compteur')); ?>;
        let titre = <?= json_encode(array_column($data['productsChart'], 'title')); ?>;
        document.getElementById('most-ordered').innerHTML = titre[0] + ' <br> (' + compteur[0] + ' fois)';

        // Create gradient for bars
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(75, 192, 192, 0.7)');
        gradient.addColorStop(1, 'rgba(75, 192, 192, 0.3)');

        // Function to create/update chart
        function createChart() {
            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Array(<?php echo count($data); ?>).fill(''), // Pas de labels visibles
                    datasets: [{
                        label: 'Valeurs',
                        data: compteur,
                        backgroundColor: gradient,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hide legend
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.7)',
                            padding: 10,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return titre[tooltipItems[0].dataIndex]; // Affiche le titre au survol
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false // Masquer les labels X
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000
                    }
                }
            });
        }

        // Initialize chart

        // Handle modal events for better responsiveness
        $('#ordersChart').on('shown.bs.modal', function() {
            createChart(); // Recreate chart when modal is shown
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if ($('#ordersChart').hasClass('show')) {
                createChart();
            }
        });
    });
</script>