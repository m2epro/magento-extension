window.AmazonTemplateDescription = Class.create(AmazonTemplateEdit, {

    // ---------------------------------------

    initialize: function()
    {
        var self = this;

        self.specificHandler = null;

        // ---------------------------------------
        self.categoryInfo = {};

        self.categoryPathHiddenInput            = $('category_path');
        self.categoryNodeIdHiddenInput          = $('browsenode_id');

        self.categoryProductDataNickHiddenInput = $('product_data_nick');
        // ---------------------------------------

        self.productDataNicksInfo = {};
        self.variationThemes      = [];

        // ---------------------------------------

        self.initValidation();
    },

    initValidation: function()
    {
        var self = this;

        self.setValidationCheckRepetitionValue('M2ePro-description-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another Policy. Policy Title must be unique.'),
                                                'Template_Description', 'title', 'id',
                                                M2ePro.formData.general.id,
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        Validation.add('M2ePro-validate-category', M2ePro.translator.translate('You should select Category and Product Type first'), function(value) {

            if (!self.isNewAsinAccepted()) {
                return true;
            }

            return $('category_path').value != '';
        });
    },

    // ---------------------------------------

    setSpecificHandler: function(object)
    {
        var self = this;
        self.specificHandler = object;
    },

    // ---------------------------------------

    isNewAsinAccepted: function()
    {
        return $('new_asin_accepted').value == 1;
    },

    checkMarketplaceSelection: function()
    {
        return $('marketplace_id').value != '';
    },

    checkSpecificsReady: function()
    {
        var self = AmazonTemplateDescriptionObj;

        if (!self.specificHandler.isReady()) {
            alert(M2ePro.translator.translate('You should select Category and Product Type first'));
            self.goToGeneralTab();
        }
    },

    //########################################

    duplicate_click: function($super, $headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-description-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another Policy. Policy Title must be unique.'),
                                                'Template_Description', 'title', '','',
                                                M2ePro.php.constant('Ess_M2ePro_Helper_Component_Amazon::NICK'));

        if (M2ePro.customData.category_locked) {

            M2ePro.customData.category_locked = false;
            this.hideCategoryWarning('category_locked_warning_message');
            $('edit_category_link').show();

            $('product_data_nick_select').removeAttribute('disabled');
        }

        if (M2ePro.customData.marketplace_locked) {

            M2ePro.customData.marketplace_locked = false;
            $('marketplace_locked_warning_message').remove();

            if (!M2ePro.customData.marketplace_force_set) {
                $('marketplace_hidden_input').remove();
                $('marketplace_id').removeAttribute('disabled');
            }
        }

        if (M2ePro.customData.new_asin_switcher_locked) {

            M2ePro.customData.new_asin_switcher_locked = false;
            $('new_asin_locked_warning_message').remove();

            if (!M2ePro.customData.new_asin_switcher_force_set) {
                $('new_asin_accepted_hidden_input').remove();
                $('new_asin_accepted').removeAttribute('disabled');
            }
        }

        $super($headId, M2ePro.translator.translate('Add Description Policy'));
    },

    // ---------------------------------------

    save_click: function($super, url, confirmText, templateNick)
    {
        var self = AmazonTemplateDescriptionObj;

        self.specificHandler.prepareSpecificsDataToPost();
        $super(url, confirmText, templateNick);
    },

    save_and_edit_click: function($super, url, tabsId, confirmText, templateNick)
    {
        var self = AmazonTemplateDescriptionObj;

        self.specificHandler.prepareSpecificsDataToPost();
        $super(url, tabsId, confirmText, templateNick);
    },

    //########################################

    onChangeMarketplace: function()
    {
        var self = AmazonTemplateDescriptionObj;
        self.resetCategory();
    },

    onClickEditCategory: function()
    {
        var self = AmazonTemplateDescriptionObj;

        if (!self.checkMarketplaceSelection()) {
            return alert(M2ePro.translator.translate('You should select Marketplace first.'));
        }

        AmazonTemplateDescriptionCategoryChooserObj.showEditCategoryPopUp();
    },

    onChangeProductDataNick: function()
    {
        var self = AmazonTemplateDescriptionObj;

        self.saveRecentProductDataNick(this.value);

        self.resetRequiredAttributesForProductType();
        self.setProductDataNick(this.value);
    },

    onChangeNewAsinAccepted: function()
    {
        var self = AmazonTemplateDescriptionObj;

        // ---------------------------------------
        var onlyAsinBlocks = $$('.hide-when-asin-is-disabled');

        onlyAsinBlocks.invoke('hide');
        if (this.value == 1) {

            onlyAsinBlocks.invoke('show');

            var worldWideIdMode = $('worldwide_id_mode');
            worldWideIdMode.simulate('change');

            if ($('registered_parameter').value == '' &&
                $('worldwide_id_custom_attribute').value == '') {
                worldWideIdMode.value = -1;
            }

            $('item_package_quantity_mode').simulate('change');
            $('number_of_items_mode').simulate('change');
        }
        // ---------------------------------------

        // set is required
        parseInt(this.value) ? $('category_path').addClassName('required-entry')
                             : $('category_path').removeClassName('required-entry');

        self.updateSpanRequirements($('category_path'), this.value);

        self.updateFieldRequirements($('manufacturer_mode'), this.value);
        self.updateFieldRequirements($('brand_mode'), this.value);
        self.updateFieldRequirements($('image_main_mode'), this.value);

        var chooser = $('number_of_items_mode');
        if (chooser.getAttribute('required_attribute_for_new_asin')) {
            if(this.value == 0) chooser.value = '';
            self.updateFieldRequirements(chooser, this.value, 'M2ePro-required-when-visible');
        }

        chooser = $('item_package_quantity_mode');
        if (chooser.getAttribute('required_attribute_for_new_asin')) {
            if(this.value == 0) chooser.value = '';
            self.updateFieldRequirements(chooser, this.value, 'M2ePro-required-when-visible');
        }
        // ---------------------------------------
    },

    // ---------------------------------------

    onChangeWorldwideId: function()
    {
        var target = $('worldwide_id_custom_attribute');

        target.value = '';
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE')) {
            AmazonTemplateDescriptionObj.updateHiddenValue(this, target);
        }
    },

    onChangeRegisteredParameter: function()
    {
        var worldwideIdMode = $('worldwide_id_mode'),
            noneOption      = worldwideIdMode.down('option'),
            currentValue    = worldwideIdMode.getAttribute('data-current-value');

        if (!this.value) {
            noneOption.hide();
            worldwideIdMode.simulate('change');

            if ($('worldwide_id_custom_attribute').value == '') {
                worldwideIdMode.selectedIndex = -1;
            }
        } else {
            noneOption.show();
            if (currentValue == '') {
                worldwideIdMode.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_NONE');
            }
            $('worldwide_id_custom_attribute').value = ''
        }
        worldwideIdMode.setAttribute('data-current-value', '');
    },

    // ---------------------------------------

    setCategory: function(categoryInfo, notSetProductTypeForceIfOnlyOne)
    {
        var self = this;
        notSetProductTypeForceIfOnlyOne = notSetProductTypeForceIfOnlyOne || false;

        this.categoryInfo = categoryInfo;

        this.categoryPathHiddenInput.value   = this.getInterfaceCategoryPath(categoryInfo);
        this.categoryNodeIdHiddenInput.value = categoryInfo.browsenode_id;

        this.updateCategoryPathSpan(this.getInterfaceCategoryPath(categoryInfo, true));

        this.updateAvailableProductTypes();

        if (self.categoryInfo.product_data_nicks.length == 1 && !notSetProductTypeForceIfOnlyOne) {
            self.setProductDataNickFromTree(self.categoryInfo.product_data_nicks[0]);
        }

        this.hideCategoryWarning('category_is_not_accessible_message');
    },

    setProductDataNickFromTree: function(productDataNick)
    {
        $$('#product_data_nick_select option').each(function(el) {

            var optGroup = $(el).up('optgroup');

            if (el.value == productDataNick && optGroup &&
                optGroup.getAttribute('is_recent') === null && optGroup.getAttribute('is_recommended') === null)
            {
                el.setAttribute('selected', 'selected');
                return true;
            }
        });

        AmazonTemplateDescriptionObj.setProductDataNick(productDataNick);
    },

    setProductDataNick: function(productDataNick)
    {
        var self = this;

        this.categoryProductDataNickHiddenInput.value = productDataNick;

        self.categoryInfo['required_attributes'] = {};

        $H(this.productDataNicksInfo).each(function(item){

            if (item.key == productDataNick) {
                self.categoryInfo['required_attributes'] = item.value.required_attributes;
                return true;
            }
        });

        this.updateRequiredAttributesForProductType();
        this.updateWarningMessagesVisibility();

        this.specificHandler.reset();
        this.specificHandler.run(this.categoryInfo, productDataNick);
    },

    saveRecentProductDataNick: function(productDataNick)
    {
        if (productDataNick == '') {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_template_description/saveRecentProductDataNick'), {
            method     : 'post',
            parameters : {
                marketplace_id:    $('marketplace_id').value,
                product_data_nick: productDataNick
            }
        });
    },

    // ---------------------------------------

    resetCategory: function()
    {
        this.categoryInfo = {};

        this.categoryPathHiddenInput.value   = '';
        this.categoryNodeIdHiddenInput.value = '';

        this.resetCategoryPathSpan();
        this.resetProductDataNick();

        this.hideCategoryWarning('category_variation_warning_message');
    },

    resetProductDataNick: function()
    {
        this.categoryProductDataNickHiddenInput.value = '';

        this.resetRequiredAttributesForProductType();

        $('product_data_nick_tr').hide();
        $('product_data_nick_select').update();

        this.specificHandler.reset();
    },

    // ---------------------------------------

    prepareEditMode: function()
    {
        var self = AmazonTemplateDescriptionObj;

        if (M2ePro.formData.general.product_data_nick == '' ||
            M2ePro.formData.general.browsenode_id == '' ||
            M2ePro.formData.general.category_path == '') {

            return;
        }

        var callback = function(transport) {

            if (!transport.responseText) {

                self.resetCategory();
                self.showCategoryWarning('category_is_not_accessible_message');

            } else {

                var categoryInfo = transport.responseText.evalJSON();

                self.setCategory(categoryInfo, true);
                var isProductTypeAvailable = self.isProductTypeAvailable(M2ePro.formData.general.product_data_nick);

                if (isProductTypeAvailable) {
                    self.setProductDataNickFromTree(M2ePro.formData.general.product_data_nick);
                } else {
                    self.specificHandler.resetFormDataSpecifics();
                }

                if (M2ePro.customData.category_locked) {

                    self.showCategoryWarning('category_locked_warning_message');
                    $('edit_category_link').hide();

                    $('product_data_nick_select').setAttribute('disabled', 'disabled');
                }

                if (!isProductTypeAvailable) {
                    self.showCategoryWarning('category_is_not_accessible_message');
                    $('product_data_nick_select').removeAttribute('disabled');
                }
            }
        };

        AmazonTemplateDescriptionCategoryChooserObj.getCategoryInfoFromDictionaryBrowseNodeId(
            M2ePro.formData.general.browsenode_id,
            M2ePro.formData.general.category_path,
            callback
        );
    },

    // ---------------------------------------

    showCategoryWarning: function(item)
    {
        var me = $(item);

        var atLeastOneWarningShown = $$('#category_warning_messages span.category-warning-item').any(function(obj) {
            return $(obj).id != me.id && $(obj).visible();
        });

        if (atLeastOneWarningShown && me.previous('span.additional-br')) {
            me.previous('span.additional-br').show();
        }

        $(item).show();
        $('category_warning_messages').show();
    },

    hideCategoryWarning: function(item)
    {
        var me = $(item);
        $(item).hide();

        var atLeastOneWarningShown = $$('#category_warning_messages .category-warning-item').any(function(obj) {
            return $(obj).visible();
        });

        if (me.previous('span.additional-br')) {
            me.previous('span.additional-br').hide();
        }

        !atLeastOneWarningShown && $('category_warning_messages').hide();
    },

    // ---------------------------------------

    updateCategoryPathSpan: function(path)
    {
        $('category_path_span').update(path);
    },

    resetCategoryPathSpan: function()
    {
        var span = $('category_path_span');
        span.innerHTML = '<span style="color: grey; font-style: italic">' + M2ePro.translator.translate('Not Selected') + '</span>';
    },

    resetRequiredAttributesForProductType: function()
    {
        this.resetManufacturerPartNumberRequired();
        this.resetTargetAudienceRequired();
        this.resetItemDimensionWeightRequired();
        this.resetItemPackageQuantityRequired();
        this.resetNumberOfItemsRequired();
    },

    updateRequiredAttributesForProductType: function()
    {
        this.updateManufacturerPartNumberRequired();
        this.updateTargetAudienceRequired();
        this.updateItemDimensionWeightRequired();
        this.updateItemPackageQuantityRequired();
        this.updateNumberOfItemsRequired();
    },

    updateWarningMessagesVisibility: function()
    {
        var self = AmazonTemplateDescriptionObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_template_description/getVariationThemes'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                marketplace_id:     $('marketplace_id').value,
                product_data_nick: self.categoryProductDataNickHiddenInput.value
            },
            onSuccess: function(transport) {

                self.variationThemes = transport.responseText.evalJSON();

                self.variationThemes.length == 0 ? self.showCategoryWarning('category_variation_warning_message')
                                                 : self.hideCategoryWarning('category_variation_warning_message');
            }
        });
    },

    //########################################

    resetManufacturerPartNumberRequired: function()
    {
        this.updateFieldRequirements($('manufacturer_part_number_mode'), 0);
    },

    resetItemDimensionWeightRequired: function()
    {
        var chooser = $('item_dimensions_weight_mode');
        chooser.down('option').show(); // 'None' option
    },

    resetTargetAudienceRequired: function()
    {
        var targetAudienceChooser = $('target_audience_mode');
        targetAudienceChooser.removeAttribute('disabled');

        var hiddenInput = targetAudienceChooser.up('td.value').down('input[type="hidden"]');
        hiddenInput && hiddenInput.remove();

        AmazonTemplateDescriptionDefinitionObj.forceClearElements('target_audience');
    },

    resetItemPackageQuantityRequired: function()
    {
        var chooser = $('item_package_quantity_mode');

        this.updateFieldRequirements(chooser, 0);
        chooser.removeAttribute('required_attribute_for_new_asin');
    },

    resetNumberOfItemsRequired: function()
    {
        var chooser = $('number_of_items_mode');

        this.updateFieldRequirements(chooser, 0);
        chooser.removeAttribute('required_attribute_for_new_asin');
    },

    // ---------------------------------------

    updateManufacturerPartNumberRequired: function()
    {
        if (!this.categoryInfo.required_attributes.hasOwnProperty('/DescriptionData/MfrPartNumber')) {
            return;
        }

        this.updateFieldRequirements($('manufacturer_part_number_mode'), 1);
    },

    updateTargetAudienceRequired: function()
    {
        if (!this.categoryInfo.required_attributes.hasOwnProperty('/DescriptionData/TargetAudience')) {
            return;
        }

        var targetAudienceChooser = $('target_audience_mode');

        targetAudienceChooser.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::TARGET_AUDIENCE_MODE_CUSTOM');
        targetAudienceChooser.simulate('change');
        targetAudienceChooser.setAttribute('disabled', 'disabled');

        targetAudienceChooser.up('td.value').appendChild(new Element('input', {
            name  : targetAudienceChooser.name,
            type  : 'hidden',
            value : M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::TARGET_AUDIENCE_MODE_CUSTOM')
        }));

        this.categoryInfo.required_attributes['/DescriptionData/TargetAudience'].each(function(value) {
            AmazonTemplateDescriptionDefinitionObj.forceFillUpElement('target_audience', value);
        });
    },

    updateItemDimensionWeightRequired: function()
    {
        if (!this.categoryInfo.required_attributes.hasOwnProperty('/DescriptionData/ItemDimensions/Weight')) {
            return;
        }

        var chooser = $('item_dimensions_weight_mode');
        chooser.down('option').hide(); // 'None' option
        if (chooser.value == M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_MODE_NONE')) {
            chooser.value = M2ePro.php.constant('Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_MODE_CUSTOM_VALUE');
        }

        chooser.simulate('change');
    },

    updateItemPackageQuantityRequired: function()
    {
        if (!this.categoryInfo.required_attributes.hasOwnProperty('/ItemPackageQuantity')) {
            return;
        }

        var chooser = $('item_package_quantity_mode');

        chooser.setAttribute('required_attribute_for_new_asin', 'true');

        if (this.isNewAsinAccepted()) {
            this.updateFieldRequirements(chooser, 1, 'M2ePro-required-when-visible');
        }
    },

    updateNumberOfItemsRequired: function()
    {
        if (!this.categoryInfo.required_attributes.hasOwnProperty('/NumberOfItems')) {
            return;
        }

        var chooser = $('number_of_items_mode');

        chooser.setAttribute('required_attribute_for_new_asin', 'true');

        if (this.isNewAsinAccepted()) {
            this.updateFieldRequirements(chooser, 1, 'M2ePro-required-when-visible');
        }
    },

    //########################################

    updateAvailableProductTypes: function()
    {
        var self = AmazonTemplateDescriptionObj;

        $('product_data_nick_tr').show();

        var renderingCallback = function(transport) {

            var response = transport.responseText.evalJSON();

            if (!response) {
                return;
            }

            var html = '';

            if (Object.keys(response['grouped_data']).length > 0) {

                $H(response['grouped_data']).each(function(data) {

                    var group            = data.key,
                        productDataNicks = data.value;

                    html += '<optgroup label="' + group + '">';

                    $H(productDataNicks).each(function(el) {
                        html += '<option value="' + el.value.product_data_nick + '">' + el.value.title + '</option>';
                    });

                    html += '</optgroup>';
                });
            }

            var recommendedHtml = '';

            if (self.categoryInfo.product_data_nicks.length > 0) {

                recommendedHtml += '<optgroup is_recommended="1" label="' + M2ePro.translator.translate('Recommended') + '">';

                self.categoryInfo.product_data_nicks.each(function(el){

                    var title = typeof response['product_data'][el] != 'undefined' ? response['product_data'][el]['group'] + ' > ' + response['product_data'][el]['title']
                                                                                   : el;

                    recommendedHtml += '<option value="' + el + '">' + title + '</option>';
                });

                recommendedHtml += '</optgroup>';
            }

            var recentHtml = '';

            if (Object.keys(response['recent_data']).length > 0) {

                recentHtml += '<optgroup is_recent="1" label="' + M2ePro.translator.translate('Recent') + '">';

                $H(response['recent_data']).each(function(data) {

                    var title = data.value['group'] + ' > ' + data.value['title'];
                    recentHtml += '<option value="' + data.key + '">' + title + '</option>';
                });

                recentHtml += '</optgroup>';
            }

            html = '<option style="display: none;"></option>' + recommendedHtml + recentHtml + html;
            $('product_data_nick_select').update(html);
        };

        new Ajax.Request(M2ePro.url.get('adminhtml_amazon_template_description/getAvailableProductTypes'), {
            method: 'post',
            asynchronous: false,
            parameters: {
                marketplace_id: $('marketplace_id').value,
                browsenode_id:  self.categoryNodeIdHiddenInput.value
            },
            onSuccess: function(transport) {

                self.productDataNicksInfo = transport.responseText.evalJSON()['product_data'];
                renderingCallback.call(self, transport);
            }
        });
    },

    isProductTypeAvailable: function(productType)
    {
        var result = false;

        $H(this.productDataNicksInfo).each(function(el){

            if (productType == el.key) {
                result = true;
                return true;
            }
        });

        return result;
    },

    //########################################

    updateSpanRequirements: function(element, dependence)
    {
        var label = element.up('tr').down('td.label').down('label');

        label.innerHTML = label.innerHTML.replace(' <span class="required">*</span>','');
        if (parseInt(dependence)) label.innerHTML += ' <span class="required">*</span>';
    },

    updateFieldRequirements: function(element, dependence, className)
    {
        className = className || 'required-entry';

        // ---------------------------------------
        var firstOption = element.select('option').first();
        if (firstOption.value == '0') {

            firstOption.show();

            var hiddenOpt = element.select('option.hidden-opt').first();
            hiddenOpt && hiddenOpt.remove();

            if (parseInt(dependence)) {

                firstOption.hide();

                element.appendChild(new Element('option', {
                    style: 'display: none;',
                    class: 'hidden-opt'
                }));
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        if (parseInt(dependence) && element.value == 0) {
            element.value = '';
        }
        // ---------------------------------------

        // ---------------------------------------
        parseInt(dependence) ? element.addClassName(className)
                             : element.removeClassName(className);
        // ---------------------------------------

        element.simulate('change');
        this.updateSpanRequirements(element, dependence);
    },

    // ---------------------------------------

    getInterfaceCategoryPath: function(categoryInfo, withBrowseNodeId)
    {
        withBrowseNodeId = withBrowseNodeId || false;

        var path = categoryInfo.path != null ? categoryInfo.path.replace(/>/g,' > ') + ' > ' + categoryInfo.title
                                             : categoryInfo.title;

        return !withBrowseNodeId ? path : path + ' ('+categoryInfo.browsenode_id+')';
    },

    // ---------------------------------------

    goToGeneralTab: function()
    {
        amazonTemplateDescriptionEditTabsJsTabs.showTabContent($('amazonTemplateDescriptionEditTabs_general'));
    }

    // ---------------------------------------
});