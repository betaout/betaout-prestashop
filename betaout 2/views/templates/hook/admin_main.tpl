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

<script type="text/javascript" src="{$basedir |escape:'html'}../js/jquery/datepicker/jquery-ui-personalized-1.6rc4.packed.js"></script>
{literal}
<script type="text/javascript">
	$(document).ready(function() {
		$(".datepicker").datepicker({
			prevText:"",
			nextText:"",
			dateFormat:"yy-mm-dd"});
	});
</script>
{/literal}

<h2>{$name |escape:'html'}</h2>

{if $errors}
<div class="alert error">
	<img src="../modules/{$dirname |escape:'html'}/warning.gif" />
	<ul>
		{foreach from=$errors item=error}
			<li>{$error |escape:'html'}</li>
		{/foreach}
	</ul>
</div>
{/if}

<p>{$message |escape:'html'}</p>

{if $csv_data |escape:'html'}<pre>{print_r($csv_data |escape:'html')}</pre>{/if}

<form method="post" target="_blank" name="form2" action="/">
    <fieldset>
    	<legend>
    		<img src="../modules/{$dirname |escape:'html'}/logo_1.gif" />{l s='Export Orders to QuickBooks CSV'}
                
    	</legend>
    	
    	
        <label for="sdate">Start Date</label>
    	<div class="margin-form"><input type="text" name="sdate" id="sdate" class="" /></div>
	
    	<label for="edate">End Date</label>
    	<div class="margin-form"><input type="text" name="edate" id="edate" class="" /></div>
	
        <div class="margin-form"><input type="submit" name="submitFilter" value="{l s='Download CSV' mod='betaout'}" /></div>
        <p><a href="http://www.betaout.com" target="_new" >Copyright &copy;2015 Beataout </a></p>
    </fieldset>
</form>
