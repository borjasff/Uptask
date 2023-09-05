<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id','nombre', 'email', 'password', 'token', 'confirmado'];

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password2'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;

    } 

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2;
    public $password_actual;
    public $password_nuevo;
    public $token;
    public $confirmado;

    //validar el login de usuarios
    public function validarLogin(){
        if(!$this->email){
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        // SI NO PASA EMAIL SERÁ NO VÁLIDO
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][] = 'Email no válido';
        }
        if(!$this->password){
            self::$alertas['error'][] = 'El Password es obligatorio';
        }
        return self::$alertas;
    }

    //validar para cuentas nuevas
    public function validarNuevaCuenta(){
        if(!$this->nombre){
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->email){
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password){
            self::$alertas['error'][] = 'El Password es obligatorio';
        }
        if(strlen($this->password) < 6){
            self::$alertas['error'][] = 'El Password debe tener mínimo 6 caracteres';
        }
        if($this->password !== $this->password2 ){
            self::$alertas['error'][] = 'Los Password son diferentes';
        }
        return self::$alertas;
    }

    //valida un email
    public function validarEmail(){
        if(!$this->email){
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        // SI NO PASA EMAIL SERÁ NO VÁLIDO
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][] = 'Email no válido';
        }
        return self::$alertas;
    }

    //validar password
    public function validarPassword() {
        if(!$this->password){
            self::$alertas['error'][] = 'El Password es obligatorio';
        }
        if(strlen($this->password) < 6){
            self::$alertas['error'][] = 'El Password debe tener mínimo 6 caracteres';
        }
        return self::$alertas;
    }

    //validar el perfil a actualizar
    public function validar_perfil() {
        if(!$this->nombre){
            self::$alertas['error'][] = 'El Nombre es Obligatorio';
        }
        if(!$this->email){
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        return self::$alertas;
    }

    //para nuevo password
    public function nuevo_password() : array {
        if(!$this->password_actual){
            self::$alertas['error'][] = 'El Password Actual no puede ir vacio';
        }
        if(!$this->password_nuevo){
            self::$alertas['error'][] = 'El Password Nuevo no puede ir vacio';
        }
        if(strlen($this->password_nuevo) < 6){
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    //comrpobar el password
     public function comprobar_password() : bool{
        return password_verify($this->password_actual, $this->password);
     }


    //hash password
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //generar token, si quieres mas seguridad, engloba uniqid en md5
    public function crearToken() : void {
        $this->token = uniqid();
    }
}