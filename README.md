composer
------------------------

Enter following commands to install/uninstall module:

```bash
cd MAGE2_ROOT_DIR
## INSTALL
composer config repositories.nord_shipfunk git git@github.com:Shipfunk/magento2-plugin.git
composer require nord/module-shipfunk:dev-master
bin/magento module:enable Nord_Shipfunk --clear-static-content
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
## UNINSTALL
bin/magento module:disable Nord_Shipfunk --clear-static-content
bin/magento module:uninstall Nord_Shipfunk --clear-static-content
bin/magento setup:static-content:deploy
composer remove nord/module-shipfunk
```


zip package
------------------------

Download zip package from [here](https://github.com/Shipfunk/magento2-plugin/archive/master.zip) and unzip into your *app/code/Nord/Shipfunk/* folder.

```bash
cd MAGE2_ROOT_DIR
## INSTALL
bin/magento module:enable Nord_Shipfunk --clear-static-content
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
## UNINSTALL
bin/magento module:disable Nord_Shipfunk --clear-static-content
rm -rf app/code/Nord/Shipfunk
bin/magento setup:static-content:deploy
```

database cleanup
-------------------------

If desired, execute these queries to remove database content produced by Shipfunk module

```sql
USE your_database_name;
DROP TABLE quote_selected_pickup;
DROP TABLE sales_order_selected_pickup;
DELETE FROM eav_attribute WHERE attribute_code LIKE 'shipfunk_%';
DELETE FROM setup_module WHERE module='Nord_Shipfunk';
```
