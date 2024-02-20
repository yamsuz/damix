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
					}
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
                    }
                });
            });
			break;
		case 'popup':
			$( '.xform_popup .select2_simple' ).each( function(){
				$( this ).select2({
					placeholder: $( this ).attr( 'data-placeholder' ),
					allowClear: true,
					language:
					{
						noResults: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.aucunresultat' ); ?>';
						},
						searching: function(){
							return '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.select2.rechercheencours' ); ?>';
						}
					},
					dropdownParent: $( '.ui-dialog' )
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
                    dropdownParent: $( '.ui-dialog' )
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

$( document ).ready( function(){   
    pageload( 'page' );
});