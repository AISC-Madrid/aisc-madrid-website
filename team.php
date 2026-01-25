<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

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
          Nada sería posible sin las personas que forman nuestro equipo. AISC Madrid reúne estudiantes de distintas
          carreras y edades, fomentando un espacio donde la creatividad y la colaboración van de la mano.
        </h6>
      </div>

      <!-- Auto load members test -->
      <div class="mt-5 row">
        <?php foreach ($active_members as $member): ?>
          <div class="col-sm-6 col-lg-3">
            <div class="team-box text-center">
              <div class="team-wrapper">
                <div class="team-member">
                  <a href="<?= $member['socials'] ?>" target="_blank">
                    <img src="<?= htmlspecialchars($member['image_path']) ?>" alt="<?= $member['full_name'] ?>"
                      class="img-fluid rounded">
                  </a>
                </div>
              </div>
              <h5 class="mt-3" style="color: var(--background)"><?= $member['full_name'] ?></h5>
              <p class="text-muted" data-en="<?= htmlspecialchars($member['position_en']) ?>"
                data-es="<?= htmlspecialchars($member['position_es']) ?>">
                <?= htmlspecialchars($member['position_es']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
  </section>


  <?php include('assets/footer.php'); ?>

  <script src="js/navbar.js"></script>
</body>

</html>