
<div class="damix-dt__content-filtre-bouton">
	<div class="damix-dt__body-filtre">
		<div class="damix-dt__filtre" >
		{foreach $filtres as $filter }
			<div class="damix-dt__filtre_cell">
				<label class="damix-dt__label-filtre">{$filter[ 'header' ]}</label>
				<div class="row">
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