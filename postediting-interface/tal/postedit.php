<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: connexion.php");
  exit;
}
$member_token = $_SESSION["token"];
$_SESSION['from'] = "post-edit";

//require_once "../config.php"; // fichier de connexion à la base
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base

// Define variables
$error_msg = ""; $success_msg = "";
$trans_sys_id = null;
$user_id = null;

$expert_user = 1; $article_id = ""; $search_time = null;
$orig_trad = ""; $postedit_text = ""; $new_postedit_text = ""; $postedit_id = null;
$title = ""; $venue = ""; $authors = ""; $abstract_en = ""; $year = ""; $author_names = "";
$remarques = ""; $severity = ""; $new_severity = "";
$prob_document = 0; $prob_style = 0; $prob_grammar = 0; $prob_terminology = 0;
$prob_faithfulness = 0; $prob_spelling_punctuation = 0;
$remarques_err = ""; $postedit_err = ""; $severity_err = ""; $keyword_err = "";
$prob_faithfulness_err = ""; $prob_grammar_err = ""; $prob_style_err = "";
$prob_document_err = ""; $prob_terminology_err = "";
$prob_spelling_punctuation_err = "";
$major_err = 0;
$new_prob_style = ""; $new_prob_document = "";
$new_prob_grammar = ""; $new_prob_faithfulness = ""; $new_prob_terminology = "";
$new_prob_spelling_punctuation = "";

