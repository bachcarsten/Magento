OrderBySkuForm = Class.create();

OrderBySkuForm.prototype = {
    initialize: function (formId) {
        this.table = $(formId).select('tbody')[0];
        this.templateSyntax = /(^|.|\r|\n)({{(\w+)}})/;
        this.templateText =
            '<tr class="sku-row-{{index}}">'
                + '<td><div class="input-box"><input type="text" name="products[{{index}}][sku]" class="input-text" /></div></td>'
                + '<td class="last"><div class="input-box">'
                + '<input type="text" name="products[{{index}}][qty]" value="1" class="input-text qty validate-number" /></div></td>'
                + '<td id="remove-{{index}}" class="remove">X</td> '
            + '</tr>';
        this.itemCount = 1;
        $('remove-0').observe('click', function() {
            this.removeRow(0);
        }.bind(this));
    },

    addRow: function() {
        this.template = this.template = new Template(this.templateText, this.templateSyntax);
        var row = this.template.evaluate({index: this.itemCount});
        this.table.insert({bottom: row});
        var id = this.itemCount;
        $('remove-' + this.itemCount).observe('click', function() {
            this.removeRow(id);
        }.bind(this));
        this.itemCount++;
    },

    removeRow: function(id) {
        if (this.table.select('tr').length > 1) {
            this.table.select('tr.sku-row-'+id)[0].remove();
        } else {
            this.table.select('tr')[0].select('input')[0].value = '';
            this.table.select('tr')[0].select('input')[1].value = 1;
        }
    }
};