{**
 * Табы
 *
 * @param array     $items
 * @param string    $activeItem
 * @param string    $justify        Горизонтальное выравнивание
 * @param bool      $vertical       
 * 
 *}
{component_define_params params=[ 'items', 'classes', 'attributes', 'bmods', 'id' ]}

{block 'panes_content'}{strip}
       
    <div class="tab-content {$contentClasses} {cmods name="nav" mods=$bmods delimiter="-"}" id="nav-tabContent">
        {foreach $items as $item name="panes"}
            {$isActive = $item.active}
            {if $activeItem}
                {$isActive = ($activeItem == $item.name) }
            {/if}
            {if $item.is_enabled|default:true}
                <div class="tab-pane fade {if $isActive}show active{/if} {$item.paneClasses}" 
                     id="nav-pane-{$id}{$item.name|default:$smarty.foreach.panes.index}" 
                     role="tabpanel" 
                     aria-labelledby="nav-tab-{$id}{$item.name|default:$smarty.foreach.panes.index}">
                    {$item.content}
                </div>
            {/if}
        {/foreach}
    </div>
{/strip}{/block}