date_default_timezone_set('Europe/Paris');
$current_time = date('Y-m-d H:i:s', time());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $article_id = $_POST["article_id"];
  $search_time = $_POST["search_time"];
  $trans_sys_id = $_POST["trans_sys_id"];
  //$translation_id = $POST["translation_id"];

  // Get article text, text to postedit, meta-data, etc.
  if (empty($article_id)) {
    $error_msg .= "Aucun article trouvé. ";
    $major_err = 1;
    //var_dump($search_time, $article_id, $trans_sys_id);
    //print_r("st = " .$search_time . ", art = " .$article_id . ", trans = " . $trans_sys_id);
  } else {
    // Get user information (is it an expert?)
    $sql = "SELECT id, expert FROM User_tal WHERE CONCAT(token, id) = ?;";
    if ($stmt_user = mysqli_prepare($link, $sql)) {
      mysqli_stmt_bind_param($stmt_user, "s", $member_token);
      if (mysqli_stmt_execute($stmt_user)) {
        mysqli_stmt_store_result($stmt_user);
        if (mysqli_stmt_num_rows($stmt_user) == 1) {
          mysqli_stmt_bind_result($stmt_user, $user, $expert);
          if (mysqli_stmt_fetch($stmt_user)) {
            $expert_user = $expert;
            $user_id = $user;
          } else {
            $major_err = 1;
            $error_msg .= "Récupération de l'utilisateur (3). ";
          }
        } else {
          $major_err = 1;
          $error_msg .= "Récupération de l'utilisateur (2). ";
        }
      } else {
        $major_err = 1;
        $error_msg .= "Récupération de l'utilisateur (1). ";
      }
    } else {
      $major_err = 1;
      $error_msg .= "Récupération de l'utilisateur (0). ";
    } // end prepare

    // Get the original article information and text
    $sql = "SELECT Article_tal.id, Article_tal.id_hal, title, Article_tal.abstract_en, Translation_tal.id, Translation_tal.text, Article_tal.author_names, Article_tal.year, Article_tal.venue
    FROM Article_tal INNER JOIN Translation_tal ON Article_tal.id = Translation_tal.article_id
    WHERE Article_tal.id = ? and Translation_tal.trans_sys_id = ?;";
    if ($stmt = mysqli_prepare($link, $sql)) {
      mysqli_stmt_bind_param($stmt, "ss", $article_id, $trans_sys_id);
      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
          mysqli_stmt_bind_result($stmt, $article_id, $hal_id, $title, $abstract_en, $translation_id, $orig_text, $author_names, $year, $venue);
          $table_body = "";
          if (mysqli_stmt_fetch($stmt)) {
            $table_body .= "<tr><td>" . $article_id . "</td><td>" . $title . "</td><td>" . $year . "</td><td>". $venue . "</td></tr>";
            $postedit_text = $orig_text;
          } else {
            $major_err = 1;
            $error_msg .= "Affichage de l'article. ";
          }
        } elseif (mysqli_stmt_num_rows($stmt) < 1) {
          $major_err = 1;
          $error_msg .= "Article non trouvé. ";
        } else {
          $major_err = 1;
          $error_msg .= "Ambiguïté dans la récupération de l'article. ";
        }
      } else {
        $major_err = 1;
        $error_msg .= "Récupération de l'article. ";
      } // end execution
    } else {
      $major_err = 1;
      $error_msg .= "Préparation de l'article. ";
    } // end preparation

    // Check to make sure a finalised postedit does not already exist for this article
    $sql_find_finalised = "SELECT id, text, free_text, severity FROM Postedit_tal WHERE translation_id = ? AND user_id = ? AND finalised = 1;";
    if ($stmt_find_finalised = mysqli_prepare($link, $sql_find_finalised)) {
      mysqli_stmt_bind_param($stmt_find_finalised, "ss", $translation_id, $user_id);
      if (mysqli_stmt_execute($stmt_find_finalised)) {
        mysqli_stmt_store_result($stmt_find_finalised);
        if (mysqli_stmt_num_rows($stmt_find_finalised) > 0) {
          // Postedit already exists
          $error_msg .= "Vous n'êtes pas autorisé(e) à post-éditer la même traduction plusieurs fois (".$translation_id."). ";
          $major_err = 1;
        }
      } else {
        $error_msg .= "Erreur d'accès à la base de données. ";
        $major_err = 1;
      } // end execution
    } else {
      $error_msg .= "Erreur de préparation dans la récupération d'instances. ";
      $major_err = 1;
    }// end preparation

    //print_r('<br>search time= ' . $search_time);
    //print_r('<br>user id=' . $user_id);
    //print_r('<br>trans sys id=' . $trans_sys_id);
    //print_r("<br>translation id= " . $translation_id);
    if ($major_err == 0) {
      // If a postedit does not already exist w/ this search time, create a new one
      $sql_find = "SELECT id FROM Postedit_tal WHERE
      translation_id = ? AND user_id = ? AND finalised = 0 AND search_time = ?;";
      if ($stmt_find = mysqli_prepare($link, $sql_find)) {
        mysqli_stmt_bind_param($stmt_find, "sss", $translation_id, $user_id, $search_time);
        if (mysqli_stmt_execute($stmt_find)) {
          mysqli_stmt_store_result($stmt_find);
          // several postedits found - this is a major problem
          if (mysqli_stmt_num_rows($stmt_find) == 0) {
            //print_r("<br>Adding a new postedit: " . $search_time . ", " . $user_id . ", ". $translation_id);
            $sql_insert = "INSERT INTO Postedit_tal
            (text, start_time, translation_id, user_id, search_time)
            VALUES (?,?,?,?,?);";
            if ($stmt_insert = mysqli_prepare($link, $sql_insert))  {
              // Bind variables to the prepared statement as parameters
              mysqli_stmt_bind_param($stmt_insert, "sssss", $postedit_text,
              $current_time, $translation_id, $user_id,
              $search_time);
              // execute and get error message
              $stmt_insert_ok = mysqli_stmt_execute($stmt_insert);
              if (! $stmt_insert_ok) {
                $error_msg .= "Initialisation de la post-édition. ";
              }
            } else {
              $error_msg .= "Préparation de la post-édition. ";
            } // end preparation
          } // end if 0 rows
        } else {
          $error_msg .= "Execution error (0)";
        } // end execution
      } // end preparation

      // Get id of saved postedit
      $sql_find = "SELECT id, text, free_text, severity FROM Postedit_tal WHERE
      translation_id = ? AND user_id = ? AND finalised = 0 AND search_time = ?;";
      if ($stmt_find = mysqli_prepare($link, $sql_find)) {
        mysqli_stmt_bind_param($stmt_find, "sss", $translation_id, $user_id, $search_time);
        if (mysqli_stmt_execute($stmt_find)) {
          mysqli_stmt_store_result($stmt_find);
          // several postedits found - this is a major problem
          if (mysqli_stmt_num_rows($stmt_find) == 1) {
            mysqli_stmt_bind_result($stmt_find, $postedit_id, $postedit_text, $remarques, $severity);
            mysqli_stmt_fetch($stmt_find);
          } else {
            $error_msg .= "No postedit found or several postedits found (getting saved id)";
            $major_err = 1;
          }
        } else {
          $error_msg .= "Execution error (getting saved id)";
          $major_err = 1;
        }
      } else {
        $error_msg .= "Preparation error (getting saved id)";
        $major_err = 1;
      }

      //print_r("<br>postedit id = " . $postedit_id);
      // reinitialise translation text - TODO: also reinitialise time??
      if (isset($_POST['reinit-text'])) {
        $postedit_text = $orig_text;
        $severity = NULL;
        $remarques = "";

        // save a new postedit if it does not already exist
        // or technical error with this example, log as being problematic
      } else if (isset($_POST['finalise-text']) || isset($_POST['signal-error'])) {

        // get values from the form
        $new_postedit_text = htmlspecialchars($_POST["new_postedit_text"] ?? "");
        $new_remarques = htmlspecialchars($_POST["new_remarques"] ?? "");

        if (isset($_POST['finalise-text'])) {
          // Postedition must not be empty
          if (empty(trim($new_postedit_text ?? ""))) {
            $postedit_err .= "Ce texte ne doit pas être vide.";
            $error_msg .= "La traduction post-éditée ne peut pas être vide.<br>";
          }
          // Postedition must not exceed 5000 characters
          if (strlen(trim($new_postedit_text ?? "")) > 5000) {
            $postedit_err .= "Le texte ne doit pas dépasser 5000 caractères.<br>";
            $error_msg .= "La traduction post-éditée dépassent la limite autorisée (5000 caractères).<br>";
          }
          // Free text must not exceed 1000 characters
          if (strlen(trim($new_remarques ?? "")) > 2000) {
            $remarques_err .= "Le texte ne doit pas dépasser 2000 caractères.";
            $error_msg .= "Les remarques libres dépassent la limite autorisée (2000 caractères). ";
          }
        }

        // Only log general severity for non-expert users
        if ($expert_user != 1) {
          $new_severity = htmlspecialchars($_POST["new_severity"] ?? "");
          // Check the validity of the information entered

          // Severity must either not be specified or be in the allowed values
          if (!preg_match('/^(aucun|peu grave|moyennement grave|grave)$/', trim($new_severity ?? ""))) {
            if (isset($_POST['finalise-text'])) {
              $severity_err .= "Une valeur doit être spécifiée";
              $error_msg .= "Il est obligatoire d'indiquer l'importance des problèmes constatés.<br> ";
            } else {
              $new_severity = NULL;
              //print_r('hi'.$new_severity."\n");
            }
          }
          // Only log fine-grained problems if the user is an expert (not visible to non-expert users)
        } else {
          if (!empty($_POST["prob_faithfulness"])) {
            $new_prob_faithfulness = htmlspecialchars($_POST["prob_faithfulness"] ?? "");
          }
          if (!empty($_POST["prob_grammar"])) {
            $new_prob_grammar = htmlspecialchars($_POST["prob_grammar"] ?? "");
          }
          if (!empty($_POST["prob_terminology"])) {
            $new_prob_terminology = htmlspecialchars($_POST["prob_terminology"] ?? "");
          }
          if (!empty($_POST["prob_style"])) {
            $new_prob_style = htmlspecialchars($_POST["prob_style"] ?? "");
          }
          if (!empty($_POST["prob_spelling_punctuation"])) {
            $new_prob_spelling_punctuation = htmlspecialchars($_POST["prob_spelling_punctuation"] ?? "");
          }
          if (!empty($_POST["prob_document"])) {
            $new_prob_document = htmlspecialchars($_POST["prob_document"] ?? "");
          }


          if (!preg_match('/^[1-4]$/', trim($new_prob_faithfulness ?? ""))) {
            $prob_faithfulness_err .= "La valeur entrée n'est pas valide (". $new_prob_faithfulness .") ";
            $new_prob_faithfulness = NULL;
            //$error_msg .= "faith";
          }
          if (!preg_match('/^[1-4]$/', trim($new_prob_grammar ?? ""))) {
            $prob_grammar_err .= "La valeur entrée n'est pas valide (". $new_prob_grammar .") ";
            $new_prob_grammar = NULL;
            //$error_msg .= "gram";
          }
          if (!preg_match('/^[1-4]$/', trim($new_prob_terminology ?? ""))) {
            $prob_terminology_err .= "La valeur entrée n'est pas valide (". $new_prob_terminology .") ";
            $new_prob_terminology = NULL;
            //$error_msg .= "term";
          }
          if (!preg_match('/^[1-4]$/', trim($new_prob_style))) {
            $prob_style_err .= "La valeur entrée n'est pas valide (". $new_prob_style .") ";
            $new_prob_style = NULL;
            //$error_msg .= "style";
          }
          if (!preg_match('/^[1-4]$/', trim($new_prob_spelling_punctuation ?? ""))) {
            $prob_spelling_punctuation_err .= "La valeur entrée n'est pas valide (". $new_prob_spelling_punctuation .") ";
            $new_prob_spelling_punctuation = NULL;
            //$error_msg .= "spell";
          }
          if (!preg_match('/^[1-4]$/', trim($new_prob_document ?? ""))) {
            $prob_document_err .= "La valeur entrée n'est pas valide (". $new_prob_document .") ";
            $new_prob_document = NULL;
            //$error_msg .= "doc";
          }
          if (! (empty($prob_faithfulness_err) && empty($prob_grammar_err) && empty($prob_terminology_err)
          && empty($prob_style_err) && empty($prob_spelling_punctuation_err) && empty($prob_document_err))) {
            $error_msg .= "Il manque au moins une valeur pour indiquer les problèmes identifiés";
          }
        }
        // No error messages if an error is being signalled
        if (isset($_POST['signal-error'])) {
          $prob_document_error = ""; $prob_style_err = ""; $prob_grammar_err = "";
          $prob_terminology_err = ""; $prob_spelling_punctuation_err = "";
          $prob_faithfulness_err = "";
          $error_msg = "";
        }

        // Update with new (saved) values
        $postedit_text = $new_postedit_text;
        $remarques = $new_remarques;
        $severity = $new_severity;
        $prob_faithfulness = $new_prob_faithfulness;
        $prob_grammar = $new_prob_grammar;
        $prob_style = $new_prob_style;
        $prob_terminology = $new_prob_terminology;
        $prob_document = $new_prob_document;
        $prob_spelling_punctuation = $new_prob_spelling_punctuation;

        // Only update the entry if no errors have previously been detected
        if (empty($postedit_err) && empty($remarques_err) && empty($severity_err)
        && empty($prob_faithfulness_err)&& empty($prob_grammar_err)
        && empty($prob_style_err) && empty($prob_document_err)
        && empty($prob_spelling_punctuation_err) && empty($prob_terminology_err)) {

          if (isset($_POST['finalise-text'])) {
            $error_found = 0;
          } else if (isset($_POST['signal-error'])) {
            $error_found = 1;
          }
          var_dump($severity);
          if ($expert_user == 1) {
            $sql_update = "UPDATE Postedit_tal SET text=?, finalised=1, end_time=?, free_text=?, severity=?, prob_faithfulness=?, prob_grammar=?, prob_terminology=?, prob_style=?, prob_spelling_punctuation=?, prob_document=?, tech_error=? WHERE id=?;" ;
          } else {
            $sql_update = "UPDATE Postedit_tal SET text=?, finalised=1, end_time=?, free_text=?, severity=?, tech_error=? WHERE id=?;" ;
          }


          if ($stmt_update = mysqli_prepare($link, $sql_update))  {

            // Bind variables to the prepared statement as parameters (different depending on whether expert or not)
            if ($expert_user == 1) {
              mysqli_stmt_bind_param($stmt_update, "ssssssssssss", $new_postedit_text,
              $current_time, $new_remarques, $new_severity, $new_prob_faithfulness,
              $new_prob_grammar, $new_prob_terminology, $new_prob_style, $new_prob_spelling_punctuation,
              $new_prob_document, $error_found, $postedit_id);
            } else {
              mysqli_stmt_bind_param($stmt_update, "ssssss", $new_postedit_text, $current_time,
              $new_remarques, $new_severity, $error_found, $postedit_id);
            }
            $stmt_update_ok = mysqli_stmt_execute($stmt_update);
            if ($stmt_update_ok) {
              if (isset($_POST['finalise-text'])) {
                $success_msg .= "Finalisé&nbsp;!";
                $_SESSION['from'] = "finalised";
              } else if (isset($_POST['signal-error'])) {

                $_SESSION['from'] = "error-signaled";
              }


              header('Location: articles.php');
              exit();
            } else {
              $error_msg .= "Une erreur est survenue pendant la finalisation. ";
            }
          } else {
            $error_msg .= "Une erreur est survenue pendant la préparation de la demande de finalisation. ";
          }
        }
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="matos.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
  <title>MATOS - postédit</title>
</head>
<body>
  <!-- Responsive navbar-->
  <div w3-include-html="menu.php"></div>
  <!-- Header-->
  <header class="pt-3 bg-light">
    <div class="container px-md-5">
      <div class="p-2 bg-light rounded-3 text-center">
        <div class="m-2 m-lg-5">
          <h3 class="display-7 fw-bold">Post-éditez la traduction d'un titre et d'un résumé dans le domaine du TAL</h3>
        </div>
      </div>
    </div>
  </header>
  <!-- Page Content-->
  <div class="pt-2">
    <div class="container px-lg-5">
      <div class="row pt-2">
        <div class="col-lg-12">
          <div class="alert alert-info" role="alert">
            <p class="font-weight-bold">Instructions&nbsp;:</p>
            <p class="pb-3">Modifiez le texte (titre et résumé) pour qu'il soit clair, compréhensible
              et acceptable, comme vous le feriez pour une publication dans un journal
              en français (p. ex. la revue TAL). Pour ce faire, merci de ne pas vous servir
              d'outils de traduction automatique. Dans la mesure du possible, merci de faire
              cette révision sans vous interrompre pour que la durée enregistrée corresponde
              au temps effectif de post-édition.</p>
              <p><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Attention&nbsp;: Si vous quittez
                cette page (en fermant la fenêtre ou en revenant sur la page
                précédente, vous perdrez les modifications apportées).</p>
              </div>
            </div>
            <div class="col-lg-12">
              <?php if (!empty($error_msg)):?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  <?php echo "Erreur: " . $error_msg ?>
                </div>
              <?php endif ?>
              <?php if (!empty($success_msg)):?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  <?php echo $success_msg ?>
                </div>
              <?php endif; ?>
            </div>
            <?php if ($major_err): ?>
              <div class="col-lg-12 text-center">
                <a href="articles.php" class="btn btn-danger" role="button">Retour</a>
              </div>
            <?php endif ?>


            <div class="col-lg-12">
              <?php if (!$major_err): ?>
                <table class="table table-sm ">
                  <thead>
                    <tr>
                      <th scope="col">Titre&nbsp;:</th>
                      <th scope="col"><?php echo $title ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Publié dans&nbsp;:</td>
                      <td><?php echo $venue ?></td>
                    </tr>
                    <tr>
                      <td>Auteurs&nbsp;:</td>
                      <td><?php echo $author_names ?></td>
                    </tr>
                    <tr>
                      <td>Année&nbsp;:</td>
                      <td><?php echo $year ?></td>
                    </tr>
                    <tr>
                      <td>ID Hal&nbsp;:</td>
                      <td><?php echo $hal_id ?></td>
                    </tr>
                  </tbody>
                </table>
              <?php endif; ?>
              <!--<p>Titre de l'article&nbsp;: <?php echo $title ?></p>
              <p>Publié dans&nbsp;: et année: <?php echo $venue . " (" . $year . ")"?></p>-->
            </div>
          </div> <!-- /Row -->
        </div> <!-- /Container -->
      </div> <!-- /pt-2  -->
      <div class="pt-2 mb-2">
        <div class="container px-lg-5">
          <div class="row pt-2">
            <div class="col-lg-12">
              <?php if (!$major_err): ?>
                <form method="post" action="postedit.php">
                  <input type="hidden" name="search_time" value="<?php echo $search_time;?>">

                  <div class="form-group my-3">
                    <label for="orig_text"><span class="font-weight-bold">Résumé d'origine&nbsp;:</span></label>
                    <textarea rows=10 readonly disabled="disabled" class="form-control bg-white" id="orig_text" name="orig_text"><?php echo $title . "\n\n" . $abstract_en ?></textarea>
                  </div>
                  <div class="form-group my-3">
                    <label for="trad_text" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample"><span class="font-weight-bold">Traduction automatique (cliquez pour ouvrir) <i class="fas fa-chevron-down"></i></span></label>
                    <div class="collapse" id="collapseExample">
                      <textarea rows=10 readonly disabled="disabled" class="form-control bg-white" id="trad_text" name="orig_trad"><?php echo $orig_text ?></textarea>
                    </div>
                  </div>
                  <div class="form-group my-3" >
                    <label for="new_postedit_text"><span class="font-weight-bold">Traduction à post-éditer&nbsp;:</span></label>
                    <textarea rows=10 class="form-control <?php echo (!empty($postedit_err)) ? 'is-invalid' : ''; ?>" id="new_postedit_text" name="new_postedit_text" maxlength="5000"><?php echo $postedit_text ?></textarea>
                  </div>
                    <span class="invalid-feedback"><?php echo $postedit_err; ?></span>
                  <!--</div>-->

                  <!-- Severity of errors seen -->
                  <input type="hidden" id="article_id" name="article_id" value="<?php echo $article_id ?>">
                  <input type="hidden" id="translation_id" name="translation_id" value="<?php echo $translation_id ?>">
                  <input type="hidden" id="trans_sys_id" name="trans_sys_id" value="<?php echo $trans_sys_id ?>">
                  Quelle importance donneriez-vous aux problèmes de traduction constatés&nbsp;?
                  <div class="form-group my-3">
                    <div class="form-check">
                      <input class="form-check-input <?php echo (!empty($severity_err)) ? 'is-invalid' : ''; ?>" type="radio" name="new_severity" id="new_severity_aucun" value="aucun" <?php if ($severity == "aucun"):?>checked <?php endif ?>>
                      <label class="form-check-label" for="new_severity_aucun"> Aucun problème</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input <?php echo (!empty($severity_err)) ? 'is-invalid' : ''; ?>" type="radio" name="new_severity" id="new_severity_peu_grave" value="peu grave" <?php if ($severity == "peu grave"):?>checked <?php endif ?>>
                      <label class="form-check-label" for="new_severity_peu_grave"> Peu grave (orthographe, ponctuation, etc.)</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input <?php echo (!empty($severity_err)) ? 'is-invalid' : ''; ?>" type="radio" name="new_severity" id="new_severity_moy_grave" value="moyennement grave" <?php if ($severity == "moyennement grave"):?>checked <?php endif ?>>
                      <label class="form-check-label" for="new_severity_moy_grave"> Moyennement grave (ne gênent pas la compréhension mais linguistiquement ou stylistiquement inacceptables)</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input <?php echo (!empty($severity_err)) ? 'is-invalid' : ''; ?>" type="radio" name="new_severity" id="new_severity_grave" value="grave" <?php if ($severity == "grave"):?>checked <?php endif ?>>
                      <label class="form-check-label" for="new_severity_grave"> Grave (gênent la compréhension, manquent de fidélité au contenu d'origine)</label>
                      <span class="invalid-feedback"><?php echo $severity_err ?></span>
                    </div>
                  </div>

                  <?php if ($expert_user == 1): ?>
                    <!-- Types of errors seen -->
                    Lesquels de ces problèmes étaient présents dans la traduction&nbsp;?

                    <table class="table table-sm mt-2">
                      <thead>
                        <tr>
                          <th scope="col"></th>
                          <th scope="col">Aucun problème</th>
                          <th scope="col">Peu grave</th>
                          <th scope="col">Moyennement grave</th>
                          <th scope="col">Grave</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $invalid = (empty($prob_faithfulness_err)) ? "": "invalid-row";
                        echo "<tr class=\"". $invalid . "\">";
                        echo "  <td class=\"" . $invalid . "\">Fidélité au sens</td>";
                        for ($num=1; $num<=4; $num++) {
                          $checked = ($prob_faithfulness == $num) ? "checked": "";
                          echo "<td class=\"" . $invalid . "\" align=\"center\"><input class=\"form-check-input\" type=\"radio\" " . $checked . " value=\"" . $num . "\" id=\"prob_faithfulness\" name=\"prob_faithfulness\"></td>";
                        }?>
                      </tr>
                      <?php
                      $invalid = (empty($prob_grammar_err)) ? "": "invalid-row";
                      echo "<tr class=\"". $invalid . "\">";
                      echo "  <td class=\"" . $invalid . "\">Grammaire</td>";
                      for ($num=1; $num<=4; $num++) {
                        $checked = ($prob_grammar == $num) ? "checked": "";
                        $invalid = (empty($prob_grammar_err)) ? "": "invalid-row";
                        echo "<td class=\"" . $invalid . "\" align=\"center\"><input class=\"form-check-input\" type=\"radio\" " . $checked . " value=\"" . $num . "\" id=\"prob_grammar\" name=\"prob_grammar\"></td>";
                      }?>
                    </tr>
                    <?php
                    $invalid = (empty($prob_terminology_err)) ? "": "invalid-row";
                    echo "<tr class=\"". $invalid . "\">";
                    echo "  <td class=\"" . $invalid . "\">Terminologie</td>";
                    for ($num=1; $num<=4; $num++) {
                      $checked = ($prob_terminology == $num) ? "checked": "";
                      $invalid = (empty($prob_terminology_err)) ? "": "invalid-row";
                      echo "<td class=\"" . $invalid . "\" align=\"center\"><input class=\"form-check-input\" type=\"radio\" " . $checked . " value=\"" . $num . "\" id=\"prob_terminology\" name=\"prob_terminology\"></td>";
                    }?>
                  </tr>
                  <?php
                  $invalid = (empty($prob_spelling_punctuation_err)) ? "": "invalid-row";
                  echo "<tr class=\"". $invalid . "\">";
                  echo "  <td class=\"" . $invalid . "\">Orthographe et ponctuation</td>";
                  for ($num=1; $num<=4; $num++) {
                    $checked = ($prob_spelling_punctuation == $num) ? "checked": "";
                    $invalid = (empty($prob_spelling_punctuation_err)) ? "": "invalid-row";
                    echo "<td class=\"" . $invalid . "\" align=\"center\"><input class=\"form-check-input\" type=\"radio\" " . $checked . " value=\"" . $num . "\" id=\"prob_spelling_punctuation\" name=\"prob_spelling_punctuation\"></td>";
                  }?>
                </tr>
                <?php
                $invalid = (empty($prob_style_err)) ? "": "invalid-row";
                echo "<tr class=\"". $invalid . "\">";
                echo "  <td class=\"" . $invalid . "\">Style</td>";
                for ($num=1; $num<=4; $num++) {
                  $checked = ($prob_style == $num) ? "checked": "";
                  $invalid = (empty($prob_style_err)) ? "": "invalid-row";
                  echo "<td class=\"" . $invalid . "\" align=\"center\"><input class=\"form-check-input\" type=\"radio\" " . $checked . " value=\"" . $num . "\" id=\"prob_style\" name=\"prob_style\"></td>";
                }?>
              </tr>
              <?php
              $invalid = (empty($prob_document_err)) ? "": "invalid-row";
              echo "<tr class=\"". $invalid . "\">";
              echo "  <td class=\"" . $invalid . "\">Cohérence du document</td>";
              for ($num=1; $num<=4; $num++) {
                $checked = ($prob_document == $num) ? "checked": "";
                $invalid = (empty($prob_document_err)) ? "": "invalid-row";
                echo "<td class=\"" . $invalid . "\" align=\"center\"><input class=\"form-check-input\" type=\"radio\" " . $checked . " value=\"" . $num . "\" id=\"prob_document\" name=\"prob_document\"></td>";
              }?>
            </tr>
          </tbody>
        </table>

      <?php endif; ?>

      <div class="form-group my-3">
        <label for="new_remarques"><span class="font-weight-bold">
          <?php
          if ($expert_user == 1) {
            echo "Autres problèmes et remarques libres (optionnels)&nbsp;:";
          } else {
            echo "Remarques libres (optionnel)&nbsp;:";
          }
          ?></span></label>
          <textarea class="form-control <?php echo (!empty($remarques_err)) ? 'is-invalid' : ''; ?>" id="new_remarques" name="new_remarques" rows="3" maxlength="2000"><?php echo $remarques ?></textarea>
          <span class="invalid-feedback"><?php echo $remarques_err; ?></span>
        </div>
        <input type="submit" class="btn btn-success" name="finalise-text" value="Finaliser" onclick="return confirm('Voulez-vous vraiment finaliser? Vous ne pourrez plus revenir sur cette traduction')" >
        <input type="submit" class="btn btn-danger" name="reinit-text" id="reinit-text" value="Reinitialiser" onclick="return confirm('Attention. Si vous confirmer la réinitialisation, vous perdrez vos modifications')">
        <input type="submit" class="btn btn-link" name="signal-error" id="signal-error" value="Signaler une erreur technique (p. ex: pas de résumé)" onclick="return confirm("Confirmez-vous qu&apos;il y a une erreur qui rend cet exemple inexplotable&nbsp;? Si oui, indiquez la nature de l&apos;erreur dans &quot;Remarques libres&quot;.")>
      </form>
    <?php endif; ?>
  </div>
</div>
</div>
</div>
<!--</div>-->
<!-- Footer-->
<footer class="py-5 bg-secondary">
  <div class="container">
    <p class="m-0 text-center
    text-white">Copyright &copy; ANR MATOS 2023</p>
  </div>
</footer>
<!-- Bootstrap core JS-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Core theme JS-->
<!-- jquery -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/7.1.0/bootstrap-slider.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="js/include_html.js"></script>

<script>includeHTML();
  var rangeSlider = function(){
    var slider = $('.range-slider'),
    range = $('.range-slider__range'),
    value = $('.range-slider__value');

    slider.each(function(){

      value.each(function(){
        var value = $(this).prev().attr('value');
        $(this).html(value);
      });

      range.on('input', function(){
        $(this).next(value).html(this.value);
      });
    });
  };

  rangeSlider();

</script>
</body>
</html>
