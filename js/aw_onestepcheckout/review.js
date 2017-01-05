/* CART SUMMARY */
AWOnestepcheckoutReviewCart = Class.create();
AWOnestepcheckoutReviewCart.prototype = {
    initialize: function(config){
        this.container = $$(config.containerSelector).first();
        this.wrapContainer = $$(config.wrapContainerSelector).first();
        this.cartContainer = $$(config.cartContainerSelector).first();
        this.overlayConfig = config.overlayConfig;
        this.timer = new AWOnestepcheckoutHelperTimer(Object.extend(config.timer, {
            overlayConfig: config.overlayConfig
        }));

        var me = this;
        var origFn = this.wrapContainer.addActionBlocksToQueueBeforeFn;
        this.wrapContainer.addActionBlocksToQueueBeforeFn = function() {
            origFn();
            me.timer.onTimerCancelElClick();
        }
    }
};

/* COUPON CODE */
AWOnestepcheckoutReviewCoupon = Class.create();
AWOnestepcheckoutReviewCoupon.prototype = {
    initialize: function(config) {
        // init dom elements
        this.msgContainer = $$(config.msgContainerSelector).first();
        this.couponCodeInput = $(config.couponCodeInput);
        // init urls
        this.applyCouponUrl = config.applyCouponUrl;
        // init messages
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.jsErrorMsg = config.jsErrorMsg;
        this.jsSuccessMsg = config.jsSuccessMsg;
        // init config
        this.isCouponApplied = config.isCouponApplied;
        // init "Apply Coupon Button"
        this.isApplyCouponButton = config.isApplyCouponButton;
        this.applyCouponButton = $$(config.applyCouponButtonSelector).first();
        this.cancelCouponButton = $$(config.cancelCouponButtonSelector).first();
        // init behaviour
        this.ajaxRequestId = 0;
        this.init();
    },
    init: function() {
        if (this.isApplyCouponButton) {
            if (this.applyCouponButton) {
                this.applyCouponButton.observe('click', this.applyCoupon.bind(this));
                this.cancelCouponButton.observe('click', this.applyCoupon.bind(this))
            }
        } else {
            if (this.couponCodeInput) {
                this.couponCodeInput.observe('change', this.applyCoupon.bind(this))
            }
        }
    },
    applyCoupon: function(e) {
        this.removeMsg();
        if (this.isApplyCouponButton) {
            if (!this.isCouponApplied) {
                this.couponCodeInput.addClassName('required-entry');
                var validationResult = Validation.validate(this.couponCodeInput)
                this.couponCodeInput.removeClassName('required-entry');
                if (!validationResult) {
                    return;
                }
            } else {
                this.couponCodeInput.setValue('');
            }
        } else {
            if (!this.couponCodeInput.getValue() && !this.isCouponApplied) {
                return;
            }
        }
        var me = this;
        this.ajaxRequestId++;
        var currentAjaxRequestId = this.ajaxRequestId;
        var requestOptions = {
            method: 'post',
            parameters: {
                coupon_code: this.couponCodeInput.getValue()
            },
            onComplete: function(transport){
                if (currentAjaxRequestId !== me.ajaxRequestId) {
                    return;
                }
                me._onAjaxCouponActionCompleteFn(transport);
            }
        };
        AWOnestepcheckoutCore.updater.startRequest(this.applyCouponUrl, requestOptions);
    },
    _onAjaxCouponActionCompleteFn: function(transport) {
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            this.showError(this.jsErrorMsg);
            return;
        }
        this.isCouponApplied = json.coupon_applied;
        if (json.success) {
            var successMsg = this.jsSuccessMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                successMsg = json.messages;
            }
            this.showSuccess(successMsg);
            if (this.isCouponApplied) {
                this.applyCouponButton.hide();
                this.cancelCouponButton.show();
            } else {
                this.applyCouponButton.show();
                this.cancelCouponButton.hide();
            }
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                errorMsg = json.messages;
            }
            this.showError(errorMsg);
        }
    },
    showError: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.errorMessageBoxCssClass, this.msgContainer);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.msgContainer, {
            style: {
                height: this.msgContainer.down().getHeight() + 'px'
            },
            duration: 0.3,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },
    showSuccess: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.successMessageBoxCssClass, this.msgContainer);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.msgContainer, {
            style: {
                height: this.msgContainer.down().getHeight() + 'px'
            },
            duration: 0.3,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },
    removeMsg: function() {
        if (this.msgContainer.down()) {
            var me = this;
            new Effect.Morph(this.msgContainer, {
                style: {
                    height: 0 + 'px'
                },
                duration: 0.3,
                afterFinish: function(e) {
                    AWOnestepcheckoutCore.removeMsgFromBlock(me.msgContainer, me.errorMessageBoxCssClass);
                    AWOnestepcheckoutCore.removeMsgFromBlock(me.msgContainer, me.successMessageBoxCssClass);
                }
            });
        }
    }
};

