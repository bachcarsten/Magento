AmazonCategoryHandler = Class.create();
AmazonCategoryHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function(M2ePro,specificHandler)
    {
        var self = this;

        self.M2ePro = M2ePro;
        self.specificHandler = specificHandler;
        self.specificHandler.categoryHandler = self;

        self.initPopUp('search_category',850,550,self.M2ePro.text.search_category);
        self.initPopUp('browse_category',600,500,self.M2ePro.text.browse_category);

        self.xsdsTr = $('xsds_tr');
        self.categoriesTr  = $('categories_tr');
        self.changeButton  = $('category_change_button_container');
        self.confirmButton = $('category_confirm_button_container');
        self.nodeTitleEl   = $('node_title');
        self.categoriesContainer = $('categories_container');
        self.categoryXsdHashHiddenInput = $('xsd_hash');
        self.categoryIdentifiersHiddenInput = $('category_identifiers');

        self.searchCategoryButton = $('search_category_button');
        self.browseCategoryButton = $('browse_category_button');

        self.searchKeywordsInput = $('search_keywords');

        self.categoryPathHiddenInput = new Element('input', {
            'type': 'hidden',
            'name': "category[path]",
            'class': 'required-entry'
        });
        self.changeButton.insert({
            after: self.categoryPathHiddenInput
        });

        if (self.M2ePro.formData.category.category_path) {
            self.categoryPathHiddenInput.value = self.M2ePro.formData.category.category_path;
            self.showSpanWithCategoryPath(self.M2ePro.formData.category.category_path);
            self.specificHandler.run(self.M2ePro.formData.category.xsd_hash);
        }
    },

    //----------------------------------

    showSpanWithCategoryPath: function(path)
    {
        var spanEl = new Element('span',{'class': 'nobr','style': 'font-weight: bold'});
        this.changeButton.insert({'before': spanEl});
        spanEl.insert(path);
    },

    //----------------------------------

    checkAttributeSetSelection: function()
    {
        if (!AttributeSetHandlerObj.checkAttributeSetSelection()) {
            amazonCategoryEditTabsJsTabs.showTabContent($('amazonCategoryEditTabs_general'));
        }
    },

    checkSpecificsReady: function()
    {
        if (!this.specificHandler.xsd_hash) {
            alert(this.M2ePro.text.select_category_first);
            amazonCategoryEditTabsJsTabs.showTabContent($('amazonCategoryEditTabs_general'));
        }
    },

    //----------------------------------

    node_title_change: function(element)
    {
        this.parentId = null;
        this.categoriesTr.show();
        this.categoriesContainer.show();
        this.categoriesContainer.update();
        this.removeSpanWithCategoryPath();
        this.changeButton.hide();
        this.confirmButton.hide();
        this.xsdsTr.hide();

        this.getCategories(element.down('option[value=' + element.value + ']').getAttribute('node_hash'),function(transport) {
            this.categories = transport.responseText.evalJSON();
            this.showCategories(this.categoriesContainer);
        });

    },

    //----------------------------------

    getCategories: function(nodeHash,callback)
    {
        var self = this;

        new Ajax.Request(self.M2ePro.url.getCategories,
        {
            method : 'get',
            asynchronous : true,
            parameters : {
                node_hash : nodeHash
            },
            onSuccess: function(transport) {
                callback.call(self,transport);
            }
        });
    },

    //----------------------------------

    showCategories: function(container)
    {
        var self       = this;
        var categories = [];

        var old = $('xsds');
            old && old.parentNode.removeChild(old);

        self.xsdsTr.hide();
        self.categoryPathHiddenInput.value = '';
        self.confirmButton.hide();
        self.removeContainers(container.parentNode,container);
        self.specificHandler.specificsContainer.update();
        self.specificHandler.specificsMainContainer.hide();

        var selectEl = container.appendChild(new Element('select'));
            selectEl.appendChild(new Element('option',{'value': 'empty','style': 'display: none'}));

        self.categories.each(function(category) {
            category.parent_id === self.parentId && categories.push(category)
        });

        categories.sort(function(a,b) {
            return a.sorder - b.sorder;
        });

        if (categories.length == 0 || self.getCategoryInfo('id',self.parentId).is_listable == 1) {
            self.confirmButton.show();
            if (categories.length == 0) {
                selectEl.hide();
                return '';
            }
        }

        categories.each(function(category) {
            selectEl.appendChild(new Element('option',{'value': category.id}))
                    .update(category.title);
        });

        selectEl.observe('change',function(event) {
            self.parentId = this.value;
            self.showCategories(container.appendChild(new Element('div')));
        });
    },

    //----------------------------------

    getCategoryInfo: function(key,value)
    {
        var info = {};
        this.categories.each(function(category) {
            if (value == category[key]) {
                return info = category;
            }
        });
        return info;
    },

    removeContainers: function(container,doNotDeleteContainer)
    {
        container.childElements().each(function(child) {
            child.localName == 'div' && child != doNotDeleteContainer && container.removeChild(child)
        });
    },

    //----------------------------------

    confirmCategory: function()
    {
        this.categoriesContainer.hide();
        this.confirmButton.hide();
        this.specificHandler.specificsContainer.update();
        this.specificHandler.specificsMainContainer.hide();

        var categoryInfo = this.getCategoryInfo('id',this.parentId);
        var categoryPath = categoryInfo.path.replace(/->/g,' > ') + ' > ' + categoryInfo.title;

        if (!categoryInfo.xsd_hash) {
            return this.renderGetXsdHash(categoryInfo,categoryPath);
        }

        this.categoryPathHiddenInput.value = categoryPath;
        this.changeButton.show();
        this.categoriesTr.hide();

        $$('input[name=category[node_title]]').shift().value = this.nodeTitleEl.value;

        this.nodeTitleEl.value = '';
        this.categoryXsdHashHiddenInput.value = categoryInfo.xsd_hash;
        this.categoryIdentifiersHiddenInput.value = Object.toJSON({
            item_types: categoryInfo.item_types.evalJSON(),
            browsenode_id: categoryInfo.browsenode_id
        });

        this.specificHandler.run(categoryInfo.xsd_hash);
        this.browse_category.close();

        this.searchCategoryButton.parentNode.parentNode.hide();
        this.showSpanWithCategoryPath(categoryPath);
    },

    changeCategory: function()
    {
        this.changeButton.hide();
        this.confirmButton.show();
        this.removeSpanWithCategoryPath();
        this.categoriesContainer.show();
        this.searchCategoryButton.parentNode.parentNode.show();
        this.categoryPathHiddenInput.value = '';
        this.specificHandler.xsd_hash = '';
    },

    //----------------------------------

    removeSpanWithCategoryPath: function()
    {
        var span = this.changeButton.previous('span');
        span && span.remove();
    },

    //----------------------------------

    renderGetXsdHash: function(categoryInfo,categoryPath)
    {
        var self = this;
            self.xsdsTr.show();

        var old = $('xsds');
            old && old.remove();

        var select = new Element('select',{
            'id': 'xsds',
            'class': 'required-entry'
        });

        self.categoriesContainer.show();
        self.categoriesContainer.update(new Element('span',{
            'class': 'nobr',
            'style': 'font-weight: bold'
        }).update(categoryPath));

        $('xsd_hash_container').update(select);

        select.observe('change',function() {
            self.specificHandler.specificsMainContainer.hide();
            self.specificHandler.run(this.value);

            self.categoryIdentifiersHiddenInput.value = Object.toJSON({
                item_types: categoryInfo.item_types.evalJSON(),
                browsenode_id: categoryInfo.browsenode_id
            });
            self.categoryXsdHashHiddenInput.value = this.value;
            self.categoryPathHiddenInput.value = categoryPath;

            $$('input[name=category[node_title]]').shift().value = self.nodeTitleEl.value;

            self.nodeTitleEl.value = '';
            self.categoriesTr.hide();
            self.xsdsTr.hide();
            self.changeButton.show();
            self.searchCategoryButton.parentNode.parentNode.hide();
            self.browse_category.close();

            self.showSpanWithCategoryPath(categoryPath)
        });

        new Ajax.Request(self.M2ePro.url.getXsds,
        {
            method : 'get',
            asynchronous : true,
            parameters : {
                node_hash : categoryInfo.node_hash
            },
            onSuccess: function(transport) {
                select.update();
                select.appendChild(new Element('option',{'style': 'display: none'}));

                transport.responseText.evalJSON().each(function(xsd) {
                    select.appendChild(new Element('option',{'value': xsd.hash}))
                          .insert(xsd.title);
                });
            }
        });
    },

    //----------------------------------

    confirmAttributeSets: function()
    {
        AttributeSetHandlerObj.confirmAttributeSets();

        $$('select.attributes').each(function(el) {
            el.innerHTML = AttributeSetHandlerObj.attrData;
            el.value = '';
        });
    },

    //----------------------------------

    initPopUp: function(contentId,width,height,title)
    {
        this[contentId] = new Window({
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            top: 100,
            title: title,
            width: width,
            height: height,
            zIndex: 100,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        this[contentId].getContent().update($(contentId));
    },

    //----------------------------------

    searchClick: function()
    {
        $('error_block').hide();
        var keywords = this.searchKeywordsInput.value.trim();

        new Ajax.Request(this.M2ePro.url.searchCategory,
        {
            method : 'get',
            asynchronous : true,
            parameters : {
                keywords : keywords
            },
            onSuccess: (function(transport) {

                var response = transport.responseText;

                if (response.length == 0) {
                    this.resetSearchClick();
                    return $('error_block').show() && $('error_message').update(this.M2ePro.text.internal_error);
                }

                if (response.isJSON() && response.evalJSON().result == 'error') {
                    this.resetSearchClick();
                    return $('error_block').show() && $('error_message').update(response.evalJSON().message);
                }

                $('searchGrid').update(response);

            }).bind(this)
        });
    },

    resetSearchClick: function()
    {
        this.searchKeywordsInput.focus();
        this.searchKeywordsInput.value = '';
        $('searchGrid').update();
    },

    confirmSearchClick: function(categoryInfo)
    {
        this.changeButton.show();
        this.categoriesContainer.hide();
        this.confirmButton.hide();
        this.specificHandler.specificsContainer.update();
        this.specificHandler.specificsMainContainer.hide();

        var categoryPath = categoryInfo.path.replace(/->/g,' > ') + ' > ' + categoryInfo.title;

        this.categoryPathHiddenInput.value = categoryPath;

        this.categoriesTr.hide();

        $$('input[name=category[node_title]]').shift().value = this.nodeTitleEl.select('option[node_hash='+ categoryInfo.node_hash +']').shift().getAttribute('value');

        this.categoryXsdHashHiddenInput.value = categoryInfo.xsd_hash;
        this.categoryIdentifiersHiddenInput.value = Object.toJSON({
            item_types: categoryInfo.item_types,
            browsenode_id: categoryInfo.browsenode_id
        });
        this.specificHandler.run(categoryInfo.xsd_hash);
        this.search_category.close();

        this.searchCategoryButton.parentNode.parentNode.hide();
        this.showSpanWithCategoryPath(categoryPath);
        this.resetSearchClick();
    }

    //----------------------------------
});