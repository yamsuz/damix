<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/?>function _ajax(){};

_ajax.prototype.error = function( data ){
    console.dir( data );
};

_ajax.prototype.json = function( url, data, handler ){
    
    $.ajax({
        url: url,
        data : data,
        dataType : 'json',
        type :'POST',
        success : function(d){
            handler( d );
        },
        error : this.error
    });
};
_ajax.prototype.jhjc = function( url, data, handler ){
    
    $.ajax({
        url: url,
        data : data,
        dataType : 'json',
        type :'POST',
        success : function(d){
            if( d )
            {
                if( d.jhjc.js != undefined )
                {
                    let js  = d.jhjc.js;
					if( js.length > 0 )
					{
						d.js = js.length;
						for( let i = 0; i < js.length; i ++)
						{
							importJS( js[i], function(){
								d.js --;
								
								if( d.js == 0)
								{
									handler( d );
								}
							});
						}
					}
					else
					{
						handler( d );
					}
				}
            }
           
        },
        error : this.error
    });
};

_ajax.prototype.erreur = function(doc)
{
    var i;
    if(!doc)return false;
    if( doc )
    {
        if( doc.error )
        {
            if( doc.error instanceof Array )
            {
                var m = '', i = 0;
                for( i = 0; i < doc.error.length; i ++)
                {
                    m += doc.error[i]['locale'] + "<br>";
                }
                // alert( m );
				alert_error( m );
            }
            else
            {
                // alert( doc.error );
				alert_error( doc.error );
            }
            return false;
        }
    }
    return true;
}
_ajax.prototype.params = function(s)
{
    let out = new FormData();
    
    if( s instanceof Object )
    {
        for( var i in s )
        {
            if( s[i] instanceof Array )
            {
                for( var j = 0; j < s[i].length; j ++ )
                {
                    if( s[i][j] instanceof Object )
                    {
                        out.append( '@' + i + '[]', JSON.stringify(s[i][j]) );
                    }
                    else
                    {
                        out.append( i + '[]', s[i][j] );
                    }
                }
            }
            else if( s[i] instanceof FileList )
            {
                for( var j = 0; j < s[i].length; j ++ )
                {
                    if( s[i][j] instanceof File )
                    {
                        out.append( i + '[]', s[i][j] );
                    }
                }
            }
            else if( s[i] instanceof File )
            {
                out.append( i, s[i] );
            }
            else if( s[i] instanceof Object )
            {
                out.append( '@' + i, JSON.stringify(s[i]) );
            }
            else
            {
                out.append( i, s[i] );
            }
        }
    }
    return out;
};


var $ajax = new _ajax();