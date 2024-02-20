<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/?>function xFormsManager()
{
    this.id = '';
    this.form = [];
    this.updated = false;
	
	this.getform = function(){
		return $( '#' + this.id );
	};
    
    this.setValuesFromObject = function( values, group, element, prefix, suffix )
    {
        let i;
        if( values ) {
            for( i in values )
            {
                this.setValue( i, values[i], group, element, prefix, suffix);
            }
        }
    };
    
	this.clear = function()
	{
        let o, element = $( '#' + this.id );
        
		for( i in this.form )
		{
			o = this.getElement( this.form[i].name, element );
			if( o )
			{
				switch( o.nodeName )
				{
					case 'INPUT':
					case 'TEXTAREA':
					case 'SELECT':
						o.val( '' );
						break;
					default:
						console.dir( o );
				}
			}
		}
	};
	
    this.setValue = function( name, value, group, element, prefix, suffix )
    {
        let el;
        if( element == undefined )
        {
            element = $( '#' + this.id );
        }
        if( prefix == undefined )
        {
            prefix = '';
        }
        if( suffix == undefined )
        {
            suffix = '';
        }
        
        el = this.getElement(  prefix + name + suffix, element, group );
        if( el )
        {
            el.val( value );
        }
    };
    
    this.getElement = function( name, element, group )
    {
        var el;
        if( element == undefined ) {
            element = $( '#' + this.id );
        }
        else if( typeof element == 'string' ) {
            element = this.getElement( element );
        }
        el = element.find( '[name="' + name + '"]'  + ( group ? '.xfrm_gp_' + group : '' ));
        if( el && el.length > 0 ) { return el.get(0); }
        
        if( name.substring( name.length - 2, name.length ) != '[]' )
        {
            el = element.find( '#' + name  + ( group ? '.xfrm_gp_' + group : '' ) );
            if( el && el.length > 0 ) { return el.get(0); }
        }
        return null;
    };
    
    this.getValue = function( name, element )
    {
        var e = this.getElement( name, element );
        if( e ) { return e.val(); }
        return '';
    };
    
    this.getParams = function( group, element, all )
    {
        return this.baseGetParamsObject( 'form', group, element, all );
    };
    
    this.baseGetParamsObject = function( property, group, element, all )
    {
        var el, els, p, i, j, name, g, n;
        
        if( typeof element == 'string' ) {
            element = this.getElement( element );
        }
        
        (all === undefined ? all = true : null);
        
        p = {};

        for( i = 0; i < this[property].length; i++ )
        {            
			name = this[property][i].name;
            if( name.match( /\[\]$/gi ) )
            {
                els = $( ( ! element ? '#' + this.id : '' ) + ' [name="' + name + '"]' + ( group ? '.xfrm_gp_' + group : '' ), element );
				
                if( els.length > 0 )
                {
                    if( els[0].type == 'select-multiple' )
                    {
                        name = name.substring( 0, name.length-2 );
                        
                        p[ name ] = [];
                        for( j = 0; j < els[0].selectedOptions.length; j++ )
                        {
                            p[ name ].push( els[0].selectedOptions[j].val() );
                        }
                    }
                    else
                    {
                        if( p.detail === undefined )
                        {
                            p.detail = [];
                        }
                        
                        for( j = 0; j < els.length; j++ )
                        {
                            if( p.detail[j] === undefined )
                            {
                                p.detail[j] = {};
                            }
                            
                            if(  els[j].hasClass( 'xfrm_bool' ) ){
                                n = els[j].id;
                            }
                            else
                            {
                                n = name.substring( 0, name.length-2 );
                            }
                            
                            p.detail[j][ n ] = els[j].val();
                        }
                    }
				}
            }
            else
            {
                el = $( ( ! element ? '#' + this.id : '' ) + ' [name="' + name + '"]' + ( group ? '.xfrm_gp_' + group : '' ), element );
                if( el.length > 0 ) 
                {
                    if( el.length == 1 ) 
                    {
                        if( el[0].hasClass( 'xfrm_radio' ) ){
                            p[ el[0].id ] = el[0].checked ? 1 : 0;
                        }
                        else{
                            p[ name ] = el[0].val(); 
                        }
                    }
                    else if( el.length > 1 || all == 1 ) 
                    {
                        p[ name ] = [];
                        for( j = 0; j < el.length; j++ )
                        {
                            if( el[j].hasClass( 'xfrm_radio' ) ){
                                p[ el[j].id ] = el[j].checked ? 1 : 0;
                            }
                            else{
                                p[ el[j].id ] = [];
                                p[ el[j].id ] = el[j].val();
                            }
                        }
                    }
                }
            }
        }
        
        return p;
    };
   
    this.check = function ( doc, element ){
        let o, c = doc.check, a = '';
        $( '.xfrm_check_error' ).removeClass('xfrm_check_error' );
        $( '.xfrm_row_check_error' ).removeClass('xfrm_row_check_error' );
        if( doc.check != undefined && doc.check.length > 0 )
        {
            for( let i = 0; i < c.length; i ++)
            {
                o = this.getElement( c[i].property, element );
                o.addClass( 'xfrm_check_error' );
                $(o).parents('.form-group.row').addClass( 'xfrm_row_check_error' );
                a += c[i].locale + "<br />";
            }
            alert_error( a );
            return false;
        }
        
        return true;
    };
}