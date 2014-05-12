<!-- Block gblogger tags -->
<div id="gblogger_block_left" class="block gblogger_tags_block">
	<h4 class="title_block">{l s='Blog tags' mod='gblogger'}</h4>
	<p class="block_content">
{if $tags}
	{foreach from=$tags item=tag name=myLoop}
		<a href="{$tag.link}" title="{l s='More about' mod='gblogger'} {$tag.name}" class="{$tag.class} {if $smarty.foreach.myLoop.last}last_item{elseif $smarty.foreach.myLoop.first}first_item{else}item{/if}">{$tag.name}</a>
	{/foreach}
{else}
	{l s='No tags have been founded.' mod='gblogger'}
{/if}
	</p>
</div>
<!-- /Block gblogger tags -->
