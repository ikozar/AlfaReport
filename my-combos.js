
Ext.onReady(function(){
//****************************************
	Ext.BLANK_IMAGE_URL = "http://localhost/ext-2.0/resources/images/default/s.gif";
	Ext.data.Store.prototype.filter = function(property, value, anyMatch, caseSensitive){
		this.filtParam = {property: property, value: value, anyMatch: anyMatch, caseSensitive: caseSensitive};
		var fn = this.createFilterFn(property, value, anyMatch, caseSensitive);
		return fn ? this.filterBy(fn) : this.clearFilter();
	};

	Ext.DataView.prototype.refresh = function(){
		this.clearSelections(false, true);
		this.el.update("");
		var html = [];
		var records = this.store.getRange();
		if(records.length < 1){
			this.el.update(this.emptyText);
			this.all.clear();
			return;
		}
		this.tpl.overwrite(this.el, this.collectData(records, 0), this.store.filtParam);
		this.all.fill(Ext.query(this.itemSelector, this.el.dom));
		this.updateIndexes(0);
	};
/*
	Ext.DataView.prototype._initComponent = Ext.DataView.prototype.initComponent;
	Ext.DataView.prototype.initComponent = function(){
		if(typeof this.tpl == "string")
			this.tpl = new Ext.Template(this.tpl);
		this._initComponent();
	};
*/

	Ext.tree.TreePanel.prototype.getHeight = function(){
		return this.getFrameHeight();	// getSize().height	getInnerHeight()
	};

	Ext.tree.TreePanel.prototype.afterRender = function(){
		Ext.tree.TreePanel.superclass.afterRender.call(this);
		if ( !this.root )
			return;
		this.root.render();
		if(!this.rootVisible){
			this.root.renderChildren();
		}
	};

	Ext.Template.prototype.overwrite = function(el, values, filtParam, returnElement){
		el = Ext.getDom(el);
		var innerHTML = this.applyTemplate(values);
		if ( filtParam && filtParam.value )
		{
				var re = new RegExp('(' + filtParam.value + ')([^>]*)<', 'gi')
//				innerHTML = innerHTML.replace(re, '<span class="x-find">$1</span>$2<');
		  }
		el.innerHTML = innerHTML;
		return returnElement ? Ext.get(el.firstChild, true) : el.firstChild;
		};
	
/*	
	Ext.tree.TreeNodeUI.prototype._updateExpandIcon = Ext.tree.TreeNodeUI.prototype.updateExpandIcon;
	Ext.tree.TreeNodeUI.prototype.updateExpandIcon = function(){
		if ( this.node.attributes.icon == 'none' )
			return;
		this._updateExpandIcon();
	};
*/

	Ext.tree.TreeNodeUI.prototype.renderChildren = function(suppressEvent){
		if(suppressEvent !== false){
			this.fireEvent("beforechildrenrendered", this);
		}
		var cs = this.childNodes || [];
		for(var i = 0, len = cs.length; i < len; i++){
			cs[i].render(true);
		}
		this.childrenRendered = true;
	};

	Ext.tree.TreeNodeUI.prototype.renderElements = function(n, a, targetNode, bulkRender){
		// add some indent caching, this helps performance when rendering a large tree
		this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';

		var cb = typeof a.checked == 'boolean';

		var href = a.href ? a.href : Ext.isGecko ? "" : "#";
		var buf = ['<li class="x-tree-node"><div ext:tree-node-id="',n.id,'" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls,'" unselectable="on">',
			'<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
//			'<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
			'<img src="', this.emptyIcon, 
				a.icon == 'none' ? '" />' : '" class="x-tree-ec-icon x-tree-elbow" />',
			'<img src="', ( a.icon == 'none' ? null : a.icon ) || this.emptyIcon, 
					( a.icon == 'none' ? '"' : '" class="x-tree-node-icon'),
					( a.icon && a.icon != 'none' ? " x-tree-node-inline-icon" : ""),
					(a.iconCls ? " "+a.iconCls : ""),
					'" unselectable="on" />',
			cb ? ('<input class="x-tree-node-cb" type="checkbox" ' + (a.checked ? 'checked="checked" />' : '/>')) : '',
			'<a hidefocus="on" class="x-tree-node-anchor" href="',href,'" tabIndex="1" ',
				 a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", 
				 '><span',
				 a.stl_node ? ' style="' + a.stl_node + '"' : '',
				 ' unselectable="on">',n.text,"</span></a></div>",
			'<ul class="x-tree-node-ct" style="display:none;"></ul>',
			"</li>"].join('');

		var nel;
		if(bulkRender !== true && n.nextSibling && (nel = n.nextSibling.ui.getEl())){
			this.wrap = Ext.DomHelper.insertHtml("beforeBegin", nel, buf);
		}else{
			this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf);
		}
		
		this.elNode = this.wrap.childNodes[0];
		this.ctNode = this.wrap.childNodes[1];
		var cs = this.elNode.childNodes;
		this.indentNode = cs[0];
		this.ecNode = cs[1];
		this.iconNode = cs[2];
		var index = 3;
		if(cb){
			this.checkbox = cs[3];
			index++;
		}
		this.anchor = cs[index];
		this.textNode = cs[index].firstChild;
		
	};

	Ext.form.ComboBox.prototype.resizable = true;
	Ext.form.ComboBox.prototype.filtFromBegin = true;

if(1)
{
	Ext.form.ComboBox.prototype.onFocus = function(e, el){
		if ( !this.el || !this.wrap )
			return;
		if(!this.list)
			this.initList();

		if ( el && el != this.el.dom )
		{
			this.triggerBlur();
			this.wrap.value = this.value;
			this.el = Ext.get(el);
			this.wrap = Ext.get(el.parentNode);
			this.value = this.wrap.value;
			this.hasFocus = false;
			var lw = this.listWidth || Math.max(this.wrap.getWidth(), this.minListWidth);
			this.list.setWidth(lw);
		}
		Ext.form.ComboBox.superclass.onFocus.call(this);
   };

	Ext.form.ComboBox.prototype.onRender = function(ct, position){
		if ( !this.el.dom.getAttribute("autocomplete") )
			Ext.form.ComboBox.superclass.onRender.call(this, ct, position);
		else
		{
			this.wrap = new Ext.Element( this.el.dom.parentNode, true );
			var img = this.el.dom.nextSibling;
			if ( img.tagName != 'IMG' )
				img = img.nextSibling;
			this.trigger = new Ext.Element( img, true );
			this.initTrigger();
		}
		if(this.hiddenName){
			this.hiddenField = this.el.insertSibling({tag:'input', type:'hidden', name: this.hiddenName, id: (this.hiddenId||this.hiddenName)},
					'before', true);
			this.hiddenField.value =
				this.hiddenValue !== undefined ? this.hiddenValue :
				this.value !== undefined ? this.value : '';

			// prevent input submission
			this.el.dom.removeAttribute('name');
		}
		if(Ext.isGecko){
			this.el.dom.setAttribute('autocomplete', 'off');
		}

		if(!this.lazyInit){
			this.initList();
		}else{
			this.on('focus', this.initList, this, {single: true});
		}

		if(!this.editable){
			this.editable = true;
			this.setEditable(false);
		}
	};

	Ext.form.ComboBox.prototype.initList_ = Ext.form.ComboBox.prototype.initList;
	Ext.form.ComboBox.prototype.initList = function(){
		this.initList_();
		this.innerList.setWidth('100%');
		this.innerList.setHeight('100%');
	};

	Ext.form.ComboBox.prototype.initEvents = function(){
		Ext.form.ComboBox.superclass.initEvents.call(this);

		this.keyNav = new Ext.KeyNav(this.el, {
			"up" : function(e){
				this.inKeyMode = true;
				this.selectPrev();
			},
			
			"down" : function(e){
				if(!this.isExpanded()){
//					this.onTriggerClick();
					this.onTriggerClick(e, this.el.dom);
				}else{
					this.inKeyMode = true;
					this.selectNext();
				}
			},
			
			"enter" : function(e){
				this.onViewClick();
				//return true;
			},
			
			"esc" : function(e){
				this.collapse();
			},
			
			"tab" : function(e){
				this.onViewClick(false);
				return true;
			},
			
			scope : this,
			
			doRelay : function(foo, bar, hname){
				if(hname == 'down' || this.scope.isExpanded()){
					return Ext.KeyNav.prototype.doRelay.apply(this, arguments);
				}
				return true;
			},
			
			forceKeyDown : true
		});
		this.queryDelay = Math.max(this.queryDelay || 10,
			this.mode == 'local' ? 10 : 250);
		this.dqTask = new Ext.util.DelayedTask(this.initQuery, this);
		if(this.typeAhead){
			this.taTask = new Ext.util.DelayedTask(this.onTypeAhead, this);
		}
		if(this.editable !== false){
			this.el.on("keyup", this.onKeyUp, this);
		}
		if(this.forceSelection){
			this.on('blur', this.doForce, this);
		}
	};

	Ext.form.ComboBox.prototype.onTypeAhead = function(){
		if(this.store.getCount() > 0){
			var r = this.store.getAt(0);
			var newValue = r.data[this.displayField];
			var len = newValue.length;
			if ( this.filtFromBegin )
			{
				var selStart = this.getRawValue().length;
				if(selStart != len){
					this.setRawValue(newValue);
					this.selectText(selStart, newValue.length);
				}
			}
		}
	};

	Ext.form.ComboBox.prototype.doQuery = function(q, forceQuery){		//, forceAll){
		if(q === undefined || q === null){
			q = '';
		}
		if ( forceQuery )
			this.lastQuery = '';
		forceAll = ( q == '' );
		var qe = {
			query: q,
			forceAll: forceAll,
			combo: this,
			cancel:false
		};
		if(this.fireEvent('beforequery', qe)===false || qe.cancel){
			return false;
		}
		this.innerList.q = q;
if(0)
{
		if ( q != '' && !this.header.isVisible() )
		{
			this.header.show();
			this.restrictHeight();
		}
		else if ( q == '' && this.header.isVisible() )
		{
			this.header.hide();
			this.restrictHeight();
		}
}

		q = qe.query;
		forceAll = qe.forceAll;
		if(forceAll === true || (q.length >= this.minChars)){
			if(this.lastQuery !== q){
				this.lastQuery = q;
				if(this.mode == 'local'){
					this.selectedIndex = -1;
					if(forceAll){
						this.store.clearFilter();
					}else{
						this.store.filter(this.displayField, q, !this.filtFromBegin);
					}
					this.onLoad();
				}else{
					this.store.baseParams[this.queryParam] = q;
					this.store.load({
						params: this.getParams(q)
					});
					this.expand();
				}
			}else{
				this.selectedIndex = -1;
				this.onLoad();   
			}
		}
	};
	
	Ext.form.ComboBox.prototype.onTriggerClick = function(e, elDom){
		if(this.disabled){
			return;
		}
		if(this.isExpanded()){
			this.collapse();
			this.el.focus();
		}else {
//			this.onFocus({});
			this.onFocus(e, Ext.DomQuery.selectNode("INPUT", elDom.parentNode));
			if(this.triggerAction == 'all') {
				this.doQuery(this.allQuery);		//, true, true);
			} else {
				this.doQuery(this.getRawValue());
			}
			this.el.focus();
		}
	};
	
	Ext.tree.TreeLoader.prototype.createNode_ = Ext.tree.TreeLoader.prototype.createNode;
	Ext.tree.TreeLoader.prototype.createNode = function(attr) {
		var n = this.createNode_(attr);
		n.text = attr[this.displayField];
		return n;
	};

/*
	Ext.form.ComboBox.prototype.onLoad_ = Ext.form.ComboBox.prototype.onLoad;
	Ext.form.ComboBox.prototype.onLoad = function(function(){
		if(this.store.getCount() > 0){
			
		}
	}
*/

	Ext.layout.CardLayout.prototype.renderAll = function(ct, target)
	{
/*
			if(this.deferredRender){
				this.renderItem(this.activeItem, undefined, target);
			}else{
				Ext.layout.CardLayout.superclass.renderAll.call(this, ct, target);
			}
*/
		if ( !target.dom.firstChild )
		{
			var nameCont = ct.initialConfig.renderTable;
			target.dom.insertBefore(Ext.get(nameCont).dom, null);
		}
	};

	HRC.Manager.getDataRow_ = HRC.Manager.getDataRow;
	HRC.Manager.getDataRow = function(nameData, rs_key, rs_nom)
	{
		var field = this.field;	
		if ( ( field == "kod_kls_svyaz" || field == "nomer_svyaz" )
			&& nameData == "torg_sotr" )
		{
			var $row = this.getDataRow_(nameData, rs_key, rs_nom);
			return this.ar_Data["torg_svyaz"][$row["kod_org"]+"*"+$row["kod_sotr"]][0];
		}
		else
			return this.getDataRow_(nameData, rs_key, rs_nom);
	};


}

//****************************************
	Ext.QuickTips.init();

	var tabs = new Ext.TabPanel({
		renderTo: 'regTABS-OUTER',
		renderTable: 'regTABS',		//MainTable',
		width: '100%',
		height: 300,
		activeTab: 0,
		frame: true,
		enableTabScroll: true,
		defaults: {autoScroll:true},
		items:[
			{contentEl:'ctrlTAB/MAIN', title: 'Tab 1'},
			{contentEl:'ctrlTAB/torg_podr', title: 'Tab 2'}
		]
	});

});
