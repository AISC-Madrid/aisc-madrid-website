<!DOCTYPE html>
<html lang="es">

<?php include("../../assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../assets/nav.php"); ?>

    <div class="container scroll-margin my-5 flex-fill">
        <!-- Header -->
        <div class="text-center mb-5 px-3 px-md-5">
            <h2 class="fw-bold mb-4" style="color: var(--muted);">Dashboard AISC Madrid</h2>
            <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
            <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px;">
                Visualiza las estadísticas de los estudiantes que se han unido a nuestra comunidad.
            </h6>
        </div>

        <?php
        include('../../assets/db.php');

        // Mapping values to descriptive names
        $ai_labels_map = [
            "ml" => "Machine Learning",
            "dl" => "Deep Learning",
            "nn" => "Redes Neuronales",
            "transformers" => "Transformers / Modelos de Lenguaje",
            "nlp" => "Procesamiento de Lenguaje Natural",
            "cv" => "Visión por Computador",
            "rl" => "Aprendizaje por Refuerzo",
            "generative_ai" => "IA Generativa",
            "ai_ethics" => "Ética en IA",
            "robotics" => "Robótica",
            "data_viz" => "Visualización de Datos",
            "edge_ai" => "IA en el Edge / IoT"
        ];

        // Initialize counts
        $ai_topics_count = [];
        $course_year_count = [];
        $degree_count = [];
        $knowledge_level_count = [];

        $result = $conn->query("SELECT * FROM form_submissions");
        while ($row = $result->fetch_assoc()) {
            // AI topics
            $topics = explode(", ", $row['interests'] ?? '');
            foreach ($topics as $t) {
                if (!empty($t)) {
                    $label = $ai_labels_map[$t] ?? $t;
                    $ai_topics_count[$label] = ($ai_topics_count[$label] ?? 0) + 1;
                }
            }

            // Course year
            $year = $row['course_year'];
            if (!empty($year)) $course_year_count[$year] = ($course_year_count[$year] ?? 0) + 1;

            // Degree
            $deg = $row['degree'];
            if (!empty($deg)) $degree_count[$deg] = ($degree_count[$deg] ?? 0) + 1;

            // Knowledge level
            $level = $row['knowledge_level'];
            if (!empty($level)) $knowledge_level_count[$level] = ($knowledge_level_count[$level] ?? 0) + 1;
        }
        $conn->close();

        // JSON for charts
        $course_year_labels = json_encode(array_keys($course_year_count));
        $course_year_data = json_encode(array_values($course_year_count));
        $degree_labels = json_encode(array_keys($degree_count));
        $degree_data = json_encode(array_values($degree_count));
        $knowledge_labels = json_encode(array_keys($knowledge_level_count));
        $knowledge_data = json_encode(array_values($knowledge_level_count));
        ?>

        <div class="row g-4">
            <!-- AI Topics Tag Cloud -->
            <div class="col-12">
                <div class="form-card p-4 shadow-sm">
                    <h5 class="mb-3" style="color: var(--primary);">Áreas de interés</h5>
                    <div class="d-flex flex-wrap gap-2" id="ai-topic-cloud">
                        <?php
                        foreach ($ai_topics_count as $topic => $count):
                            // Scale font size based on count (min 0.8rem, max 2.5rem)
                            $size = 0.8 + min($count, 10) * 0.17;

                            // Generate random color
                            $r = rand(50, 200);
                            $g = rand(50, 200);
                            $b = rand(50, 200);
                            $color = "rgb($r,$g,$b)";
                        ?>
                            <span class="fw-bold badge"
                                style="font-size: <?= $size ?>rem; color: <?= $color ?>;">
                                <?= htmlspecialchars($topic) ?> (<?= $count ?>)
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>


            <!-- Course Year Chart -->
            <div class="col-md-6">
                <div class="form-card p-4 shadow-sm h-100">
                    <h5 class="mb-3" style="color: var(--primary);">Curso</h5>
                    <canvas id="courseYearChart"></canvas>
                </div>
            </div>

            <!-- Degree Chart -->
            <div class="col-md-6">
                <div class="form-card p-4 shadow-sm h-100">
                    <h5 class="mb-3" style="color: var(--primary);">Grado</h5>
                    <canvas id="degreeChart"></canvas>
                </div>
            </div>

            <!-- Knowledge Level Chart -->
            <div class="col-md-6">
                <div class="form-card p-4 shadow-sm h-100">
                    <h5 class="mb-3" style="color: var(--primary);">Nivel de conocimientos en IA</h5>
                    <canvas id="knowledgeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php include('../../assets/footer.php'); ?>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Charts -->
    <script>
        function createBarChart(ctxId, labels, data, color, horizontal = false) {
            const ctx = document.getElementById(ctxId).getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Estudiantes',
                        data: data,
                        backgroundColor: color,
                        borderColor: color.replace('0.7', '1'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: horizontal ? 'y' : 'x',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function createPieChart(ctxId, labels, data, colors) {
            const ctx = document.getElementById(ctxId).getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        }

        createBarChart('courseYearChart', <?= $course_year_labels ?>, <?= $course_year_data ?>, 'rgba(255, 99, 132, 0.7)');
        createBarChart('degreeChart', <?= $degree_labels ?>, <?= $degree_data ?>, 'rgba(75, 192, 192, 0.7)', true);
        createPieChart('knowledgeChart', <?= $knowledge_labels ?>, <?= $knowledge_data ?>,
            ['rgba(255, 206, 86, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(153, 102, 255, 0.7)']);
    </script>

    <style>
        .form-card {
            border-radius: 0.75rem;
            background-color: #fff;
            border: 1px solid #e2e2e2;
        }
    </style>
</body>

</html>