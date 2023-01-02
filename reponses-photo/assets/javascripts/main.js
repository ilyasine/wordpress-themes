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
    if ($('body').hasClass('search')) {
        var content_date = $('#hidden_content_date').val(); 
        var content_type = $('#hidden_content_type').val(); 
        if (content_date.length) {
            $('#search_content_date').val(content_date).change();
        }
        if (content_type.length) {
            $('#search_content_type').val(content_type).change();
        }
        $('#search_content_date, #search_content_type').on('change', function() {
            $('#search_content_form').submit();
        });
    }
    if( $.fn.shave ){
        $('#blockRight .most_popular .title-item-small').shave(60);
        $('.post_caption .post_title a , #homeBody .title-item-home a').shave(50);
        $('.bloc-agenda .post .post_caption .post_title a').shave(46);
    }
    update_href_social_media();

    $("#date").click(function(){
        $("#date").addClass('selected');
    });

});

function update_href_social_media(){
    $_ankers=$('.navbar-sociallink li a');
    for (let i = 0; i < $_ankers.length; i++) {
        if( $($_ankers[i]).attr('href') != 'javascript:void(0);' ){
            datahref = $($_ankers[i]).attr('href');
            $($_ankers[i]).attr('data-href', datahref);
            $($_ankers[i]).attr('href','javascript:void(0);');
        }         
    }
}