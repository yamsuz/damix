<script type="text/javascript">
{literal}
var selector = '{/literal}{$selector}{literal}', cloneline, clonefilter;

$(document).ready(function(){
    cloneline = $('#datatable .clone');
    cloneline.css('display', '');
    cloneline.removeClass('clone');
    cloneline.remove();
    clonefilter = $('#filter .clone');
    clonefilter.css('display', '');
    clonefilter.removeClass('clone');
    clonefilter.remove();
    $('[name="list"]').bind('change', function(){
        tableload();
    });
    $( "#tabs" ).tabs();
    $('[name="list"]')[0].selectFirst();
    tableload();
});

function tableload()
{
    let p = {};

    p.s = selector;
    p.n = $('[name="list"]').val();

    if( p.n == '' )
    {
        return;
    }

    $ajax.json( 
        {/literal}{urljsstring 'damix~datatableconfiguration:load'}{literal},
        p,
        function( doc )
        {
            if( $ajax.erreur( doc ) )
            {
			
				{/literal}{ifacl2 'cmr.core.datatable.configuration.defaut'}{literal}
				$('#orm').html( doc.ormselector );
				{/literal}{/ifacl2}{literal}
				if( doc.list != undefined )
				{
					loaddata(doc.list.visible, true);
					loaddata(doc.list.hidden, false);
				}
				if( doc.filter != undefined )
				{
					loadfilter(doc.filter.visible, true);
					loadfilter(doc.filter.hidden, false);
				}
            }
        }
    );
}

function loadfilter(a, v)
{
    let i, o, c, r;

    o = $('#filter tbody.' + (v ? 'visible' : 'hidden'));

    o.find( 'tr' ).remove();
    if( a )
    {
        for( i = 0; i < a.length; i ++)
        {
            c = clonefilter.clone();
            c.find( '[name="field[]"]' ).html( a[i].name );
            c.find( '[name="ref[]"]' ).val( a[i].ref );
            c.find( '[name="header[]"]' ).html( a[i].header );
            c.find( '[name="locale[]"]' ).html( a[i].locale );
            c.find( '[name="datatype[]"]' ).val( a[i].datatype );
            c.find( '[name="selector[]"]' ).val( a[i].selector );
            c.find( '[name="operator[]"]' ).val( a[i].operator );
            c.find( '[name="cols[]"]' ).val( a[i].column );
            c.find( '[name="rows[]"]' ).val( a[i].row );
            r = c.find( '[name="visible[]"]' );

            r.bind('click', function(){
                rowchecked('filter', this);
            });
            r[0].checked = v;


            o.append( c );
        }
    }
    if( v )
    {
        dragdrop();
    }
}

function loaddata(a, v)
{
    let i, o, c, r;

    o = $('#datatable tbody.' + (v ? 'visible' : 'hidden'));

    o.find( 'tr' ).remove();
    if( a )
    {
        for( i = 0; i < a.length; i ++)
        {
            c = cloneline.clone();
            c.find( '[name="ref[]"]' ).val( a[i].ref );
            c.find( '[name="field[]"]' ).html( a[i].name );
            c.find( '[name="header[]"]' ).html( a[i].header );
            c.find( '[name="locale[]"]' ).html( a[i].locale );
            c.find( '[name="datatype[]"]' ).val( a[i].datatype );
            c.find( '[name="order[]"]' ).val( a[i].order );
            c.find( '[name="from[]"]' ).val( a[i].from );
            c.find( '[name="sort[]"]' ).val( a[i].sort );
			if( a[i].functions != undefined && a[i].functions.content != undefined)
			{
				c.find( '[name="formulecontent[]"]' ).val( a[i].functions.content );
			}
            c.find( '[name="formule[]"]' ).bind( 'click', function(){
				openformule(this);
			} );
            
            r = c.find( '[name="visible[]"]' );

            r.bind('click', function(){
                rowchecked('datatable', this);
            });
            r[0].checked = v;


            o.append( c );
        }
    }
    if( v )
    {
        dragdrop();
    }
}

function rowchecked(elt, obj)
{
    let row = obj.parentNode.parentNode;

    if( !obj.checked )
    {
        o = $('#'+elt+' tbody.hidden');

        row.parentNode.removeChild( row );
        o.append( row );
    }
    else
    {
        o = $('#'+elt+' tbody.visible');

        row.parentNode.removeChild( row );
        o.append( row );
    }
	dragdrop();
    refreshorder();
}

