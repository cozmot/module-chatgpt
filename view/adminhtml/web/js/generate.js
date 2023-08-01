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

require([
    'jquery'
], function ($) {
    'use strict';

    $(document).on('click', '.generate-chatgpt-short-content', function () {
        var descriptionField = $(this).parent().parent().find('iframe').contents().find('body');
        var sku = $("input[name='product[sku]']").val();
        var type = 'short';
        if ($(this).attr('id') == 'product_form_description_chatgpt') {
            type = 'full';
        }
        $.ajax({
            url: window.chatGptAjaxUrl,
            type: 'POST',
            showLoader: true,
            data: {
                'form_key': FORM_KEY,
                'sku': sku,
                'type': type
            },
            success: function (response) {
                if (response.error == false) {
                    var descriptionContent = '<p>' + response.data + '</p>';
                    descriptionField.html(descriptionContent).change();
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
    });
});
