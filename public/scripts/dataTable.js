$(document).ready(function() {
    $('#tableStandar').DataTable({
        "order": [[ 0, "desc" ]],
        "language": {
            "lengthMenu": "",
            "zeroRecords": "No hay registros",
            "info": "Pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros",
            "infoFiltered": " ",
            "search": "Buscar",
            "paginate": {
                "first":    "Primero",
                "last":     "Último",
                "next":     "Siguiente",
                "previous": "Anterior"
            }
        }
    });
});