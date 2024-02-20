{if $operator == 'period' }
&nbsp;<label class="damix-dt__sublabel-filtre">{locale 'damix~lclcore.core.period.du'}</label>&nbsp;
<input type="text" {foreach $params as $name=>$value}{$name}="{$value}" {/foreach} autocomplete="off"/>
&nbsp;<label class="damix-dt__sublabel-filtre">{locale 'damix~lclcore.core.period.au'}</label>&nbsp;
<input type="text" {foreach $params as $name=>$value}{$name}="{$value}" {/foreach} autocomplete="off" />
{else}
<input type="text" {foreach $params as $name=>$value}{$name}="{$value}" {/foreach} />
{/if}