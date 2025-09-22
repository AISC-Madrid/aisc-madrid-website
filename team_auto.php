<!DOCTYPE html>
<html lang="en">

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include("assets/db.php");


// Retrieve all members from the db
$result = $conn->query("SELECT * FROM members ORDER BY id ASC");

// Separate active and past members
$active_members = [];
$past_members = [];

while ($row = $result->fetch_assoc()) {
    if ($row['active'] == 'yes') {
        $active_members[] = $row;
    } else {
        $past_members[] = $row;
    }
}
?>

<body class="d-flex flex-column min-vh-100">

  <?php include("assets/nav.php"); ?>

  <section class="section" id="team"> 
    <div class="container scroll-margin">
      <div class="text-center mb-5 px-3 px-md-5">
        <h2 class="fw-bold mb-4" style="color: var(--muted);" data-en="Meet the team" data-es="Conoce al equipo">
          Conoce al equipo
        </h2>
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
        <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px"
        data-en="Nothing would be possible without the people who make up our team. AISC Madrid brings together students from different fields and ages, fostering a space where creativity and collaboration go hand in hand."
        data-es="Nada sería posible sin las personas que forman nuestro equipo. AISC Madrid reúne estudiantes de distintas carreras y edades, fomentando un espacio donde la creatividad y la colaboración van de la mano.">
          Nada sería posible sin las personas que forman nuestro equipo. AISC Madrid reúne estudiantes de distintas carreras y edades, fomentando un espacio donde la creatividad y la colaboración van de la mano.
        </h6>
      </div>

<!-- Auto load members test -->
 <div class="row g-4" style="width:100%;">
        <?php foreach ($active_members as $member): ?>
            <div class="col-md-6 col-lg-4 event-future">
                <a href="<?= $member['socials'] ?>" class="text-decoration-none text-reset">
                    <div class="card h-100 w-100 shadow-sm">
                        <div class="card-body p-0 position-relative">
                            <div class="img-container">
                                <img src="<?= htmlspecialchars($member['image_path']) ?>" class="card-img-top" alt="<?= $member['full_name'] ?>" style="object-fit: cover;">
                            </div>
                            <div class="p-3 pb-5">
                                <h5 class="card-title mt-3 fw-bold">
                                    <?= $member['full_name'] ?>
                                </h5>
                                <p class="card-text">
                                    <i class="fas fa-calendar me-2"></i>
                                    <strong><?= date("d/m/Y", strtotime($event['start_datetime'])) ?></strong><br>
                                    <?= date("H:i", strtotime($event['start_datetime'])) ?> - <?= date("H:i", strtotime($event['end_datetime'])) ?>
                                </p>
                                <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><span><?= htmlspecialchars($event['location']) ?></span></p>
                            </div>
                            <div class="card-more-badge" data-en="More information" data-es="Saber más">Saber más</div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

      <!-- Members row -->
      <div class="mt-5 row">
        <div class="col-sm-6 col-lg-3">
          <div class="team-box text-center">
            <div class="team-wrapper">
              <div class="team-member">
                <a href="https://www.linkedin.com/in/hugocentenosanz/" target="_blank">
                  <img src="images/members/Hugo Centeno Sanz.png" alt="Hugo Centeno Sanz" class="img-fluid rounded">
                </a>
              </div>
            </div>
            <h5 class="mt-3" style="color: var(--background)">Hugo Centeno Sanz</h5>
            <p class="text-muted" data-en="President" data-es="Presidente">Presidente</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="team-box text-center">
            <div class="team-wrapper">
              <div class="team-member">
                <a href="https://www.linkedin.com/in/alfonso-mayoral-montero-9834702b2/" target="_blank">
                  <img src="images/members/Alfonso Mayoral Montero.png" alt="Alfonso Mayoral Montero" class="img-fluid rounded">
                </a>
              </div>
            </div>
            <h5 class="mt-3" style="color: var(--background)">Alfonso Mayoral Montero</h5>
            <p class="text-muted" data-en="Vicepresident" data-es="Vicepresidente">Vicepresidente</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="team-box text-center">
            <div class="team-wrapper">
              <div class="team-member">
                <a href="https://www.linkedin.com/in/lauren-gallego-ropero/" target="_blank">
                  <img src="images/members/Lauren Gallego Ropero.png" alt="Lauren Gallego Ropero" class="img-fluid rounded">
                </a>
              </div>
            </div>
            <h5 class="mt-3" style="color: var(--background)">Lauren Gallego Ropero</h5>
            <p class="text-muted" data-en="Vicepresident" data-es="Vicepresidente">Vicepresidente</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="team-box text-center">
            <div class="team-wrapper">
              <div class="team-member">
                <a href="https://www.linkedin.com/in/alejandrobarrosobueso/" target="_blank">
                  <img src="images/members/Alejandro Barroso Bueso.png" alt="Alejandro Barroso Bueso" class="img-fluid rounded">
                </a>
              </div>
            </div>
            <h5 class="mt-3" style="color: var(--background)">Alejandro Barroso Bueso</h5>
            <p class="text-muted" data-en="Webmaster" data-es="Administrador Web">Administrador Web</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="team-box text-center">
            <div class="team-wrapper">
              <div class="team-member">
                <a href="https://www.linkedin.com/in/yagocabanes/" target="_blank">
                  <img src="images/members/Yago Cabanes Corvera.png" alt="Yago Cabanes Corvera" class="img-fluid rounded">
                </a>
              </div>
            </div>
            <h5 class="mt-3" style="color: var(--background)">Yago Cabanes Corvera</h5>
            <p class="text-muted" data-en="Legal and Finance Manager" data-es="Gestor Legal y Financiero">Gestor Legal y Financiero</p>
          </div>
        </div>
        

      </div><!-- End members row -->

    </div>
  </section>


    <?php include('assets/footer.php'); ?>

    <script src="js/language.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>