$( document ).ready(function() {
   BT.formUnsaved();

});


let BT = {

	lang : $('html').attr('lang'),


   formUnsaved: function() {
      if($('#unsavedFormModal').length > 0) {
         let formAtStart = $('form.form').serialize();
         let modalUnsavedForm = new bootstrap.Modal(document.getElementById('unsavedFormModal'))

         $('.backButton').on('click',function(event) {
            let formAtEnd = $('form.form').serialize();
            if(formAtStart !== formAtEnd) {
               event.preventDefault();
               modalUnsavedForm.show();
            }
         });
      }
   },
}
