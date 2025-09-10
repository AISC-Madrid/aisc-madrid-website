<!DOCTYPE html>
<html lang="es">

<?php include("../../assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../assets/nav.php"); ?>

    <div class="container scroll-margin my-5 flex-fill">
        <div class="text-center mb-5 px-3 px-md-5">
            <h2 class="fw-bold mb-4" style="color: var(--muted);">
                Dashboard AISC Madrid
            </h2>
            <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
            <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px;">
                Visualiza las estadísticas de los estudiantes que se han unido a nuestra comunidad.
            </h6>
        </div>

        <?php
        include('../../assets/db.php'); // DB connection
        $result = $conn->query("SELECT * FROM form_submissions");
        $ai_topics_count = [];
        $course_year_count = [];
        $degree_count = [];
        $knowledge_level_count = [];

        while ($row = $result->fetch_assoc()) {
            // AI topics
            $topics = explode(", ", $row['ai_topics'] ?? '');
            foreach ($topics as $topic) {
                if (!empty($topic)) {
                    if (!isset($ai_topics_count[$topic])) $ai_topics_count[$topic] = 0;
                    $ai_topics_count[$topic]++;
                }
            }

            // Course year
            $year = $row['course_year'];
            if (!empty($year)) {
                if (!isset($course_year_count[$year])) $course_year_count[$year] = 0;
                $course_year_count[$year]++;
            }

            // Degree
            $deg = $row['degree'];
            if (!empty($deg)) {
                if (!isset($degree_count[$deg])) $degree_count[$deg] = 0;
                $degree_count[$deg]++;
            }

            // Knowledge level
            $level = $row['knowledge_level'];
            if (!empty($level)) {
                if (!isset($knowledge_level_count[$level])) $knowledge_level_count[$level] = 0;
                $knowledge_level_count[$level]++;
            }
        }

        $conn->close();

        $ai_topics_labels = json_encode(array_keys($ai_topics_count));
        $ai_topics_data = json_encode(array_values($ai_topics_count));
        $course_year_labels = json_encode(array_keys($course_year_count));
        $course_year_data = json_encode(array_values($course_year_count));
        $degree_labels = json_encode(array_keys($degree_count));
        $degree_data = json_encode(array_values($degree_count));
        $knowledge_labels = json_encode(array_keys($knowledge_level_count));
        $knowledge_data = json_encode(array_values($knowledge_level_count));
        ?>

        <div class="row g-4">
            <div class="col-12">
                <div class="form-card p-4 shadow-sm">
                    <h5 class="mb-3" style="color: var(--primary);">Áreas de interés</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        foreach ($ai_topics_count as $topic => $count) {
                            // Adjust size based on count (min 0.8rem, max 2rem)
                            $size = 0.8 + min($count, 10) * 0.12; // caps at ~2 rem
                            echo '<span class="badge bg-primary" style="font-size: ' . $size . 'rem;">' . htmlspecialchars($topic) . ' (' . $count . ')</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-card p-4 shadow-sm h-100">
                    <h5 class="mb-3" style="color: var(--primary);">Curso</h5>
                    <canvas id="courseYearChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-card p-4 shadow-sm h-100">
                    <h5 class="mb-3" style="color: var(--primary);">Grado</h5>
                    <canvas id="degreeChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-card p-4 shadow-sm h-100">
                    <h5 class="mb-3" style="color: var(--primary);">Nivel de conocimientos en IA</h5>
                    <canvas id="knowledgeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php include('../../assets/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        

        const yearCtx = document.getElementById('courseYearChart').getContext('2d');
        new Chart(yearCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $course_year_labels; ?>,
                datasets: [{
                    label: 'Estudiantes',
                    data: <?php echo $course_year_data; ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const degreeCtx = document.getElementById('degreeChart').getContext('2d');
        new Chart(degreeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $degree_labels; ?>,
                datasets: [{
                    label: 'Estudiantes',
                    data: <?php echo $degree_data; ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        const knowledgeCtx = document.getElementById('knowledgeChart').getContext('2d');
        new Chart(knowledgeCtx, {
            type: 'pie',
            data: {
                labels: <?php echo $knowledge_labels; ?>,
                datasets: [{
                    data: <?php echo $knowledge_data; ?>,
                    backgroundColor: ['rgba(255, 206, 86, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(153, 102, 255, 0.7)'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
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