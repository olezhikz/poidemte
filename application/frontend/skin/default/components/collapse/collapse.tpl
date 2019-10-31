{**
 * Коллапс
 *}
 
{extends "component@collapse.layout"}

{block 'options' append}
    {$id = "collapse{math equation='rand()'}"}
    
{/block}

{block "content" append}{$params|print_r}
    {if $button}
        {if is_array($button)}
            {component "button" params=$button}
        {else}
            {component "button" 
                text = $button
                attr = [
                    'data-toggle'   => "collapse",
                    'data-target'   => "#{$id}", 
                    role            => "button",
                    'aria-expanded' => "false", 
                    'aria-controls' => $id
                ]}
        {/if}
    {/if}
    
    <div class="collapse {$classes}" {cattr list=$attr}>
        {$content}
    </div>

{/block}
