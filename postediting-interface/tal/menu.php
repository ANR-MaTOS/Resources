<?php
session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
   <div class="container-fluid px-lg-5">
      <a class="navbar-brand" href="index.php">MATOS post-&eacute;dition</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php">Accueil</a></li>
            <li class="nav-item"><a class="nav-link"
               href="about.php">À propos</a></li>
            <li class="nav-item"><a class="nav-link"
               href="contact.php">Contact</a></li>
            <li class="nav-item"><a class="nav-link" href="articles.php">Post-édition</a></li>
            <li class="nav-item"><a class="nav-link" href="inscription.php">Inscription</a></li>
            <?php
            if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || empty($_SESSION["token"])){
              echo "    <li class=\"nav-item\">\n";
              echo "    <a class=\"nav-link\" href=\"connexion.php\"><i class=\"fa fa-user-circle\"></i> Connexion</a>\n";
            } else {
              $member_token = $_SESSION["token"];
              $trunc_tok = substr($member_token, 0, 10);
              echo "    <li class=\"nav-item dropdown\">\n";
              echo "    <a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"navbarDropdownPortfolio\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\"><i class=\"fa fa-user-circle\"></i> <b>$trunc_tok...</b>\n</a>\n";
              echo "    <div class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"navbarDropdownPortfolio\">\n";
              echo "      <a class=\"dropdown-item\" href=\"compte.php\">Mon compte</a>\n";
              echo "      <a class=\"dropdown-item\" href=\"deconnexion.php\">Déconnexion</a>\n";
              echo "    </div>\n";
            }
            echo "</li>";
                  ?>
                </ul>
              </div>
              </div>
              </nav>
