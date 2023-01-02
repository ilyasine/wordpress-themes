document.getElementById('question').onsubmit = function(){
    var nom = document.getElementById("choix_181363").value;
    var prenom = document.getElementById("choix_181366").value;
    var email = document.getElementById("choix_376333").value;
    var tele = document.getElementById("choix_1620564").value;
    var data = nom+'|'+prenom+'|'+email+'|'+tele;
    window.parent.postMessage( "iframe_data" +  data ,'*');
};