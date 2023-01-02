$(document).ready(function() {
    $(".btn-lp").click(function() {
        $('html, body').animate({
            scrollTop: $("#form_leads").offset().top - 100}, 
            1000);
    });
    $('input.formid_field').attr('name', 'formid_field');
});
jQuery('.ninja-forms-form').on('submitResponse', function(e, response) {
    var ga_id = site_config_js.new_ga_lp_girendiere.ID;
    var ga_text = site_config_js.new_ga_lp_girendiere.text_ID;
     if (response.errors === false) {
         fbq('track', 'lead');
         gtag('event', 'conversion', {'send_to': ''+ga_id+'/'+ga_text+''});
    }
});