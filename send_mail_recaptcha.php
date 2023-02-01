<?php
    ini_set('display_errors', 1);
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    // Store File with Email Address, Password, and Secret Key outside Public Directory
    // require_once '../mail_settings.php';

    require("src/PHPmailer.php");
    require("src/Exception.php");
    require("src/SMTP.php");

    $errors = '';
    
    if (empty($_POST['name'])) {
        // Do Nothing if POST Request is Empty

    } else {
        $name = $_POST['name']; 
        $email_address = $_POST['email']; 
        $subject = $_POST['subject']; 
        $message = $_POST['message']; 
        $recaptchaResponse = $_POST['g-recaptcha-response'];

        // Verify Valid Email Address
        if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email_address)){
            $errors .= "\n Error: Invalid email address";
        }

        $recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$recaptchaResponse}";
        $verify = json_decode(file_get_contents($recaptchaUrl));

        // Verify reCAPTCHA success
        if (!$verify->success) {
            $errors .= "\n Recaptcha failed";
        }

        if(empty($errors)) {
            // PHP MAIL
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_OFF; //SMTP::DEBUG_SERVER
                $mail->CharSet = "utf-8";                   
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = $from_email;                            //SMTP username
                $mail->Password   = $password;                              //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
                $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            
                // Recipients
                $mail->setFrom($from_email, 'Web Contact');
                $mail->addAddress($to_email);  
                $mail->addReplyTo($email_address);
                    
                // Email Content
                $mail->isHTML(true);                                        
                $mail->Subject = 'Website Notification: ' . $subject;
                $mail->Body    = 'Website Message:<br><br><b>From</b>: <span style="text-transform: uppercase;">' . $name . '</span><br><b>Email</b>: ' . $email_address . '<br><br>The contents of the message is as follows:<br><p style="padding: 1rem 0; border-top: 1px solid #000000; border-bottom: 1px solid #000000;">' . $message . '</p><br>This is an automated message.';
                $mail->AltBody = 'Website Message: From: ' . $name . '. Email: ' . $email_address . '. The contents of the message is as follows:' . $message . ' This is an automated message.';
            
                $mail->send(); 
                echo "Message successful";
                
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
            // Re-direct URL
            header('Location: thank-you#notification');
            
        } else {
            echo $errors;
            // header('Location: auth-failure#notification');
        }
    }
    
?>