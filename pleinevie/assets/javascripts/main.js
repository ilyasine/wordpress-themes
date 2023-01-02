jQuery(document).ready(function($){
    if( $('body').hasClass('page-name-contacter-dpd')){
            //add placeholders
            $('.first-name').attr("placeholder","Prénom");
            $('.last-name').attr("placeholder","Nom");
            $('.address-mail').attr("placeholder","Adresse mail");
            $('.affiliate').attr("placeholder","N° d'abonné");
            $('.phone').attr("placeholder","N° de téléphone");
            $('.postal-address').attr("placeholder","Adresse postale");
            $('.postal-code').attr("placeholder","Code postal");
            $('.city').attr("placeholder","Ville");
            $('.object').attr("placeholder","Objet de votre message");
            $('.message').attr("placeholder","Message");

            //add descriptions
            $( ".first-name-wrap" ).before( "<p class='p-form'><strong>Vos coordonées</strong></p>" );
            $( ".postal-address-wrap" ).before( "<p class='p-form'><strong>Adresse postale</strong></p>" );
            $( ".object-wrap" ).before( "<p class='p-form'><strong>Votre message</strong></p>" );
    }
    $('.menu-children').each(function() {
        var _this = $(this);
        var parent = _this.attr('parent');
        _this.appendTo('.menu-item-'+parent);
    });
   
});
