
        if( typeof ArrComptes == 'undefined' ) {
            var ArrComptes= new Array(); //A
        }


        function comptesSelected (compte, libelle){
            console.log(compte);
            let pos = ArrComptes.indexOf(compte + " - " + libelle);
            //console.info(ArrComptes);
            if (pos==-1){
                ArrComptes.push(compte + " - " + libelle);
            }
            else{
                ArrComptes.splice(pos,1)
            }
            
        }

        $('#datatables-balance').on('change', 'input[type="checkbox"]', function( ) {
            console.log("select")
            var table = $('#datatables-balance').DataTable();
            var id = $(this).attr('id');
            var row = table.rows(id-1).data();
            var compte= row[0].Compte;
            var libelle= row[0].Libelle;
            comptesSelected(compte, libelle);
            document.getElementById('form_selected').textContent= ArrComptes.join("\n");
        } );

        $("#RattCpts").click(function(e){
            e.preventDefault();
            console.log("RattCpts")
           if(ArrComptes.length==0){
               alert("aucun compte selectionné")
           }
           else{
                $("#RattCpts").unbind('click').click();
           }
        } );

        $("#closeItemForm").click(function(e){
            this.classList.remove('itemForm-active');;
        } );

        $("#copenItemForm").click(function(e){
            this.classList.add('itemForm-active');;
        } );