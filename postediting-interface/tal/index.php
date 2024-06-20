<?php
session_start();

//$member_token = $_SESSION["token"];
//require_once "../config.php"; // fichier de connexion à la base
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base

if (!empty($_SESSION['from'])) {
  if ($_SESSION['from'] == 'inscription') {
    $token_connnection = htmlspecialchars($_SESSION['token_connect']);
    $success_msg .= "Vous êtes inscrit(e)s&nbsp;! Votre jeton de connexion est&nbsp;:<br>" . $token_connnection . "<br>Ne le perdez pas&nbsp;
    vous en aurez besoin chaque fois que vous vous connectez. Il vous a aussi
    été envoyé par mail (vérifiez dans votre boîte de spams).";
  }
}
$_SESSION['from'] = "index";

//phpinfo();

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
    <title>MATOS - accueil</title>
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
                    <h3 class="text-center">TAL (Traitement Automatique des Langues)</h3>
                     <p class="fs-6 mt-4">Dans le cadre du projet <a href="https://anr-matos.github.io">ANR MATOS</a>,
                       vous êtes invités à participer à une
                       campagne de post-édition de traductions de titres et de
                       résumés d'articles dans le domaine du TAL. Il s'agit de titres et de résumés d'articles
                       écrits en anglais collectés sur le <a href="https://hal.science">site HAL</a>,
                       traduits automatiquement en français.
                       Nous vous encourageons à post-éditer en particulier vos propres résumés&nbsp;!</p>

                      <p>Pour participer, vous devez d'abord vous inscrire pour générer votre jeton de connexion unique.
                        L'inscription prendra seulement 1 minute ou deux. Veillez à garder précieusement le mail indiquant
                        votre jeton de connexion car si vous le perdez, vous devrez refaire une inscription.</p>

                      <p>Pour plus d'informations sur le projet, cliquez <a href="about.php">ici</a>.</p>

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
                    <!--<div class="col-lg-6 col-xxl-4 mb-5">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4 p-lg-5 pt-0 pt-lg-0">
                                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-4 mt-n4"><i class="bi bi-collection"></i></div>
                                <h2 class="fs-4 fw-bold">Inscrivez-vous</h2>
                                <p class="m-0 mt-2">Générez votre token personnalisé</p>
                                <a class="btn btn-primary btn-md mt-2" href="inscription.php">Cliquez ici</a><p class="mb-0"></p>
                            </div>
                        </div>
                    </div>-->

                    <div class="col-6 mb-5 mt-0 mx-auto text-center">
                      <a class="btn btn-primary btn-md mt-2" href="articles.php"><h2 class="fs-4 fw-bold">Commencez à post-éditer&nbsp;!</h2></a>
                      <!--<p>Vous devez d'abord vous connecter. Connectez-vous <a href="connexion.php">ici</a>.</p>
                      <p>Pas encore inscrit(e)? Générez votre jeton de connexion <a href="inscription.php">ici</a>.</p>-->
                    </div>
                    <!--<div class="col-lg-6 col-xxl-4 mb-5">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center p-4 p-lg-5 pt-0 pt-lg-0">
                                <div class="feature bg-primary bg-gradient text-white rounded-3 mb-4 mt-n4"><i class="bi bi-cloud-download"></i></div>
                                <h2 class="fs-4 fw-bold">Connectez-vous</h2>
                                <p class="mb-0">En utilisant votre token</p>
                                <a class="btn btn-primary btn-md mt-2" href="connexion.php">Cliquez ici</a><p class="mb-0"></p>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="py-5 bg-secondary"">
            <div class="container"><p class="m-0 text-center
	      text-white">Copyright &copy; ANR MATOS 2023-2024</p></div>
        </footer>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="js/include_html.js"></script>
        <script>includeHTML();</script>
    </body>
</html>
