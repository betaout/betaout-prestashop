{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://betaout.com
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@betaout.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://betaout.com  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
   
</script>

{literal}
    <!-- Start of Betaout Code -->
    
    <!-- End of Betaout Code -->
{/literal}
{literal}
    <!-- Start of Betaout Code -->
    <script type="text/javascript">
        var _bout = _bout || [];
        var _boutAKEY = {/literal} "{$apiKey|escape:'htmlall':'UTF-8'}"{literal}, _boutPID = {/literal}"{$projectId|escape:'htmlall':'UTF-8'}"{literal}; 
        var d = document, f = d.getElementsByTagName("script")[0], _sc = d.createElement("script"); _sc.type = "text/javascript";
        _sc.async = true;
        _sc.src = "//d22vyp49cxb9py.cloudfront.net/jal-v2.min.js";
        f.parentNode.insertBefore(_sc, f);
        _bout.push(["identify", {
                 "customer_id":{/literal}"{$c_id|escape:'htmlall':'UTF-8'}"{literal},
                 "email":{/literal}"{$email|escape:'htmlall':'UTF-8'}"{literal},
                 "device_id":"",
                 "phone":{/literal}"{$phone|escape:'htmlall':'UTF-8'}"{literal}
               }
           ]);
    </script>
    <!-- End of Betaout Code -->
{/literal}





