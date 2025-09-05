// confirmations.js
function confirmDelete(element, type, id, name) {
    event.preventDefault();
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: type === 'task' 
            ? `¿Eliminar la tarea "${name}"? Esta acción no se puede deshacer.`
            : `¿Eliminar al usuario "${name}"? Se eliminarán todas sus tareas asociadas.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = element.href;
        }
    });
}