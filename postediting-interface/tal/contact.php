<?php
session_start();

//$member_token = $_SESSION["token"];
<<<<<<<< HEAD:htdocs/tal/tal-backup/contact.php
require_once "../config.php"; // fichier de connexion à la base
========
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base
>>>>>>>> 5b091bab2134dee9ea9bb82a0041e51b9f6e022e:htdocs/tal/contact.php

if (!empty($_SESSION['from'])) {
  if ($_SESSION['from'] == 'inscription') {
    $success_msg .= "Vous êtes inscrites&nbsp;! Vérifiez vos mails (y compris dans le spam) pour trouver votre token de connexion.";
  }
}
$_SESSION['from'] = "index";

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
  <link rel="stylesheet" href="matos.css">
  <title>MATOS - Contact</title>
  <meta charset="utf-8" />
</head>
<body>
  <!-- Responsive navbar-->
  <div w3-include-html="menu.php"></div>
  <!-- Header-->
  <header class="pt-3">
    <div class="container px-md-5 pb-1 pt-5">
      <div class="row p-2 rounded-3 text-justify">
        <div class="col-8 mx-auto">
          <h1 class="display-7 fw-bold text-center">Expérience de post-édition de la traduction automatique</h1>

        </div>
      </div>
    </div>
  </header>
  <!-- Page Content-->
  <section class="pt-2">
    <div class="container px-lg-5">
      <?php if (!empty($error_msg)):?>
        <div class="alert alert-danger" role="alert">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <?php echo "Erreur: " . $error_msg ?>
        </div>
      <?php endif ?>
      <?php if (!empty($success_msg)):?>
        <div class="alert alert-success" role="alert">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <?php echo $success_msg ?>
        </div>
      <?php endif; ?>
      <!-- Page Features-->
      <div class="row gx-lg-5">
        <h1>Contact</h1>
        <p>Pour toute question sur le projet, la campagne de post-édition ou vos données ou pour signaler une erreur dans le site, n'hésitez pas à nous contacter à
          <script>var s="=b!isfg>#nbjmup;dpoubduAbos.nbupt/gs#?dpoubduAbos.nbupt/gs=0b?";var m="";for(var i=0;i<s.length;i++)m+=String.fromCharCode(s.charCodeAt(i)-1);document.write(m);</script><noscript>You must enable JavaScript to see this text.</noscript>
        </p>
      </div>
    </div>
  </div>
</section>
<!-- Footer-->
<footer class="py-5 bg-secondary mt-auto">
  <div class="container"><p class="m-0 text-center
    text-white">Copyright &copy; ANR MATOS 2023</p></div>
  </footer>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="js/include_html.js"></script>
  <script>includeHTML();</script>
</body>
</html>
