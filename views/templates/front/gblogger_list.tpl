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

                <div class="post-footer">
                    <span class="post-published">{l s='Published on' mod='gblogger'} {$post->published}</span>
                    {if $post->replies->totalItems > 0}
                        <span class="post-comments">
                <a href="{$post->url}#comments" title="{l s='Comments about' mod='gblogger'} {$post->title}">{$post->replies->totalItems} {l s='comment(s)' mod='gblogger'}</a>
            </span>
                    {/if}
                    {if $post->tags}
                        <div class="post-tags">
                            {l s='Tags:' mod='gblogger'}
                            {foreach from=$post->tags item=tag name=myLoop}
                                <span class="post-tags"><a href="{$tag.link}" title="{l s='More about' mod='gblogger'} {$tag.name}">{$tag.name}</a></span> ,
                            {/foreach}
                        </div>
                    {/if}
                </div>

                <div><a href="{$post->url}" title="{l s='More about' mod='gblogger'} {$post->title}">{l s='Full article' mod='gblogger'}</a></div>
            </li>
        {/foreach}
    </ul>
    {$paginator}
{else}
    {l s='No posts was founded!' mod='gblogger'}
{/if}
