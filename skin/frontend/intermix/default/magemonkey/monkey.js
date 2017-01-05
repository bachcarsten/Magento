removeManageNewsletter = function(){
    var newsletter = $$('div.content ul li a[href*="newsletter/manage"]');
    if(newsletter.length){
        newsletter.first().up().remove();
    }
}

document.observe("dom:loaded", function() {

    var monkeyEnabled = $$('div.content ul li a[href*="monkey/customer_account/index"]');
    var monkeyEnabledActive = $$('div.content ul li.mailchimp');

    if(monkeyEnabled.length || monkeyEnabledActive.length){
        removeManageNewsletter();

        //If in Dashboard, change "edit" link for "Newsletters"
        var editLink = $$('div.account-box a[href*="newsletter/manage"]');
        if(editLink.length){
            editLink.first().writeAttribute('href', monkeyEnabled.first().readAttribute('href'));
        }
    }

});
