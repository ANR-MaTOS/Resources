<?php
session_start();
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base

// define variables
$success_msg = ""; $error_msg = "";
$token = ""; $mt_hal_utile = ""; $nat_langs = ""; $other_langs = ""; $email = "";
$exp_dur=""; $articles_en = ""; $articles_fr = ""; $trans_use = ""; $avis_trad = "";
$consent = "";
$exp_dur_err = ""; $nat_langs_err = ""; $other_langs_err = ""; $email_err = ""; $avis_trad_err = ""; $mt_hal_utile_err= ""; $consent_err = "";

function generateMessageID() {
  return sprintf(
    "<%s.%s@%s>",
    base_convert(microtime(), 10, 36),
    base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
    $_SERVER['SERVER_NAME']
  );
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Something posted
  if (isset($_POST['confirm'])) {
    date_default_timezone_set('Europe/Paris');
    $current_time = microtime();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $token_prefix = hash_hmac("sha1", $current_time . $ip_address, rand());

    $new_nat_langs = htmlspecialchars($_POST["new_nat_langs"]);
    $new_other_langs = htmlspecialchars($_POST["new_other_langs"]);
    $new_exp_dur = str_replace("&lt;", "<", htmlspecialchars($_POST["new_exp_dur"]));
    $new_avis_trad = htmlspecialchars($_POST["new_avis_trad"]);
    $new_trans_use = isset($_POST["new_trans_use"]) ? 1 : 0;
    $new_email = htmlspecialchars($_POST["new_email"]);
    $new_consent = isset($_POST["new_consent"]) ? 1 : 0;
    $new_articles_en = isset($_POST["new_articles_en"]) ? 1 : 0;
    $new_articles_fr = isset($_POST["new_articles_fr"]) ? 1 : 0;
    $new_mt_hal_utile = htmlspecialchars($_POST["new_mt_hal_utile"]);

    // non-empty variables and check maximum lengths
    if (empty(trim($new_nat_langs)) || strlen(trim($new_nat_langs)) > 256) {
      $error_msg .= "Le texte pour vos langues maternelles dépasse le nombre de caractères maximum.<br>";
      $nat_langs_err = "Votre langue maternelle est obligatoire et ne doit pas dépasser 256 caractères.";
    }
    if (strlen(trim($new_other_langs)) > 256) {
      $error_msg .= "Le texte pour vos autres langues parlés dépasse le nombre de caractères maximum.<br>";
      $other_langs_err = "Ce texte ne doit pas dépasser 256 caractères.";
    }
    if (! preg_match("/^(<3|3-10|10\+)$/", $new_exp_dur)) {
      $error_msg .= "La valeur entrée pour votre expérience n'est pas valide.<br>";
      $exp_dur = "La valeur entrée n'est pas valide.";
    }
    if (empty(trim($new_email))) {
      $error_msg .= "Votre adresse e-mail est obligatoire pour vous envoyer votre token de connexion.<br>";
      $email_err = "Votre adresse e-mail est obligatoire pour vous envoyer votre token de connexion.";
    }
    if (empty(trim($new_mt_hal_utile))) {
      $error_msg .= "Il faut choisir une option pour l'utilité d'un outil de traduction dans HAL.<br>";
      $mt_hal_utile_err = "Il faut choisir une des options.";
    }
    // Comment must not exceed 2000 characters
    if (strlen(trim($new_avis_trad)) > 2000) {
      $error_msg .= "Les autres commentaires ne doivent pas dépasser 2000 caractères (espaces comprises).<br>";
      $avis_trad_err = "Ce texte doit contenir un maximum de 2000 caractères (espaces comprises).";
    }
    // Consent must be accepted
    if ($new_consent == 0) {
      $error_msg .= "Il est obligatoire d'accepter ces conditions pour créer un compte.<br>";
      $consent_err = "Il est obligatoire d'accepter ces conditions pour créer un compte.";
    }

    $sql_save = "INSERT INTO User_tal (token, native_langs, other_langs, experience, written_en, written_fr, mt_tools, utility_mt_hal, free_text, accepted_conditions) VALUES (?,?,?,?,?,?,?,?,?,?);";
    // Only update the entry if no errors have previously been detected
    if (empty($consent_err) && empty($email_err) && empty($exp_dur) && empty($nat_langs_err) && empty($mt_hal_utile_err) && empty($avis_trad_err) && empty($other_langs_err)) {
      if ($stmt_save = mysqli_prepare($link, $sql_save)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt_save, "ssssssssss", $token_prefix, $new_nat_langs, $new_other_langs, $new_exp_dur, $new_articles_en, $new_articles_fr, $new_trans_use, $new_mt_hal_utile, $new_avis_trad, $new_consent);
        $stmt_save_ok = mysqli_stmt_execute($stmt_save);
        if ($stmt_save_ok) {
          $success_msg .= "Sauvegardé&nbsp! ";

          $full_token = "";
          // get complete token (token + id) of user
          $sql_user = "SELECT CONCAT(token, id) FROM User_tal WHERE token = ?";
          if ($stmt_user = mysqli_prepare($link, $sql_user)) {
            mysqli_stmt_bind_param($stmt_user, "s", $token_prefix);
            if (mysqli_stmt_execute($stmt_user)) {
              mysqli_stmt_store_result($stmt_user);
              mysqli_stmt_bind_result($stmt_user, $full_token);
              mysqli_stmt_fetch($stmt_user);
            } else {
              $error_msg .= "Execution error in getting final token";
            }
          } else {
            $error_msg .= "Preparation error in getting final token";
          }

          // send email with the necessary information
          $messageid = generateMessageID();
          if (mail($new_email,"Campagne d'évaluation MATOS - informations de connexion",
          "Cher/chère collègue,

Votre compte sur le site de post-édition de résumés d'articles dans le cadre du projet ANR MATOS a été initialisé avec succès. Votre jeton de connexion personnalisé est le suivant :

          ".$full_token."

Ce jeton est strictement personnel et ne doit pas être partagé avec d'autres personnes. Vous en aurez besoin pour vous connecter sur le site, récupérer les post-éditions que vous réalisez et éventuellement pour rétracter vote consentement dans le futur si vous le souhaitez.

Vous pouvez désormais commencer à post-éditer des résumés d'articles. Vous pouvez accéder à l'interface via ce lien: http://postedition.anr-matos.fr

Vous trouverez des informations supplémentaires sur le projet ici: http://postedition.anr-matos.fr/tal/about.php.

En vous remerciant par avance de votre participation à cette étude,

Cordialement

R. Bawden, E. de la Clergerie, L. Romary, F. Yvon",
	         "From:matos.postedit@gmail.com" ."\n" . "Message-ID:" . $messageid)) {
             $_SESSION['from'] = "inscription";
             $_SESSION['token_connect'] = $full_token;
             header('Location: index.php');
             exit();
           } else {
            $success_msg .= "tried to send email but failed...";
          }
        } else {
          $error_msg .= "Une erreur est survenue pendant la sauvegarde de l'utilisateur. ";
        }
      } else {
        $error_msg .= "Une erreur est survenue pendant la préparation de la demande de sauvegarde de l'utilisateur. ";
      }
    }
    // Update with new (saved) values
    $email = $new_email;
    $articles_en = $new_articles_en;
    $articles_fr = $new_articles_fr;
    $exp_dur = $new_exp_dur;
    $nat_langs = $new_nat_langs;
    $other_langs = $new_other_langs;
    $trans_use = $new_trans_use;
    $mt_hal_utile = $new_mt_hal_utile;
    $avis_trad = $new_avis_trad;
    $consent = $new_consent;
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <meta name="description" content="">
      <meta name="author" content="">
      <link rel="stylesheet" href="matos.css">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
      <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

      <!--link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css" integrity="sha384-QYIZto+st3yW+o8+5OHfT6S482Zsvz2WfOzpFSXMF9zqeLcFV0/wlZpMtyFcZALm" crossorigin="anonymous">-->
      <link href="matos.css" rel="stylesheet" type="text/css"><title>MATOS - inscription</title>
   </head>
   <body>
     <!-- Responsive navbar-->
     <div w3-include-html="menu.php"></div>
      <!-- Header-->
      <header class="pt-2 mb-4 bg-light">
         <div class="container px-md-5">
            <div class="p-2 bg-light rounded-3 text-center">
               <div class="m-2 m-lg-5">
                  <h3 class="display-7 fw-bold">Inscrivez-vous
                     pour g&eacute;n&eacute;rer votre jeton de connexion
                  </h3>
               </div>
            </div>
         </div>
      </header>
      <!-- Page Content-->
      <div class="container px-lg-5 mb-5">

        <?php if (!empty($error_msg)):?>
          <div class="alert alert-danger" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            Il existe des erreurs dans le formulaire&nbsp:<br><?php echo $error_msg ?>
          </div>
        <?php endif ?>
        <?php if (!empty($success_msg)):?>
          <div class="alert alert-success" role="alert">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <?php echo $success_msg ?>
          </div>
        <?php endif ?>
         <!-- Page Features-->
         <p class="mx-5">Merci de remplir les informations suivantes pour initialiser votre compte.
           Votre jeton de connexion personnalisé et des informations de connexion vous seront envoyés
           par mail quand vous cliquerez sur "Créer mon compte" à la fin du formulaire.
         </p>
         <form class="px-md-5 mt-3" method="post">
            <div class="form-group my-3">
               <label for="new_nat_langs">Langue(s) maternelle(s)&nbsp;:</label>
               <input type="text" class="form-control <?php echo (!empty($nat_langs_err)) ? 'is-invalid' : ''; ?>" name="new_nat_langs" id="new_nat_langs" aria-describedby="nat_langs_help"
                  placeholder="P. ex. anglais, français" value="<?php echo $nat_langs ?>" maxlength="256">
               <span class="invalid-feedback"><?php echo $nat_langs_err; ?></span>
               <small id="nat_langs_help" class="form-text text-muted">Si vous avez plusieurs langues maternelles, séparez-les par des virgules.</small>
            </div>
            <div class="form-group my-3">
               <label for="new_other_langs">Autres langues (et niveau)&nbsp;:</label>
               <input type="text" class="form-control <?php echo (!empty($other_langs_err)) ? 'is-invalid' : ''; ?>" id="new_other_langs" name="new_other_langs" aria-describedby="other_langs_help"
                  placeholder="P. ex. espagnol (d&eacute;butant), grec (courant)" value="<?php echo $other_langs ?>" maxlength="256">
               <span class="invalid-feedback"><?php echo $other_langs_err; ?></span>
               <small id="other_langs_help" class="form-text text-muted">Si vous parlez plusieurs autres langues, séparez-les par des virgules. Vous pouvez aussi préciser le niveau entre parenthèses après chaque langue.</small>
            </div>
            <div class="form-group my-3">
               <label for="new_exp_dur">Nombre d'années d'expérience post-master dans les domaines des sciences de la terre et de l'environnement et physique &nbsp;:</label>
               <select class="form-control <?php echo (!empty($exp_dur_err)) ? 'is-invalid' : ''; ?>" id="new_exp_dur" name="new_exp_dur">
                  <option value="<3" <?php if ($exp_dur == "<3"):?>selected <?php endif ?>>&lt;3 ans</option>
                  <option value="3-10" <?php if ($exp_dur == "3-10"):?>selected <?php endif ?>>3-10 ans</option>
                  <option value="10+" <?php if ($exp_dur == "10+"):?>selected <?php endif ?>>10+ ans</option>
               </select>
               <span class="invalid-feedback"><?php echo $exp_dur_err; ?></span>
            </div>
            Vous avez d&eacute;j&agrave;...
            <div class="form-group form-check my-3">
               &nbsp;<input type="checkbox" class="form-check-input" name="new_articles_en" id="new_articles_en" <?php if ($articles_en == "1"):?>checked <?php endif ?>>
               <label class="form-check-label" for="new_articles_en">...&eacute;crit des articles de
               recherche en anglais.</label>
            </div>
            <div class="form-group form-check my-3">
               &nbsp;<input type="checkbox" class="form-check-input" name="new_articles_fr" id="new_articles_fr" <?php if ($articles_fr == "1"):?>checked <?php endif ?>>
               <label class="form-check-label" for="new_articles_fr">...&eacute;crit des articles de recherche en français.</label>
            </div>
            <div class="form-group form-check my-3">
               &nbsp;<input type="checkbox" class="form-check-input" name="new_trans_use" id="new_trans_use" <?php if ($trans_use == "1"):?>checked <?php endif ?>>
               <label class="form-check-label"
                  for="new_trans_use">...utilis&eacute; des outils de traduction automatique pour aider &agrave;
               r&eacute;diger un article ou un r&eacute;sum&eacute; d'article.</label>
            </div>
            Si la plateforme HAL proposait un outil de traduction
            automatique des titres et des résumés, est-ce que vous trouveriez cet outil&nbsp;:
            <div class="form-group form-check my-3 form-control <?php echo (!empty($mt_hal_utile_err)) ? 'is-invalid' : ''; ?>" style="border: 0px;">
               <div class="radio">
                  <input type="radio" id="new_hal_utile_utile" name="new_mt_hal_utile" value="utile" <?php if ($mt_hal_utile == "utile"):?>checked <?php endif ?>> <label for="new_hal_utile_utile">Utile</label>
               </div>
               <div class="radio">
                  <input type="radio" id="new_hal_utile_inutile" name="new_mt_hal_utile" value="inutile" <?php if ($mt_hal_utile == "inutile"):?>checked <?php endif ?>> <label for="new_hal_utile_inutile">Inutile</label>
               </div>
               <div class="radio disabled">
                  <input type="radio" id="new_hal_utile_na" name="new_mt_hal_utile" value="na" <?php if ($mt_hal_utile == "na"):?>checked <?php endif ?>> <label for="new_hal_utile_na"> Pas d'avis</label>
               </div>
            </div>
            <span class="invalid-feedback"><?php echo $mt_hal_utile_err; ?></span>
            <div class="form-group my-3">
               <label for="new_avis_trad">D'autres commentaires (facultatif)&nbsp;?</label>
               <textarea class="form-control <?php echo (!empty($avis_trad_err)) ? 'is-invalid' : ''; ?>" id="new_avis_trad" name="new_avis_trad" rows="3" maxlength="2000"><?php echo $avis_trad; ?></textarea>
               <span class="invalid-feedback"><?php echo $avis_trad_err; ?></span>
            </div>
            <div class="form-group">
               <label for="new_email">Adresse e-mail</label>
               <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="new_email" name="new_email"
                  aria-describedby="emailHelp" placeholder="Adresse e-mail" value="<?php echo $email ?>">
               <span class="invalid-feedback"><?php echo $email_err; ?></span>
               <small id="emailHelp" class="form-text
                  text-muted">Nous ne conserverons pas votre adresse e-mail. Elle ne sert qu'à vous transmettre votre jeton de connexion.</small>
            </div>
            <div class="form-group form-check my-3 form-control <?php echo (!empty($consent_err)) ? 'is-invalid' : ''; ?>" style="border: 0px">
               &nbsp;<input type="checkbox" class="form-check-input" id="new_consent" name="new_consent" <?php if ($consent == "1"):?>checked <?php endif ?>>
	             Je confirme avoir pris connaissance du <a href="consentement.html"
                  target="_blank">formulaire du consentement <i class="far fa-xs fa-external-link-alt"></i></a> et être d'accord avec son contenu.
	         </div>
           <span class="invalid-feedback"><?php echo $consent_err; ?></span>
	         <div class="text-center">
             <input type="submit" class="btn btn-primary" name="confirm" id="confirm" value="Confirmer">
	         </div>
      </form>

      </div>
      <!-- Footer-->
      <footer class="py-5 bg-secondary" style="margin-top:auto;">
         <div class="container">
            <p class="m-0 text-center
               text-white">Copyright &copy; ANR MATOS 2023-2024</p>
         </div>
      </footer>
      <!-- Bootstrap core JS-->
      <!-- jquery -->
      <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
      <!-- Core theme JS-->
      <!-- jquery -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
      <!-- Core theme JS-->

      <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
      <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/7.1.0/bootstrap-slider.min.js"></script>-->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
      <script src="js/include_html.js"></script>
      <script>
          includeHTML();
          $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip()
          })
      </script>
   </body>
</html>
