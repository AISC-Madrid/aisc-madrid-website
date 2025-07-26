<!DOCTYPE html>
<html lang="en">

<?php include("../assets/head.php"); ?>

<body style="color:black;">
    <?php include("../assets/nav.php"); ?>


    <!-- Main Content -->
    <div class="container-fluid scroll-margin">
        <div class="bg-dark row px-3">
            <div class="col-lg-4 d-flex align-items-end justify-content-center p-0 h-100">
                <img src="/images/events/event1/Frame 16.png" class="card-img-top" alt="Event Image" style="width: 300px; height:300px; object-fit: cover; position:relative; top:32px;">

            </div>
            <div class="col-lg-8 pt-5 container py-lg-2 d-flex flex-column align-items-start justify-content-center">
                <div class="mb-3">
                    <a href="#" class="badge bg-aisc-event text-decoration-none">Evento</a>
                    <h1 class="text-light display-5 fw-bold mt-2">Jornada de Bienvenida<br> 2025-2026</h1>
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
                            
<!--                             <a href="" class="btn btn-sm btn-outline-secondary me-1" title="iCal">
                                <i class="fab fa-apple"></i>
                            </a>
                            <a href="" class="btn btn-sm btn-outline-secondary" title="Outlook">
                                <i class="fab fa-windows"></i>
                            </a> -->
                        </div>



                    <!-- <div class="mb-3">
                        <i class="fas fa-ticket-alt me-2"></i>
                        <strong>Free</strong><br>
                        <a href="" class="btn btn-primary mt-2">
                            Register <i class="fas fa-long-arrow-alt-right"></i>
                        </a>
                    </div> -->

                    <!-- <div class="mb-3">
                        <i class="fas fa-link me-2"></i>
                        <a href="" class="text-decoration-none" target="_blank">
                            Visit the event website
                        </a>
                    </div> -->

                    <div class="mt-3 mb-5">
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Share <i class="fas fa-share-alt"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <!-- Add url= link event -->
                                <li><a class="dropdown-item" href="https://www.linkedin.com/shareArticle?mini=true&url" target="_blank"><i class="fab fa-linkedin-in me-2"></i>LinkedIn</a></li>
                                <li><a class="dropdown-item" href="https://x.com/intent/tweet/?url=" target="_blank"><i class="fab fa-x-twitter me-2"></i>X</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <section style="padding: 2rem;">
                    <!-- Speaker section -->
                    <!--<p><strong>Speaker:</strong> Xuanhe Zhao, PhD, Professor of Mechanical Engineering, and of Civil and Environmental Engineering, <i>Massachusetts Institute of Technology</i></p>  -->
                    

                    <p><strong>¡Visítanos durante la Jornada de Bienvenida!</strong></p>
                    <p>Acércate a nuestro stand, conoce la asociación y explora en directo el potencial de dos áreas clave de la inteligencia artificial:</p>
                    <p><strong>Reconocimiento de imagen en tiempo real</strong></p>
                    <p>Pon a prueba tu destreza en un juego de plataformas interactivo controlado mediante <i>computer vision</i>: una cámara detectará y rastreará el movimiento de tu mano lo que servirá como control del juego.</p>
                    <p><strong>Generación de imágenes con IA</strong></p>
                    <p>Crea tu propio avatar personalizado con <strong>Stable Diffusion</strong>, un modelo de difusión basado en redes neuronales profundas que transforma descripciones de texto <i>(prompts)</i> en imágenes realistas de alta calidad.</p>
                    <p>¡Te esperamos el 8 de septiembre de 10 a 17 en la EPS de la Universidad Carlos III!</p>


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

    <script src="../js/language.js"></script>
    <script src="../js/event.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>