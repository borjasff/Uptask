<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {

    //login
    public static function login (Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)){
                //verificar el usuario existe
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->confirmado ){
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                } else {
                    //usuario existe
                    if(password_verify($_POST['password'], $usuario->password)){
                        //nos retorna true o false

                        //iniciar sesión
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        //para permitir acceder al usuario a paginas ocultas si no inicia sesión
                        $_SESSION['login'] = true;

                        //redireccionar
                       header('Location: /dashboard');
                    }else{
                        Usuario::setAlerta('error', 'Password incorrecto');
                    }
                }
            }
        }
        $alertas = Usuario::getAlertas();

        //render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesion',
            'alertas' => $alertas
        ]);
    }

    //logout
    public static function logout (){
        session_start();

        $_SESSION = [];

        header('Location: /');

    }

    //crear
    public static function crear (Router $router){
        $alertas = [];
        //INSTANCIAMOS EL USUARIO
        $usuario = new Usuario;
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //comprobamos los datos del usuario y validamos la cuenta para crearla
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if(empty($alertas)){
                //revisamos una columna concreta para verificar si ya existe ese correo
                $existeUsuario = Usuario::where('email', $usuario->email);

                //si existe...
                if($existeUsuario){
                    Usuario::setAlerta('error','El Usuario ya está registrado');
                    $alertas = Usuario::getAlertas();

            }else{
                //hashear el password
                $usuario->hashPassword();

                //eliminar password2
                unset($usuario->password2);

                //generar token
                $usuario->crearToken();

                //creamos un nuevo usuario
                $resultado = $usuario->guardar();

                //Enviar email
                $email = new Email($usuario->email, $usuario->nombre, $usuario->token );
                
                $email->enviarConfirmacion();

                if($resultado){
                    header('Location: /mensaje');
                }
            }
        }
        }

        //render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear tu Cuenta en Uptask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    //olvide
    public static function olvide(Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)){
                //buscamos el usuario
                $usuario = Usuario::where('email', $usuario->email);
                if($usuario && $usuario-> confirmado){
                    //usuario encontrado
                    //generar nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //actualizar el usuario
                    $usuario->guardar();
                    //enviar el email
                    $email = new Email( $usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    //imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                }
                else{
                    //añadimos el error
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                }
            }
        }
        //llamamos la alertas de exito o error
        $alertas = Usuario::getAlertas();

        //render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);
    }

    //restablecer
    public static function reestablecer (Router $router){
        //generamos el token
        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) header('Location: /');

        //indentificar el usuario con este token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token No válido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            //añadir un nuevo password
            $usuario->sincronizar($_POST);

            //validar el PASSWORD
            $alertas = $usuario->validarPassword();

            if(empty($alertas)){
                //hassear password
                $usuario->hashPassword();
                
                //eliminar token
                $usuario->token = null;
                //guardar usuario en la bd
                $resultado = $usuario -> guardar();
                //redireccionar
                if($resultado) header('Location: /');
            }

        }
        //para obtener todas las alertas
        $alertas = Usuario::getAlertas();
        //render a la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Recuperar tu Cuenta en Uptask',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }
    //confirmacion de cuenta
    public static function mensaje (Router $router){
        //render a la vista
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada en Uptask'
        ]);
    }

    //confirmacion de cuenta
    public static function confirmar (Router $router){

        $token = s($_GET['token']);

        if(!$token) header('location: /');

        //encontrar al usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            //si no tiene token o no es valido
            Usuario::setAlerta('error', 'Token No Válido');

        } else{
            //confirmar la cuenta y guardar en la base de datos
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);

            //guardar en la bd
            $usuario->guardar();

            //si todo funciona mostrar
            Usuario::setAlerta('exito', 'Cuenta Verificada Correctamente');

        }

        $alertas = Usuario::getAlertas();

        //render a la vista
        $router->render('auth/confirmar', [
            'titulo' => 'Cuenta verificada en Uptask',
            'alertas' => $alertas
        ]);
    }

}