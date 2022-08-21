<?php

session_start();
$pdo = require_once __DIR__ . '/../../models/database.php';

const ERROR_REQUIRED = "Veuillez renseigner ce champ";
const ERROR_LENGTH = "Le champ doit faire entre 2 et 10 caractères";
const ERROR_EMAIL = "L'email n'est pas valide";
$error = null;
$errors = [
    'username' => null,
    'email' => null,
    'password' => null
];
$email = null;
$password = null;
$username = null;
$isnew = null;
# Récupérer les saisies dans la variable _POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST = filter_input_array(INPUT_POST, [
        'password' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'email' => FILTER_SANITIZE_EMAIL,
        'username' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
    ]);

    # Récupérer les valeurs épurées – vide si non renseignées:
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $isnew = (isset($_POST['username'])) ? 1 : 0;
    $username = $_POST['username'] ?? '';

    # Mise en place des tests
    if (!$email) {
        $errors['email'] = ERROR_REQUIRED;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ERROR_EMAIL;
    }
    if (!$password) {
        $errors['password'] = ERROR_REQUIRED;
    }
    if ($isnew && !$username) {
        $errors['username'] = ERROR_REQUIRED;
    }

    # se connecter
    if (!$isnew) {
        if ($password && $email) {
            # Tester l'existence
            $statementUser = $pdo->prepare('SELECT id FROM user WHERE email = ?');
            $statementUser->execute([$email]);
            $id = $statementUser->fetch();
            if (!$id) {
                $error = 'Erreur : il n\'y a aucun compte actif associé à cette adresse email.';
            } else {
                $statementUser = $pdo->prepare('SELECT * FROM user WHERE email = ?');
                $statementUser->execute([$email]);
                $user = $statementUser->fetch();
                if (password_verify($password, $user['password'])) {
                    $statementSession = $pdo->prepare('INSERT INTO session VALUES(DEFAULT, :userid)');
                    $statementSession->bindValue(':userid', $user['id']);
                    $statementSession->execute();
                    $sessionId = $pdo->lastInsertId();
                    setcookie('session', $sessionId, time() + 60 * 60 * 24 * 14, "", "", false, true);
                    $_SESSION['username'] = $user['username'];
                    header('Location: \index.php');
                } else {
                    $error = 'Mot de passe invalide';
                }
            }
        }
    }


    # s'inscrire
    else {
        if ($isnew && $password && $email && $username) {

            # Tester l'existence
            $statementUser = $pdo->prepare('SELECT id FROM user WHERE email = ?');
            $statementUser->execute([$email]);
            $id = $statementUser->fetch();
            if ($id) {
                $error = 'Cet E-mail est déja utilisé';
            }
            # Creer'
            else {
                $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
                $statement = $pdo->prepare('INSERT INTO user VALUES (
DEFAULT,
:email,
:username,
:password
)');

                $statement->bindValue(':email', $email);
                $statement->bindValue(':username', $username);
                $statement->bindValue(':password', $hashedPassword);
                $statement->execute();
                $isnew = 0;
            }
        }
    }


    # envoyer un mail
    /**$dest = "clotilde.cherqui@gmail.com";
$objet = "Rendez-vous";
$message = "
<font face='arial'>
    Bonjourn
    Prière de se retrouver sur Skype à <b>18h</b> aujourd'hui.n
    Merci et bonne journée.
</font>
";
$entetes = "From: clotilde.cherqui@gmail.comn";
$entetes .= "Cc: chiny@example.comn";
$entetes .= "Content-Type: text/html; charset=iso-8859-1";

if (mail($dest, $objet, $message, $entetes))
echo "Mail envoyé avec succès.";
else
echo "Un problème est survenu.";
exit;**/
}
