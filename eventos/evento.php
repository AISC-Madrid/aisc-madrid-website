<!DOCTYPE html>
<html lang="en">

<?php include("../assets/head.php"); ?>

<body style="color:black;">
    <?php include("../assets/nav.php"); ?>


    <!-- Main Content -->
    <div class="container-fluid scroll-margin">
        <div class="bg-dark row px-3">
            <div class="col-lg-4 d-flex align-items-end justify-content-center p-0 h-100">
                <img src="https://live.staticflickr.com/4562/37696404454_88e0ff976b.jpg" class="card-img-top" alt="Event Image" style="width: 300px; height:300px; object-fit: cover; position:relative; top:32px;">

            </div>
            <div class="col-lg-8 pt-5 container py-lg-2 d-flex flex-column align-items-start justify-content-center">
                <div class="mb-3">
                    <a href="#" class="badge bg-primary text-decoration-none">Evento</a>
                    <h1 class="text-light display-5 fw-bold mt-2">Jornada de bienvenida<br> 2025-2026</h1>
                    <p class="text-light">
                        
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-muted row px-3">
            <!-- Sidebar -->
            <div class="col-lg-4 pt-5 bg-white d-flex justify-content-center align-items-start">
                <div style="width:70%;">
                    <div class="mb-3">
                        <i class="fas fa-calendar me-2"></i>
                        <strong>8 de Septiembre de 2025</strong><br>
                        10:00 a 17:00
                        
                    </div>

                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span>EPS Universidad Carlos III</span>
                    </div>

                    <div class="my-2">
                            <span class="me-2">Add to calendar:</span>
                            <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=Tech%20Conference%20Madrid&dates=20250730T160000Z/20250730T180000Z&location=Madrid,%20Spain&details=Conferencia%20con%20Jane%20Doe%20sobre%20innovaci%C3%B3n%20tecnol%C3%B3gica." class="btn btn-sm btn-outline-secondary me-1" title="Google Calendar">
                                <i class="fab fa-google"></i>
                            </a>
                            <a href="https://events.stanford.edu/event/merging-humans-and-machines-innovation-and-translation.ics" class="btn btn-sm btn-outline-secondary me-1" title="iCal">
                                <i class="fab fa-apple"></i>
                            </a>
                            <a href="https://events.stanford.edu/event/merging-humans-and-machines-innovation-and-translation.ics" class="btn btn-sm btn-outline-secondary" title="Outlook">
                                <i class="fab fa-windows"></i>
                            </a>
                        </div>



                    <!-- <div class="mb-3">
                        <i class="fas fa-ticket-alt me-2"></i>
                        <strong>Free</strong><br>
                        <a href="https://stanford.zoom.us/webinar/register/9017483612598/WN_E9gq0X-iTDq7UYQLhaYEHA" class="btn btn-primary mt-2">
                            Register <i class="fas fa-long-arrow-alt-right"></i>
                        </a>
                    </div> -->

                    <!-- <div class="mb-3">
                        <i class="fas fa-link me-2"></i>
                        <a href="https://wearable.su.domains/merging-humans-and-machines-innovation-and-translation/" class="text-decoration-none" target="_blank">
                            Visit the event website
                        </a>
                    </div> -->

                    <div class="mt-3 mb-5">
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Share <i class="fas fa-share-alt"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="https://www.linkedin.com/shareArticle?mini=true&url=https://events.stanford.edu/event/merging-humans-and-machines-innovation-and-translation" target="_blank"><i class="fab fa-linkedin-in me-2"></i>LinkedIn</a></li>
                                <li><a class="dropdown-item" href="https://x.com/intent/tweet/?url=https%3A%2F%2Fevents.stanford.edu%2Fevent%2Fmerging-humans-and-machines-innovation-and-translation" target="_blank"><i class="fab fa-x-twitter me-2"></i>X</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <section style="padding: 2rem;">

                    <p><strong>Speaker:</strong> Xuanhe Zhao, PhD, Professor of Mechanical Engineering, and of Civil and Environmental Engineering, <i>Massachusetts Institute of Technology</i></p>

                    <p><strong>Info event:</strong> Whereas human tissues and organs are mostly soft, wet, and bioactive, machines are commonly hard, dry, and abiotic... In this talk, I will discuss two examples of merging humans and machines by posing two challenges in science and technology:</p>

                    <!-- <div class="mb-3">
                        <span class="badge bg-secondary me-2">Class/Seminar</span>
                        <span class="badge bg-secondary me-2">Science</span>
                        <span class="badge bg-secondary">Engineering/Technology</span>
                    </div> -->

                </section>

            </div>
        </div>
    </div>


    <?php include('../assets/footer.php'); ?>


    <script>
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.querySelector(button.getAttribute('data-bs-target'));
                const showText = button.querySelector('.show-map, .show-stream');
                const hideText = button.querySelector('.hide-map, .hide-stream');
                if (target.classList.contains('show')) {
                    showText.classList.remove('d-none');
                    hideText.classList.add('d-none');
                } else {
                    showText.classList.add('d-none');
                    hideText.classList.remove('d-none');
                }
            });
        });
    </script>


    <script src="../js/language.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>