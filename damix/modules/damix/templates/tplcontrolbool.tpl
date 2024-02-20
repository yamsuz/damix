<select {foreach $params as $name=>$value}{$name}="{$value}" {/foreach}>
	<option value=""></option>
	<option value="1">{locale 'damix~lclcore.core.bool.oui'}</option>
	<option value="0">{locale 'damix~lclcore.core.bool.non'}</option>
</select>