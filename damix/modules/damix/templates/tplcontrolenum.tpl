<select {foreach $params as $name=>$value}{$name}="{$value}" {/foreach}>
	<option></option>
	{foreach $values as $key => $val}
	<option value="{$key}">{$val}</option>
	{/foreach}
</select>