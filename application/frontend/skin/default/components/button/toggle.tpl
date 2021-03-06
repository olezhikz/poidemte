{**
 * Группа кнопок переключателей
 *
 * @param string  $classes        Список классов основного блока (через пробел)
 * @param array   $attributes     Список атрибутов основного блока
 * @param array   $items     Список атрибутов основного блока
 *}

{$component = "btn"}
 
{component_define_params params=[ 'items', 'classes', 'attributes', 'name', 'bmods' ]}

{block 'options' append}
    {* Название компонента *}
    {$component = "btn-group"}
    
    {$mods = "{$mods} toggle"}
    
    {$attr['data-toggle'] = "buttons"}
    
{/block}

{block 'button_toggle_content'}
    <div {cattr list=$attr}>
        {foreach $items as $item}
            <label class="{$item.classes} {if $item.checked}active{/if}" 
                   {cattr list=$item.attributes}>
                <input type="radio" name="{$name}" value="{$item.value}" {if $item.id}id="{$item.id}"{/if}
                       autocomplete="off" {if $item.checked}checked{/if}> 
                {if $item.icon}
                    {if is_array($item.icon)}
                        {component "icon" params=$item.icon}
                    {else}
                        {component "icon" icon=$item.icon display='s' classes="{if $text}mr-1{/if}"}
                    {/if}                    
                {/if}
                {$item.text}

            </label>
        {/foreach}
    </div>   
{/block}