/* POINTS & REWARDS from AW */
AWOnestepcheckoutReviewPoints = Class.create();
AWOnestepcheckoutReviewPoints.prototype = {
    initialize: function(config) {
        // init dom elements
        this.container = $$(config.containerSelector).first();
        this.msgContainer = $$(config.msgContainerSelector).first();
        this.pointsAmountInput = $(config.pointsAmountInput);
        // init urls
        this.applyPointsUrl = config.applyPointsUrl;
        // init messages
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.jsErrorMsg = config.jsErrorMsg;
        this.jsSuccessMsg = config.jsSuccessMsg;
        // init validator
        this.amountValidationError = config.amountValidationError;
        // init config
        this.isPointsApplied = config.isPointsApplied;
        this.customerPoints = config.customerPoints;
        this.neededPoints = config.neededPoints;
        this.limitedPoints = config.limitedPoints;
        // init behaviour
        this.ajaxRequestId = 0;
        this.init();
    },
    init: function() {
        this.checkAmountValue();
        this.addAmountValidator();
        if (this.pointsAmountInput) {
            this.pointsAmountInput.observe('change', this.applyPoints.bind(this))
        }
    },
    applyPoints: function() {
        this.removeMsg();
        this.checkAmountValue();
        if ((this.pointsAmountInput.getValue() == 0) && !this.isPointsApplied) {
            return;
        }
        if (this.validatePointsAmount()) {
            var me = this;
            this.ajaxRequestId++;
            var currentAjaxRequestId = this.ajaxRequestId;
            var requestOptions = {
                method: 'post',
                parameters: {
                    points_amount: this.pointsAmountInput.getValue()
                },
                onComplete: function(transport) {
                    if (currentAjaxRequestId !== me.ajaxRequestId) {
                        return;
                    }
                    me._onAjaxCompleteFn(transport);
                }
            };
            AWOnestepcheckoutCore.updater.startRequest(this.applyPointsUrl, requestOptions);
        }
    },
    checkAmountValue: function() {
        if (!this.pointsAmountInput.getValue()) {
            this.pointsAmountInput.setValue(0)
        }
    },
    getAdvice: function() {
        if (this.container.select('.validation-advice').first()) {
            return this.container.select('.validation-advice').first()
        }
        return null;
    },
    hideAdvice: function() {
        var adviceElement = this.getAdvice();
        if (adviceElement) {
            adviceElement.setStyle({
                'overflow': 'hidden'
            });
            new Effect.Morph(adviceElement, {
                style: {
                    height: '0px'
                },
                duration: 0.2,
                afterFinish: function() {
                    adviceElement.setStyle({
                        'overflow' : '',
                        'height'   : '',
                        'display'  : 'none'
                    });
                }
            });
        }
    },
    showAdvice: function() {
        var adviceElement = this.getAdvice();
        if (adviceElement) {
            if (adviceElement.getStyle('display') == 'none') {
                adviceElement.setStyle({'visibility':'hidden', 'display':'block'});
                var adviceElementRealHeight = adviceElement.getHeight();
                adviceElement.setStyle({
                    'height'     : '0px',
                    'minHeight'  : '0px',
                    'display'    : '',
                    'visibility' : ''
                });
                new Effect.Morph(adviceElement, {
                    style: {
                        height: adviceElementRealHeight + 'px'
                    },
                    duration: 0.2
                });
            }
        }
    },
    getMaxAvailablePointsAmount: function() {
        return Math.min(this.customerPoints, this.neededPoints, this.limitedPoints);
    },
    validatePointsAmount: function() {
        this.pointsAmountInput
            .addClassName('validate-points-amount')
            .addClassName('points-amount-max-' + this.getMaxAvailablePointsAmount())
        ;
        var result = Validation.validate(this.pointsAmountInput);
        this.pointsAmountInput
            .removeClassName('validate-points-amount')
            .removeClassName('points-amount-max-' + this.getMaxAvailablePointsAmount())
        ;
        if (result) {
            this.hideAdvice();
        } else {
            this.showAdvice();
        }
        return result;
    },
    addAmountValidator: function() {
        Validation.add(
            'validate-points-amount',
            this.amountValidationError,
            function(value, element) {
                var reMax = new RegExp(/^points-amount-max-[0-9]+$/);
                var result = true;
                $w(element.className).each(function(name, index) {
                    if (name.match(reMax) && result) {
                        var maxAmount = name.split('-')[3];
                        result = (parseInt(value) <= parseInt(maxAmount));
                    }
                });
                return result;
            }
        );
    },
    _onAjaxCompleteFn: function(transport) {
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            this.showError(this.jsErrorMsg);
            return;
        }
        this.isPointsApplied = json.points_applied;
        if (json.success) {
            var successMsg = this.jsSuccessMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                successMsg = json.messages;
            }
            this.showSuccess(successMsg);
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                errorMsg = json.messages;
            }
            this.showError(errorMsg);
        }
    },
    showError: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.errorMessageBoxCssClass, this.msgContainer);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.msgContainer, {
            style: {
                height: this.msgContainer.down().getHeight() + 'px'
            },
            duration: 0.3,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },
    showSuccess: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.successMessageBoxCssClass, this.msgContainer);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.msgContainer, {
            style: {
                height: this.msgContainer.down().getHeight() + 'px'
            },
            duration: 0.3,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },
    removeMsg: function() {
        if (this.msgContainer.down()) {
            var me = this;
            new Effect.Morph(this.msgContainer, {
                style: {
                    height: 0 + 'px'
                },
                duration: 0.3,
                afterFinish: function(e) {
                    AWOnestepcheckoutCore.removeMsgFromBlock(me.msgContainer, me.errorMessageBoxCssClass);
                    AWOnestepcheckoutCore.removeMsgFromBlock(me.msgContainer, me.successMessageBoxCssClass);
                }
            });
        }
    }
};

