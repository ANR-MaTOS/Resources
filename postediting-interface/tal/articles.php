<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: connexion.php");
  exit;
}
$member_token = $_SESSION["token"];

//require_once "../config.php"; // fichier de connexion à la base
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base

$new_keyword = ""; $new_prep_keyword = ""; $table_body = ""; $disabled = "";
$selected_keyword = "";
$kw_passed = "";
$keyword_err = ""; $error_msg = ""; $success_msg = "";

// Fonctions pour vérifier les inputs
function input_string_ok($input) {
  return preg_match('/^[\p{L}\p{N}_\+\/:,\.;\(\)\[\]\!\?\'‘’\"“”\@\&=\-—–0-9\# ]*$/u', $input);
}

date_default_timezone_set('Europe/Paris');


//print_r('begin: ' . $new_keyword . ", " . $selected_keyword . "<br>");

if ($_SESSION['from'] == "finalised"){
  $success_msg .= "Post-edit sauvegardé&nbsp!";
} else if ($_SESSION['from'] == "error-signaled"){
  $success_msg .= "Votre signalement d'erreur a été enregistré&nbsp;!";
}
$_SESSION['from'] = "articles";

// Get id of user
$user = null;
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

// Get any keywords to filter the entries
if (! empty($_POST["new_keyword"])) {
  //print_r('new kw before: ' . $new_keyword . ", " . $selected_keyword . "<br>");
  if ($_POST['search_keyword'] == 'Delete') {
    $selected_keyword = "";
    $_POST["new_keyword"] = "";
    //print_r('deleted: ' . $new_keyword . ", " . $selected_keyword . "<br>");
  }
  //if (input_string_ok($_POST["new_keyword"])) {
  $selected_keyword=trim($_POST["new_keyword"]);
  $new_keyword = trim($_POST["new_keyword"]);
  //$new_prep_keyword = "%".$selected_keyword."%";
  //print_r('new kw after: ' . $new_keyword . ", " . $selected_keyword . "<br>");
  //} else {
  //  $keyword_err .= "Erreur&nbsp;: Caractère(s) invalide(s) dans votre recherche";
  //  $connexion_err .= "Erreur&nbsp;: Caractère(s) invalide(s) dans votre recherche";
  //}
} else if (isset($_GET['new_keyword']) ) {
  $selected_keyword=trim($_GET["new_keyword"]);
  $new_keyword = trim($_GET["new_keyword"]);
  $new_prep_keyword = "%".$selected_keyword."%";
  //print_r('get kw: ' . $new_keyword . ", " . $selected_keyword . "<br>");
  //print_r('prep kw: ' .$new_prep_keyword . "<br>");
}
//$new_keyword = trim($_POST["new_keyword"]);
$new_prep_keyword = "%".$selected_keyword."%";

//print_r("kw = " .$_GET['new_keyword']);

// Random article
$rand_article_id = "";
$trans_sys_id = "";
$sql_rand = "SELECT Article_tal.id, Translation_tal.trans_sys_id
FROM Article_tal INNER JOIN Translation_tal ON Translation_tal.article_id = Article_tal.id
WHERE (Article_tal.id, Translation_tal.trans_sys_id) NOT IN
(SELECT Translation_tal.article_id, Translation_tal.trans_sys_id
  FROM Postedit_tal INNER JOIN Translation_tal on Postedit_tal.translation_id = Translation_tal.id
  WHERE Postedit_tal.finalised = 1 AND user_id = ?)
