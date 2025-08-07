<?php
require_once 'auth.php';
// Fonction de nettoyage des entrées utilisateur
function sanitize_input($data) {
    $data = trim($data); // Supprime les espaces en début et fin
    $data = stripslashes($data); // Supprime les antislashs
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Protège contre les XSS
    return $data;
}
// Rediriger si déjà connecté
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = sanitize_input($_POST['nom']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        if (register_user($nom, $email, $password)) {
            // Envoi d'une notification à l'admin
            $to = 'ralphonsehaja@gmail.com';
            $subject = 'Nouvelle inscription sur la Bibliothèque';
            $message = "Un nouvel utilisateur vient de s'inscrire :\nNom : $nom\nEmail : $email";
            $headers = "From: noreply@bibliotheque.com\r\n";
            mail($to, $subject, $message, $headers);

            $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            header('refresh:2;url=login.php');
        } else {
            $error = 'Cette adresse email est déjà utilisée';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.5rem;
            color: #95a5a6;
            transition: color 0.3s ease;
        }

        .form-group input:focus + i {
            color: #667eea;
        }

        .form-group small {
            display: block;
            margin-top: 0.5rem;
            color: #7f8c8d;
            font-size: 0.8rem;
            font-style: italic;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e1e8ed;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            background: linear-gradient(90deg, #e74c3c, #f39c12, #27ae60);
            width: 0%;
            transition: width 0.3s ease;
        }

        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background: #fee;
            color: #e74c3c;
            border: 1px solid #fadbd8;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .register-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e1e8ed;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
            color: #764ba2;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .btn-back:hover {
            background: rgba(102, 126, 234, 0.2);
            color: #764ba2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .btn-back i {
            font-size: 0.9rem;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 40%;
            right: 20%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-card {
                padding: 2rem;
                margin: 1rem;
            }

            .register-header h1 {
                font-size: 1.5rem;
            }

            .register-header .logo {
                font-size: 2.5rem;
            }

            .form-group input {
                padding: 0.875rem 0.875rem 0.875rem 2.5rem;
            }

            .form-group i {
                top: 2.25rem;
                left: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 10px;
            }

            .register-card {
                padding: 1.5rem;
            }

            .register-header h1 {
                font-size: 1.25rem;
            }

            .register-header .logo {
                font-size: 2rem;
            }
        }

        /* Animation pour les champs de mot de passe */
        .password-match {
            border-color: #27ae60 !important;
            background: #f8fff9 !important;
        }

        .password-mismatch {
            border-color: #e74c3c !important;
            background: #fff8f8 !important;
        }

        .password-match + i {
            color: #27ae60 !important;
        }

        .password-mismatch + i {
            color: #e74c3c !important;
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo">📚</div>
                <h1>Créer un compte</h1>
                <p>Rejoignez notre communauté de lecteurs</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom" required 
                           value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                           placeholder="Entrez votre nom complet">
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="Entrez votre email">
                    <i class="fas fa-envelope"></i>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required minlength="6"
                           placeholder="Minimum 6 caractères">
                    <i class="fas fa-lock"></i>
                    <small>Le mot de passe doit contenir au moins 6 caractères</small>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Confirmez votre mot de passe">
                    <i class="fas fa-lock"></i>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </form>
            
            <div class="register-footer">
                <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
                <div style="margin-top: 1rem;">
                    <a href="login.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validation en temps réel des mots de passe
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('strengthBar');

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength += 25;
            if (password.match(/[a-z]/)) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            return strength;
        }

        function checkPasswordMatch() {
            if (confirmPassword.value === '') {
                confirmPassword.classList.remove('password-match', 'password-mismatch');
                return;
            }
            
            if (password.value === confirmPassword.value) {
                confirmPassword.classList.remove('password-mismatch');
                confirmPassword.classList.add('password-match');
            } else {
                confirmPassword.classList.remove('password-match');
                confirmPassword.classList.add('password-mismatch');
            }
        }

        password.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            strengthBar.style.width = strength + '%';
        });

        confirmPassword.addEventListener('input', checkPasswordMatch);
        password.addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>