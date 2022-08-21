
$(document).ready( function () {
    $('#datatables-balance').dataTable({
                                responsive: true,
                                order: [[ 2, 'asc' ]],
                                scrollY:  "500px",
                                scrollCollapse: true,
                                paging:         true,
                                aoColumns: [
                                    { mData: 'Etat' },
                                    { mData: 'ComptePC' },
                                    { mData: 'Compte' },
                                    { mData: 'LibellePC' },
                                    { mData: 'Libelle' },
                                    { mData: 'Credit' },
                                    { mData: 'Debit'},
                                    { mData: 'Select'}
                                ],
                                columnDefs: [
                                    { type: 'formatted-num', targets: 5 },
                                    { type: 'formatted-num', targets: 6 },
                                    { targets: [5, 6], className: 'dt-body-right' },
                                    { targets: [0,3,4], className: 'dt-body-left' },
                                    { targets: [1, 2,7], className: 'dt-body-center' }
                                ],

                                sDom: 'RC<"clear">lfrtip',
                                colVis: {
                                    buttonText: 'Show/Hide Columns',
                                    order: 'alpha',
                                    container : '#colvis'
                                },
                                select: true
                            } );

    $('#datatables-planComptable').dataTable({
                                responsive: true,
                                order: [[ 0, 'asc' ]],
                                scrollY:  "500px",
                                scrollCollapse: true,
                                paging:         true,
                                aoColumns: [
                                    { mData: 'ComptePC' },
                                    { mData: 'LibellePC' },
                                ],
                                columnDefs: [
                                    { targets: [1], className: 'dt-body-left' },
                                    { targets: [0], className: 'dt-body-center' }
                                ],

                                sDom: 'RC<"clear">lfrtip',
                                colVis: {
                                    buttonText: 'Show/Hide Columns',
                                    order: 'alpha',
                                    container : '#colvis'
                                },
                            } );
                            
    $('.buttons-colvis').detach().appendTo('#colvis')
} );
