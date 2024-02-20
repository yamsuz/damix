    

<div>
	{foreach $boutons as $bouton }
		{if $bouton['visible']}
			{if $bouton['acl'] != ''}
				{ifacl $bouton['acl']}
					{$bouton['html']}
				{/ifacl}
			{else}
				{$bouton['html']}
			{/if}
		{/if}
	{/foreach}
</div>
