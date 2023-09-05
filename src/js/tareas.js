//IIFE
(function(){

    obtenerTareas();
    let tareas = [];
    let filtradas = [];

    // Boton para mostrar el modal de agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', function() {
        mostrarFormulario()
    });

    //filtros de busqueda
    const filtros = document.querySelectorAll('#filtros input[type="radio"]');
    //iteramos y asociamos
    //console.log(filtros);
    filtros.forEach(radio =>{
        radio.addEventListener('input', filtrarTareas);
    });

    function filtrarTareas(e){
        const filtro = e.target.value;

        if(filtro !== ''){
            filtradas = tareas.filter(tarea => tarea.estado === filtro)
        } else{
            filtradas = [];
        }
        mostrarTareas();
    }

    async function obtenerTareas() {
        //obtenemos las tareas
        try {
            const id = obtenerProyecto();
            const url = `/api/tareas?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            tareas = resultado.tareas;
            mostrarTareas();

        } catch (error) {
            console.log(error);
        }
    }
    function mostrarTareas(){
        limpiarTareas();
        //obtener cuantas quedan pendientes y cuantas completas
        totalPendientes();
        totalCompletas();

        const arrayTareas = filtradas.length ? filtradas : tareas;

        console.log(tareas);
        if(arrayTareas.length === 0){
            const contenedorTareas = document.querySelector('#listado-tareas');

            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'No hay Tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            return;
        }

        //diccionario de estados para el boton
        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        }

        arrayTareas.forEach(tarea => {
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;
            nombreTarea.ondblclick = function (){
                //modificamos una copia del objeto editable y pasamos la tarea para mostrar el texto
                mostrarFormulario(editar = true, {...tarea});
            }

            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');

            //botones
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.ondblclick = function (){
                //modificamos una copia del objeto
                cambiarEstadoTarea({...tarea});
            }

            const btnEliminarTarea = document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.tareaId = tarea.id;
            btnEliminarTarea.textContent = 'Eliminar';
            btnEliminarTarea.ondblclick = function (){
                //eliminamos la tarea
                confirmarEliminarTarea({...tarea});
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);

            const listadoTareas = document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);

        });
    }
    //tareas pendientes
    function totalPendientes(){
        const totalPendientes = tareas.filter(tarea => tarea.estado === "0");
        const pendientesRadio = document.querySelector('#pendientes');

        if(totalPendientes.length === 0){
            pendientesRadio.disabled = true;
        } else{
            pendientesRadio.disabled = false;
        }
    }

    //tareas completas
    function totalCompletas(){
        const totalCompletas = tareas.filter(tarea => tarea.estado === "1");
        const completasRadio = document.querySelector('#completadas');

        if(totalCompletas.length === 0){
            completasRadio.disabled = true;
        } else{
            completasRadio.disabled = false;
        }
    }

    //creación del formulario 
    function mostrarFormulario(editar = false, tarea = {} ){
        console.log(tarea);
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar Tarea' : 'Añade una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input type="text" name="tarea" id="tarea" placeholder="${tarea.nombre ? 'Edita la Tarea' : 'Añadir tarea al proyecto actual'}" value="${tarea.nombre ? tarea.nombre : ''}"></input>
                </div>
                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea"  value="${tarea.nombre ? 'Guardar Cambios' : 'Añadir Tarea'}" />
                        <button type="button" class="cerrar-modal">Cancelar</button>
                    </div>
                </form>
        `;
        //para la transición del formulario
        setTimeout(() => {
            const formmulario = document.querySelector('.formulario');
            formmulario.classList.add('animar');
        },0);



        //utilizamos delegation para identificar que elemento damos click e identificar ciertas acciones
        modal.addEventListener('click', function(e){
            e.preventDefault();
            //cacelar
            if(e.target.classList.contains('cerrar-modal')){
                const formmulario = document.querySelector('.formulario');
                formmulario.classList.add('cerrar');
                //eliminamos el div
                //para la transición de eliminar
                setTimeout(() => {
                    modal.remove();
                },500);  
            } 
            //añadir tarea
            if(e.target.classList.contains('submit-nueva-tarea')){
                const nombreTarea = document.querySelector('#tarea').value.trim();
                if(nombreTarea === ''){
                    //console.log('tarea no tiene nombre');
                    //mostrar alerta de error
                    mostrarAlerta('El nombre de la tarea es Obligatorio', 'error', document.querySelector('.formulario legend'));
                    //para que no se ejecute más código
                    return;
                }
                if(editar){
                    //editamos la tarea en el servidor
                    tarea.nombre = nombreTarea;
                    actualizarTarea(tarea);
                } else{
                    //agregamos la tarea en el servidor
                    agregarTarea(nombreTarea);
                }
            }
            //console.log(e.target);
        }) 
        document.querySelector('body').appendChild(modal);
    }

    //muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia){
        //evitar multiples alertas
        const alertaPrevia = document.querySelector('.alerta');
        if(alertaPrevia){
            alertaPrevia.remove();
        }
        //creamos un div con unas clases de estilos y su mensaje
        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;

        //Inserta la alerta antes del Legend
        //existen appendChild, insertBefore, parentElement, nextElementSibling
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);
        
        //eliminar la alerta después de 5 segundos
        setTimeout(() => {
            alerta.remove();
        },5000)
    }

    //consultar el servidor para añadir una nueva tarea en el proyecto actual
    async function agregarTarea(tarea){
        //construimos la peticion
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());


        try {
            //la url del servidor 
            const url = '/api/tarea';
            // decimos que es formato post ya que de forma base es get (utilizamos el metodo async await)
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();


            mostrarAlerta(
                resultado.mensaje, 
                resultado.tipo, 
                document.querySelector('.formulario legend')
            );

            if(resultado.tipo === 'exito'){
                const modal = document.querySelector('.modal');
                    //para la transición de eliminar
                    setTimeout(() => {
                        modal.remove();
                    },3000);
                //agregar el objeto de tarea al global de tareas
                    const tareaObj = {
                        //replicamos la estructura de la API
                        id: String(resultado.id),
                        nombre: tarea,
                        estado: "0",
                        proyectoId: resultado.proyectoId
                    }
                    //lo agregamos a tareas el nuevo objeto
                    tareas = [...tareas, tareaObj];
                    mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }
    }

    //actualizamos el estado de la tarea
    function cambiarEstadoTarea(tarea){

        //actualizamos la tarea
        const nuevoEstado = tarea.estado === "1"? "0" : "1";
        tarea.estado = nuevoEstado;
        actualizarTarea(tarea);
    }

    //actualizar tarea
    async function actualizarTarea(tarea){
        //extraemos los valores de tarea
        const {estado, id, nombre, proyectoId} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        //obtenemos el valor de cada dato: for(let valor of datos.values()){ console.log(valor);}
        try {
            const url = '/api/tarea/actualizar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const  resultado = await respuesta.json();
            if(resultado.respuesta.tipo === 'exito'){
                //mostramos una alerta de actualización
                //mostrarAlerta(resultado.respuesta.mensaje, resultado.respuesta.tipo, document.querySelector('.contenedor-nueva-tarea'));

                Swal.fire(
                    resultado.respuesta.mensaje,
                    resultado.respuesta.mensaje,
                    'success'
                );

                const modal = document.querySelector('.modal');
                if(modal) {
                    modal.remove();
                }

                //para refrescar creamos un nuevo arreglo con .map actualizado
                tareas = tareas.map(tareaMemoria => {
                    if(tareaMemoria.id === id){
                        tareaMemoria.estado = estado;
                        tareaMemoria.nombre = nombre;
                    }
                    return tareaMemoria;
                });
                mostrarTareas();
            }
        } catch (error) {
            console.log(error);
        }


    }
    //eliminamos la tarea con el boton
    function confirmarEliminarTarea(tarea){
        Swal.fire({
            title: '¿Eliminar Tarea?',
            showCancelButton: true,
            confirmButtonText: 'Si',
            cancelButtonText: `No`,
          }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                eliminarTarea(tarea);
            }
          })
    }
    //eliminar tarea
    async function eliminarTarea(tarea){
                //extraemos los valores de tarea
                const {estado, id, nombre } = tarea;

                const datos = new FormData();
                datos.append('id', id);
                datos.append('nombre', nombre);
                datos.append('estado', estado);
                datos.append('proyectoId', obtenerProyecto());

        try {
            const url = '/api/tarea/eliminar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const  resultado = await respuesta.json();
            if(resultado.resultado){
                //mostramos una alerta de eliminación
                //mostrarAlerta(resultado.mensaje, resultado.tipo, document.querySelector('.contenedor-nueva-tarea'));
                Swal.fire('Eliminado!', resultado.mensaje, 'success');
                //para refrescar creamos un nuevo arreglo con filter, trae todas las diferentes a las que damos click
                tareas = tareas.filter( tareaMemoria => tareaMemoria.id !==tarea.id)
                mostrarTareas();
        }} catch (error) {
            console.log(error);
        }}
    
    //obtenemos los diferentes objetos
    function obtenerProyecto(){
                //accedemos al valor de la url
                const proyectoParams = new URLSearchParams(window.location.search);
                const proyecto = Object.fromEntries(proyectoParams.entries());
                return proyecto.id;
    }
    function limpiarTareas(){
        const listadoTareas = document.querySelector('#listado-tareas');
        while(listadoTareas.firstChild){
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }
})();
