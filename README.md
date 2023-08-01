# magento2-chatgpt

Cozmot ChatGPT Extension for Magento 2 for product description generation. This
extension empowers online store owners with a comprehensive set of functionalities to streamline the process and create
product descriptions.

#### 1.1 Install

```
composer require cozmot/module-chatgpt
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

#### 1.2 Upgrade

```
composer update cozmot/module-chatgpt
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

Run compile if your store in Product mode:

```
php bin/magento setup:di:compile
```

## Version
v1.0.0 Initial Commit
v1.0.1 Initial Code Commit
