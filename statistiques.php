<?php
declare(strict_types=1);
$titre = "Statistiques";
$description = "Statistiques Meteo";
require_once './include/header.inc.php';
?>
<?php
// Read CSV data
$csvFile = 'villeconsult.csv';
$labels = [];
$data = [];
$communes = []; // Array to store commune data for the table

if (file_exists($csvFile)) {
    $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        [$name, $visits] = explode(',', $line);
        $labels[] = $name;
        $data[] = (int)$visits;
        $communes[] = ['name' => $name, 'visits' => (int)$visits]; // Store for table
    }
}
?>

<main class="main-content">
    
                                                                                                                                                                                                                                                <br/>
                                                                                                                                                                                                        <br/>
                                                                                                                                                                                                                                    <br/>
                                                                                                                                                                                                     <br/>
                                                                                                                                                                                                                <br/>
    <h2>Nombre de visites par commune</h2>
    <!-- Data Table Section -->
    <div class="table-responsive">
        <table class="table table-striped">
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
                    <td><?= htmlspecialchars(trim($commune['name'], '"')) ?></td>
                    <td><?= number_format($commune['visits'], 0, ',', ' ') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    
<!--

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js">


<canvas id="visitChart" width="800" height="400"></canvas>



    document.addEventListener('DOMContentLoaded', function() {
        try {
            const ctx = document.getElementById('visitChart');
            if (!ctx) {
                console.error('Canvas element not found');
                return;
            }

            // Ensure data is properly encoded
            const labels = <?= json_encode($labels ?? []) ?>;
            const chartData = <?= json_encode($data ?? []) ?>;
            
            if (!Array.isArray(labels) || !Array.isArray(chartData)) {
                console.error('Invalid chart data format');
                return;
            }

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre de visites',
                        data: chartData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
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
        } catch (error) {
            console.error('Error initializing chart:', error);
        }
    });
</script>


        -->





    
    </main>
    
    <?php require_once "./include/footer.inc.php"; ?>