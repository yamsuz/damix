Element.prototype.val = function(p_value){
    if( this.value != undefined ) {
        if( p_value !== undefined ) {
            this.value = p_value;
            if( this.hasClass( 'xfrm_bool' ) || this.hasClass( 'xfrm_radio' ) ){
                this.checked = p_value == 1 ? true : false;
            }
            if( this.hasClass( 'xktfrm_radio' ) ){
                $(this).prop('checked', p_value == 1 ? true : false);
            }
            if( this.hasClass( 'select2_simple' ) ){
                let o = $(this);
				if( p_value == null || p_value == '' ){
					o.val(null).trigger('change');
				}
				else{
					o.val( p_value );
					if( p_value == null || o.find( 'option[value="' + p_value + '"]' ).length > 0 ){
						o.trigger('change');
					}
				}
            }
        }
        if( this.hasClass( 'xfrm_bool' ) || this.hasClass( 'xfrm_radio' ) ){
            return this.checked ? '1' : '0';
        }
        if( this.hasClass( 'xktfrm_radio' ) ){
            return $(this).prop('checked') ? 1 : 0;
        }
        return this.value;
    }
    if( this.textContent != undefined ){
        if( p_value !== undefined ) {
            this.textContent = p_value;
        }
        return this.textContent;
    }
    return '';
};


<?php
/**
 * Cette fonction permet vérifier l'existence d'une classe css
 */
?>Element.prototype.hasClass = function(className)
{
    var regex;
    if(this.classList != undefined) {
        return this.classList.contains(className);
    } else {
        regex = new RegExp('^(' + className + ')$', 'gi');
        if(this.className.search(regex) >= 0) {
            return true;
        }
        
        regex = new RegExp('^(' + className + ')(\\s+)', 'gi');
        if(this.className.search(regex) >= 0) {
            return true;
        }
        
        regex = new RegExp('(\\s+)(' + className + ')$', 'gi');
        if(this.className.search(regex) >= 0) {
            return true;
        }
        
        regex = new RegExp('(\\s+)(' + className + ')(\\s+)', 'gi');
        if(this.className.search(regex) >= 0) {
            return true;
        }
        
        return false;
    }
};


<?php
/**
 * Cette fonction permet d'ajouter une classe css
 */
?>Element.prototype.addClass = function(p_c)
{
    var c, i, j;
    for( j = 0; j < arguments.length; j++ ) {
        c = arguments[j].split(/\s/gi);
        for( i = 0; i < c.length; i++ ) {
            if(this.classList != undefined) {
                this.classList.add(c[i]);
            } else {
                if( !this.hasClass(c[i]) ) {
                    this.className += ' ' + c[i];
                }
            }
        }
    }
};

<?php
/**
 * Cette fonction permet de supprimer une class Css
 */
?>Element.prototype.removeClass = function(p_c)
{
    var r, c, i, j;
    for( j = 0; j < arguments.length; j++ ) {
        c = arguments[j].split(/\s/gi);
        for( i = 0; i < c.length; i++ ) {
            if(this.classList != undefined) {
                this.classList.remove(c[i]);
            } else {
                r = new RegExp('^(' + c[i] + ')$', 'gi');
                if(this.className.search(r) >= 0) {
                    this.className = this.className.replace(r, '');
                }
                
                r = new RegExp('^(' + c[i] + ')(\\s+)', 'gi');
                if(this.className.search(r) >= 0) {
                    this.className = this.className.replace(r, '');
                }
                
                r = new RegExp('(\\s+)(' + c[i] + ')$', 'gi');
                if(this.className.search(r) >= 0) {
                    this.className = this.className.replace(r, '');
                }
                
                r = new RegExp('(\\s+)(' + c[i] + ')(\\s+)', 'gi');
                if(this.className.search(r) >= 0) {
                    this.className = this.className.replace(r, ' ');
                }
            }
        }
    }
};


<?php
/**
 * Cette fonction permet d'ajouter un attribut
 */
