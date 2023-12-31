/*
 * Cozmot
 *
 * NOTICE OF LICENSE
 * This source file is subject to the cozmot.com license that is
 * available through the world-wide-web at this URL:
 * https://cozmot.com/end-user-license-agreement
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Commerce
 * @package     Module
 * @copyright   Copyright (c) Cozmot (https://cozmot.com/)
 * @license     https://cozmot.com/end-user-license-agreement
 *
 */

define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function ($, url, alert) {
    'use strict';

    var htmlCodeMixin = {
        defaults: {
            editProductPageSelector: 'catalog-product-edit',
            newProductPageSelector: 'catalog-product-new',
            wysiwigDivSelector: '.admin__control-wysiwig',
            bodySelector: $("body")
        },

        isBtnVisible: function () {
            var isEnabled = window.isUpChatGptEnabled,
                isProductPage = $('body').hasClass(this.editProductPageSelector),
                isProductEditPage = $('body').hasClass(this.newProductPageSelector);
            if (isEnabled && (isProductPage || isProductEditPage)) {
                return true;
            }
            return false;
        },

        clickChatGptGenerateContent: function (data, event) {
            var self = this;
            var sku = $("input[name='product[sku]']").val();
            $.ajax({
                url: window.chatGptAjaxUrl,
                type: 'POST',
                showLoader: true,
                data: {
                    'form_key': FORM_KEY,
                    'sku': sku,
                    'type': 'full'
                },
                success: function (response) {
                    if (response.error == false) {
                        var targetField = event.currentTarget;
                        self._setChatGptContent(response.data, targetField);
                    } else {
                        alert({
                            title: $.mage.__('API Error'),
                            content: response.data
                        });
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log(errorThrown);
                }
            });
        },

        _setChatGptContent: function (content, targetField) {
            var descriptionField = $(targetField).parents(this.wysiwigDivSelector).next('textarea');
            descriptionField.val(content).change();
        }
    };

    return function (target) {
        return target.extend(htmlCodeMixin);
    };
});
