<!DOCTYPE html>
<html lang="es">

<?php include("../../assets/head.php"); ?>

<body class="d-flex flex-column min-vh-100">
    <?php include("../../assets/nav.php"); ?>

    <div class="container scroll-margin my-5 flex-fill">
        <div class="text-center mb-5 px-3 px-md-5">
            <h2 class="fw-bold mb-4" style="color: var(--muted);"
                data-es="Únete a AISC Madrid"
                data-en="Join AISC Madrid">
                Únete a AISC Madrid
            </h2>
            <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
            <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px;"
                data-es="Rellena este formulario para unirte a nuestra comunidad y mantenerte informado."
                data-en="Fill out this form to join our community and stay updated.">
                Rellena este formulario para unirte a nuestra comunidad y mantenerte informado.
            </h6>
        </div>

        <?php
        $error_msg = $_GET['error'] ?? '';
        $success_msg = $_GET['success'] ?? '';
        ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="alert alert-success text-center">
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>


        <section class="container-fluid mb-5 ">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="border-0 form-card no-hover p-4">
                        <form id="multiStepForm" method="POST" action="events/presentation/save_data.php">

                            <!-- Step 1: Full Name -->
                            <div class="form-step active">
                                <label for="full_name" class="form-label" style="color: black"
                                    data-es="Nombre y apellidos" data-en="Full Name">Nombre y apellidos*</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Michael Scott" required>
                                <button type="button" class="btn btn-primary mt-3 next-btn" data-es="Siguiente" data-en="Next">Siguiente</button>
                            </div>

                            <!-- Step 2: Email -->
                            <div class="form-step">
                                <label for="email" class="form-label" style="color: black"
                                    data-es="Correo UC3M" data-en="UC3M Email">Correo UC3M*</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="100499081@alumnos.uc3m.es" required>
                                <button type="button" class="btn btn-secondary mt-3 prev-btn" data-es="Anterior" data-en="Previous">Anterior</button>
                                <button type="button" class="btn btn-primary mt-3 next-btn" data-es="Siguiente" data-en="Next">Siguiente</button>
                            </div>

                            <!-- Step 3: Degree & Year -->
                            <div class="form-step">
                                <label for="degree" class="form-label" style="color: black" data-es="Grado" data-en="Degree">Grado</label>
                                <select class="form-select" id="degree" name="degree" required>
                                    <option value="" disabled selected data-es="Selecciona tu grado" data-en="Select your degree">Selecciona tu grado</option>

                                    <!-- Grados -->
                                    <option value="ciencias" data-es="Grado en Ciencias" data-en="Bachelor in Sciences">Grado en Ciencias</option>
                                    <option value="dual_ciencia_ingenieria_datos_tel" data-es="Doble Grado en Ciencia e Ingeniería de Datos e Ingeniería en Tecnologías de Telecomunicación" data-en="Double Degree in Data Science & Engineering and Telecommunication Engineering">Doble Grado en Ciencia e Ingeniería de Datos e Ingeniería en Tecnologías de Telecomunicación</option>
                                    <option value="dual_fisica_industriales" data-es="Doble Grado en Ingeniería Física e Ingeniería en Tecnologías Industriales" data-en="Double Degree in Physics Engineering & Industrial Technologies Engineering">Doble Grado en Ingeniería Física e Ingeniería en Tecnologías Industriales</option>
                                    <option value="dual_info_ade" data-es="Doble Grado en Ingeniería Informática y Administración de Empresas" data-en="Double Degree in Computer Engineering & Business Administration">Doble Grado en Ingeniería Informática y Administración de Empresas</option>
                                    <option value="ai" data-es="Grado en Inteligencia Artificial" data-en="Bachelor in Artificial Intelligence">Grado en Inteligencia Artificial</option>
                                    <option value="applied_math" data-es="Grado en Matemática Aplicada" data-en="Bachelor in Applied Mathematics">Grado en Matemática Aplicada</option>
                                    <option value="data_engineering" data-es="Grado en Ciencia e Ingeniería de Datos" data-en="Bachelor in Data Science & Engineering">Grado en Ciencia e Ingeniería de Datos</option>
                                    <option value="aero" data-es="Grado en Ingeniería Aeroespacial" data-en="Bachelor in Aerospace Engineering">Grado en Ingeniería Aeroespacial</option>
                                    <option value="biomed" data-es="Grado en Ingeniería Biomédica" data-en="Bachelor in Biomedical Engineering">Grado en Ingeniería Biomédica</option>
                                    <option value="comunicaciones_moviles" data-es="Grado en Ingeniería de Comunicaciones Móviles y Espaciales" data-en="Bachelor in Mobile & Space Communications Engineering">Grado en Ingeniería de Comunicaciones Móviles y Espaciales</option>
                                    <option value="internet_ciberseguridad" data-es="Grado en Ingeniería de Internet y Ciberseguridad" data-en="Bachelor in Internet & Cybersecurity Engineering">Grado en Ingeniería de Internet y Ciberseguridad</option>
                                    <option value="energia" data-es="Grado en Ingeniería de la Energía" data-en="Bachelor in Energy Engineering">Grado en Ingeniería de la Energía</option>
                                    <option value="sonido_ia" data-es="Grado en Ingeniería de Sonido e Imagen con Inteligencia Artificial" data-en="Bachelor in Sound & Image Engineering with AI">Grado en Ingeniería de Sonido e Imagen con Inteligencia Artificial</option>
                                    <option value="electrica" data-es="Grado en Ingeniería Eléctrica" data-en="Bachelor in Electrical Engineering">Grado en Ingeniería Eléctrica</option>
                                    <option value="electronica" data-es="Grado en Ingeniería Electrónica Industrial y Automática" data-en="Bachelor in Industrial & Automation Electronics Engineering">Grado en Ingeniería Electrónica Industrial y Automática</option>
                                    <option value="telecom" data-es="Grado en Ingeniería en Tecnologías de Telecomunicación" data-en="Bachelor in Telecommunication Technologies Engineering">Grado en Ingeniería en Tecnologías de Telecomunicación</option>
                                    <option value="telecom_master" data-es="Grado en Ingeniería en Tecnologías de Telecomunicación + Máster en Ingeniería de Telecomunicación" data-en="Bachelor + Master in Telecommunication Engineering">Grado en Ingeniería en Tecnologías de Telecomunicación + Máster en Ingeniería de Telecomunicación</option>
                                    <option value="industriales" data-es="Grado en Ingeniería en Tecnologías Industriales" data-en="Bachelor in Industrial Technologies Engineering">Grado en Ingeniería en Tecnologías Industriales</option>
                                    <option value="industriales_master" data-es="Grado en Ingeniería en Tecnologías Industriales + Máster en Ingeniería Industrial" data-en="Bachelor + Master in Industrial Engineering">Grado en Ingeniería en Tecnologías Industriales + Máster en Ingeniería Industrial</option>
                                    <option value="fisica" data-es="Grado en Ingeniería Física" data-en="Bachelor in Physics Engineering">Grado en Ingeniería Física</option>
                                    <option value="informatica" data-es="Grado en Ingeniería Informática" data-en="Bachelor in Computer Engineering">Grado en Ingeniería Informática</option>
                                    <option value="informatica_master" data-es="Grado en Ingeniería Informática + Máster en Ingeniería Informática" data-en="Bachelor + Master in Computer Engineering">Grado en Ingeniería Informática + Máster en Ingeniería Informática</option>
                                    <option value="mecanica" data-es="Grado en Ingeniería Mecánica" data-en="Bachelor in Mechanical Engineering">Grado en Ingeniería Mecánica</option>
                                    <option value="robotica" data-es="Grado en Ingeniería Robótica" data-en="Bachelor in Robotics Engineering">Grado en Ingeniería Robótica</option>
                                    <option value="math_computation" data-es="Grado en Matemáticas y Computación" data-en="Bachelor in Mathematics & Computing">Grado en Matemáticas y Computación</option>
                                    º <!-- Másters -->
                                    <option value="master_derecho_tel" data-es="Máster Universitario en Derecho de las Telecomunicaciones, Protección de datos, Audiovisual y Sociedad de la Información" data-en="Master in Telecommunications Law, Data Protection, Audiovisual & Information Society">Máster Universitario en Derecho de las Telecomunicaciones, Protección de datos, Audiovisual y Sociedad de la Información</option>

                                    <option value="master_propiedad_intelectual" data-es="Máster Universitario en Propiedad Intelectual" data-en="Master in Intellectual Property">Máster Universitario en Propiedad Intelectual</option>

                                    <option value="dual_master_derecho_tel" data-es="UC3M - Doble Máster en Derecho de las Telecomunicaciones, Protección de datos, Audiovisual y Sociedad de la Información y Acceso a las Profesiones de Abogacía y Procura (Ed. Marzo)" data-en="UC3M - Double Master in Telecommunications Law, Data Protection, Audiovisual & Information Society and Legal Practice Access (March edition)">UC3M - Doble Máster en Derecho de las Telecomunicaciones, Protección de datos, Audiovisual y Sociedad de la Información y Acceso a las Profesiones de Abogacía y Procura (Ed. Marzo)</option>

                                    <option value="master_mba" data-es="Máster Universitario en Administración de Empresas - MBA" data-en="Master in Business Administration - MBA">Máster Universitario en Administración de Empresas - MBA</option>

                                    <option value="master_direccion_empresas" data-es="Máster Universitario en Dirección de Empresas" data-en="Master in Business Management">Máster Universitario en Dirección de Empresas</option>

                                    <option value="master_rrhh" data-es="Máster Universitario en Dirección de Recursos Humanos" data-en="Master in Human Resources Management">Máster Universitario en Dirección de Recursos Humanos</option>

                                    <option value="master_finanzas" data-es="Máster Universitario en Finanzas" data-en="Master in Finance">Máster Universitario en Finanzas</option>

                                    <option value="master_marketing" data-es="Máster Universitario en Marketing" data-en="Master in Marketing">Máster Universitario en Marketing</option>

                                    <option value="master_comunicacion_publi" data-es="Máster Universitario en Comunicación Publicitaria" data-en="Master in Advertising Communication">Máster Universitario en Comunicación Publicitaria</option>

                                    <option value="master_ux" data-es="Máster Universitario en Experiencia de Usuario y Análisis de Información Digital" data-en="Master in User Experience & Digital Information Analysis">Máster Universitario en Experiencia de Usuario y Análisis de Información Digital</option>

                                    <option value="master_ml_salud" data-es="Máster Universitario en Aprendizaje Automático para la Salud" data-en="Master in Machine Learning for Health">Máster Universitario en Aprendizaje Automático para la Salud</option>

                                    <option value="master_ciberseguridad" data-es="Máster Universitario en Ciberseguridad" data-en="Master in Cybersecurity">Máster Universitario en Ciberseguridad</option>

                                    <option value="master_cti" data-es="Máster Universitario en Ciencia y Tecnología Informática" data-en="Master in Computer Science & Technology">Máster Universitario en Ciencia y Tecnología Informática</option>

                                    <option value="master_analiticas_bio" data-es="Máster Universitario en Ciencias y Tecnologías Analíticas y Bioanalíticas (INTERUNIVERSITARIO)" data-en="Master in Analytical & Bioanalytical Sciences and Technologies (INTERUNIVERSITY)">Máster Universitario en Ciencias y Tecnologías Analíticas y Bioanalíticas (INTERUNIVERSITARIO)</option>

                                    <option value="master_energias_renovables_electricos" data-es="Máster Universitario en Energías Renovables en Sistemas Eléctricos" data-en="Master in Renewable Energies in Electrical Systems">Máster Universitario en Energías Renovables en Sistemas Eléctricos</option>

                                    <option value="master_energias_renovables_termicos" data-es="Máster Universitario en Energías Renovables en Sistemas Térmicos" data-en="Master in Renewable Energies in Thermal Systems">Máster Universitario en Energías Renovables en Sistemas Térmicos</option>

                                    <option value="master_estadistica_datos" data-es="Máster Universitario en Estadística para la Ciencia de Datos" data-en="Master in Statistics for Data Science">Máster Universitario en Estadística para la Ciencia de Datos</option>

                                    <option value="master_biomedicas" data-es="Máster Universitario en Gestión y Desarrollo de Tecnologías Biomédicas" data-en="Master in Biomedical Technologies Management & Development">Máster Universitario en Gestión y Desarrollo de Tecnologías Biomédicas</option>

                                    <option value="master_industria_4_0" data-es="Máster Universitario en Industria Conectada 4.0" data-en="Master in Connected Industry 4.0">Máster Universitario en Industria Conectada 4.0</option>

                                    <option value="master_aeronautica" data-es="Máster Universitario en Ingeniería Aeronáutica" data-en="Master in Aeronautical Engineering">Máster Universitario en Ingeniería Aeronáutica</option>

                                    <option value="master_biomecanica" data-es="Máster Universitario en Ingeniería Biomecánica y Dispositivos Médicos" data-en="Master in Biomechanics & Medical Devices Engineering">Máster Universitario en Ingeniería Biomecánica y Dispositivos Médicos</option>

                                    <option value="master_microelectronica" data-es="Máster Universitario en Ingeniería de Diseño Microelectrónico" data-en="Master in Microelectronic Design Engineering">Máster Universitario en Ingeniería de Diseño Microelectrónico</option>

                                    <option value="master_maquinas_transportes" data-es="Máster Universitario en Ingeniería de Máquinas y Transportes" data-en="Master in Machines & Transport Engineering">Máster Universitario en Ingeniería de Máquinas y Transportes</option>

                                    <option value="master_sistemas_electronicos" data-es="Máster Universitario en Ingeniería de Sistemas Electrónicos y Aplicaciones" data-en="Master in Electronic Systems & Applications Engineering">Máster Universitario en Ingeniería de Sistemas Electrónicos y Aplicaciones</option>

                                    <option value="master_telecomunicacion" data-es="Máster Universitario en Ingeniería de Telecomunicación" data-en="Master in Telecommunication Engineering">Máster Universitario en Ingeniería de Telecomunicación</option>

                                    <option value="master_diseno_industrial" data-es="Máster Universitario en Ingeniería del Diseño Industrial" data-en="Master in Industrial Design Engineering">Máster Universitario en Ingeniería del Diseño Industrial</option>

                                    <option value="master_ingenieria_espacial" data-es="Máster Universitario en Ingeniería Espacial" data-en="Master in Space Engineering">Máster Universitario en Ingeniería Espacial</option>

                                    <option value="master_ingenieria_industrial" data-es="Máster Universitario en Ingeniería Industrial" data-en="Master in Industrial Engineering">Máster Universitario en Ingeniería Industrial</option>

                                    <option value="master_informatica" data-es="Máster Universitario en Ingeniería Informática" data-en="Master in Computer Engineering">Máster Universitario en Ingeniería Informática</option>

                                    <option value="master_ia_aplicada" data-es="Máster Universitario en Inteligencia Artificial Aplicada" data-en="Master in Applied Artificial Intelligence">Máster Universitario en Inteligencia Artificial Aplicada</option>

                                    <option value="master_iot" data-es="Máster Universitario en Internet de las Cosas: Tecnologías Aplicadas" data-en="Master in Internet of Things: Applied Technologies">Máster Universitario en Internet de las Cosas: Tecnologías Aplicadas</option>

                                    <option value="master_math_applied" data-es="Máster Universitario en Matemática Aplicada y Computacional" data-en="Master in Applied & Computational Mathematics">Máster Universitario en Matemática Aplicada y Computacional</option>

                                    <option value="master_math_industrial" data-es="Máster Universitario en Matemática Industrial (INTERUNIVERSITARIO)" data-en="Master in Industrial Mathematics (INTERUNIVERSITY)">Máster Universitario en Matemática Industrial (INTERUNIVERSITARIO)</option>

                                    <option value="master_mecanica_industrial" data-es="Máster Universitario en Mecánica Industrial" data-en="Master in Industrial Mechanics">Máster Universitario en Mecánica Industrial</option>

                                    <option value="master_big_data" data-es="Máster Universitario en Métodos Analíticos para Datos Masivos: Big Data" data-en="Master in Analytical Methods for Big Data">Máster Universitario en Métodos Analíticos para Datos Masivos: Big Data</option>

                                    <option value="master_robotica" data-es="Máster Universitario en Robótica y Automatización" data-en="Master in Robotics & Automation">Máster Universitario en Robótica y Automatización</option>

                                    <option value="master_fintech" data-es="Máster Universitario en Tecnologías del Sector Financiero: Fintech" data-en="Master in Financial Sector Technologies: Fintech">Máster Universitario en Tecnologías del Sector Financiero: Fintech</option>

                                    <option value="master_cuantica" data-es="Máster Universitario en Tecnologías e Ingeniería Cuánticas" data-en="Master in Quantum Technologies & Engineering">Máster Universitario en Tecnologías e Ingeniería Cuánticas</option>

                                    <!-- Doble másters UC3M -->
                                    <option value="dual_master_telecom_iot" data-es="UC3M - Doble Máster en Ingeniería de Telecomunicación e Internet de las Cosas: Tecnologías Aplicadas" data-en="UC3M - Double Master in Telecommunication Engineering & IoT: Applied Technologies">UC3M - Doble Máster en Ingeniería de Telecomunicación e Internet de las Cosas: Tecnologías Aplicadas</option>

                                    <option value="dual_master_telecom_ml_salud" data-es="UC3M - Doble Máster en Ingeniería de Telecomunicación y Aprendizaje Automático para la Salud" data-en="UC3M - Double Master in Telecommunication Engineering & Machine Learning for Health">UC3M - Doble Máster en Ingeniería de Telecomunicación y Aprendizaje Automático para la Salud</option>

                                    <option value="dual_master_telecom_ciberseguridad" data-es="UC3M - Doble Máster en Ingeniería de Telecomunicación y Ciberseguridad" data-en="UC3M - Double Master in Telecommunication Engineering & Cybersecurity">UC3M - Doble Máster en Ingeniería de Telecomunicación y Ciberseguridad</option>

                                    <option value="dual_master_telecom_bigdata" data-es="UC3M - Doble Máster en Ingeniería de Telecomunicación y Métodos Analíticos para Datos Masivos: Big Data" data-en="UC3M - Double Master in Telecommunication Engineering & Analytical Methods for Big Data">UC3M - Doble Máster en Ingeniería de Telecomunicación y Métodos Analíticos para Datos Masivos: Big Data</option>

                                    <option value="dual_master_info_ai" data-es="UC3M - Doble Máster en Ingeniería Informática e Inteligencia Artificial Aplicada" data-en="UC3M - Double Master in Computer Engineering & Applied AI">UC3M - Doble Máster en Ingeniería Informática e Inteligencia Artificial Aplicada</option>

                                    <option value="dual_master_info_iot" data-es="UC3M - Doble Máster en Ingeniería Informática e Internet de las Cosas: Tecnologías Aplicadas" data-en="UC3M - Double Master in Computer Engineering & IoT: Applied Technologies">UC3M - Doble Máster en Ingeniería Informática e Internet de las Cosas: Tecnologías Aplicadas</option>

                                    <option value="dual_master_info_ciberseguridad" data-es="UC3M - Doble Máster en Ingeniería Informatica y Ciberseguridad" data-en="UC3M - Double Master in Computer Engineering & Cybersecurity">UC3M - Doble Máster en Ingeniería Informatica y Ciberseguridad</option>


                                </select>


                                <label for="course_year" class="form-label mt-3" style="color: black"
                                    data-es="Curso" data-en="Course Year">Curso</label>
                                <select class="form-select" id="course_year" name="course_year" required>
                                    <option value="" disabled selected data-es="Selecciona tu curso" data-en="Select your course">Selecciona tu curso</option>
                                    <option value="1" data-es="1º" data-en="1st">1º</option>
                                    <option value="2" data-es="2º" data-en="2nd">2º</option>
                                    <option value="3" data-es="3º" data-en="3rd">3º</option>
                                    <option value="4" data-es="4º" data-en="4th">4º</option>
                                    <option value="5" data-es="5º" data-en="5th">5º</option>
                                    <option value="6" data-es="6º" data-en="6th">6º</option>
                                </select>


                                <button type="button" class="btn btn-secondary mt-3 prev-btn" data-es="Anterior" data-en="Previous">Anterior</button>
                                <button type="button" class="btn btn-primary mt-3 next-btn" data-es="Siguiente" data-en="Next">Siguiente</button>
                            </div>

                            <!-- Step 4: Interests -->
                            <div class="form-step">
                                <label for="ai_topics" class="form-label" style="color: black"
                                    data-es="Áreas de interés" data-en="Areas of Interest">Áreas de interés</label>

                                <select class="form-select" id="ai_topics" name="ai_topics[]" multiple required>
                                    <option value="ml" data-es="Machine Learning" data-en="Machine Learning">Machine Learning</option>
                                    <option value="dl" data-es="Deep Learning" data-en="Deep Learning">Deep Learning</option>
                                    <option value="nn" data-es="Redes Neuronales" data-en="Neural Networks">Redes Neuronales</option>
                                    <option value="transformers" data-es="Transformers / Modelos de Lenguaje" data-en="Transformers / Language Models">Transformers / Modelos de Lenguaje</option>
                                    <option value="nlp" data-es="Procesamiento de Lenguaje Natural" data-en="Natural Language Processing">Procesamiento de Lenguaje Natural</option>
                                    <option value="cv" data-es="Visión por Computador" data-en="Computer Vision">Visión por Computador</option>
                                    <option value="rl" data-es="Aprendizaje por Refuerzo" data-en="Reinforcement Learning">Aprendizaje por Refuerzo</option>
                                    <option value="generative_ai" data-es="IA Generativa" data-en="Generative AI">IA Generativa</option>
                                    <option value="ai_ethics" data-es="Ética en IA" data-en="AI Ethics">Ética en IA</option>
                                    <option value="robotics" data-es="Robótica" data-en="Robotics">Robótica</option>
                                    <option value="data_viz" data-es="Visualización de Datos" data-en="Data Visualization">Visualización de Datos</option>
                                    <option value="edge_ai" data-es="IA en el Edge / IoT" data-en="Edge AI / IoT">IA en dispositivos y IoT</option>
                                </select>

                                <div class="form-text" data-es="Mantén pulsada Ctrl (Cmd en Mac) para seleccionar varios"
                                    data-en="Hold Ctrl (Cmd on Mac) to select multiple">
                                    Mantén pulsada Ctrl (Cmd en Mac) para seleccionar varios
                                </div>

                                <button type="button" class="btn btn-secondary mt-3 prev-btn" data-es="Anterior" data-en="Previous">Anterior</button>
                                <button type="button" class="btn btn-primary mt-3 next-btn" data-es="Siguiente" data-en="Next">Siguiente</button>
                            </div>


                            <!-- Step 5: Expectations -->
                            <div class="form-step">
                                <label for="expectations" class="form-label" style="color: black"
                                    data-es="Qué esperas de la asociación" data-en="What do you expect from the association?">
                                    Qué esperas de la asociación
                                </label>
                                <textarea class="form-control" id="expectations" name="expectations" rows="3"
                                    placeholder="Ejemplo: Aprender más sobre IA, participar en talleres y conocer a otros estudiantes interesados en tecnología. / Example: Learn more about AI, participate in workshops, and meet other students interested in technology."></textarea>

                                <button type="button" class="btn btn-secondary mt-3 prev-btn" data-es="Anterior" data-en="Previous">Anterior</button>
                                <button type="button" class="btn btn-primary mt-3 next-btn" data-es="Siguiente" data-en="Next">Siguiente</button>
                            </div>

                            <!-- Step 6: Knowledge Level -->
                            <div class="form-step">
                                <label for="knowledge_level" class="form-label" style="color: black"
                                    data-es="Nivel de conocimientos en IA" data-en="Knowledge Level in AI">Nivel de conocimientos en IA</label>
                                <select class="form-select" id="knowledge_level" name="knowledge_level">
                                    <option value="none" data-es="Ninguno" data-en="None">Ninguno</option>
                                    <option value="basic" data-es="Básico" data-en="Basic">Básico</option>
                                    <option value="intermediate" data-es="Intermedio" data-en="Intermediate">Intermedio</option>
                                    <option value="advanced" data-es="Avanzado" data-en="Advanced">Avanzado</option>
                                </select>
                                <button type="button" class="btn btn-secondary mt-3 prev-btn" data-es="Anterior" data-en="Previous">Anterior</button>
                                <button type="button" class="btn btn-primary mt-3 next-btn" data-es="Siguiente" data-en="Next">Siguiente</button>
                            </div>


                            <!-- Step 8: Consent -->
                            <div class="form-step">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                                    <label class="form-check-label form-text" for="consent"
                                        data-es="Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme."
                                        data-en="I consent to AISC Madrid storing my submitted data to contact me.">
                                        Doy mi consentimiento para que AISC Madrid almacene mis datos enviados para contactarme.
                                    </label>
                                    <a href="terms_conditions.php" target="_blank" data-es="(Leer términos y condiciones)" data-en="(Read terms and conditions)">
                                        (Leer términos y condiciones)
                                    </a>
                                </div>
                                <button type="button" class="btn btn-secondary mt-3 prev-btn" data-es="Anterior" data-en="Previous">Anterior</button>
                                <button type="submit" class="btn btn-success mt-3" data-es="Enviar" data-en="Submit">Enviar</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('../../assets/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="aisc-madrid-website/js/language.js"></script>

    <script>
        // Multi-step form logic
        const form = document.getElementById('multiStepForm');
        const steps = Array.from(document.querySelectorAll('.form-step'));
        let currentStep = 0;

        function showStep(step) {
            steps.forEach((s, i) => s.classList.toggle('active', i === step));
        }

        // Validación de campos antes de pasar al siguiente step
        document.querySelectorAll('.next-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const currentFields = steps[currentStep].querySelectorAll('input, textarea, select');
                let valid = true;
                currentFields.forEach(field => {
                    if (!field.checkValidity()) {
                        valid = false;
                        field.reportValidity(); // muestra mensaje de error nativo
                    }
                });
                if (valid && currentStep < steps.length - 1) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
        });

        document.querySelectorAll('.prev-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                }
            });
        });

        showStep(currentStep);
    </script>

    <style>
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }
    </style>

</body>

</html>