function refreshorder()
{
    let i = 1;
    $('#datatable tbody.visible tr').each( function(){
        $(this).find( '[name="order[]"]' ).val( i );
        i ++;
    });
    $('#datatable tbody.hidden tr').each( function(){
        $(this).find( '[name="order[]"]' ).val( '' );
    });
}

function dragdrop()
{
    // $( "#datatable tbody.visible" ).tableDnD({
        // dragHandle: ".dragHandle",
        // onDragStop: function(){
            // refreshorder();
        // },
        // onDragClass: "myDragClass"
    // });
	$( "#datatable tbody.visible" ).sortable({
        stop: function(){
            refreshorder();
        },
    });
	
	
}
{/literal}{ifacl2 'damix.datatable.configuration.enregistrer'}{literal}
function datatablesave()
{
    var p = {};

    p.data = [];
    p.filter = [];

    $('#datatable tbody.visible tr').each( function(){
        p.data.push( {
            'ref' : $(this).find( '[name="ref[]"]' ).val(),
            'field' : $(this).find( '[name="field[]"]' ).html(),
            'visible' : $(this).find( '[name="visible[]"]' )[0].checked,
            'header' : $(this).find( '[name="header[]"]' ).html(),
            'locale' : $(this).find( '[name="locale[]"]' ).html(),
            'datatype' : $(this).find( '[name="datatype[]"]' ).val(),
            'order' : $(this).find( '[name="order[]"]' ).val(),
            'from' : $(this).find( '[name="from[]"]' ).val(),
            'sort' : $(this).find( '[name="sort[]"]' ).val(),
            'functions' : 
				{
					'content' : $(this).find( '[name="formulecontent[]"]' ).val() 
				},
            'visible' : 1,
            });

    });

    $('#filter tbody.visible tr').each( function(){
        p.filter.push( {
            'field' : $(this).find( '[name="field[]"]' ).html(),
            'visible' : $(this).find( '[name="visible[]"]' )[0].checked,
            'header' : $(this).find( '[name="header[]"]' ).html(),
            'locale' : $(this).find( '[name="locale[]"]' ).html(),
            'cols' : $(this).find( '[name="cols[]"]' ).val(),
            'rows' : $(this).find( '[name="rows[]"]' ).val(),
            'group' : $(this).find( '[name="group[]"]' ).val(),
            'datatype' : $(this).find( '[name="datatype[]"]' ).val(),
            'selector' : $(this).find( '[name="selector[]"]' ).val(),
            'operator' : $(this).find( '[name="operator[]"]' ).val(),
            'ref' : $(this).find( '[name="ref[]"]' ).val(),
            'defaultvalue1' : $(this).find( '[name="defaultvalue1[]"]' ).val(),
            'defaultvalue2' : $(this).find( '[name="defaultvalue2[]"]' ).val(),
            'null' : $(this).find( '[name="null[]"]' ).val(),
            });
            
    });

    p.selector = selector;
    p.name = $('[name="list"]').val();

    $ajax.json( 
        {/literal}{urljsstring 'damix~datatableconfiguration:save'}{literal},
        p,
        function( doc )
        {
            if( $ajax.erreur( doc ) )
            {

            }
        }
    );
}
{/literal}{/ifacl2}{literal}

