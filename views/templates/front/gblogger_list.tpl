<div class="breadcrumb">
    <a title="{l s='Back home' mod='gblogger'}" href="{$base_dir}">{l s='Home' mod='gblogger'}</a>
    	<span class="navigation-pipe">&gt;</span><span class="navigation_end">{l s='Blog' mod='gblogger'}</span>
</div>
{if $gblogger_posts}
	<ul>
	{foreach from=$gblogger_posts item=post name=myLoop}
		<li>
    	<h2><a href="{$post->url}" title="{l s='More about' mod='gblogger'} {$post->title}">{$post->title}</a></h2>
    	{$post->content}
    	<div>{l s='Published on' mod='gblogger'} {$post->published}</div>

   		{if $post->tags}
		<div>
		{foreach from=$post->tags item=tag name=myLoop}
			<a href="{$tag.link}" title="{l s='More about' mod='gblogger'} {$tag.name}">{$tag.name}</a> ,
		{/foreach}
		</div>
		{/if}    	

    	<div><a href="{$post->url}" title="{l s='More about' mod='gblogger'} {$post->title}">{l s='Full article' mod='gblogger'}</a></div>
    	</li>
	{/foreach}
    </ul>
    {$paginator}
	{else}
		{l s='No posts was founded!' mod='gblogger'}
{/if}
