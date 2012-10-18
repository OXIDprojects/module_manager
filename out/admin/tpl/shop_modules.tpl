[{if $diff}]
<html>
<head>
<link rel="stylesheet" href="[{$shop->basetpldir}]diff.css">
<link rel="stylesheet" href="[{$shop->basetpldir}]main.css">
</head>
<body>
[{if $numchanges <= 0}]
<h3>[{ oxmultilang ident="AG_MODULEMANAGER_NO_CHANGES" }]</h3>
[{else}]
[{$diff}]
[{/if}]
</body>
</html>
[{else}]

[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{ if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<script type="text/javascript">
<!--
function _groupExp(el) {
    var _cur = el.parentNode;

    if (_cur.className == "exp") _cur.className = "";
      else _cur.className = "exp";
}
//-->
<!--
function fspopup(url) 
{
 params  = 'width='+screen.width;
 params += ', height='+screen.height;
 params += ', top=0, left=0'
 params += ', fullscreen=yes';

 newwin=window.open(url,'diff', params);
 if (window.focus) {newwin.focus()}
 return false;
}
// -->
</script>



<form name="transfer" id="transfer" action="[{ $shop->selflink }]" method="post">
    [{ $shop->hiddensid }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="shop_modules">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="actshop" value="[{ $shop->id }]">
    <input type="hidden" name="updatenav" value="">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>


[{if $finishinstall}]

<form enctype="multipart/form-data" name="installmodule" id="installmodule" action="[{ $shop->selflink }]" method="post">
<h3>[{oxmultilang ident="AG_MODULEMANAGER_INSTALL_FINISHED"}]</h3>

[{ $shop->hiddensid }]
<input type="hidden" name="cl" value="shop_modules">
<input type="hidden" name="archfile" value="[{$archfile}]">
<input type="hidden" name="changedfull" value="[{$changedfull}]">
<input type="hidden" name="fnc" value="installfinish">
<input type="hidden" name="oxid" value="[{ $oxid }]">
<input type="hidden" name="editval[oxshops__oxid]" value="[{ $oxid }]">

[{if $log}]
 <div class="groupExp">
    <div>
        <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="AG_MODULEMANAGER_INSTALL_LOG" }]</b></a>
        <dl>
            <dt>
               
               [{foreach from=$log item=logentry}][{$logentry}]<br/>[{/foreach}] 
               
            </dt>
            <dd>
               
            </dd>
            <div class="spacer"></div>
        </dl>
     </div>
</div>
[{/if}]

[{if $changedfull}]

<h4>[{oxmultilang ident="AG_MODULEMANAGER_INSTALL_CHANGEDFULLLIST"}]</h4>
           
	[{foreach from=$changes item=change}]
		<input type="checkbox" name="changes[[{$change}]]" value="[{$change}]"><a href="#" onclick="fspopup('[{ $shop->selflink }]cl=shop_modules&changedfull=[{$changedfull}]&comp=[{$change}]&fnc=compare'); return false">[{$change}]</a></input>
	[{/foreach}] 

[{/if}]

<br/><br/>
<input type="submit" name="installfinish" value="[{ oxmultilang ident="AG_MODULEMANAGER_INSTALLFINISH" }]">

[{else}]

<form name="addmodule" id="addmodule" action="[{ $shop->selflink }]" method="post">
<h3>[{oxmultilang ident="AG_MODULEMANAGER_ADD"}]</h3>
[{ $shop->hiddensid }]
<input type="hidden" name="cl" value="shop_modules">
<input type="hidden" name="fnc" value="save">
<input type="hidden" name="oxid" value="[{ $oxid }]">
<input type="hidden" name="editval[oxshops__oxid]" value="[{ $oxid }]">
<table>
	<tr>
		<td>[{oxmultilang ident="AG_MODULEMANAGER_CNAME"}]</td>
		<td>[{oxmultilang ident="AG_MODULEMANAGER_MNAME"}]</td>
		<td colspan="2">[{oxmultilang ident="AG_MODULEMANAGER_SORT"}]</td>
	</tr>
	<tr>
		<td><input type="text" name="modulename"></td>
		<td><input type="text" name="modulevalue"></td>
		<td><input type="text" name="modulepos" size="8" value="0"></td>
		<td><input type="submit" name="save" value="[{ oxmultilang ident="GENERAL_SAVE" }]"></td>
	</tr>
</table>
</form>

<form enctype="multipart/form-data" name="installmodule" id="installmodule" action="[{ $shop->selflink }]" method="post">
<h3>[{oxmultilang ident="AG_MODULEMANAGER_INSTALL"}]</h3>



[{ $shop->hiddensid }]
<input type="hidden" name="cl" value="shop_modules">
<input type="hidden" name="fnc" value="install">
<input type="hidden" name="oxid" value="[{ $oxid }]">
<input type="hidden" name="editval[oxshops__oxid]" value="[{ $oxid }]">
<table>
	<tr>
		<td>[{oxmultilang ident="AG_MODULEMANAGER_COPYTHIS"}]</td>
		<td><input type="text" name="copythis" value="copy_this"></td>
	</tr>
	<tr>
		<td>[{oxmultilang ident="AG_MODULEMANAGER_CHANGEDFULL"}]</td>
		<td><input type="text" name="changedfull" value="changed_full"></td>
	</tr>
	<tr>
		<td>[{oxmultilang ident="AG_MODULEMANAGER_INSTALLFILE"}]</td>
		<td><input type="text" name="installfile" value="sql/install.sql"></td>
	</tr>
	<tr>	
		<td>[{oxmultilang ident="AG_MODULEMANAGER_MODULEFILE"}]</td>
		<td><input type="text" name="modulesfile" value="docs/modules.txt"></td>
	</tr>
	<tr>	
		<td>[{oxmultilang ident="AG_MODULEMANAGER_MODULEENTRIES"}]</td>
		<td><textarea name="modulesentries"></textarea></td>
	</tr>
	<tr>
		<td>[{oxmultilang ident="AG_MODULEMANAGER_FILE"}]</td>
		<td><input type="file" name="file"></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="install" value="[{ oxmultilang ident="AG_MODULEMANAGER_INSTALL" }]"></td>
	</tr>
</table>
</form>

<h3>[{ oxmultilang ident="AG_MODULEMANAGER_HEAD" }]</h3>

<table width="100%">
	<thead>
		<th align="left">[{ oxmultilang ident="AG_MODULEMANAGER_CLASS" }]</th>
		<th align="left">[{ oxmultilang ident="AG_MODULEMANAGER_MODULES" }]</th>
	</thead>
	<tbody>	
	
	[{assign var=rcol1 value="#f2f2f2"}]
	[{assign var=rcol2 value="#ffffff"}]
	
	[{foreach from=$modules item=moduleEntry}]
	[{if $rcol == $rcol1}][{assign var=rcol value=$rcol2}][{else}][{assign var=rcol value=$rcol1}][{/if}]
	<tr style="background-color:[{$rcol}]">
		<td valign="top"><strong>[{$moduleEntry->name}]</strong></td>
		<td align="left">
			<table>
			[{assign var=modCount value=1}]			
			[{foreach from=$moduleEntry->entries item=entry}]
			<tr align="left">
				<td align="left">#[{$modCount}]</td>
				<td>
					<form name="myedit" id="myedit" action="[{ $shop->selflink }]" method="post">
						[{ $shop->hiddensid }]
						<input type="hidden" name="cl" value="shop_modules">
						<input type="hidden" name="fnc" value="delete">
						<input type="hidden" name="oxid" value="[{ $oxid }]">
						<input type="hidden" name="delval" value="[{$entry}]">
						<input type="hidden" name="delmod" value="[{$moduleEntry->name}]">
						<input type="hidden" name="editval[oxshops__oxid]" value="[{ $oxid }]">
						
						<a onclick="this.parentNode.submit()" class="delete" title=""></a>
					
					</form>
					
				</td>
				<td align="left">[{$entry}]</td>
				
			</tr>
			[{assign var=modCount value=$modCount+1}]
			[{/foreach}]
			</table>
		</td>
	</tr>
	[{/foreach}]
	</tbody>
</table>

[{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
[{/if}]