/**
 * 
 */
function authorizeCimSelect(object) {
    var profileContainer = $('goodahead_authorizenet_payment_profile_container');
    var elements = profileContainer.getElementsBySelector('input', 'select');
    if (object.options[object.options.selectedIndex].value) {

        elements.each(function(e) {
            e.disabled = true;
        });
        $('goodahead_authorizenet_payment_profile_container').setStyle({display: 'none'});
    } else {
        elements.each(function(e) {
            e.disabled = false;
        });
        $('goodahead_authorizenet_payment_profile_container').setStyle({display: 'block'});
    }
}