{/literal}{ifacl2 'cmr.core.datatable.configuration.defaut'}{literal}
function datatabledefaut()
{
    var p = {};

    p.data = [];
    p.filter = [];

    $('#datatable tbody.visible tr').each( function(){
        p.data.push( {
            'ref' : $(this).find( '[name="ref[]"]' ).html(),
            'field' : $(this).find( '[name="field[]"]' ).html(),
            'visible' : $(this).find( '[name="visible[]"]' )[0].checked,
            'header' : $(this).find( '[name="header[]"]' ).html(),
            'locale' : $(this).find( '[name="locale[]"]' ).html(),
            'datatype' : $(this).find( '[name="datatype[]"]' ).val(),
            'order' : $(this).find( '[name="order[]"]' ).val(),
            'from' : $(this).find( '[name="from[]"]' ).val(),
            'sort' : $(this).find( '[name="sort[]"]' ).val(),
			'functions' : 
				{
					'content' : $(this).find( '[name="formulecontent[]"]' ).val() 
				},
            'visible' : 1,
            });

    });

    $('#filter tbody.visible tr').each( function(){
        p.filter.push( {
            'field' : $(this).find( '[name="field[]"]' ).html(),
            'visible' : $(this).find( '[name="visible[]"]' )[0].checked,
            'header' : $(this).find( '[name="header[]"]' ).html(),
            'locale' : $(this).find( '[name="locale[]"]' ).html(),
            'cols' : $(this).find( '[name="cols[]"]' ).val(),
            'rows' : $(this).find( '[name="rows[]"]' ).val(),
            'group' : $(this).find( '[name="group[]"]' ).val(),
            'datatype' : $(this).find( '[name="datatype[]"]' ).val(),
            'selector' : $(this).find( '[name="selector[]"]' ).val(),
            'operator' : $(this).find( '[name="operator[]"]' ).val(),
            'ref' : $(this).find( '[name="ref[]"]' ).val(),
            'defaultvalue1' : $(this).find( '[name="defaultvalue1[]"]' ).val(),
            'defaultvalue2' : $(this).find( '[name="defaultvalue2[]"]' ).val(),
            'null' : $(this).find( '[name="null[]"]' ).val(),
            });
            
    });

    p.selector = selector;
    p.name = $('[name="list"]').val();

    $ajax.json( 
        {/literal}{urljsstring 'damix~datatableconfiguration:savedefault'}{literal},
        p,
        function( doc )
        {
            if( $ajax.erreur( doc ) )
            {

            }
        }
    );
}
{/literal}{/ifacl2}{literal}

function openformule(obj)
{
	$('#popup_content').html($(obj).parents('tr').find('[name="formulecontent[]"]').val() );
	$( "#popup_formule" ).dialog({
		width: '700px',
		resizable: true,
		buttons: [
				{
					text: "Annuler",
					click: function() {
						$( this ).dialog( "close" );
					}
				},
				{
					text: "Valider",
					click: function() {
						let h = $('#popup_content').val();
						$(obj).parents('tr').find('[name="formulecontent[]"]').val(h);
						$( this ).dialog( "close" );
					}
				}
		]
  });
}
{/literal}
        </script>


        <style>
{literal}
#datatable tbody.visible 
{
    background-color: #e4f0f5;
}
#datatable tbody.hidden 
{
    background-color: #3EF932;
}
#datatable
{
	display: block;
    overflow-y: auto;
    white-space: nowrap;
	height: 600px;
}
{/literal}
        </style>

{bouton 'damix~ctrdatatableconfiguration:index'}
<div>
screen : <select name="list">
            <option value=""/>
            <option value="default">Défaut</option>
        </select>
</div>

{ifacl2 'cmr.core.datatable.configuration.defaut'}
<div>
Orm : <span id="orm"></span>
</div>

{/ifacl2}

