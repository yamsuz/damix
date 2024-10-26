<div >
	<div class="damix-dt__content-filtre-bouton" style="display:inline-block">
		<div class="damix-dt__body-filtre">
			<div class="damix-dt__filtre" >
			{foreach $filtres as $filter }
				<div class="damix-dt__filtre_cell">
					<label class="damix-dt__label-filtre">{$filter[ 'header' ]}</label>
					<div>
						<div>
							<div class="input-group">
								{$filter[ 'html' ]}
								<div class="input-group-append">
									<button class="input-group-text btn-search" onclick="javascript:xscreen.list_load( '{$selector}' );"><span>{iconfont 'btn_recherche-filtre'}</span></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
			</div>
		</div>
	</div>

	{ifacl 'damix.datatable.menu' }
	<ul class="damix-dt-__config-menu">
		{ifacl 'damix.datatable.menu.export' }
		<li>
			<div>
				<span class="damix-ui-icon icon-save "></span>
				<a href="javascript:xscreen.list_export( '{$selector}' );" >
					{locale 'damix~lclcore.core.menu.filter.export'}
				</a>
			</div>
		</li>
		<li>
			<div>
				<span class="damix-ui-icon icon-111-refresh "></span>
				<a href="{url 'damix~datatableconfiguration:index', array( 's' => $selector )}" target="_blank" >
					{locale 'damix~lclcore.core.menu.datatable.configuration'}
				</a>
			</div>
		</li>
		{/ifacl}
	</ul>
	{/ifacl}
</div>


{literal}
<script type="text/javascript">
  $(document).ready(function(){
    $( ".damix-dt-__config-menu" ).menu();
  });
</script>
{/literal}