/* REFER A FRINED from AW */
AWOnestepcheckoutReviewReferAFriend = Class.create();
AWOnestepcheckoutReviewReferAFriend.prototype = {
    initialize: function(config) {
        // init dom elements
        this.container = $$(config.containerSelector).first();
        this.msgContainer = $$(config.msgContainerSelector).first();
        this.discountAmountInput = $(config.discountAmountInput);
        // init urls
        this.applyDiscountUrl = config.applyDiscountUrl;
        // init messages
        this.successMessageBoxCssClass = config.successMessageBoxCssClass;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.jsErrorMsg = config.jsErrorMsg;
        this.jsSuccessMsg = config.jsSuccessMsg;
        // init validator
        this.amountValidationError = config.amountValidationError;
        // init config
        this.isDiscountApplied = config.isDiscountApplied;
        this.discountLimit = config.discountLimit;
        // init behaviour
        this.ajaxRequestId = 0;
        this.init();
    },
    init: function() {
        this.checkAmountValue();
        this.addAmountValidator();
        if (this.discountAmountInput) {
            this.discountAmountInput.observe('change', this.applyDiscount.bind(this))
        }
    },
    applyDiscount: function() {
        this.removeMsg();
        this.checkAmountValue();
        if ((this.discountAmountInput.getValue() == 0) && !this.isDiscountApplied) {
            return;
        }
        if (this.validateDiscountAmount()) {
            var me = this;
            this.ajaxRequestId++;
            var currentAjaxRequestId = this.ajaxRequestId;
            var requestOptions = {
                method: 'post',
                parameters: {
                    raf_discount_amount: this.discountAmountInput.getValue()
                },
                onComplete: function(transport) {
                    if (currentAjaxRequestId !== me.ajaxRequestId) {
                        return;
                    }
                    me._onAjaxCompleteFn(transport);
                }
            };
            AWOnestepcheckoutCore.updater.startRequest(this.applyDiscountUrl, requestOptions);
        }
    },
    checkAmountValue: function() {
        var value = parseFloat(this.discountAmountInput.getValue()).toFixed(2);
        if (!this.discountAmountInput.getValue() || isNaN(value)) {
            this.discountAmountInput.setValue('0.00');
        } else {
            this.discountAmountInput.setValue(value);
        }
    },
    getAdvice: function() {
        if (this.container.select('.validation-advice').first()) {
            return this.container.select('.validation-advice').first()
        }
        return null;
    },
    hideAdvice: function() {
        var adviceElement = this.getAdvice();
        if (adviceElement) {
            adviceElement.setStyle({
                'overflow': 'hidden'
            });
            new Effect.Morph(adviceElement, {
                style: {
                    height: '0px'
                },
                duration: 0.2,
                afterFinish: function() {
                    adviceElement.setStyle({
                        'overflow' : '',
                        'height'   : '',
                        'display'  : 'none'
                    });
                }
            });
        }
    },
    showAdvice: function() {
        var adviceElement = this.getAdvice();
        if (adviceElement) {
            if (adviceElement.getStyle('display') == 'none') {
                adviceElement.setStyle({'visibility':'hidden', 'display':'block'});
                var adviceElementRealHeight = adviceElement.getHeight();
                adviceElement.setStyle({
                    'height'     : '0px',
                    'minHeight'  : '0px',
                    'display'    : '',
                    'visibility' : ''
                });
                new Effect.Morph(adviceElement, {
                    style: {
                        height: adviceElementRealHeight + 'px'
                    },
                    duration: 0.2
                });
            }
        }
    },
    validateDiscountAmount: function() {
        this.discountAmountInput
            .addClassName('validate-discount-amount')
            .addClassName('discount-amount-max-' + this.discountLimit)
        ;
        var result = Validation.validate(this.discountAmountInput);
        this.discountAmountInput
            .removeClassName('validate-discount-amount')
            .removeClassName('discount-amount-max-' + this.discountLimit)
        ;
        if (result) {
            this.hideAdvice();
        } else {
            this.showAdvice();
        }
        return result;
    },
    addAmountValidator: function() {
        Validation.add(
            'validate-discount-amount',
            this.amountValidationError,
            function(value, element) {
                var reMax = new RegExp(/^discount-amount-max-([^$]+)$/);
                var result = true;
                $w(element.className).each(function(name, index) {
                    if (name.match(reMax) && result) {
                        var maxAmount = name.split('-')[3];
                        result = parseFloat(parseFloat(value).toFixed(2)) <= parseFloat(parseFloat(maxAmount).toFixed(2));
                    }
                });
                return result;
            }
        );
    },
    _onAjaxCompleteFn: function(transport) {
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            this.showError(this.jsErrorMsg);
            return;
        }
        this.isDiscountApplied = json.discount_applied;
        if (json.success) {
            var successMsg = this.jsSuccessMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                successMsg = json.messages;
            }
            this.showSuccess(successMsg);
        } else {
            var errorMsg = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                errorMsg = json.messages;
            }
            this.showError(errorMsg);
        }
    },
    showError: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.errorMessageBoxCssClass, this.msgContainer);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.msgContainer, {
            style: {
                height: this.msgContainer.down().getHeight() + 'px'
            },
            duration: 0.3,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },
    showSuccess: function(msg, afterShowFn){
        AWOnestepcheckoutCore.showMsg(msg, this.successMessageBoxCssClass, this.msgContainer);
        //add effect for height change
        var afterShowFn = afterShowFn || new Function();
        new Effect.Morph(this.msgContainer, {
            style: {
                height: this.msgContainer.down().getHeight() + 'px'
            },
            duration: 0.3,
            afterFinish: function(e){
                afterShowFn();
            }
        });
    },
    removeMsg: function() {
        if (this.msgContainer.down()) {
            var me = this;
            new Effect.Morph(this.msgContainer, {
                style: {
                    height: 0 + 'px'
                },
                duration: 0.3,
                afterFinish: function(e) {
                    AWOnestepcheckoutCore.removeMsgFromBlock(me.msgContainer, me.errorMessageBoxCssClass);
                    AWOnestepcheckoutCore.removeMsgFromBlock(me.msgContainer, me.successMessageBoxCssClass);
                }
            });
        }
    }
};

