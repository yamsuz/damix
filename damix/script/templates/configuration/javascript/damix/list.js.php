<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/?>var xscreen = {};


xscreen.list = [];
xscreen.list_add = function( id ){
    if( this.list[ id ] == undefined )
    {
        let o = new xlist();
        o.columns = new xcolumns();
        this.list[ id ] = o;
        return o;
    }
    
    return this.list[ id ];
}
xscreen.list_get = function( sel ){
	for( let i in this.list )
    {
        if( this.list[i].selector == sel )
        {
			return this.list[i];
        }
    }
	
	return this.list[ sel ];
}
xscreen.list_show = function(){
    for( let i in this.list )
    {
		this.list[ i ].load();
    } 
}
xscreen.list_load = function(selector){
    for( let i in this.list )
    {
        if( this.list[i].selector == selector )
        {
			switch( this.list[i].type )
			{
				case 'list':
					this.list[i].load();
					break;
				case 'report':
					this.list[i].report();
					break;
				case 'kanban':
					this.list[i].kanbanload();
					break;
			}
        }
    }    
}

xscreen.list_export = function(selector){
    for( let i in this.list )
    {
        if( this.list[i].selector == selector )
        {
			this.list[i].export();
        }
    }    
}

xscreen.list_selected = function( id ){
    let o = xscreen.list_get( id );
	if( o )
	{
		return o.selected();
	}

}


class xcolumns {
	
	add(title, name){
		if( this.cols == null )
		{
			this.cols = [];
		}
		this.cols.push( {'title':title, 'data' : name});
	};
}


class xlist{
	constructor(){
		this.selector = '';
		this.type = '';
		this.params = [];
		this.selection = 'simple';
		this.scrollY = "400px";
		this.scroller = {loadingIndicator: true};
		this.serverSide = true;
		this.paging = true;
		this.searching = false;
		this.events = {};
		this.info = true;
		this.data = [];
		this.language = {
			emptyTable: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.aucunresultat' )); ?>',
			zeroRecords: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.aucunresultat' )); ?>',
			info: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.totallignes' )); ?>',
			loadingRecords: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.chargementencours' )); ?>',
			processing: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.chargementencours' )); ?>',
			decimal: ",",
			thousands: " ",
			lengthMenu: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.lengthMenu' )); ?>',
			paginate: {
                first: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.first' )); ?>',
                last: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.last' )); ?>',
                next: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.next' )); ?>',
                previous: '<?php echo addslashes(\damix\engines\locales\Locale::get( 'damix~lclcore.datatable.previous' )); ?>',
            },
		};
	}
	
	options(){
		let type = this.type;
		let obj = {
			scrollX: "100%",
			processing : this.processing,
			serverSide : this.serverSide,
			language : this.language,
			scrollY : this.scrollY,
			scrollCollapse : true,
			paging : this.paging,
			info : this.info,
			order: [],
			footer : false,
			deferRender: true,
			searching : this.searching,
			pagingType : 'full_numbers',
			scroller : this.scroller,
			createdRow: function ( row, data, index ) {
				if( type != 'report' )
				{
					$('td', row).eq(0).html('<input type="checkbox" class="primarykey" value="' + data[obj.columns[0].data].value + '"/>');
				}
				
			
				var tds = $(row).find('td');
				tds.each(function(index) {
					var $td = $(this);
				
					$td.css({
						'max-width': '200px',
						'white-space' : 'nowrap',
						'overflow' : 'hidden',
						'text-overflow': 'ellipsis',
					});
				
				});
				if( index > 30 )
				{
					$(row).hide();
				}
			 
			},
			headerCallback: function( thead, data, start, end, display ) {
				if( type != 'report' )
				{
					var o = $(thead).find('th').eq(0).html( '<input type="checkbox" class="primarykey" />' );
					
					o.unbind( 'click' );
					o.bind( 'click', function(e){
						$('td .primarykey').each(function(){
							$(this).parents('tr').click();
						});
					});
				}
			},
			aoColumnDefs: [
					{
						bSortable: false,
						aTargets: [0],
					},
					{
						targets:'_all',
						render: function ( data, type, row, meta ) {
							if( data.couleur != null )
							{
								return '<span class="kt-font-bold kt-font-' + data.couleur + '">' + data.value + '</span>';
							}
							else if( data.badge != null )
							{
								return '<span class="kt-badge kt-badge--' + data.badge + ' kt-badge--inline kt-badge--pill">' + data.value + '</span>';
							}
							else
							{
								switch( data.type )
								{
									case 'bool':
										return '<span class="kt-badge kt-badge--' + ( data.value == 1 ? 'success' : 'danger' ) + ' kt-badge--inline kt-badge--pill">' + ( data.value == 1 ? '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.core.bool.oui' ); ?>' : '<?php echo \damix\engines\locales\Locale::get( 'damix~lclcore.core.bool.non' ); ?>' ) + '</span>';
										break;
									default:
										return data.value;
										break;
								}
							}
							return data.value;
						}
					},
				],
			fnInfoCallback: function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
				return "Total : " + iEnd +" résultat(s)";
			},
			initComplete: function( settings, json ) {
				
				//	$(settings).columns.adjusts();
				
			},
			columns: [],
		};
		