?>Element.prototype.attr = function( p_n, p_v ) {
    if( p_v != undefined ) {
        if( this.setAttribute != undefined ) {
            this.setAttribute( p_n, p_v );
        }
    }
    if( this.getAttribute != undefined ) {
        return this.getAttribute( p_n );
    }
    return null;
};


<?php
/**
 * Cette fonction permet de supprimer un ou plusieurs attribut
 */
?>Element.prototype.removeAttr = function()
{
    var i;
    for( i = 0; i < arguments.length; i++ ) {
        this.removeAttribute( arguments[i] );
    }
};


<?php
/**
 * Cette fonction permet de récupérer et/ou modifier le texte interne de l'élément. Elle peut être redéfinie.
 * @param mixed p_text Texte
 * @return mixed
 */
?>Element.prototype.text = function( p_text ) {
    if( this.textContent != undefined ){
        if( p_text != undefined ) {
            this.textContent = p_text;
        }
        return this.textContent;
    }
    return '';
};


<?php
 /**
  * Permet de modifier et/ou récupérer une valeur CSS de l'objet
  * @param   string  p_n  Nom de la propriété CSS
  * @param   mixed   p_v  Nouvelle valeur de la propriété CSS
  * @param   bool    p_i  Importance de la propriété (true = importante, false = sans importance)
  * @return  string
  */
?>Element.prototype.css = function( p_n, p_v, p_i ) {
    var style;
    if (p_n) {
        if (p_i) {
            p_i = 'important';
        } else {
            p_i = '';
        }
        if (p_v != undefined) {
            this.style.setProperty(p_n, p_v, p_i);
        }
        style = this.style.getPropertyValue(p_n);
        if (style) {
            if( style == 'inherit' ) {
                return this.getComputedStyle(p_n);
            }
            return style;
        }
        this.style.setProperty(p_n, this.getComputedStyle(p_n), p_i);
        return this.style.getPropertyValue(p_n);
    }
    return '';
};

<?php
/**
 * Permet de lier un événement à l'élément, cet événement sera gardé en mémoire afin de pouvoir l'appeler plus tard
 * @param   string    n  Nom de l'événement
 * @param   function  h  Fonction événementielle
 * @param   bool      c  Utiliser la capture
 * @return  void
 */
?>Element.prototype.bind = function( n, h, c ){
    if( this.eventList == undefined ) { this.eventList = {}; }
    if( this.eventList[n] == undefined ) { this.eventList[n] = []; }
    if(this.eventList[n].indexOf(h) < 0){
        this.eventList[n].push(h);
        if( this.hasClass( 'select2_simple' ) )
        {
            $(this).on('select2:' + n, h );
        }
        else
        {
            if(this._addEventListener == undefined ) {
                this.addEventListener(n, h, c);
            } else {
                this._addEventListener(n, h, c);
            }
        }
    }
};

Element.prototype.unbind = function( n, h ){
    var i, j;
    if( this.eventList != undefined ) {
        if( this.hasClass( 'select2_simple' ) )
        {
            $(this).off('select2:' + n, h );
        }
		else
		{
			if( n == undefined && h == undefined ) {
				for( i in this.eventList ) {
					for( j = 0; j < this.eventList[i].length; j++ ) {
						this.removeEventListener( i, this.eventList[i][j] );
					}
					this.eventList[i] = [];
				}
			} else if( n != undefined && h == undefined ) {
				if( this.eventList[n] != undefined ) {
					for( j = 0; j < this.eventList[n].length; j++ ) {
						this.removeEventListener( n, this.eventList[n][j] );
					}
					this.eventList[n] = [];
				}
			} else if( n != undefined && h != undefined ) {
				if( this.eventList[n] != undefined ) {
					if( ( i = this.eventList[n].indexOf(h) ) > -1 ) {
						this.removeEventListener( n, this.eventList[n][i] );
						this.eventList[n].splice(i,1);
					}
				}
			}
        }
    }
};


HTMLSelectElement.prototype.selectFirst = function( vide )
{
    var n ;
    if( vide )
    {
        n = 0;
    }
    else
    {
        n = 1;
    }
    if( this.options.length == n + 1 ) {
         this.options[ n ].selected = true;
    }
};