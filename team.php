<!DOCTYPE html>
<html lang="en">

<?php include("assets/head.php"); ?>

<?php
include("assets/db.php");

// Retrieve all members from the db
$result = $conn->query("SELECT * FROM members ORDER BY id ASC");

$active_members = [];
$honor_members = [];
$past_members = [];

while ($row = $result->fetch_assoc()) {
  if ($row['is_honor'] == 'yes') {
    $honor_members[] = $row;
  } elseif ($row['active'] == 'yes') {
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
        <!-- Sustitución de data-attributes por translation-key -->
        <h2 class="fw-bold mb-4" style="color: var(--muted);" translation-key="team_title"></h2>
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
        <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px" translation-key="team_subtitle"></h6>
      </div>

      <div class="mt-5 row">
        <?php foreach ($active_members as $member): ?>
          <div class="col-sm-6 col-lg-3">
            <div class="team-box text-center">
              <div class="team-wrapper">
                <div class="team-member">
                  <?php
                  $social_url = $member['socials'] ?? '';
                  $allowed_hosts = ['linkedin.com', 'github.com', 'twitter.com', 'instagram.com', 'x.com'];
                  $parsed = parse_url($social_url);
                  $safe_social = '#';
                  if ($parsed && isset($parsed['host'])) {
                      foreach ($allowed_hosts as $host) {
                          if (str_ends_with($parsed['host'], $host)) {
                              $safe_social = htmlspecialchars($social_url, ENT_QUOTES, 'UTF-8');
                              break;
                          }
                      }
                  }
                  ?>
                  <a href="<?= $safe_social ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?= htmlspecialchars(cdn_from_image_path($member['image_path'])) ?>" alt="<?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                      class="img-fluid rounded">
                  </a>
                </div>
              </div>
              <h5 class="mt-3" style="color: var(--background)"><?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?></h5>
              <p class="text-muted">
                <?= htmlspecialchars($_SESSION['lang'] == 'en' ? $member['position_en'] : $member['position_es']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
  </section>

  <?php if (!empty($honor_members)): ?>
  <section class="section" id="honor-members">
    <div class="container scroll-margin">
      <div class="text-center mb-5 px-3 px-md-5">
        <h2 class="fw-bold mb-4" style="color: var(--muted);" translation-key="honor_title"></h2>
        <div class="mx-auto mb-4" style="width:60px; height:3px; background: var(--primary); border-radius:2px;"></div>
        <h6 class="lh-lg text-muted mx-auto" style="max-width: 700px" translation-key="honor_subtitle"></h6>
      </div>
      
      <div class="mt-5 row justify-content-center">
        <?php foreach ($honor_members as $member): ?>
          <div class="col-sm-6 col-lg-3">
            <div class="team-box text-center">
              <div class="team-wrapper">
                <div class="team-member">
                  <?php
                  $social_url = $member['socials'] ?? '';
                  $parsed = parse_url($social_url);
                  $safe_social = '#';
                  if ($parsed && isset($parsed['host'])) {
                      foreach ($allowed_hosts as $host) {
                          if (str_ends_with($parsed['host'], $host)) {
                              $safe_social = htmlspecialchars($social_url, ENT_QUOTES, 'UTF-8');
                              break;
                          }
                      }
                  }
                  ?>
                  <a href="<?= $safe_social ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?= htmlspecialchars(cdn_from_image_path($member['image_path'])) ?>"
                      alt="<?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                      class="img-fluid rounded">
                  </a>
                </div>
              </div>
              <h5 class="mt-3" style="color: var(--background)"><?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?></h5>
              <p class="text-muted">
                <?= htmlspecialchars($_SESSION['lang'] == 'en' ? $member['position_en'] : $member['position_es']) ?>
              </p>
              <?php if (!empty($member['graduation_year'])): ?>
                <p class="text-muted"><em>Class of '<?= htmlspecialchars($member['graduation_year']) ?></em></p>
              <?php endif; ?>
              <?php if (!empty($member['honor_quote'])): ?>
                <p class="fst-italic text-muted">"<?= htmlspecialchars($member['honor_quote']) ?>"</p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php include('assets/footer.php'); ?>

  <script src="js/language.js"></script>
</body>
</html>