<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Inclure l'autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';


// Configuration SMTP
define('SMTP_HOST', 'mail.privateemail.com');
define('SMTP_USERNAME', 'contact@ismail-mouyahada.com');
define('SMTP_PASSWORD', 'dummy-smtp-password');
define('SMTP_PORT', 465);
define('SMTP_FROM_EMAIL', 'contact@ismail-mouyahada.com'); // Changé pour correspondre au SMTP_USERNAME
define('SMTP_FROM_NAME', 'MOUYAHADA Ismail');
define('SMTP_TO_EMAIL', 'ismail.mouyahada@gmail.com');

// Fonction pour nettoyer les données
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);
    
    // Validation des champs
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Le nom est requis";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }
    
    if (empty($subject)) {
        $errors[] = "Le sujet est requis";
    }
    
    if (empty($message)) {
        $errors[] = "Le message est requis";
    }
    
    // Si pas d'erreurs, envoyer l'email
    if (empty($errors)) {
        $mail = new PHPMailer(true);
        
        try {
            // Configuration du serveur
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = "tls"; // Changé pour SSL au lieu de TLS
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            // Debug mode si nécessaire (à commenter en production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;

            // Expéditeur et destinataires
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress(SMTP_TO_EMAIL); // Envoyer à votre adresse principale
            $mail->addReplyTo($email, $name);

            // Contenu de l'email
            $mail->isHTML(true);
            $mail->Subject = "Nouveau message de contact: " . $subject;
            
            // Corps du message en HTML
            $messageBody = "
            <h2>Nouveau message de contact via le formulaire du site</h2>
            <p><strong>Nom:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Sujet:</strong> {$subject}</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ";

            $mail->Body = $messageBody;
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $messageBody));

            if ($mail->send()) {
                // Email envoyé avec succès
                $response = array(
                    "status" => "success",
                    "message" => "Votre message a été envoyé. Merci !"
                );
            } else {
                throw new Exception($mail->ErrorInfo);
            }

        } catch (Exception $e) {
            $response = array(
                "status" => "error",
                "message" => "Une erreur est survenue lors de l'envoi : " . $e->getMessage()
            );
        }
    } else {
        // Erreurs de validation
        $response = array(
            "status" => "error",
            "message" => implode(", ", $errors)
        );
    }
    
    // Retourner la réponse en JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>