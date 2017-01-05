AmazonTemplateSellingFormatHandler = Class.create();
AmazonTemplateSellingFormatHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function()
    {
        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_SellingFormat', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-validate-price-coefficient', M2ePro.text.price_coef_error, function(value)
        {
            if (value == '') {
                return true;
            }

            if (value == '0' || value == '0%') {
                return false;
            }

            return value.match(/^[+-]?\d+[.,]?\d*[%]?$/g);
        });

//        TODO uncomment code
//        Validation.add('M2ePro-validate-qty-coefficient', M2ePro.text.price_coef_error, function(value)
//        {
//            if (value == '') {
//                return true;
//            }
//
//            if (value == '0' || value == '0%') {
//                return false;
//            }
//
//            return value.match(/^[+-]?\d+$/g);
//        });
    },

    //----------------------------------

    duplicate_click: function($headId)
    {
        var attrSetEl = $('attribute_sets_fake');

        if (attrSetEl) {
            $('attribute_sets').remove();
            attrSetEl.observe('change', AttributeSetHandlerObj.changeAttributeSets);
            attrSetEl.id = 'attribute_sets';
            attrSetEl.name = 'attribute_sets[]';
            attrSetEl.addClassName('M2ePro-validate-attribute-sets');

            AttributeSetHandlerObj.confirmAttributeSets();
        }

        if ($('attribute_sets_breadcrumb')) {
            $('attribute_sets_breadcrumb').remove();
        }
        $('attribute_sets_container').show();
        $('attribute_sets_buttons_container').show();

        this.setValidationCheckRepetitionValue('M2ePro-price-tpl-title',
                                                M2ePro.text.title_not_unique_error,
                                                'Template_SellingFormat', 'title', '',
                                                '');

        CommonHandlerObj.duplicate_click($headId);
    },

    //----------------------------------

    attribute_sets_confirm: function()
    {
        AttributeSetHandlerObj.confirmAttributeSets();

        AttributeSetHandlerObj.renderAttributesWithEmptyOption('qty_custom_attribute', 'qty_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('price_custom_attribute', 'price_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('sale_price_custom_attribute', 'sale_price_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('sale_price_start_date_custom_attribute', 'sale_price_start_date_custom_attribute_td');
        AttributeSetHandlerObj.renderAttributesWithEmptyOption('sale_price_end_date_custom_attribute', 'sale_price_end_date_custom_attribute_td');
    },

    //----------------------------------

    qty_mode_change: function()
    {
        var self = AmazonTemplateSellingFormatHandlerObj;

        $('qty_custom_attribute_tr', 'qty_custom_value_tr', 'qty_coefficient_td').invoke('hide');

//        TODO uncomment code
//        if (this.value == self.QTY_MODE_PRODUCT || this.value == self.QTY_MODE_ATTRIBUTE) {
//            $('qty_coefficient_td').show();
//        }

        if (this.value == self.QTY_MODE_NUMBER) {
            $('qty_custom_value_tr').show();
        } else if (this.value == self.QTY_MODE_ATTRIBUTE) {
            if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
                this.value = self.QTY_MODE_PRODUCT;
                return;
            }

            $('qty_custom_attribute_tr').show();
        }
    },

    //----------------------------------

    price_mode_change: function()
    {
        var self = AmazonTemplateSellingFormatHandlerObj;

        if (this.value == self.PRICE_ATTRIBUTE) {
            if (AttributeSetHandlerObj.checkAttributeSetSelection()) {
                $('price_custom_attribute_tr').show();
            } else {
                this.value = self.PRICE_PRODUCT;
            }
        } else {
            $('price_custom_attribute_tr').hide();
        }

        self.updateCustomerGroupIdVisibility();
    },

    //----------------------------------

    sale_price_mode_change: function()
    {
        var self = AmazonTemplateSellingFormatHandlerObj;

        if (this.value == self.PRICE_NONE || this.value == self.PRICE_NOT_SET) {
            $('sale_price_start_date_mode_tr', 'sale_price_end_date_mode_tr', 'sale_price_coefficient_td').invoke('hide');
            $('sale_price_start_date_value_tr', 'sale_price_end_date_value_tr').invoke('hide');
            $('sale_price_start_date_custom_attribute_tr', 'sale_price_end_date_custom_attribute_tr').invoke('hide');
        } else if (this.value == self.PRICE_SPECIAL) {
            $('sale_price_coefficient_td').show();
            $('sale_price_start_date_mode_tr', 'sale_price_end_date_mode_tr').invoke('hide');
            $('sale_price_start_date_value_tr', 'sale_price_end_date_value_tr').invoke('hide');
            $('sale_price_start_date_custom_attribute_tr', 'sale_price_end_date_custom_attribute_tr').invoke('hide');
        } else {
            $('sale_price_start_date_mode_tr', 'sale_price_end_date_mode_tr', 'sale_price_coefficient_td').invoke('show');
            $('sale_price_start_date_mode').simulate('change');
            $('sale_price_end_date_mode').simulate('change');
        }

        if (this.value == self.PRICE_ATTRIBUTE) {
            if (AttributeSetHandlerObj.checkAttributeSetSelection()) {
                $('sale_price_custom_attribute_tr').show();
            } else {
                $('sale_price_start_date_mode_tr', 'sale_price_end_date_mode_tr', 'sale_price_coefficient_td').invoke('hide');
                $('sale_price_start_date_value_tr', 'sale_price_end_date_value_tr').invoke('hide');
                $('sale_price_start_date_custom_attribute_tr', 'sale_price_end_date_custom_attribute_tr').invoke('hide');
                this.value = self.PRICE_NONE;
            }
        } else {
            $('sale_price_custom_attribute_tr').hide();
        }

        self.updateCustomerGroupIdVisibility();
    },

    sale_price_start_date_mode_change: function()
    {
        var self = AmazonTemplateSellingFormatHandlerObj;

        if (this.value == self.DATE_VALUE) {
            $('sale_price_start_date_value_tr').show();
            $('sale_price_start_date_custom_attribute_tr').hide();
        } else {
            $('sale_price_start_date_value_tr').hide();
            $('sale_price_start_date_custom_attribute_tr').show();
        }

    },

    sale_price_end_date_mode_change: function()
    {
        var self = AmazonTemplateSellingFormatHandlerObj;

        if (this.value == self.DATE_VALUE) {
            $('sale_price_end_date_value_tr').show();
            $('sale_price_end_date_custom_attribute_tr').hide();
        } else {
            $('sale_price_end_date_value_tr').hide();
            $('sale_price_end_date_custom_attribute_tr').show();
        }

    },

    //----------------------------------

    updateCustomerGroupIdVisibility: function()
    {
        var self = AmazonTemplateSellingFormatHandlerObj;
        var displayCustomerGroup = $('price_mode', 'sale_price_mode').any(
            function (select) { return select.value == self.PRICE_FINAL; }
        );

        if (displayCustomerGroup) {
            $('magento_block_amazon_template_selling_format_customer_group_id').show();
        } else {
            $('magento_block_amazon_template_selling_format_customer_group_id').hide();
            $('customer_group_id').value = 0;
        }
    }

    //----------------------------------
});