/* COMMENTS or DDAN from AW */
AWOnestepcheckoutReviewComments = Class.create();
AWOnestepcheckoutReviewComments.prototype = {
    initialize: function(config) {
        this.container = $$(config.containerSelector).first();
        this.newRowCount = config.newRowCount||5;
        this.saveValuesUrl = config.saveValuesUrl;

        var me = this;
        this.container.select('textarea').each(function(textarea) {
            textarea.setStyle({
                'overflow-y': 'hidden'
            });
            me.initShowEffectObserver(textarea);
        });
        Form.getElements(this.container).each(function(element){
            element.observe('change', me.requestToValuesSave.bind(me));
        });
    },

    requestToValuesSave: function(e) {
        new Ajax.Request(this.saveValuesUrl, {
            method: 'post',
            parameters: Form.serialize(this.container, true)
        });
    },

    initShowEffectObserver: function(textarea) {
        var originalScrollHeight = textarea.scrollHeight;
        var originalRowCount = parseInt(textarea.getAttribute('rows'));
        var originalHeight = parseInt(textarea.getStyle('height'));

        var me = this;
        textarea.observe('focus', function(e){
            var currentRowCount = originalRowCount +
                (((textarea.scrollHeight - originalScrollHeight) * originalRowCount) /  originalHeight);
            if (currentRowCount < me.newRowCount) {
                currentRowCount = me.newRowCount;
            } else {
                currentRowCount++; //add on empty line
            }
            var currentHeight = (originalHeight/originalRowCount)*currentRowCount;
            me.doChangeRowsAttributeEffect(textarea, currentRowCount, currentHeight, function(){
                textarea.setStyle({
                    'overflow-y': 'auto'
                });
            });
        });
        textarea.observe('blur', function(e){
            var lengthOfValue = textarea.getValue().strip().length;
            if (lengthOfValue === 0) {
                me.doChangeScrollOfTextareaEffect(textarea, function(){
                    textarea.setStyle({
                        'overflow-y': 'hidden'
                    });
                    me.doChangeRowsAttributeEffect(textarea, originalRowCount, originalHeight);
                });
            } else {
                var newHeight = (originalHeight/originalRowCount)*me.newRowCount;
                me.doChangeScrollOfTextareaEffect(textarea, function(){
                    textarea.setStyle({
                        'overflow-y': 'hidden'
                    });
                    me.doChangeRowsAttributeEffect(textarea, me.newRowCount, newHeight);
                });
            }
        });
    },

    doChangeRowsAttributeEffect: function(textarea, newRows, newHeight, afterFinish) {
        if (textarea.effect) {
            textarea.effect.cancel();
        }
        var afterFinish = afterFinish||new Function();
        textarea.effect = new Effect.Morph(textarea, {
            style: {
                height: newHeight + "px"
            },
            duration: 0.5,
            afterFinish:function() {
                textarea.setAttribute('rows', newRows);
                delete textarea.effect;
                afterFinish();
            }
        });
    },

    doChangeScrollOfTextareaEffect: function(textarea, afterFinish) {
        if (textarea.effect) {
            textarea.effect.cancel();
        }
        var afterFinish = afterFinish||new Function();
        if (textarea.scrollTop === 0) {
            afterFinish();
            return;
        }
        new Effect.Tween(textarea, textarea.scrollTop, 0, {
            duration: 0.5,
            afterFinish:function() {
                afterFinish();
            }
        }, 'scrollTop');
    }
};

