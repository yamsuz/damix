<?php 
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/
    $dir = \damix\core\urls\Url::getBasePath();
	\damix\engines\logs\log::log( $dir );
?>

function importJS( src, h )
{
    if( $( 'script[src="' + src + '"]' ).length == 0 )
    {
        let s = document.createElement( 'script' );
        s.setAttribute( 'src', src );
        s.setAttribute( 'type', 'text/javascript' );
		s.onload = h;
		s.onerror = function() {
			alert("Error loading " + this.src); 
		};
        document.head.append(s);
		
        return s;
    }
    if( h )
    {
        h();
    }
    
    return false;
}

function pageload( type )
{
	switch( type )
	{
		case 'page':
			$( '.select2_simple' ).each( function(){
				$( this ).select2({
					placeholder: $( this ).attr( 'data-placeholder' ),
					allowClear: true,
					width: 'style',
					language: 
					{
						noResults: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.aucunresultat' ); ?>';
						},
						searching: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.rechercheencours' ); ?>';
						}
					},
					width: 'style' 
				});
			});
            $( '.select2_multiple' ).each( function(){
                $( this ).select2({
                    placeholder: $( this ).attr( 'data-placeholder' ),
					maximumSelectionLength: $( this ).attr( 'selectmultiplemax' ),
                    allowClear: true,
                    language:
					{
                        noResults: function(){
                            return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.aucunresultat' ); ?>';
                        },
						searching: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.rechercheencours' ); ?>';
						},
						maximumSelected: function(){
							return "<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.nombreselectionmax' ); ?>";
						}
                    },
					width: 'style' 
                });
            });
			$('.select2-remote').each(function(){
				$(this).select2({
					ajax: {
						url: '<?php echo \damix\core\urls\Url::getPath( 'damix~forms:selectdata' );?>',
						dataType: 'json',
						delay: 250,
						type: "POST",
						data: function (params) {
							return {
								selector: this.attr('damix-select'),
								q: params.term, // search term
								page: params.page
							};
						},
						processResults: function (data, params) {
							params.page = params.page || 1;

							return {
								results: data.items,
								pagination: {
									more: (params.page * 30) < data.total_count
								}
							};
						},
					},
					placeholder: $( this ).attr( 'data-placeholder' ),
					minimumInputLength: 1,
					language: {
						"noResults" : function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.noResults' ); ?>';
						},
						"loadingMore" : function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.loadingMore' ); ?>';
						},
						"inputTooShort" : function(e){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.inputTooShort' ); ?>';
						},
						searching: function() {
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.searching' ); ?>';
						},
					},
				});
			});
			break;
		case 'popup':
			$( '.xform_popup .select2_simple' ).each( function(){
				$( this ).select2({
					placeholder: $( this ).attr( 'data-placeholder' ),
			
					language:
					{
						noResults: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.aucunresultat' ); ?>';
						},
						searching: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.rechercheencours' ); ?>';
						}
					},
					dropdownParent: $( '.ui-dialog' ),
					width: 'style' 
				});
			});
            $( '.xform_popup .select2_multiple' ).each( function(){
                $( this ).select2({
                    placeholder: $( this ).attr( 'data-placeholder' ),
					maximumSelectionLength: $( this ).attr( 'selectmultiplemax' ),
                    allowClear: false,
                    language:
					{
                        noResults: function(){
                            return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.aucunresultat' ); ?>';
                        },
						searching: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.rechercheencours' ); ?>';
						},
						maximumSelected: function(){
							return "<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.nombreselectionmax' ); ?>";
						}
                    },
                    dropdownParent: $( '.ui-dialog' ),
					width: 'style' 
                });
            });
			$('.select2-remote').each(function(){
				$(this).select2({
					ajax: {
						url: '<?php echo \damix\core\urls\Url::getPath( 'damix~forms:selectdata' );?>',
						dataType: 'json',
						delay: 250,
						type: "POST",
						data: function (params) {
							return {
								selector: this.attr('damix-select'),
								q: params.term, // search term
								page: params.page
							};
						},
						processResults: function (data, params) {
							params.page = params.page || 1;

							return {
								results: data.items,
								pagination: {
									more: (params.page * 30) < data.total_count
								}
							};
						},
					},
					dropdownParent: $( '.ui-dialog' ),
					placeholder: $( this ).attr( 'data-placeholder' ),
					minimumInputLength: 1,
					language: {
						"noResults" : function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.noResults' ); ?>';
						},
						"loadingMore" : function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.loadingMore' ); ?>';
						},
						"inputTooShort" : function(e){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.inputTooShort' ); ?>';
						},
						searching: function() {
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.searching' ); ?>';
						},
					},
				});
			});
			break;
		default:
			break;
	}
	
	
	
	let o = $( '.datepicker' );
	if( o && o.datepicker)
	{
		o.datepicker( "destroy" );
		o.datepicker({
			dateFormat: '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.format' )); ?>',
			language: '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.language' )); ?>',
			closeText: '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.closetext' )); ?>',
			prevText: '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.prevtext' )); ?>',
			nextText: '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.nexttext' )); ?>',
			currentText: '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.currenttext' )); ?>',
			monthNames: [ '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.janvier' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.fevrier' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.mars' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.avril') ); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.mai' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.juin' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.juillet' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.aout' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.septembre' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.octobre' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.novembre' )); ?>', '<?php echo str_replace('\'', '\\\'', \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnames.decembre' )); ?>' ],
			monthNamesShort: [ '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.janvier' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.fevrier' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.mars' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.avril' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.mai' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.juin' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.juillet' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.aout' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.septembre' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.octobre' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.novembre' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.monthnamesshort.decembre' ); ?>' ],
			dayNames: [ '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.dimanche' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.lundi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.mardi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.mercredi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.jeudi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.vendredi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynames.samedi' ); ?>', ],
			dayNamesShort: [ '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.dimanche' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.lundi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.mardi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.mercredi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.jeudi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.vendredi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesshort.samedi' ); ?>', ],
			dayNamesMin: [ '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.dimanche' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.lundi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.mardi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.mercredi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.jeudi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.vendredi' ); ?>', '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.daynamesmin.samedi' ); ?>', ],
			weekHeader: '<?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.weekheader' ); ?>',
			showWeek: true,
			firstDay: <?php echo \damix\engines\locales\Locale::get( \damix\engines\tools\xDate::LOCALE_SELECTOR . '.local.datepicker.firstday' ); ?>,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: "" 
		});
	}
	o = $( '.timepicker' );
	
	if( o && o.timepicker )
	{
		o.timepicker({});
	}
	
	
	
}

function alert_error( msg )
{
	let d, b = $( 'body' );
	
	d = document.getElementById('alert_error');
	if( !d )
	{
		d = document.createElement( 'div' );
		d.id = 'alert_error';
		b.append( d );
	}
	
	if( msg instanceof Array )
	{
		var m = '', i = 0;
		for( i = 0; i < msg.length; i ++)
		{
			m += msg[i] + "<br>";
		}
		msg = m;
	}
	
	d.innerHTML = msg;
	
	$( "#alert_error" ).dialog({
		title: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.popup.error.title' ); ?>',
		modal: true,
		buttons: [{
			text: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.ok.label' ); ?>',
			title: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.ok.title' ); ?>',
			icon: '',
			class: 'btn btn-damix-vert1 damix-dt_btn-action',
			click: function(){$( this ).dialog( "close" );}
		}]
    });
}


$( document ).ready( function(){   
    pageload( 'page' );
});