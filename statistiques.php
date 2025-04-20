<?php
declare(strict_types=1);
$titre = "Statistiques";
$description = "Statistiques Meteo";
require_once './include/header.inc.php';

// Lecture des données CSV
$csvFile = 'villeconsult.csv';
$labels = [];
$data = [];
$communes = [];

if (file_exists($csvFile)) {
    $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        [$name, $visits] = explode(',', $line);
        $labels[] = trim($name, '"');
        $data[] = (int)$visits;
        $communes[] = ['name' => trim($name, '"'), 'visits' => (int)$visits];
    }
}
?>

<main class="main-content">
    <div style="margin-top: 50px;"></div>
    
    <h2 id="auto-weather-title1">Statistiques des visites par commune</h2>
    
    <!-- Section Graphique -->
    <div class="container mb-5">
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container" style="position: relative; height:60vh; width:100%">
                    <canvas id="visitChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section Tableau -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Commune</th>
                                <th scope="col">Visites</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($communes as $index => $commune): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= htmlspecialchars($commune['name']) ?></td>
                                <td><?= number_format($commune['visits'], 0, ',', ' ') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Inclusion de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérification des données
    const labels = <?= json_encode($labels) ?>;
    const chartData = <?= json_encode($data) ?>;
    
    if (!labels.length || !chartData.length) {
        console.warn('Aucune donnée à afficher');
        return;
    }

    // Configuration du graphique
    const ctx = document.getElementById('visitChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nombre de visites',
                data: chartData,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: 'Nombre de visites'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Communes'
                    }
                }
            }
        }
    });
});
</script>

<?php require_once "./include/footer.inc.php"; ?>