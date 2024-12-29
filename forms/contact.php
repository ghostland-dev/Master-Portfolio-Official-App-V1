<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

// SMTP Configuration
define('SMTP_HOST', '-');
define('SMTP_USERNAME', '-');
define('SMTP_PASSWORD', '-'); // Ensure this is accurate
define('SMTP_PORT', 465); // Using port 587 for STARTTLS
define('SMTP_FROM_EMAIL', '-');
define('SMTP_FROM_NAME', 'MOUYAHADA Ismail');
define('SMTP_TO_EMAIL', 'ismail.mouyahada@gmail.com');

 
// Function to sanitize input
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $subject = cleanInput($_POST['subject'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');

    // Validation
    $errors = [];
    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Le nom est requis et ne doit pas dépasser 100 caractères.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Un email valide est requis.";
    }
    if (empty($subject) || strlen($subject) > 150) {
        $errors[] = "Le sujet est requis et ne doit pas dépasser 150 caractères.";
    }
    if (empty($message) || strlen($message) > 1000) {
        $errors[] = "Le message est requis et ne doit pas dépasser 1000 caractères.";
    }

    // Process email if no errors
    if (empty($errors)) {
        $mail = new PHPMailer(true);  // Exception handling enabled
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS for port 587
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            // Enable SMTP debugging for error details
            $mail->SMTPDebug = 2; // More detailed debug output

            // Sender and recipient
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress(SMTP_TO_EMAIL);  // Send to your email
            $mail->addReplyTo($email, $name);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "Nouveau message de contact: " . $subject;
            $mail->Body = "
                <h2>Nouveau message via le formulaire du site</h2>
                <p><strong>Nom:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Sujet:</strong> {$subject}</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br($message) . "</p>
            ";
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $message));

            // Send the email
            if ($mail->send()) {
                $response = [
                    "status" => "success",
                    "message" => "Votre message a été envoyé avec succès !"
                ];
            }
        } catch (Exception $e) {
            $response = [
                "status" => "error",
                "message" => "Une erreur est survenue lors de l'envoi : " . $e->getMessage()
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => implode(", ", $errors)
        ];
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