/* NEWSLETTER or Advanced Newsletter from AW */
AWOnestepcheckoutReviewNewsletter = Class.create();
AWOnestepcheckoutReviewNewsletter.prototype = {
    initialize: function(config) {
        this.container = $$(config.containerSelector).first();
        this.generalInput = $$(config.generalInputSelector).first();
        this.segmentsContainer = $$(config.segmentsContainerSelector).first();
        this.saveValuesUrl = config.saveValuesUrl;

        if (this.generalInput) {
            this.generalInput.observe('click', this.onSubscriptionChecked.bind(this));
        }
        var me = this;
        Form.getElements(this.container).each(function(element){
            element.observe('click', me.requestToSaveValues.bind(me));
        });
    },

    requestToSaveValues: function(e) {
        new Ajax.Request(this.saveValuesUrl, {
            method: 'post',
            parameters: Form.serialize(this.container, true)
        })
    },

    onSubscriptionChecked: function(e) {
        var me = this;
        if (this.segmentsContainer) {
            if (this.generalInput.getValue()) {
                this.showSegments();
            } else {
                this.hideSegments();
            }
        }
    },

    showSegments: function() {
        this._changeHeightToWithEffect(this._collectRealSegmentsHeight());
    },

    hideSegments: function() {
        this._changeHeightToWithEffect(0);
    },

    _changeHeightToWithEffect: function (height) {
        var me = this;
        if (this.effect) {
            this.effect.cancel();
        }
        this.effect = new Effect.Morph(this.segmentsContainer, {
            style: {'height': height + "px"},
            duration: 0.5,
            afterEffect: function(){
                delete me.effect;
            }
        });
    },

    _collectRealSegmentsHeight: function() {
        var originalHeightStyle = this.segmentsContainer.getStyle('height');
        this.segmentsContainer.setStyle({'height': 'auto'});
        var realHeight = this.segmentsContainer.getHeight();
        this.segmentsContainer.setStyle({'height': originalHeightStyle});
        return realHeight;
    }
};

