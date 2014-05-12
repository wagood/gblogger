<div class="breadcrumb">
    <a title="{l s='Back home' mod='gblogger'}" href="{$base_dir}">{l s='Home' mod='gblogger'}</a>
    	<span class="navigation-pipe">&gt;</span>
    	<span class="navigation_end"><a title="{l s='Blog' mod='gblogger'}" href="{$gblogger_link_to_list}">{l s='Blog' mod='gblogger'}</a></span>    	
    	{if $post}
    		<span class="navigation-pipe">&gt;</span>
    		<span class="navigation_end">{$post->title}</span>
    	{/if}
</div>
<div class="block_box_center">
    {if $post}
	<div id="block_content" class="content_block">
    	<h2>{$post->title}</h2>
    	{$post->content}
	</div>
   	<div>{l s='Published on' mod='gblogger'} {$post->published}</div>

	{if $post->tags}
	<div>
	{foreach from=$post->tags item=tag name=myLoop}
		<a href="{$tag.link}" title="{l s='More about' mod='gblogger'} {$tag.name}">{$tag.name}</a> ,
	{/foreach}
	</div>
	{/if}    	

   		
    {$paginator}
    {else}
		{l s='No post was founded!' mod='gblogger'}
	{/if}
</div>