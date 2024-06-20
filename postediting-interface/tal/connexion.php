<?php
session_start();

$member_token = "";
if (!empty($_SESSION["token"])) {
  $member_token = $_SESSION["token"];
}
$member_token_err = ""; $error_msg = ""; $success_msg = "";
$new_member_token = "";

if (!empty($_SESSION['from'])) {
  if ($_SESSION['from'] == 'inscription') {
    $token_connnection = htmlspecialchars($_SESSION['token_connect']);
    $success_msg .= "Vous êtes inscrit(e)s&nbsp;! Votre jeton de connexion est&nbsp;:<br>" . $token_connnection . "<br>Ne le perdez pas&nbsp;
    vous en aurez besoin chaque fois que vous vous connectez. Il vous a aussi
    été envoyé par mail (vérifiez dans votre boîte de spams).";
  }
}

//require_once "../config.php"; // fichier de connexion à la base
require_once "/home/daqasno/postedition/config.php"; // fichier de connexion à la base

if (! empty($_POST["member_token"])) {
  $new_member_token = $_POST["member_token"];
  if (empty($new_member_token)) {
    $error_msg .= "Jeton vide. Veuillez entrer votre jeton de connexion.";
    $member_token_err .= "Ce jeton est vide.";
  } else {
  // Check that the token exists
  $sql = "SELECT * from User_tal where CONCAT(token, id) = ?;";
  if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $new_member_token);
    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_store_result($stmt);
      if (mysqli_stmt_num_rows($stmt) == 0) {
        $member_token_err .= "Ce jeton n'est pas reconnu.";
        $error_msg .= "Jeton non reconnu. Veuillez vérifier le mail qui vous a été envoyé au moment de votre inscription.";
      } else {
        session_start();
	      // Store data in session variables
	      $_SESSION["loggedin"] = true;
	      $_SESSION["token"] = $new_member_token;
        $_SESSION['from'] = "connection";
//	      header("location: /accueil_membre.php");
	      header("location: articles.php");
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
    <title>MATOS - connexion</title>
 </head>
 <body>
    <!-- Responsive navbar-->
    <div w3-include-html="menu.php"></div>
    <!-- Header-->
    <header class="pt-0 bg-light">
       <div class="container w-100 align-items-center">
          <div class="p-1 rounded-3 text-center">
             <div class="m-2 m-lg-5">
                <h2 class="display-7 fw-bold">Connexion</h3>
             </div>
          </div>
       </div>
    </header>
    <div class="features-icons bg-light text-center">
       <div class="container">
          <div class="row pt-0 pb-1">
             <div class="col-8 mx-auto">
               <?php if (!empty($error_msg)):?>
                 <div class="alert alert-warning" role="alert">
                   <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                   <?php echo $error_msg ?>
                 </div>
               <?php endif; ?>
               <?php if (!empty($success_msg)):?>
                 <div class="alert alert-success" role="alert">
                   <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                   <?php echo $success_msg ?>
                 </div>
               <?php endif; ?>
              </div>
              <div class="col-8 mx-auto">

                  <!--<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">-->
                  <form class="px-md-5 mt-1" method="post">
                      <div class="form-group my-3">
                        <label>Entrez votre jeton de connexion ici&nbsp;:</label>
                        <input type="username" size="65" maxlength="65" autocomplete="username" name="member_token" class="form-control <?php echo (!empty($member_token_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_member_token; ?>"/>
                        <span class="invalid-feedback"><?php echo $member_token_err; ?></span>
                      </div>
                      <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Connectez-vous">
                      </div>
                  </form>
                  <p>Pas encore inscrit(e)&nbsp;? Inscrivez-vous et générez votre jeton de connexion <a href ="inscription.php">ici</a>.</p>
             </div>
          </div>
       </div>
    </div>
    <!-- Footer-->
    <footer class="py-5 bg-secondary mt-auto">
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
