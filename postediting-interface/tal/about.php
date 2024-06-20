<?php
session_start();

//$member_token = $_SESSION["token"];
//require_once "../config.php"; // fichier de connexion à la base
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base


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
  <title>MATOS - à propos</title>
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

        <h4>Quel est le but de cette recherche&nbsp;?</h4>

        <p>Cette étude s'inscrit dans le cadre du projet <a href="http://anr-matos.fr">ANR MaTOS</a> (Machine Translation for Open Science), qui s'intéresse au développement de méthodes et d'outils pour faciliter la traduction automatique de documents en texte intégral pour des écrits scientifiques (articles, communications, projets et protocoles de recherche, etc).</p>

        <p>L'étude des méthodes de traduction pour des documents complets se heurte à un problème de méthode, lié à la mesure la qualité des traductions. Les mesures automatiques de la qualité sont insuffisamment précises pour détecter les erreurs typiques des systèmes de traduction en domaine de spécialité: problèmes de co-référence, incohérence des choix lexicaux, mésusage de la terminologie scientifique, etc. L'alternative est de recueillir des évaluations humaines auprès de sujets humains, ce qui demande de s'assurer qu'ils disposent des compétences pour réaliser la tâche.</p>

        <p>Le projet MaTOS prévoit la mise en place d'une expérience de collecte de jugements humains par post-édition auprès du plus large échantillon possible d'utilisateurs de la plateforme HAL. La post-édition correspond à l'édition d'une traduction automatique pour en dériver une version acceptable; il s'agit d'une pratique largement développée dans l'industrie de la traduction, également très utilisée dans les milieux scientifiques. Cette activité correspond à une tâche réaliste, qui nous donnera indirectement accès à des mesures de qualité telles que perçues par des expert(e)s du domaine.</p>

        <p>
          Le but principal donc de cette recherche est de préparer une étude à grande échelle qui sera conduite en 2024 sur la plateforme HAL. Cette étude pilote vise principalement à évaluer la qualité actuelle des systèmes de traduction automatique pour des traductions de textes scientifiques, et à mesurer l'effort qui serait nécessaire à des spécialistes du domaine pour réviser ces traductions automatiques de manière à les rendre publiables. Elle permettra en second lieu de comparer objectivement plusieurs systèmes de traduction automatique.
        </p>

        <h4>Qui peut participer à l'étude&nbsp;?</h4>

        <p>
          La seule condition pour participer est une expertise avérée dans le domaine du TAL, correspondant à l'achèvement d'un Master 2 dans le domaine, ainsi qu'une bonne maitrise de la langue française, suffisante pour rédiger de manière autonome un résumé d'article en français.
        </p>

        <h4>Si vous participez, comment vont être traitées les données recueillies pour la recherche&nbsp;?</h4>

        <p>
          Dans le cadre de cette recherche, il vous sera dans un premier temps demandé de vous créer un compte; dans un second temps, vous serez invités à effectuer des révisions (ou post-éditions) de traductions automatiques de titres et de résumés d'articles dans le domaine du TAL. Les révisions serviront à améliorer les versions françaises de traductions réalisées depuis l'anglais.
        </p>

        <p>
          Durant la procédure d'inscription, vous serez invités à répondre à quelques questions relatives à votre connaissance du domaine et votre maitrise de la langue française. Vous devrez également consentir à l'exploitation future des traductions produites par vos soins. Il vous sera enfin attribué un identifiant unique qui vous permettra d'accéder à la plate-forme de post-édition. Aucune information personnelle identifiante n'est collectée.
        </p>

        <p>
          Les sessions de révisions se déroulent sur une plateforme dédiée, sur laquelle vous vous connectez en utilisant l'identifiant attribué précédemment. Chaque session correspond à la correction d'une unique traduction d'un résumé d'article, à choisir dans une liste. Au terme de chaque session, seuls sont enregistrés (1) le texte révisé, et (2) la durée de la session.
        </p>

        <p>
          Si vous décidez d’arrêter de participer à la recherche, les données recueillies seront supprimées sur simple demande par courrier électronique (voir la page <a href="contact.php">Contact</a>). Il suffira de mentionner à l'appui de votre demande l'identifiant unique associé à votre compte.
        </p>

        <h4>Quels sont vos droits ?</h4>

        <p>
          Votre participation à cette recherche est entièrement libre et volontaire.
        </p>

        <p>
          Vous pourrez, tout au long de la recherche et à son issue, demander des informations des explications sur le déroulement de la recherche au responsable scientifique de l'étude.
        </p>

        <p>
          Vous pouvez vous retirer à tout moment de la recherche sans justification, et demander que toutes les traductions que vous avez révisées soient supprimées de notre base de données.
        </p>

        <h4>D'où viennent les titres et résumés utilisés dans l'étude&nbsp;?</h4>
        <p>Les titres et les résumés des articles utilisés dans l'étude proviennent tous de l'archive ouverte HAL, extraits à l'aide de l'API HAL. Ils font partie des métadonnées fournies par l'API. Il s'agit d'un sous-ensemble d'articles associés à la catégorie "informatique", sélectionnés en fonction du lieu de publication et des mots-clés du domaine du TAL qui ont été identifiés dans le titre, dans le résumé ou dans les meta-données.
        </p>

      </div>
    </div>
  </div>
</section>
<!-- Footer-->
<footer class="py-5 bg-secondary">
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
