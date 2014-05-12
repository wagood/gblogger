<!-- Block gblogger --> 
<div id="gblogger_block_left" class="block"> 
  <h4>{l s='Last blog posts' mod='gblogger'}</h4>
  <div class="block_content">
    {if $gblogger_posts}
    <ul>
	{foreach from=$gblogger_posts item=post name=myLoop}
		<li>
		<a href="{$post->url}"" title="{l s='More about' mod='gblogger'} {$post->title}">{$post->title}</a>
		</li>
	{/foreach}
	<li>
		<a href="{$gblogger_link_to_list}"" title="{l s='Full list of posts' mod='gblogger'}">{l s='Full list of posts' mod='gblogger'}</a>
	</li>
    </ul>
	{else}
		{l s='No posts was founded!' mod='gblogger'}
	{/if}
  </div>
</div>
<!-- /Block gblogger -->