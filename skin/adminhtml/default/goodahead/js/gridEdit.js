varienGridMassaction.prototype.apply = function() {
    if(varienStringArray.count(this.checkedString) == 0) {
        if (this.getSelectedItem().id != 'upd_inventory') {
            alert(this.errorText);
            return;
        }
    }

    var item = this.getSelectedItem();

    if(!item) {
        this.validator.validate();
        return;
    }
    this.currentItem = item;

    var fieldName = (item.field ? item.field : this.formFieldName);
    var fieldsHtml = '';

    if(this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
        return;
    }

    this.formHiddens.update('');
    new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: this.checkedString}));
    new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

    if (this.currentItem.id == 'upd_inventory') {
        var elems = $('productGrid_table').select('tbody [type="text"][name^="increase_qty_by"]');

        var i = 0;
        elems.each(function(node){
            var nodeValue = parseInt(node.value);
            if (nodeValue && nodeValue != 0) {
                new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: node.name, value: nodeValue}));
                i++;
            }

        }.bind(this));

        if (i == 0) {
            alert('No any records were changed in "Increase Qty By" field!');
            return;
        }
    }

    if(!this.validator.validate()) {
        return;
    }

    if(this.useAjax && item.url) {
        new Ajax.Request(item.url, {
            'method': 'post',
            'parameters': this.form.serialize(true),
            'onComplete': this.onMassactionComplete.bind(this)
        });
    } else if(item.url) {
        this.form.action = item.url;
        this.form.submit();
    }
}