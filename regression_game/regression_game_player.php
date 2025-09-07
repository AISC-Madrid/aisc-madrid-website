<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <!-- Custom CSS -->


    <!-- Base URL -->

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            text-align: center;
            background: #f7f7f7;
        }

        #chart {
            max-width: 80%;
            height: auto;
            cursor: crosshair;
        }

        .notice {
            font-size: 14px;
            color: gray;
            margin-top: 10px;
        }

        /* Small devices: max-width 576px */
        @media (max-width: 576px) {
            .chart-container {
                max-height: 50vh;
            }
        }
    </style>
</head>

<body>
    <div class="text-dark w-100 d-flex flex-column align-items-center justify-content-start" style="height: 120vh; background-color: transparent; border: none; box-shadow: none; ">

        <!-- Title + Info (30%) -->
        <div class="d-flex flex-column align-items-center justify-content-center" style="flex: 2; width: 100%;">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-around bg-muted rounded-3 shadow-lg p-2 w-100" style="max-width: 80%;">
                <!-- Brand / Logo -->
                <a class="navbar-brand w-100 w-sm-50 d-flex justify-content-center mb-3 mb-sm-0" href="/" title="AISC Madrid - Inicio">
                    <img class="img-fluid w-100 w-sm-50" src="../images/logos/PNG/AISC Madrid Logo Color.png" alt="Logo de AISC Madrid">
                </a>

                <!-- Title / Info -->
                <div class="w-100 w-sm-50 d-flex flex-column align-items-center justify-content-around text-center">
                    <h1 class="pt-3 fw-bold fs-2">📈 Regression Game</h1>
                    <p class="text-muted mb-4">Guess the line, minimize the error, and climb the leaderboard!</p>
                </div>
            </div>
        </div>


        <!-- Game Area (80%) -->
        <div class="row w-100 py-2 px-4" style="flex:8;">

            <!-- Leaderboard -->
            <div id="error-log" class=" col-12 col-sm-3 bg-white text-dark rounded-3 py-3 me-3 mb-3 shadow-lg" style=" overflow-y: auto;
                        background-color: transparent; 
                        border: none; 
                        box-shadow: none; ">
                <h4 class="fw-bold text-center text-warning mb-3">Leaderboard</h4>
                <ul id="error-log-list"
                    style="list-style-type: none; padding-left: 0; font-size: 1.1rem; line-height: 1.6;">
                </ul>
            </div>

            <!-- Chart -->
            <div class="col-12 col-sm-7 bg-white rounded-3 p-0 p-sm-3 chart-container" style="flex-grow: 1; display: flex; align-items: center; justify-content: center; background-color: transparent; border: none; box-shadow: none; ">
                <canvas class="w-100 h-100" id="chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Enter your email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="email" id="modalEmail" class="form-control" placeholder="your@email.com" required>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submitEmail" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalTitle">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
                    <!-- Message goes here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Name Modal -->
    <div class="modal fade" id="fullNameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="modalFullName" class="form-control" placeholder="Your full name" required>
                </div>
                <div class="modal-footer">
                    <button type="button" id="submitFullName" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        const canvas = document.getElementById("chart");

        const ctx = canvas.getContext("2d");
        let generatedPoints = [];
        let newGame = true;
        let userInfo = null;
        let userPlayed = false;
        let guessLine = null;
        let tempClicks = [];
        let email = "";
        let fullName = "";

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
                },
                plugins: {
                    legend: {
                        labels: {
                            filter: function(legendItem, chartData) {
                                // Show only the dataset with label "Your Guess"
                                return legendItem.text === "Your Guess";
                            }
                        }
                    }
                }
            }
        });

        function showMessage(message, title = "Message") {
            const modalEl = document.getElementById("messageModal");
            modalEl.querySelector("#messageModalBody").innerHTML = message;
            modalEl.querySelector(".modal-title").textContent = title;

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }


        function fetchGamePoints() {
            $.getJSON("get_random_points.php", function(data) {
                if (!data || !Array.isArray(data)) return;

                // Save in variable for later use
                generatedPoints = data.map(p => ({
                    x: p.x,
                    y: p.y
                }));

                // Populate chart dataset[0]
                chart.data.datasets[0].data = generatedPoints;

                chart.update();
            });
        }


        // Ask user email
        function checkUser() {
            const emailModalEl = document.getElementById('emailModal');
            const fullNameModalEl = document.getElementById('fullNameModal');
            const emailModal = new bootstrap.Modal(emailModalEl);
            const fullNameModal = new bootstrap.Modal(fullNameModalEl);

            emailModal.show();

            // Submit email
            $("#submitEmail").off("click").on("click", function() {
                email = $("#modalEmail").val().trim();
                if (!email) {
                    showMessage("Email is required", "Error");
                    return;
                }

                $.post("register_user.php", {
                    email: email
                }, function(res) {
                    if (!res.success) {
                        if (res.message === "Not registered") {
                            // Hide email modal and show full name modal
                            emailModal.hide();
                            fullNameModal.show();
                            return;
                        } else {
                            showMessage(res.message, "Error");
                        }
                        return;
                    }

                    // Email exists → store user info
                    userInfo = {
                        id: res.user_id,
                        full_name: res.full_name,
                        email: email
                    };
                    userPlayed = res.already_played;
                    newGame = !userPlayed;
                    emailModal.hide();

                    if (userPlayed){
                        showMessage("You already played!", "Info"); 

                    } 
                    else showMessage("Welcome, " + userInfo.full_name + "! Click two points to guess the line.", "Welcome!");
                }, "json");
            });

            // --- Handle full name submission (registration) ---
            $("#submitFullName").off("click").on("click", function() {
                const fullNameVal = $("#modalFullName").val().trim();
                if (!fullNameVal) {
                    showMessage("Full name is required", "Error");
                    return;
                }

                // Use the email captured from the previous step
                $.post("register_user.php", {
                    email,
                    full_name: fullNameVal
                }, function(res) {
                    if (!res.success) {
                        showMessage(res.message, "Error");
                        return;
                    }

                    fullNameModal.hide();

                    // Registration succeeded → store user info and continue
                    userInfo = {
                        id: res.user_id,
                        full_name: fullNameVal,
                        email: email
                    };
                    userPlayed = false;
                    newGame = true;

                    showMessage("Welcome, " + fullNameVal + "! Click two points to guess the line.", "Welcome!");
                }, "json").fail(function() {
                    console.error('AJAX error on full name submission');
                    showMessage("An error occurred. Please try again.", "Error");
                });
            });
        }



        // Convert click to chart coords
        function getChartCoordinates(event) {
            const rect = canvas.getBoundingClientRect();
            const xPixel = event.clientX - rect.left;
            const yPixel = event.clientY - rect.top;
            const chartArea = chart.chartArea;
            const xRange = chart.scales.x.max - chart.scales.x.min;
            const yRange = chart.scales.y.max - chart.scales.y.min;
            const x = chart.scales.x.min + (xPixel - chartArea.left) / (chartArea.right - chartArea.left) * xRange;
            const y = chart.scales.y.max - (yPixel - chartArea.top) / (chartArea.bottom - chartArea.top) * yRange;
            return {
                x,
                y
            };
        }

        function playAgain() {
            fetchGamePoints();
            if (!newGame) {
                $.post("register_user.php", {
                    email
                }, function(res) {
                    if (!res.success) {
                        alert(res.message);
                        return;
                    }
                    userPlayed = res.already_played;
                    if (!userPlayed) {
                        showMessage("NEW GAME!!", "Info");
                        newGame = true;
                        // Remove only this user’s entry from displayedGuesses
                        displayedGuesses = {};
                        chart.data.datasets = chart.data.datasets.slice(0, 2);
                        chart.data.datasets[1].data = []; // remove all points from dataset 1

                        chart.update();

                        //Load random game points
                        fetchGamePoints();
                    }
                }, "json");
            }
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


        function getRandomColor() {
            return 'hsl(' + (Math.random() * 360) + ', 100%, 40%)';
        }

        // Handle clicks for guess
        canvas.addEventListener('click', function(event) {
            if (!userInfo || userPlayed) return;
            const {
                x,
                y
            } = getChartCoordinates(event);
            tempClicks.push({
                x,
                y
            });

            chart.data.datasets[1].data = tempClicks;
            chart.update();

            if (tempClicks.length === 2) {
                const p1 = tempClicks[0],
                    p2 = tempClicks[1];
                tempClicks = [];
                const slope = (p2.y - p1.y) / (p2.x - p1.x);
                const intercept = p1.y - slope * p1.x;
                const error = computeMSE(generatedPoints, slope, intercept);
                const randomColor = getRandomColor();

                // ✅ Add line to chart as "Your Guess"
                chart.data.datasets.push({
                    label: "Your Guess",
                    data: [{
                            x: -10,
                            y: slope * -10 + intercept
                        },
                        {
                            x: 10,
                            y: slope * 10 + intercept
                        }
                    ],
                    type: 'line',
                    borderColor: randomColor,
                    borderWidth: 4, // thicker so it's visible
                    fill: false,
                    pointRadius: 0
                });
                chart.update();

                // Save guess to server
                $.post("submit_guess.php", {
                    user_id: userInfo?.id ?? 0, // fallback 0 if null
                    x1: p1.x,
                    y1: p1.y,
                    x2: p2.x,
                    y2: p2.y,
                    color: randomColor,
                    error: error
                }, "json");

                userPlayed = true;
                newGame = false;
            }

        });

        //Display new guesses
        let displayedGuesses = {}; // store user IDs already drawn

        function fetchGuesses() {
            $.getJSON("get_guesses.php", function(data) {
                if (!Array.isArray(data)) return;

                // --- Add new guesses to chart ---
                data.forEach(g => {
                    if (!g || g.user_id == null) return; // skip invalid entries
                    // Skip guesses we already displayed
                    if (displayedGuesses[g.user_id]) return;

                    let chart_label = g.full_name;
                    let chart_borderWidth = 1;
                    console.log(g.user_id);
                    console.log(userInfo.id);
                    const currentUserId = userInfo?.id ? parseInt(userInfo.id, 10) : 0;
                    const guessUserId = g.user_id ? parseInt(g.user_id, 10) : 0;

                    if (guessUserId !== currentUserId) {
                        chart.data.datasets.push({
                            label: chart_label,
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
                            borderWidth: chart_borderWidth,
                            fill: false,
                            pointRadius: 0
                        });
                    }



                    // Mark as displayed
                    displayedGuesses[g.user_id] = true;
                });

                chart.update();

                // --- Leaderboard (Top 10 by error) ---
                // Sort by error ascending
                let sorted = [...data].sort((a, b) => a.error - b.error).slice(0, 3);

                // Clear current leaderboard
                const logList = document.getElementById("error-log-list");
                logList.innerHTML = "";

                // Add top 10
                data.forEach((g, i) => {
                    const li = document.createElement("li");
                    const firstName = g.full_name.split(' ')[0];
                    const capitalized = firstName.charAt(0).toUpperCase() + firstName.slice(1).toLowerCase();

                    li.innerHTML = `<strong style="color:${g.color}">Top ${i + 1}: ${capitalized} — Error = ${Math.round(g.error * 100) / 100}</strong>`;
                    logList.appendChild(li);
                });
            });
        }


        // Initialize
        checkUser();
        // Call this function whenever you want to refresh the chart
        fetchGamePoints();
        setInterval(fetchGuesses, 2000);
        setInterval(playAgain, 2000);
    </script>




</body>

</html>