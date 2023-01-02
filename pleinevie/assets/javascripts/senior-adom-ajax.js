$(document).ready(function() {
    window.addEventListener( "message", function(e) {
    	var data = e.data ;
    	if(data && data.indexOf){
    		if(data.indexOf('iframe_data') > -1){
    			var text = data.substr(11);
                var data_arr = text.split('|');
                var nom = data_arr[0];
                var prenom = data_arr[1];
                var email = data_arr[2];
                var tele = data_arr[3];
                if (nom && prenom && email && tele) {            
                    $.ajax({
                        type:"GET",
                        url:'/',
                        data : {
                            action    : 'senior_adom_send_leads',
                            lastname  : nom,
                            firstname : prenom,
                            mail      : email,
                            phone     : tele,
                        },
                        dataType:'json',
                        success: function(response) {
                            console.log('lead sended successfully');
                        }
                    });
                }
    		}
    	}
    });
});