/* TERMS & CONDITIONS */
AWOnestepcheckoutReviewTerms = Class.create();
AWOnestepcheckoutReviewTerms.prototype = {
    initialize: function(config) {
        this.container = $$(config.containerSelector).first();
        this.items = $$(config.itemsSelector);
        this.linkFromItemSelector = config.linkFromItemSelector;
        this.checkboxFromItemSelector = config.checkboxFromItemSelector;
        this.descriptionContainerFromItemSelector = config.descriptionContainerFromItemSelector;
        this.popup = {};
        this.popup.container = $$(config.popup.containerSelector).first();
        this.popup.contentContainer = $$(config.popup.contentContainerSelector).first();
        this.popup.acceptContainer = $$(config.popup.acceptContainerSelector).first();
        this.popup.acceptBtn = $$(config.popup.acceptBtnSelector).first();
        this.popup.closeEl = $$(config.popup.closeElSelector).first();
        this.popup.overlay = $$(config.popup.overlaySelector).first();
        this.initObservers();
    },

    initObservers: function() {
        var me = this;
        this.items.each(function(item){
            var link = item.select(me.linkFromItemSelector).first();
            var description = item.select(me.descriptionContainerFromItemSelector).first();
            if (!link || !description) {
                return;
            }
            link.observe('click', function(e){
                me.currentItem = item;
                me.showPopupWithDescription(description.innerHTML);
            });
        });
        this.popup.closeEl.observe('click', this.onCloseClick.bind(this));
        this.popup.acceptBtn.observe('click', this.onAcceptClick.bind(this));
    },

    showPopupWithDescription: function(descriptionText) {
        this._updatePopup(descriptionText);
        this.popup.container.setStyle({'display': 'block', 'left' : '9999999px'});
        this._resizePopup();
        this.popup.container.setStyle({'display': 'none'});
        this._showOverlay();
        this._showPopup();
        Event.observe(window, 'resize', this._resizePopup.bind(this));
    },

    onOverlayClick: function(e) {
        this.onCloseClick(e);
    },

    onAcceptClick: function(e) {
        if (this.currentItem) {
            var checkbox = this.currentItem.select(this.checkboxFromItemSelector).first();
            if (checkbox) {
                checkbox.checked = true;
            }
        }
        this.onCloseClick(e);
    },

    onCloseClick: function(e) {
        this._hideOverlay();
        this._hidePopup();
        Event.stopObserving(window, 'resize', this._resizePopup.bind(this));
    },

    _updatePopup: function(descriptionText) {
        this.popup.contentContainer.setStyle({'height': 'auto'});
        this.popup.contentContainer.update(descriptionText);
    },

    _resizePopup: function() {
        this.popup.container.setStyle({height: 'auto'});
        var top = (document.viewport.getHeight() - this.popup.container.getHeight())/2;
        var left = (document.viewport.getWidth() - this.popup.container.getWidth())/2;
        if (top < 50) {
            top = 50;
            this.popup.container.setStyle({
                height: (document.viewport.getHeight() - 100) + 'px'
            });
        }
        var contentHeight = this.popup.container.getHeight() - parseInt(this.popup.container.getStyle('padding-top')) -
            parseInt(this.popup.container.getStyle('padding-bottom')) - this.popup.acceptContainer.getHeight();
        this.popup.contentContainer.setStyle({'height': contentHeight + 'px'});
        this.popup.container.setStyle({
            left: left + 'px',
            top: top + 'px'
        });
    },

    _showOverlay: function() {
        this._applyShowEffect(this.popup.overlay);
        this.popup.overlay.observe('click', this.onOverlayClick.bind(this));
    },

    _hideOverlay: function() {
        this._applyHideEffect(this.popup.overlay);
        this.popup.overlay.stopObserving('click', this.onOverlayClick.bind(this));
    },

    _showPopup: function() {
        this._applyShowEffect(this.popup.container);
    },

    _hidePopup: function() {
        this._applyHideEffect(this.popup.container);
    },

    _applyShowEffect: function(el) {
        var originalStyle = {
            '-moz-opacity': (el.getStyle('-moz-opacity') || '1') + "",
            'opacity':  (el.getStyle('opacity') || '1') + "",
            'filter':  (el.getStyle('filter') || 'alpha(opacity=100)') + ""
        }
        el.setStyle({
            '-moz-opacity': '0',
            'opacity': '0',
            'filter': 'alpha(opacity=0)',
            'display': 'block'
        });
        new Effect.Morph(el, {
            style: originalStyle,
            duration: 0.3
        });
    },

    _applyHideEffect: function(el) {
        var originalStyle = {
            '-moz-opacity': (el.getStyle('-moz-opacity') || '1') + "",
            'opacity':  (el.getStyle('opacity') || '1') + "",
            'filter':  (el.getStyle('filter') || 'alpha(opacity=100)') + ""
        }
        new Effect.Morph(el, {
            style: {
                '-moz-opacity': '0',
                'opacity': '0',
                'filter': 'alpha(opacity=0)'
            },
            duration: 0.3,
            afterFinish: function() {
                el.setStyle({
                    'display': 'none'
                });
                el.setStyle(originalStyle);
            }
        });
    }
};