<div id="tabs">
	<ul>
		<li>
			<a href="#tabs-1">Table</a>
		</li>
		<li>
			<a href="#tabs-2">Filtre</a>
		</li>
	</ul>

	<div id="tabs-1" style="">
		<table id="datatable">
			<thead>
				<tr>
					<th class="dragHandle" style="cursor:move">{iconfont 'btn_selection'}</th>
					<th>
						&nbsp;
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.propriete'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.locale'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.entete'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.typedonnee'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.ordre'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.trie'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.formule'}
					</th>
				</tr>
			</thead>
			<tr class="clone" style="display:none;">
				<td class="dragHandle" style="cursor:move">{iconfont 'btn_selection'}</td>
				<td>
					<input type="hidden" name="from[]" />
					<input type="hidden" name="ref[]" />
					<input type="checkbox" name="visible[]" />
				</td>
				<td>
					<div name="field[]"/>
				</td>
				<td>
					<div name="locale[]" />
				</td>
				<td>
					<div name="header[]" contenteditable="true"/>
				</td>
				<td>
					<select name="datatype[]">
						<option value=""/>
						<option value="varchar">{locale 'damix~lclcore.datatable.datatype.string'}</option>
						<option value="phone">{locale 'damix~lclcore.datatable.datatype.phone'}</option>
						<option value="int">{locale 'damix~lclcore.datatable.datatype.int'}</option>
						<option value="decimal">{locale 'damix~lclcore.datatable.datatype.decimal'}</option>
						<option value="date">{locale 'damix~lclcore.datatable.datatype.date'}</option>
						<option value="datetime">{locale 'damix~lclcore.datatable.datatype.datetime'}</option>
						<option value="time">{locale 'damix~lclcore.datatable.datatype.time'}</option>
						<option value="color">{locale 'damix~lclcore.datatable.datatype.color'}</option>
						<option value="percent">{locale 'damix~lclcore.datatable.datatype.percent'}</option>
						<option value="bool">{locale 'damix~lclcore.datatable.datatype.bool'}</option>
					</select>
				</td>
				<td>
					<input type="number" name="order[]" step="1" value=""/>
				</td>
				<td>
					<select name="sort[]">
						<option value=""/>
						<option value="asc">{locale 'damix~lclcore.datatable.order.asc'}</option>
						<option value="desc">{locale 'damix~lclcore.datatable.order.desc'}</option>
					</select>
				</td>
				<td>
					<a href="#" name="formule[]" title="Enregistrer">{iconfont 'btn_selection'}</a>
					<input type="hidden" name="formulecontent[]"/>
				</td>
			</tr>
			<tbody class="visible">
			</tbody>
			<tr>
				<td colspan="7">Propriété disponible</td>
			</tr>
			<tbody class="hidden">
			</tbody>
		</table>
	</div>
	<div id="tabs-2">
		
		<table id="filter">
			<thead>
				<tr>
					<th class="dragHandle" style="cursor:move">{iconfont 'btn_selection'}</th>
					<th>
						&nbsp;
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.propriete'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.entete'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.locale'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.datatype'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.colonne'}
					</th>
					<th>
						{locale 'damix~lclcore.datatable.header.ligne'}
					</th>
				</tr>
			</thead>
			<tr class="clone" style="display:none;">
				<td class="dragHandle" style="cursor:move">{iconfont 'btn_selection'}</td>
				<td>
					<input type="checkbox" name="visible[]" />
					<input type="hidden" name="group[]" />
					<input type="hidden" name="datatype[]" />
					<input type="hidden" name="defaultvalue1[]" />
					<input type="hidden" name="defaultvalue2[]" />
					<input type="hidden" name="selector[]" />
					<input type="hidden" name="ref[]" />
					<input type="hidden" name="null[]" />
				</td>
				<td>
					<div name="field[]"/>
				</td>
				<td>
					<div name="header[]"/>
				</td>
				<td>
					<div name="locale[]"/>
				</td>
				<td>
					<select name="operator[]">
						<option value=""><option>
						<option value="eq">{locale 'damix~lclcore.filter.operator.eq'}</option>
						<option value="noteq">{locale 'damix~lclcore.filter.operator.noteq'}</option>
						<option value="lt">{locale 'damix~lclcore.filter.operator.lt'}</option>
						<option value="lteq">{locale 'damix~lclcore.filter.operator.lteq'}</option>
						<option value="gt">{locale 'damix~lclcore.filter.operator.gt'}</option>
						<option value="gteq">{locale 'damix~lclcore.filter.operator.gteq'}</option>
						<option value="like">{locale 'damix~lclcore.filter.operator.like'}</option>
						<option value="begin">{locale 'damix~lclcore.filter.operator.begin'}</option>
						<option value="end">{locale 'damix~lclcore.filter.operator.end'}</option>
						<option value="period">{locale 'damix~lclcore.filter.operator.period'}</option>
					</select>
				</td>
				<td>
					<input type="number" name="cols[]" style="width:75px" step="1" value=""/>
				</td>
				<td> 
					<input type="number" name="rows[]" style="width:75px" step="1" value=""/>
				</td>
			</tr>
			<tbody class="visible">
			</tbody>
			<tr>
				<td colspan="7">Propriété disponible</td>
			</tr>
			<tbody class="hidden">
			</tbody>
		</table>
		
		
		
	</div>

</div>



<div id="popup_formule" title="Basic dialog" style="display: none;">
	<input name="idmissioncommande" type="hidden"/>
	<div class="kt-portlet">
		<form class="kt-form">
			<div class="kt-portlet__body">
				<div class="kt-section kt-section--first">
					<div class="kt-section__body">
						<div class="form-group row">
							<div class="col-lg-4">
								<label class="col-form-label">Formule</label>
							</div>
							<div class="col-lg-8">
								<textarea id="popup_content" style="height: 163px;width:100%"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>