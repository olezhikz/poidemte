{**
 * Основной шаблон компонента от него наследуются все шаблоны компонентов
 
 * @param string  $mods = "success" Список модификторов основного блока (через пробел)
 * @param string  $popover          Всплывающий контент на элементе
 * @param string  $classes          Список классов основного блока (через пробел)
 * @param array   $attr             Список атрибутов основного блока
 * @param string  $role             Вспомогательный атрибут role
 * @param string  $tag              Тег основного элемента
 *}
{strip}
    {block name="before_options"}{/block}
 
    {block 'options'}
        {component_define_params params=[ 
            'attr',  
            'classes',
            'mods',
            'role',
            'popover',
            'tag',
            'hook',
            'content'
        ]}

        {*   для отображения всплывающего элемента*}
        {if $popover}

            {if is_array($popover)}
                {foreach $popover as $popover_key => $popover_param}
                    {$attr["data-{$popover_key}"] = $popover_param}
                {/foreach}

            {else}
                {$attr["data-toggle"] = "popover"}
                {$attr["data-content"] = $popover}
                {$attr["data-placement"] = "top"}
                {$attr["data-trigger"] = "hover"}
            {/if}
        {/if} 

        {if $role}
            {$attr['role'] = $role}
        {/if}

    {/block}


    {if $hook}
        {hook 
            run         = $hook 
            params      = $params 
            array       = true 
            array_merge = true 
            assign      = 'params'}
    {/if}

    {if $component or $classes}
        {$attr.class = "{$component} $classes"}
    {/if}

    {if $component and $mods}

        {$attr.class = "{cmods name=$component mods=$mods delimiter="-"} {$attr.class}"}

    {/if}
    
    {$params.attr = $attr}

    {block name="before_content"}{/block}

    {block name="content"}{/block}

    {block name="after_content"}{/block}
{/strip}