ORDER BY RAND() LIMIT 1;";
  if ($stmt_random = mysqli_prepare($link, $sql_rand)) {
    mysqli_stmt_bind_param($stmt_random, "s", $user_id);
    if (mysqli_stmt_execute($stmt_random)) {
      mysqli_stmt_store_result($stmt_random);
      mysqli_stmt_bind_result($stmt_random, $rand_article_id, $trans_sys_id);
      mysqli_stmt_fetch($stmt_random);
    } else {
      $error_msg .= "Erreur d'exécution de la requête (rand)";
    }
  } else {
    $error_msg .= "Erreur de préparation de la requête (rand)";
  }

  //$rand_form = "\n";
  $rand_form = "<input type=\"hidden\" name=\"article_id\" value=\"" . $rand_article_id . "\">\n";
  $rand_form .= "<input type=\"hidden\" name=\"trans_sys_id\" value=\"" . $trans_sys_id . "\">\n";
  $rand_form .= "<input type=\"hidden\" name=\"search_time\" value=\"" . date('Y-m-d h:i:s', time()). "\">\n";
  $rand_form .= "Vous pouvez aussi <button type=\"submit\" class=\"btn btn-link mx-0 px-0\">";
  $rand_form .= "choisir un article au hasard&nbsp;!</button>";
  //$rand_form .= "</form>\n";

  $total_num = "";
  // Get number of entries
  $sql = "SELECT COUNT(DISTINCT Article_tal.id)
  FROM Translation_tal INNER JOIN Article_tal on Translation_tal.article_id = Article_tal.id
  WHERE CONCAT(title, author_names, year) LIKE ? AND (article_id, Translation_tal.id) NOT IN
  (SELECT Translation_tal.article_id, Translation_tal.id
    FROM Postedit_tal INNER JOIN Translation_tal on Postedit_tal.translation_id = Translation_tal.id
    WHERE Postedit_tal.finalised = 1 AND user_id = ?);";
    if ($stmt = mysqli_prepare($link, $sql)) {
      mysqli_stmt_bind_param($stmt, "ss", $new_prep_keyword, $user_id);
      if (mysqli_stmt_execute($stmt)) {
        //mysqli_stmt_bind_result($stmt, $total_num);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $total_num);
        mysqli_stmt_fetch($stmt);
      } else {
        $error_msg .= "Erreur d'exécution de la requête (0)";
      }
    } else {
      $error_msg .= "Erreur de préparation de la requête (0)";
    }

    $error_msg = "";
    if (!isset ($_GET['pagenum']) ) {
      $beginpage = 1;
      $pagenum = 1;
    } else {
      $pagenum = $_GET['pagenum'];
      if ($pagenum < 1) {
        $pagenum = 1;
      }
      $beginpage = (intdiv($pagenum - 1, 3) * 3) +1;
    }
    $num_per_page = 50;
    $start = ($num_per_page * ($pagenum - 1));
    $end = $start + $num_per_page;
    $last_page = intdiv($total_num, 50);

    // select all articles for which a postedit can be done
    //$sql = "SELECT id, title, author_names, abstract_en, year, venue from Article ORDER BY id LIMIT ?, ?";
    $sql = "SELECT DISTINCT Article_tal.id, Article_tal.id_hal, Article_tal.title, Article_tal.author_names, Article_tal.abstract_en, Article_tal.year, Article_tal.venue
    FROM Translation_tal INNER JOIN Article_tal on Translation_tal.article_id = Article_tal.id
    WHERE CONCAT(title, author_names, year, Article_tal.id_hal) LIKE ? AND (article_id, Translation_tal.id) NOT IN
    (SELECT Translation_tal.article_id, Translation_tal.id
      FROM Postedit_tal INNER JOIN Translation_tal on Postedit_tal.translation_id = Translation_tal.id
      WHERE Postedit_tal.finalised = 1 AND user_id = ?)
      ORDER BY RAND(?) LIMIT ?, ?";

      if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssss", $new_prep_keyword, $user_id, $user_id, $start, $end);
        if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);
          $numrows = mysqli_stmt_num_rows($stmt);
          if ($numrows >= 1) {
            mysqli_stmt_bind_result($stmt, $id, $id_hal, $title, $author_names, $abstract_en, $year, $venue);
            $table_body = "";
            while (mysqli_stmt_fetch($stmt)) {
              if (empty($selected_keyword) ||
                  preg_match("/^(.*?)(" . $selected_keyword . ")(.*?)$/i", $id_hal, $idhal_kw_match) ||
                  preg_match("/^(.*?)(" . $selected_keyword . ")(.*?)$/i", $title, $title_kw_match) ||
                  preg_match("/^(.*?)(" . $selected_keyword . ")(.*?)$/i", $author_names, $author_kw_match) ||
                  preg_match("/^(.*?)(" . $selected_keyword . ")(.*?)$/i", $year, $year_kw_match)) {

                // get number of postedits already done for this article
                $user_count = 0;
                $sql_count = "SELECT COUNT(*)
                FROM Article_tal INNER JOIN Translation_tal on Translation_tal.article_id = Article_tal.id
                INNER JOIN Postedit_tal on Postedit_tal.translation_id = Translation_tal.id
                WHERE Article_tal.id = ? AND Postedit_tal.finalised = 1 AND Postedit_tal.user_id = ?;";
                if ($stmt_count = mysqli_prepare($link, $sql_count)) {
                  mysqli_stmt_bind_param($stmt_count, "ss", $id, $user_id);
                  if (mysqli_stmt_execute($stmt_count)) {
                    mysqli_stmt_store_result($stmt_count);
                    mysqli_stmt_bind_result($stmt_count, $user_count);
                    mysqli_stmt_fetch($stmt_count);
                  }
                }

                $total_count = 0;
                $sql_count = "SELECT COUNT(*)
                FROM Article_tal INNER JOIN Translation_tal on Translation_tal.article_id = Article_tal.id
                INNER JOIN Postedit_tal on Postedit_tal.translation_id = Translation_tal.id
                INNER JOIN User_tal on User_tal.id = Postedit_tal.user_id
                WHERE Article_tal.id = ? AND Postedit_tal.finalised = 1 AND (User_tal.expert is NULL or User_tal.expert = 0);";
                if ($stmt_count = mysqli_prepare($link, $sql_count)) {
                  mysqli_stmt_bind_param($stmt_count, "s", $id);
                  if (mysqli_stmt_execute($stmt_count)) {
                    mysqli_stmt_store_result($stmt_count);
                    mysqli_stmt_bind_result($stmt_count, $total_count);
                    mysqli_stmt_fetch($stmt_count);
                  }
                }

                // get id of translation system
                $sql_trans = "SELECT Trans_sys.id, Translation_tal.id
                FROM Trans_sys INNER JOIN Translation_tal on Translation_tal.trans_sys_id = Trans_sys.id
                WHERE Translation_tal.article_id = ? and Trans_sys.id NOT IN
                (SELECT Translation_tal.trans_sys_id
                  FROM Postedit_tal INNER JOIN Translation_tal on Postedit_tal.translation_id = Translation_tal.id
                  WHERE user_id = ? and finalised = 1 and article_id = ?)
                  ORDER BY RAND() LIMIT 1;";
                  if ($stmt_trans = mysqli_prepare($link, $sql_trans)) {
                    mysqli_stmt_bind_param($stmt_trans, "sss", $id, $user_id, $id);
                    if (mysqli_stmt_execute($stmt_trans)) {
                      mysqli_stmt_store_result($stmt_trans);
                      if (mysqli_stmt_num_rows($stmt_trans) == 1) {
                        mysqli_stmt_bind_result($stmt_trans, $tmp_trans_sys_id, $translation_id);
                        if (mysqli_stmt_fetch($stmt_trans)) {
                          $trans_sys_id = $tmp_trans_sys_id;
                        } else {
                          $major_err = 1;
                          $error_msg .= "Récupération du système de traduction (3). ";
                        }
                      } else {
                        $major_err = 1;
                        $error_msg .= "Récupération du système de traduction(2). ";
                      }
                    } else {
                      $major_err = 1;
                      $error_msg .= "Récupération du système de traduction (1). ";
                    }
                  } else {
                    $major_err = 1;
                    $error_msg .= "Récupération du système de traduction (0). ";
                  } // end prepare

                $table_body .= "<tr><td>";
                $table_body .= "<form method=\"post\" action=\"postedit.php\">\n";
                $table_body .= "<input type=\"hidden\" name=\"article_id\" value=\"" . $id . "\">\n";
                $table_body .= "<input type=\"hidden\" name=\"trans_sys_id\" value=\"" . $trans_sys_id . "\">\n";
                $table_body .= "<input type=\"hidden\" name=\"translation_id\" value=\"" . $translation_id . "\">\n";
                $table_body .= "<input type=\"hidden\" name=\"search_time\" value=\"" . date('Y-m-d h:i:s', time()). "\">\n";
                //$table_body .= "<input type=\"submit\" class=\"btn btn-link btn-sm\" value=\"<i class=\"fas fa-plus-square\"></i>\"></td><td>";
                $table_body .= "<button type=\"submit\" class=\"btn btn-link\">";
                $table_body .= "<i class=\"fa fa-pencil\"></i></td>";
                $table_body .= "</form>\n";

                $table_body .= "<td>$id_hal</td>";
                if (empty($title_kw_match)) {
                  $table_body .= "<td>$title</td>";
                } else {
                  $table_body .= "<td>$title_kw_match[1]<mark>$title_kw_match[2]</mark>$title_kw_match[3]</td>";
                }
                if (empty($author_kw_match)) {
                  $table_body .= "<td>" . $author_names . "</td>";
                } else {
                  $table_body .= "<td>$author_kw_match[1]<mark>$author_kw_match[2]</mark>$author_kw_match[3]</td>";
                }
                if (empty($year_kw_match)) {
                  $table_body .= "<td>" . $year . "</td><td>". $venue . "</td>";
                } else {
                  $table_body .= "<td>$year_kw_match[1]<mark>$year_kw_match[2]</mark>$year_kw_match[3]</td><td>". $venue . "</td>";
                }
                $table_body .= "<td>".$user_count."</td><td>".$total_count."</td>";
                $table_body .= "</tr>";
              }
            }
          } else {
            $error_msg .= "Empty entries. ";
          }
        } else {
          $error_msg .= "Execution error. ";
        }
      } else {
        $error_msg .= "Preparation error. ";
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

        <!--link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css" integrity="sha384-QYIZto+st3yW+o8+5OHfT6S482Zsvz2WfOzpFSXMF9zqeLcFV0/wlZpMtyFcZALm" crossorigin="anonymous">-->
        <link href="matos.css" rel="stylesheet" type="text/css">
        <title>MATOS - choisir un article</title>
      </head>
      <body>
        <!-- Responsive navbar-->
        <div w3-include-html="menu.php"></div>
        <!-- Header-->
        <header class="pt-0 bg-light">
          <div class="container w-100 align-items-center">
            <div class="p-1 rounded-3 text-center">
              <div class="m-2 m-lg-5">
                <h3 class="display-7 fw-bold">Choisir un article à post-éditer</h3>
              </div>
            </div>
          </div>
        </header>
        <div class="features-icons bg-light text-justify">
          <div class="container">
            <div class="row pt-2 pb-2">
              <div class="col-lg-12">
                <?php if (!empty($error_msg)):?>
                  <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <a href="#" class="btn-close" data-dismiss="alert" aria-label="close"></a>
                    <?php echo $error_msg ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($success_msg)):?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <a href="#" class="btn-close" data-dismiss="alert" aria-label="close"></a>
                    <?php echo $success_msg ?>
                  </div>
                <?php endif; ?>

                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    Sélectionnez un article du tableau ci-dessous
                    et cliquez sur <i class="fa fa-pencil"></i> pour faire une nouvelle post-édition.
                    Affinez le choix en cherchant un mot clé, nom d'auteur, année ou identifiant HAL afin de privilégier vos propres
                    articles ou les articles sur certains thèmes&nbsp;:
                    <input autocomplete="off" value="<?php if (! empty($new_keyword)) { echo $new_keyword; }?>"  id="langue" name="new_keyword" type="text" placeholder="Mot clé, nom, année ou ID HAL" data-items="8" class="input form-control input_keyword <?php echo (! input_string_ok($new_keyword)) ? 'is-invalid' : ''; ?>">
                      <button type="submit" name="search_keyword" class="btn btn-link px-1" value="Search"><i class="fa fa-arrow-circle-right"></i></button>
                      <button type="submit" name="search_keyword" class="btn btn-link px-1" value="Delete">
                        <i class="fa fa-times-circle"></i>
                      </button>
                      <span class="invalid-feedback"><?php echo $keyword_err; ?></span>
                    </form>
                    <br>
                    <p>Vous pouvez choisir le même article plusieurs fois - une traduction différente
                      sera proposée. Le nombre de post-éditions que <span class="font-italic">vous</span>
                      avez effectuées pour un article donné
                      est indiqué dans la colonne <i class="fa fa-user"></i>. Le nombre total de
                      post-éditions, tout utilisateur confondu, est dans la colonne <i class="fa fa-users"></i>.
                      <?php if ($expert_user == 1) {
                        echo "N.B.&nbsp;Ce total inclut seulement les post-éditions des
                        utiliseurs de la communauté TAL et non pas les spécialistes de traduction.";
                      }
                      ?>
                    </p>

                      <form method="post" action="postedit.php" style="margin: 0; padding: 0;">
                        <?php echo $rand_form ?>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="features-icons bg-light text-center">
                <div class="container">
                  <div class="row pt-0 pb-0">
                    <div class="col-lg-12">
                      <nav aria-label="Page navigation example">
                        <ul class="pagination pt-3">
                          <?php
                          if ($beginpage == 1) {
                            $disabled = "disabled";
                          } else {
                            $disabled = "";
                          }
                          $kw_passed = "";
                          if (! empty($new_keyword)) {
                            $kw_passed = "&new_keyword=" . urlencode($new_keyword);
                          }
                          echo "<li class=\"page-item $disabled\">";
                          echo "  <a class=\"page-link\" href=\"articles.php?pagenum=1".$kw_passed."\" tabindex=\"0\"><i class=\"fa fa-fast-backward\"></i></a>";
                          echo "</li>";
                          echo "<li class=\"page-item $disabled\">";

                          echo "  <a class=\"page-link\" href=\"articles.php?pagenum=" . $beginpage-1 .$kw_passed."\" tabindex=\"-1\"><i class=\"fa fa-step-backward\"></i></a>";
                          echo "</li>";
                          if ($beginpage == $pagenum){
                            echo "<li class=\"page-item\"><a class=\"page-link\" active href=\"articles.php?pagenum=" . $beginpage . $kw_passed."\">" . $beginpage . "</a></li>";
                          } else {
                            echo "<li class=\"page-item\"><a class=\"page-link\" href=\"articles.php?pagenum=" . $beginpage .$kw_passed."\">" . $beginpage . "</a></li>";
                          }
                          if ($last_page <= $beginpage) {
                            $disabled = "disabled";
                            $pagenum_text = "-";
                          } else {
                            $disabled = "";
                            $pagenum_text = $beginpage+1;
                          }
                          if ($beginpage == $pagenum + 1){
                            echo "<li class=\"page-item " . $disabled . "\"><a class=\"page-link\" active href=\"articles.php?pagenum=" . $beginpage+1 . $kw_passed."\">" . $pagenum_text . "</a></li>";
                          } else {
                            echo "<li class=\"page-item " . $disabled . "\"><a class=\"page-link\" href=\"articles.php?pagenum=" . $beginpage+1 .$kw_passed."\">" . $pagenum_text . "</a></li>";
                          }
                          if ($last_page <= $beginpage) {
                            $disabled = "disabled";
                            $pagenum_text = "-";
                          } else {
                            $disabled = "";
                            $pagenum_text = $beginpage+2;
                          }
                          if ($beginpage == $pagenum + 2){
                            echo "<li class=\"page-item ". $disabled . "\"><a class=\"page-link\" active href=\"articles.php?pagenum=" . $beginpage+2 .$kw_passed."\">" . $pagenum_text . "</a></li>";
                          } else {
                            echo "<li class=\"page-item " . $disabled . "\"><a class=\"page-link\" href=\"articles.php?pagenum=" . $beginpage+2 . $kw_passed. "\">" . $pagenum_text . "</a></li>";
                          }
                          if ($last_page <= $beginpage+1) {
                            $disabled = "disabled";
                          } else {
                            $disabled = "";
                          }
                          echo "<li class=\"page-item " . $disabled . "\">";
                          echo "<a class=\"page-link\" href=\"articles.php?pagenum=" . $beginpage+3 .$kw_passed."\"><i class=\"fa fa-step-forward\"></i></a>";
                          echo "</li>";
                          echo "<li class=\"page-item " . $disabled . "\">";
                          echo "<a class=\"page-link\" href=\"articles.php?pagenum=" . $last_page .$kw_passed. "\"><i class=\"fa fa-fast-forward\"></i></a>";
                          echo "</li>";
                          ?>
                        </ul>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>

              <div class="features-icons bg-light text-center">
                <div class="container">
                  <div class="row pt-1 pb-5">
                    <div class="col-lg-12">
                      <table class="table table-sm text-start">
                        <thead class='table-dark'>
                          <tr>
                            <th scope="col"></th>
                            <th scope="col">HAL id</th>
                            <th scope="col">Titre</th>
                            <th scope="col">Auteurs</th>
                            <th scope="col">Année</th>
                            <th scope="col">Lieu</th>
                            <th scope="col"><i class="fa fa-user"></i></th>
                            <th scope="col"><i class="fa fa-users"></i></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php echo $table_body ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Footer-->
              <footer class="py-5 bg-secondary">
                <div class="container">
                  <p class="m-0 text-center
                  text-white">Copyright &copy; ANR MATOS 2023</p>
                </div>
              </footer>
              <!-- jquery -->
              <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
              <!-- Bootstrap core JS-->
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
