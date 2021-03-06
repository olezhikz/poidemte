{**
 * Табы
 *
 * @param string   $id        Аттрибут id
 * 
 *}
 
{extends "component@component.layout"}
 
{block name="options" append}
    {component_define_params params=[ 
        'items', 
        'activeItem', 
        'id' 
    ]}
    
{/block}

{block 'content'}{strip}
    {component "nav"
        items = $params.items
    }    
{/strip}{/block}


{component_define_params params=[ 'items', 'activeItem', 'classes', 'attributes', 'bmods', 'id' ]}

{block 'tabs_list_content'}{strip}
    {$panes = []}
    <div class="nav {$classes} {cmods name="nav" mods=$bmods delimiter="-"} " id="nav-tab" role="tablist" {cattr list=$attributes}>
        {foreach $items as $item name="tabs"}
            {if $item.is_enabled|default:true}

                {$isActive = $item.active}
                {if $activeItem}
                    {$isActive = ($activeItem == $item.name) }
                {/if}

                <a class="nav-item nav-link {if $isActive}active{/if}"  {cattr list=$item.attributes}
                   id="nav-tab-{$id}{$item.name|default:$smarty.foreach.tabs.index}" 
                   {if {$item.url}}data-url="{$item.url}"{/if}
                   data-toggle="tab" 
                   href="#nav-pane-{$id}{$item.name|default:$smarty.foreach.tabs.index}" 
                   role="tab" 
                   aria-controls="nav-pane-{$item.name|default:$smarty.foreach.tabs.index}" 
                   aria-selected="{if $isActive}true{else}false{/if}">
                    {$item.text}
                    {if $item.badge}
                        {if is_array($item.badge)}
                            {component "badge" params=$item.badge}
                        {else}
                            {component "badge" text=$item.badge bmods="light"}
                        {/if}                    
                    {/if}
                </a>
            {/if}
        {/foreach}

    </div>    
{/strip}{/block}

