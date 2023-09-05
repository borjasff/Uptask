<?php

namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController {

    //index para traer las tareas
    public static function index (){
        
        $proyectoId = $_GET['id'];

        if(!$proyectoId) header('Location: /dashboard');

        $proyecto = Proyecto::where('url', $proyectoId);
        session_start();
        if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) header('Location: /404');
        $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);

        echo json_encode(['tareas' => $tareas]);
    }

    //crearlas
    public static function crear (){

            if($_SERVER['REQUEST_METHOD'] === 'POST'){

                session_start();

                $proyectoId = $_POST['proyectoId'];

                $proyecto = Proyecto::where('url', $proyectoId );

                //extraemos el proyectoId y retornar hacia la respuesta

                if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                    $respuesta = [
                        'tipo' => 'error',
                        'mensaje' => 'Hubo un Error al agregar la tarea'
                    ];
                    echo json_encode($respuesta);
                    return;
                    
                } 
                //todo correcto, instalar y crear la tarea
                $tarea = new Tarea($_POST);
                $tarea->proyectoId = $proyecto->id;
                $resultado= $tarea->guardar();
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $resultado['id'],
                    'mensaje' => 'Tarea Creada Correctamente',
                    'proyectoId' => $proyecto->id
                ];
                echo json_encode($respuesta);
                }
            }  
        

    //modificarlas
    public static function actualizar (){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //validar que el proyecto exista
            $proyecto = Proyecto::where('url', $_POST['proyectoId'] );

            session_start();

            //si no existe
            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al actualizar la tarea'
                ];
            echo json_encode($respuesta);
            return;

            } 
            //instanciar la tarea con el nuevo estado
            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;

            $resultado = $tarea->guardar();
            if($resultado){
                $respuesta = [
                'tipo' => 'exito',
                'id' => $tarea->id,
                'proyectoId' => $proyecto->id,
                'mensaje' => 'Actualizado correctamente'
                ];
                echo json_encode(['respuesta' => $respuesta]);
            }
        }
    }

    //eliminarlas
    public static function eliminar (){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //validar que el proyecto exista
            $proyecto = Proyecto::where('url', $_POST['proyectoId'] );

            session_start();

            //si no existe
            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un Error al eliminar la tarea'
                ];
            echo json_encode($respuesta);
            return;

            } 
            //instanciar la tarea con el nuevo estado
            $tarea = new Tarea($_POST);
            $resultado = $tarea->eliminar();

            $resultado = [
                'resultado' => $resultado,
                'mensaje' => 'Eliminado correctamente',
                'tipo' => 'exito'
                ];

            echo json_encode($resultado);
        }
    }
}