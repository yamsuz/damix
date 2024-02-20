<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/?>/*source jqueryui.dialog*/

var popup = new winpop();

function winpop()
{
    this._instance = {};
    
    this.load = function( o ){
        let p = {};
        
        p.s = o.selector;
        
        $ajax.jhjc( 
            '<?php echo \damix\core\urls\Url::getPath( 'damix~forms:load' );?>',            
            p,
            function( doc )
            {
                if( $ajax.erreur( doc ) )
                {
                
                    let d, b = $( 'body' );
                    d = document.createElement( 'div' );
                    
                    d.id = o.id;
                    b.append( d );
                    d.innerHTML = doc.html;
                    
                    xevent.call( o.selector, 'loaded', {} );
                }
            }
        );
        
    };
    this.get = function( s ){
        if( this._instance[ s ] == undefined )
        {
            this._instance[ s ] = {};
            this._instance[ s ].selector = s;
            this._instance[ s ].id = s.replace( /~/g, '_' );
            this._instance[ s ].buttons = [];
        
            let i = this._instance[ s ];
           
            xevent.bind( s, 'loaded', function(){
				$( '#' + i.id ).addClass( 'xform_popup' );
				let o = $( '#' + i.id );
				if( o && o.dialog )
				{
					i.object = o.dialog({autoOpen: false});
					xevent.call( s, 'get', i );
				}
            });
            this.load( i );
        }
        else
        {
            this._instance[ s ].buttons = [];
            xevent.call( s, 'get', this._instance[ s ] );
        }
    };
    
    this.bind = function( s, a, h ){
        xevent.bind( s, a, h );
    };
    
    this.unbind = function( s, a, h ){
        xevent.unbind( s, a, h );
    };
    
    this.show = function( s, p ){
    
        xevent.unbind( s, 'get' );
        xevent.bind( s, 'get', function( o ){
            let d, w;
            w = o.object;
			
            d = {};
            d.title = p.title;
            d.modal = p.modal != undefined ? p.modal : true;
            d.resizable = p.resizable != undefined ? p.resizable : true;
            if( p.height ) d.height = p.height;
            if( p.width ) d.width = p.width;        
            d.buttons = o.buttons;
            if( p.validated != undefined )
            {
                d.buttons.push( {
					text: p.validated.text != undefined ? p.validated.text : '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.valider.label' ); ?>',
					title: p.validated.text != undefined ? p.validated.text : '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.valider.title' ); ?>',
					icon: p.validated.icon != undefined ? p.validated.icon : '',
					class: p.validated.class != undefined ? p.validated.class : 'btn btn-damix-vert1 damix-dt_btn-action',
					click: function(){
						xevent.call( s, 'validated', {'id' : s} );
					}
				});
            }
			else
			{
				d.buttons.push( {
					text: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.valider.label' ); ?>',
					title: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.valider.title' ); ?>',
					icon: '',
					class: 'btn btn-damix-vert1 damix-dt_btn-action',
					click: function(){
						xevent.call( s, 'validated', {'id' : s} );
					}
				});
			}
            if( p.closed != undefined )
            {
                d.buttons.push( {
					text: p.closed.text != undefined ? p.closed.text : '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.annuler.label' ); ?>',
					title: p.closed.text != undefined ? p.closed.text : '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.annuler.title' ); ?>',
					icon: p.closed.icon != undefined ? p.closed.icon : 'ui-icon-closethick',
					class: p.closed.class != undefined ? p.closed.class : 'btn btn-damix-rouge2  damix-dt_btn-action',
					click: p.closed.handler != undefined ? p.closed.handler : function(){w.dialog( 'close' );}
				});
            }
			else
			{
				d.buttons.push( {
					text: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.annuler.label' ); ?>',
					title: '<?php echo \damix\engines\locales\Locale::get( 'damix~lclicon.button.annuler.title' ); ?>',
					icon: '',
					class: 'btn btn-damix-rouge2 damix-dt_btn-action',
					click: function(){w.dialog( 'close' );}
				});
			}
			
            w.dialog( d );
            
            w.dialog( 'open' );
            pageload( 'popup' );
	
            xevent.call( s, 'opened', {'id' : s} );
        });
        
        this.get( s );
        
    };
    
    
    this.close = function( s ){
    
        xevent.bind( s, 'get', function( o ){
            let d, w;
            w = o.object;
        
            w.dialog( 'close' );
        });
        
        this.get( s );
    };
}
