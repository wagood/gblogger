<div class="breadcrumb">
    <a title="{l s='Back home' mod='gblogger'}" href="{$base_dir}">{l s='Home' mod='gblogger'}</a>
    	<span class="navigation-pipe">&gt;</span>
    	<span class="navigation_end"><a title="{l s='Blog' mod='gblogger'}" href="{$gblogger_link_to_list}">{l s='Blog' mod='gblogger'}</a></span>    	
   		<span class="navigation-pipe">&gt;</span>
   		<span class="navigation_end">{$tag}</span>
</div>
{if $gblogger_posts}
	<ul>
	{foreach from=$gblogger_posts item=post name=myLoop}
		<li>
    	<h2><a href="{$post->url}" title="{l s='More about' mod='gblogger'} {$post->title}">{$post->title}</a></h2>
    	{$post->content}
    	<div>{l s='Published on' mod='gblogger'} {$post->published}</div>
    	<div><a href="{$post->url}" title="{l s='More about' mod='gblogger'} {$post->title}">{l s='Full article' mod='gblogger'}</a></div>
    	</li>
	{/foreach}
    </ul>
    {$paginator}
	{else}
		{l s='No posts was founded!' mod='gblogger'}
{/if}
