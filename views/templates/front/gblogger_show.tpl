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

        <div class="post-footer">
            <span class="post-published">{l s='Published on' mod='gblogger'} {$post->published}</span>

            {if $post->tags}
                <div class="post-tags">
                    {l s='Tags:' mod='gblogger'}
                    {foreach from=$post->tags item=tag name=myLoop}
                        <span class="post-tags"><a href="{$tag.link}" title="{l s='More about' mod='gblogger'} {$tag.name}">{$tag.name}</a></span> ,
                    {/foreach}
                </div>
            {/if}
        </div>

        <div class="comments" id="comments">
            <a name="comments"></a>
            <h2>{$post->replies->totalItems} {l s='comment(s)' mod='gblogger'}:</h2>
            {if $post->replies->totalItems > 0}
                <div class="comments-content">

                </div>
            {/if}
        </div>

        {$paginator}
    {else}
        {l s='No post was founded!' mod='gblogger'}
    {/if}
</div>