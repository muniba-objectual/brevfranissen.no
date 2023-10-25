var $ = jQuery;



function aifs_confirm(txt){
    if(confirm(txt)) {
        aifs_spanner();
        return true;
    }
    else{
        return false;
    }
}

function aifs_confirm_initiate(txt){
    var email = $("#admin_email").val();

    if(typeof email !== 'undefined' && !email){
        alert(  aifs_js_variable.admin_email );
        return false;
    }
 
    if(!$('input#agree_to_le_terms').is(':checked')){
        alert( aifs_js_variable.le_terms );
        return false;
    }

    else if(!$('input#agree_to_freessl_tech_tos_pp').is(':checked')){
        alert( aifs_js_variable.freessl_tech_tos_pp );
        return false;
    }

    else {
        aifs_spanner();
        return true;
    }

    /*
    else if(confirm(txt)) {
        aifs_spanner();
        return true;
    }
    else{
        return false;
    } */

}

function aifs_spanner(){
    $("div.spanner").addClass("show");
    $("div.overlay").addClass("show");
}