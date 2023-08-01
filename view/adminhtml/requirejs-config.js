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

var config = {
    map: {
        '*': {
            'Magento_PageBuilder/template/form/element/html-code.html':
                'Cozmot_ChatGpt/template/html-code.html'
        }
    },
    config: {
        mixins: {
            'Magento_PageBuilder/js/form/element/html-code': {
                'Cozmot_ChatGpt/js/html-code-mixin': true
            }
        }
    }
};
