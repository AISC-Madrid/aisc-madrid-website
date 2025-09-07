<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Regression Game AISC Madrid</title>
    <meta name="description"
        content="AISC Madrid is the Artificial Intelligence Student Collective at UC3M. We organize AI events, workshops, and talks to explore real-world applications of AI in university and beyond. Join the AI movement in Madrid.">
    <link rel="canonical" href="https://aiscmadrid.com/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="https://aiscmadrid.com/images/logos/AISC Logo Square.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f7f7f7;
        }

        #chart {
            max-width: 80%;
            height: auto;
        }

        .notice {
            font-size: 14px;
            color: gray;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="text-dark w-100 d-flex flex-column align-items-center justify-content-start" style="height: 100vh; ">

        <!-- Title + Info (30%) -->
        <div class="d-flex align-items-center justify-content-around p-2" 
            style="width:80%; background-color: transparent; border: none; box-shadow: none;">
            <div class="d-flex flex-column align-items-center justify-content-around">
                <div class="text-danger fw-bold fs-2" id="info">Min error: <span id="error">0</span></div>
                <div id="error-message" class="error text-warning fw-semibold"></div>
            </div>
            <div>
                <h1 class="pt-3 fw-bold fs-2 text-center">📈 Regression Game AISC Madrid</h1>
                <p class="text-muted mb-4">Guess the line, minimize the error, and climb the leaderboard!</p>
            </div>
            <button class="btn btn-lg btn-warning fw-bold shadow" style="background-color: #EB178E; color: black;" onclick="resetGame()">Reset Game</button>
        </div>

        <!-- Game Area (80%) -->
        <div class="d-flex w-100 justify-content-around py-2 px-4" style="flex: 8; width: 100%;">

            <!-- Leaderboard -->
            <div id="error-log" class="bg-white text-dark rounded-3 py-3 me-3 shadow-lg" style="width: 30%; overflow-y: auto; background: linear-gradient(to top, #EB178E 90%, #ffff 10%);">
                <h4 class="fw-bold text-center text-dark mb-3">Leaderboard</h4>
                <ul id="error-log-list"
                    style="list-style-type: none; padding-left: 0; font-size: 1.1rem; line-height: 1.6;">
                </ul>
            </div>

            <!-- Chart -->
            <div class="bg-white rounded-3 p-3" style="flex-grow: 1; display: flex; align-items: center; justify-content: center;">
                <canvas id="chart"></canvas>
            </div>
        </div>
    </div>



    <script>
        const canvas = document.getElementById("chart");
        const ctx = canvas.getContext("2d");
        let generatedPoints = [];
        let userInfo = null;
        let userPlayed = false;
        let guessLine = null;
        let tempClicks = [];
        let minError = 0;

        // Initialize Chart.js
        const chart = new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Generated Points',
                    data: [],
                    backgroundColor: 'blue',
                    pointRadius: 4,
                    showLine: false
                }, {
                    label: 'Guessed Points',
                    data: [],
                    backgroundColor: 'black',
                    pointRadius: 5,
                    showLine: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        min: -10,
                        max: 10,
                        title: {
                            display: true,
                            text: "X Axis"
                        }
                    },
                    y: {
                        min: -10,
                        max: 10,
                        title: {
                            display: true,
                            text: "Y Axis"
                        }
                    }
                }
            }
        });

        function fetchGamePoints() {
            $.getJSON("get_random_points.php", function(data) {
                if (!data || !Array.isArray(data)) return;

                // Clear the dataset of generated points
                chart.data.datasets[0].data = [];

                // Populate dataset[0] with the fetched points
                chart.data.datasets[0].data = data.map(p => ({
                    x: p.x,
                    y: p.y
                }));

                chart.update();
            });
        }

        // Random normal distribution
        function randomNormal() {
            let u = 0,
                v = 0;
            while (u === 0) u = Math.random();
            while (v === 0) v = Math.random();
            return Math.sqrt(-2.0 * Math.log(u)) * Math.cos(2.0 * Math.PI * v);
        }


        /**
         * Compute MSE of a guessed line against generated points
         * @param {Array<{x: number, y: number}>} points - generated points
         * @param {number} slope - user's guessed slope (m)
         * @param {number} intercept - user's guessed intercept (b)
         * @returns {number} MSE
         */
        function computeMSE(points, slope, intercept) {
            if (!points || points.length === 0) return 0;

            let sumSquaredError = 0;
            for (const p of points) {
                const yPred = slope * p.x + intercept;
                const error = yPred - p.y;
                sumSquaredError += error * error;
            }

            return sumSquaredError / points.length;
        }

        /**
         * Compute the best slope and intercept using Ordinary Least Squares
         * @param {Array<{x: number, y: number}>} points - generated points
         * @returns {{slope: number, intercept: number}}
         */
        function computeBestFit(points) {
            if (!points || points.length === 0) {
                return {
                    slope: 0,
                    intercept: 0
                }; // fallback
            }

            const n = points.length;
            let sumX = 0,
                sumY = 0,
                sumXY = 0,
                sumX2 = 0;

            for (const p of points) {
                sumX += p.x;
                sumY += p.y;
                sumXY += p.x * p.y;
                sumX2 += p.x * p.x;
            }

            const denominator = (n * sumX2 - sumX * sumX);
            if (denominator === 0) {
                return {
                    slope: 0,
                    intercept: 0
                }; // all x are the same, vertical line case
            }

            const slope = (n * sumXY - sumX * sumY) / denominator;
            const intercept = (sumY - slope * sumX) / n;

            return {
                slope,
                intercept
            };
        }



        // Generate regression points
        function generateRandomPoints() {
            const slope = (Math.random() * 2 - 1) * 2;
            const intercept = (Math.random() * 4) - 2;
            const points = [];

            for (let i = 0; i < 50; i++) {
                let x = Math.random() * 20 - 10;
                let yTrue = slope * x + intercept;
                let noise = randomNormal() * 2;
                points.push({
                    x,
                    y: yTrue + noise
                });
            }
            const {
                slope: best_slope,
                intercept: best_intercept
            } = computeBestFit(points);
            console.log(computeBestFit(points));

            minError = computeMSE(points, best_slope, best_intercept);
            document.getElementById("error").textContent = minError.toFixed(2);


            generatedPoints = points;

            // Update chart
            chart.data.datasets[0].data = points;
            chart.update();

            // Send to backend
            $.ajax({
                url: "save_points.php",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify(points),
                success: function(res) {
                    console.log("Points saved:", res);
                },
                error: function(err) {
                    console.error("Error saving points:", err);
                }
            });
        }





        // Fetch new guesses
        let displayedGuesses = {}; // store user IDs already drawn

        function fetchGuesses() {
            $.getJSON("get_guesses.php", function(data) {
                if (!Array.isArray(data)) return;

                // Store new guesses in displayedGuesses
                data.forEach(g => {
                    if (!displayedGuesses[g.user_id]) {
                        displayedGuesses[g.user_id] = g; // store whole guess for later sorting
                        // Draw line on chart
                        chart.data.datasets.push({
                            label: g.full_name,
                            data: [{
                                    x: -10,
                                    y: g.slope * -10 + g.intercept
                                },
                                {
                                    x: 10,
                                    y: g.slope * 10 + g.intercept
                                }
                            ],
                            type: 'line',
                            borderColor: g.color,
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 0
                        });
                    }
                });

                // ✅ Build leaderboard
                const guessesArray = Object.values(displayedGuesses);

                // Sort by error (ascending)
                guessesArray.sort((a, b) => a.error - b.error);

                // Keep top 10
                const top10 = guessesArray.slice(0, 10);

                // Render leaderboard
                const logList = document.getElementById("error-log-list");
                logList.innerHTML = ""; // clear previous

                top10.forEach((g, idx) => {
                    const li = document.createElement("li");
                    const firstName = g.full_name.split(' ')[0];
                    const capitalized = firstName.charAt(0).toUpperCase() + firstName.slice(1).toLowerCase();

                    li.innerHTML = `<strong style="color:${g.color}">Top ${idx + 1}: ${capitalized} — Error = ${Math.round(g.error * 100) / 100}</strong>`;
                    logList.appendChild(li);
                });

                chart.update();
            });
        }



        // Reset game
        function resetGame() {
            if (!confirm("Are you sure you want to reset the game?")) return;
            $.post("reset_game.php", {}, function(res) {
                if (res.success) {
                    chart.data.datasets = chart.data.datasets.slice(0, 2);
                    chart.update();
                    tempClicks = [];
                    displayedGuesses = {};
                    generateRandomPoints();
                } else {
                    alert(res.message);
                }
            }, "json");
        }

        // Initialize
        fetchGamePoints();
        setInterval(fetchGuesses, 500);
    </script>
</body>

</html>