		return obj;
	}
	
	show(param){
	
		let obj = this.options();
		obj.ajax = {
				"url": '<?php echo \damix\core\urls\Url::getPath( 'damix~datatable:data' );?>',
				"type": "POST",
				"data": function ( d ) {
					d.list = param.list;
					
					param.list.params = param.xlist.params;
					
					d.list.filter = {};
						
					$('.xdatafiltre').each( function(){
						if( d.list.filter[this.name] == undefined ) 
						{ 
							d.list.filter[this.name] = []; 
						}
						
						let v = $(this).val();
						
						d.list.filter[this.name].push( $(this).val() );
					});

				}
			};
		
		obj.columns = this.columns.cols;
		let table;
		if( $.fn.DataTable.isDataTable( this.id + ' table.datatable' ) )
		{
			table = $( this.id + ' table.datatable').DataTable();
			table.ajax.reload(function( settings ) {
				
					// $(settings).columns.adjusts();
				
			});
		}
		else
		{
			table = $( this.id + ' table.datatable').DataTable( obj );
		}
		
		$( this.id + ' table.datatable tbody').off( 'click' );
		switch( this.selection )
		{
			case 'simple':
				let o;
				$( this.id + ' table.datatable tbody').on( 'click', 'tr', function () {
					if ( $(this).hasClass('selected') ) {
						$(this).removeClass('selected');
						o = $(this).find('.primarykey');
						if( o && o.length > 0 )
						{
							o[0].checked = false;
						}                    
					}
					else {
						table.$('.primarykey').each( function(){
						   this.checked = false; 
						});
						table.$('tr.selected').removeClass('selected');
						$(this).addClass('selected');
						o = $(this).find('.primarykey');
						if( o && o.length > 0 )
						{
							o[0].checked = true;
						}
					}
				} );
				break;
			case 'multiple':
				$( this.id + ' table.datatable tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');
					$(this).find('.primarykey').each( function(){
						this.checked = this.parentNode.parentNode.hasClass( 'selected' );
					});
				} );
				break;
		}
		
			// var me = this;
		// table.on( 'draw', function(){
			// setTimeout(function(){
				// me.rowshow(me );
			// }, 500);
		// });
		$(this.id + ' .dataTables_scrollBody').on('scroll', function(e) {
			let lignes = $( this.id + ' table.datatable tbody')[0].children;
			let h = $(lignes[0]).height();
			let v = e.target.scrollTop / ( h * lignes.length );
			
			let i = parseInt( lignes.length * v );
			let m = i + 50;

			for( let j = i; j < m; j++ ) {
				if( lignes[j] ) {
					$(lignes[j]).show();
				}
			}
		});
	}

	rowshow(xlist){
		let lignes = $( xlist.id + ' table.datatable tbody tr:hidden');
		for( let j = 0; j < 100; j++ ) {
			if( lignes[j] ) {
				$(lignes[j]).show();
			}
		}
		
		if( lignes.length >= 100 )
		{
			setTimeout(function(){
				xlist.rowshow( xlist );
			}, 100);
		}
	}

	report(){
		let param = {};
		param.list = {};
		param.list.selector = this.selector;
		
		let obj = this.options(), id = this.id + ' table.datatable';
		var columns = [];
		$ajax.json(
			'<?php echo \damix\core\urls\Url::getPath( 'core~xlist:report' );?>',
			param,
			function (data) {
			
				let c = data.columns;
				
				for (var i in c) {
					columns.push({
						data: c[i].name,
						title: c[i].data
					});
				}
				
				if ( $.fn.DataTable.isDataTable(id) ) {
					$(id).DataTable().destroy();
				}
				
				obj.data = data.rows.data;
				obj.columns = columns;
				obj.serverSide = false;
				$(id).DataTable(obj);
			}
		);
	}

	selected(){
		let o = [], a, table = $( this.id + ' table.datatable').DataTable();
		a = $( this.id + ' table.datatable tbody .selected .primarykey');
		if( a && a.length > 0 )
		{
			switch( this.selection )
			{
				case 'simple':
					o.push( a[0].val() );
					break;
				case 'multiple':
					a.each( function(){
						o.push( this.val() );
					});
					break;
			}
		}
		
		return o;
	}

	load(){
		let p = {}, me = this;
		
		if( this.type == 'list' )
		{
			p.list = {};
			p.list.name = 'default';
			p.list.selector = this.selector;

			p.xlist = this;
			me.show( p );
		}
		else
		{
			this.kanbaninit();
		}
		pageload( 'page' );
	}

	export()
	{
		let p = {};
		
		p.list = {};
		p.list.name = 'default';
		p.list.selector = this.selector;
		p.list.filter = {};
			
		$('.xdatafiltre').each( function(){
			if( p.list.filter[this.name] == undefined ) 
			{ 
				p.list.filter[this.name] = []; 
			}
			let v = $(this).val();
			if( v != '' )
			{
				p.list.filter[this.name].push( $(this).val() );
			}
		});
		
		let url = '<?php echo \damix\core\urls\Url::getPath( 'core~xlist:export' );?>';
		redirectionPost( url, p );
	}
	
	kanbanload(){
		let p = {};
		
		p.list = {};
		p.list = {};
		p.list.name = 'default';
		p.list.selector = this.selector;
		p.list.filter = {};
			
		$('.xdatafiltre').each( function(){
			if( p.list.filter[this.name] == undefined ) 
			{ 
				p.list.filter[this.name] = []; 
			}
			let v = $(this).val();
			if( v != '' )
			{
				p.list.filter[this.name].push( $(this).val() );
			}
		});
		let kb = this;
		this.kanban_clear();
		$ajax.json( 
			'<?php echo \damix\core\urls\Url::getPath( 'core~xlist:kanban' );?>',
			p,
			function( doc )
			{
				if( $ajax.erreur( doc ) )
				{
					
					for( i in doc )
					{
						let s = doc[i].bord_id;
						let b = kb.kanban.findBoard(s);
						
						if( b == undefined )
						{
							kb.addBord( s, doc[i].bord_label);
						}
						
						kb.kanban.addElement(s, {
										id: doc[i].id,
										title: doc[i].label,
									});
					}
				}
			}
		);
	}
	
	kanban_clear(){
		let k = this.kanban;
		let bc = this.kanban.boardContainer;
		
		for(let i in bc)
		{
			let id = bc[i].parentElement.dataset.id;
			if( id != undefined )
			{
				k.removeBoard( id );
			}
		}
	}

	kanban_init(){
		var obj = {
			element: this.id,
			gutter: "10px", //espacement entre chaque blocs
			widthBoard: "450px", //largeur des blocs
			itemHandleOptions:{
				enabled: true,
			},
			click: this.events.click,
			context: this.events.clickright,
			dropEl: this.events.drop,
			buttonClick: this.addCard,
			itemAddOptions: {
				enabled: false,
				content: '+ Add New Card',
				class: 'custom-button',
				footer: false
			},
			boards: []
		  };
		this.kanban = new jKanban( obj );
		this.kanbanload();
	}

	addBord( id, label ){
		let obj = {
			id: id,
			title: label,
			item: []
		};
		this.kanban.addBoards([obj]);
	}

	addCard(el, boardId){
		var formItem = document.createElement("form");
		formItem.setAttribute("class", "itemform");
		formItem.innerHTML =
		'<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="form-group"><button type="submit" class="btn btn-primary btn-xs pull-right">Submit</button><button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancel</button></div>';

		this.kanban.addForm(boardId, formItem);
		formItem.addEventListener("submit", function(e) {
			e.preventDefault();
			var text = e.target[0].value;
			this.kanban.addElement(boardId, {
				title: text
			});
			formItem.parentNode.removeChild(formItem);
		});
		document.getElementById("CancelBtn").onclick = function() {
			formItem.parentNode.removeChild(formItem);
		};
	}

	clearcondition(){
		this.params = [];
	}
	
	addcondition(table, property, op, value1, value2, group, logic){
		this.params.push( 
			{
				'table' : table,
				'property' : property,
				'operator' : op,
				'value1' : value1,
				'value2' : value2,
				'group' : group,
				'logic' : logic,
			}
		);
	}
	
	addparam(ref, property){
		this.params.push( 
			{
				'ref' : ref,
				'value1' : property,
			}
		);
	}
}
