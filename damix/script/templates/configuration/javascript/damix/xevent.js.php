<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/?>var xevent = {};


xevent.bind = function( pid, pn, phandler ){
    if( this.events == undefined ) {
        this.events = {};
    }
    if( this.events[ pid ] == undefined ) {
        this.events[ pid ] = {};
    }
    if( this.events[ pid ][ pn ] == undefined ) {
        this.events[ pid ][ pn ] = [];
    }
    return this.events[ pid ][ pn ].push( phandler );
};

xevent.unbind = function( pid, pn, ph ){
    var i, j, k, e;
    e = this.events;
    if( e == undefined ) { return; }
    if( pid == undefined ) {
        if( pn == undefined ) {
            if( ph == undefined ) {
                e = {};
            } else {
                for( i in e ) {
                    for( j in e[i] ) {
                        for( k = 0; k < e[i][j].length; k++ ) {
                            if( e[i][j][k] == ph ) {
                                e[i][j].splice( k, 1 );
                                k--;
                            }
                        }
                    }
                }
            }
        } else {
            if( ph == undefined ) {
                for( i in e ) {
                    for( j in e[i] ) {
                        if( j == pn ) {
                            e[i][pn] = [];
                        }
                    }
                }
            } else {
                for( i in e ) {
                    for( j in e[i] ) {
                        if( j == pn ) {
                            for( k = 0; k < e[i][j].length; k++ ) {
                                if( e[i][j][k] == ph ) {
                                    e[i][j].splice( k, 1 );
                                    k--;
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        if( e[pid] != undefined ) {
            if( pn == undefined ) {
                if( ph == undefined ) {
                        e[pid] = {};
                } else {
                    for( j in e[pid] ) {
                        for( k = 0; k < e[pid][j].length; k++ ) {
                            if( e[pid][j][k] == ph ) {
                                e[pid][j].splice( k, 1 );
                                k--;
                            }
                        }
                    }
                }
            } else {
                if( ph == undefined ) {
                    for( j in e[pid] ) {
                        if( j == pn ) {
                            e[pid][pn] = [];
                        }
                    }
                } else {
                    for( j in e[pid] ) {
                        if( j == pn ) {
                            for( k = 0; k < e[pid][j].length; k++ ) {
                                if( e[pid][j][k] == ph ) {
                                    e[pid][j].splice( k, 1 );
                                    k--;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    this.events = e;
};
xevent.call = function( pid, pn, pp ){
    
    let v = null;
    if( this.events == undefined ) {
        return v;
    }
    if( this.events[ pid ] == undefined ) {
        return v;
    }
    if( this.events[ pid ][ pn ] == undefined ) {
        return v;
    }

    for( let i = 0; i < this.events[ pid ][ pn ].length; i ++)
    {
        v = this.events[ pid ][ pn ][ i ]( pp );
        if( v && v.cancelEvent )
        {
            return v;
        }
    }
    return v;
};