YUI.add("axis-time-base",function(t,e){var i=t.Lang;function a(){}a.NAME="timeImpl",a.ATTRS={labelFormat:{value:"%b %d, %y"}},a.prototype={_type:"time",_maximumGetter:function(){var t=this._getNumber(this._setMaximum);return i.isNumber(t)||(t=this._getNumber(this.get("dataMaximum"))),parseFloat(t)},_maximumSetter:function(t){return this._setMaximum=this._getNumber(t),t},_minimumGetter:function(){var t=this._getNumber(this._setMinimum);return i.isNumber(t)||(t=this._getNumber(this.get("dataMinimum"))),parseFloat(t)},_minimumSetter:function(t){return this._setMinimum=this._getNumber(t),t},_getSetMax:function(){var t=this._getNumber(this._setMaximum);return i.isNumber(t)},_getSetMin:function(){var t=this._getNumber(this._setMinimum);return i.isNumber(t)},formatLabel:function(e,i){return e=t.DataType.Date.parse(e),i?t.DataType.Date.format(e,{format:i}):e},GUID:"yuitimeaxis",_dataType:"time",_getKeyArray:function(t,e){for(var a,m,r=[],u=0,s=e.length;u<s;++u)a=e[u][t],i.isDate(a)?m=a.valueOf():(m=new Date(a),i.isDate(m)?m=m.valueOf():i.isNumber(a)?m=a:i.isNumber(parseFloat(a))?m=parseFloat(a):("string"!=typeof a&&(a=a),m=new Date(a).valueOf())),r[u]=m;return r},_updateMinAndMax:function(){var t,e,i,a=this.get("data"),m=0,r=0;if(a&&a.length&&a.length>0&&(t=a.length,m=r=a[0],t>1))for(i=1;i<t;i++)e=a[i],isNaN(e)||(m=Math.max(e,m),r=Math.min(e,r));this._dataMaximum=m,this._dataMinimum=r},_getCoordFromValue:function(t,e,a,m,r){return(0,i.isNumber)(m=this._getNumber(m))?r+(m-t)*(a/(e-t)):NaN},_getNumber:function(t){return i.isDate(t)?t=t.valueOf():!i.isNumber(t)&&t&&(t=new Date(t).valueOf()),t}},t.TimeImpl=a,t.TimeAxisBase=t.Base.create("timeAxisBase",t.AxisBase,[t.TimeImpl])},"patched-v3.18.1",{requires:["axis-base"]});