<?php
global $regenrate_token;
$regenrate_token = false;

function get_token(){
    global $regenrate_token;
    $current_date = date("d-m-Y H:i:s");
    $access_token = '';
    $initial_value = array(
        'access_token' => '',
        'date_expiration' => ''
    );
    $token = get_option('residence_access_token', $initial_value);
    if(!empty($token['access_token']) && $current_date < $token['date_expiration'] && !$regenrate_token){
        $access_token = $token['access_token'];
    }else{
        $response = wp_remote_get(
            "https://residerec.e-deal.net/residerec/rws/authentication/getToken",
            array(
                'method' => 'GET',
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( 'DigtitalKey.Reworld' . ':' . 'Ml56@$Zs%1CfdqE' )
                ),
                'body' => array(),
            )
        );
        if(!is_wp_error( $response )){
            if($response['response']['code'] == 200){
                $body = json_decode($response['body'], true);
                $access_token = $body['token'];
                $expiration_date = date('d-m-Y H:i:s', strtotime('+30 minutes', strtotime($current_date)));

                // Update Token option
                $token['access_token']=$access_token;
                $token['date_expiration']=$expiration_date;
                update_option('residence_access_token',$token);
            }
        }
    }
    return $access_token;
}

function send_leads_to_residenceseniors($data){
    global $ninja_forms_processing, $regenrate_token;
    $token_invalid = false;

    $field_response = get_field_id_by_label('ResponseAPI');
    $field_data = get_field_id_by_label('DataAPI');
    if (!empty($data))
    {
        $ninja_forms_processing->update_field_value( $field_data, $data );
        $access_token = get_token();
        if(!empty($access_token)){
            $data = json_decode($data, true);
            $token = ['access_token' => $access_token];
            if (!array_key_exists('access_token', $data)) {
                $data = array_merge($token, $data);
            }else{
                $data['access_token'] = $access_token;
            }
            $response = wp_remote_get(
                "https://residerec.e-deal.net/residerec/rws/digital/pushDigitalContact",
                array(
                    'method' => 'GET',
                    'timeout' => 15,
                    'headers' => array(
                        'Content-type: application/x-www-form-urlencoded'
                    ),
                    'body' => $data,
                )
            );

            if(!is_wp_error( $response )){
                if($response['response']['code'] == 200) {
                    $body = json_decode($response['body'], true);
                    $results = json_encode($body['objects']);
                    $ninja_forms_processing->update_field_value( $field_response, $results );
                }else{
                    $body = json_decode($response['body'], true);
                    $error = '';
                    if(!empty($body)){
                        if(is_array($body['error'])){
                            $error = implode(",", $body['error']);
                        }else{
                            $error = $body['error'];
                        }
                    }
                    if (strpos($error, 'token was expired') !== false || strpos($error, 'Bad identifier') !== false) {
                        $ninja_forms_processing->update_field_value( $field_response, $error );
                        $token_invalid = true;
                    }
                }
            }
        }else{
            $ninja_forms_processing->update_field_value( $field_response, "Token n'est pas généré. Problème dans l'API." );
            $token_invalid = true;
        }
        if($token_invalid && !$regenrate_token){
            $regenrate_token = true;
            send_leads_to_residenceseniors($data);
        }
    }
}

add_action('ninja_forms_post_process', 'form_residenceseniors_processing');
function form_residenceseniors_processing(){
    global $ninja_forms_processing;

    $form_id = $ninja_forms_processing->get_form_ID();

    $id_form = $_POST['formid_field'];

    if(isset($id_form) && ($form_id == $id_form)){
        $field_results = ninja_forms_get_fields_by_form_id($form_id);
        $all_fields = $ninja_forms_processing->get_all_fields();

        $nom = $prenom = $email = $telephone = $demande = $age = $revenus = $residence_concerne_id = "";
        if(is_array($field_results) AND !empty($field_results)){
            foreach($field_results as $field){
                $field_id = $field['id'];
                $field_name = $field['data']['data_name'];
                $field_value = $all_fields[$field_id];
                if($field_name == 'last_name')
                    $nom = $field_value;
                if($field_name == 'first_name')
                    $prenom = $field_value;
                if($field_name == 'mail')
                    $email = $field_value;
                if($field_name == 'phone')
                    $telephone = $field_value;
                if($field_name == 'residence')
                    $residence_concerne_id = $field_value;
                if($field_name == 'revenus'){
                    if($field_value == 'Moins 1700€'){
                        $revenus = 'Moins18';
                    }elseif($field_value == 'Entre 1700€ et 2200€'){
                        $revenus = '18_24';
                    }elseif($field_value == 'Entre 2200€ et 2900€'){
                        $revenus = '24_29';
                    }elseif($field_value == 'Supérieur à 2900€'){
                        $revenus = 'Plus29';
                    }else{
                        $revenus = '';
                    }
                }
                if($field_name == 'age'){
                    if($field_value == 'Moins de 75 ans'){
                        $age = 'Moins75';
                    }elseif($field_value == 'Entre 75 ans et 80 ans'){
                        $age = '75et80';
                    }elseif($field_value == 'Entre 80 et 85 ans'){
                        $age = '80et85';
                    }elseif($field_value == 'Plus de 85 ans'){
                        $age = 'Plus85';
                    }else{
                        $age = '';
                    }
                }
                if($field_name == 'demande')
                    $demande = $field_value;
            }

            $data = array(
                'SilName' => $nom,
                'SilFstName' => $prenom,
                'SilMail' => $email,
                'SilPhone' => $telephone,
                'SilOppResidence_' => $residence_concerne_id,
                'SilTrancheRevenus_' => $revenus,
                'SilAgeFuturResident_' => $age,
                'SilPhoneAutStatus' => 'OK',
                'SilType_' => $demande == 1 ? 'BENEF' : 'APPAFF',
                'SilStatut_' => $demande == 1 ? 'PROSP' : 'PROCHE',
                'SilBusOriginID' => 'DGTAL',
                'SilOppRefExterne_' => 1,
                'SilCtrID' => 'FR',
                'SilConsentement_' => 'OUI',
                'SilLngID' => 'fr_FR',
                'SilTrsfAuto' => 1,
                'SilTrsfPerPolicy' => 'USE_SIMILAR_IF_CERTAIN',
                'SilContext' => 'LEAD',
                'SilTrsfUpdatePolicy' => 'REUpdPolPers',
                'SilTrsfDelete' => 0
            );
            $data_json = json_encode($data);

            do_action('ninja_forms_process');
            send_leads_to_residenceseniors($data_json);
        }
    }else{
        $ninja_forms_processing->add_error("form_id_ko", __( 'L\'identification du formulaire n\'est pas valide', 'ninja-forms' ));
        return;
    }
}