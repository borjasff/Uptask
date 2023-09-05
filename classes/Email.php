<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    protected $email;
    protected $nombre;
    protected $token;

    //instanciamos el email
    public function __construct($email, $nombre, $token){
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '070da425fe3bef';
        $mail->Password = '792333bd251baf';

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress('cuentas@uptask.com', 'uptask.com');
        $mail->Subject = 'Confirma tu Cuenta';

        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has Creado tu cuenta en Uptask, solo debes confirmarla en el siguiente enlace</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['APP_URL'] . "/confirmar?token=". $this->token ."'>Confirmar Cuenta</a></p>";
        $contenido .= "<p>Si tu no creaste esta cuenta, puedes ignorar este mensaje</p>";
        $contenido .= '<html>';


        $mail->Body = $contenido;

        //enviar el email
        $mail->send();
    }

    public function enviarInstrucciones(){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress('cuentas@uptask.com', 'uptask.com');
        $mail->Subject = 'Restablecer Password';

        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Parece que has olvidado tu password, accede al siguiente enlace para recuperarlo</p>";
        $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['APP_URL']. "/reestablecer?token=". $this->token ."'>Restablecer Password</a></p>";
        $contenido .= "<p>Si tu no creaste esta cuenta, puedes ignorar este mensaje</p>";
        $contenido .= '<html>';


        $mail->Body = $contenido;

        //enviar el email
        $mail->send();
    }
}