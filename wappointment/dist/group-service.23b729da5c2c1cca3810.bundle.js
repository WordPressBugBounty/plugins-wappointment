(window.webpackJsonp=window.webpackJsonp||[]).push([[18],{"5pyh":function(e,t,r){"use strict";var i=r("5oH9"),n=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}();var o=function(e){function t(){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),function(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}(t,i["a"]),n(t,[{key:"endpoints",value:function(){return{save:{method:"post",route:"service"},get:{method:"get",route:"settings"}}}}]),t}();t.a=o},OiRY:function(e,t,r){"use strict";var i={computed:{currency:function(){return this.currencySymb},priceFormat:function(){return this.wooAddonActive?window.wappointment_woocommerce.currency_format:window.apiWappointment.currency.format},wooAddonActive:function(){return void 0!==window.wappointment_woocommerce&&window.wappointment_woocommerce.installed_at},wooCurrency:function(){return this.wooAddonActive?window.wappointment_woocommerce.currency_symbol:""},wooCurrencyText:function(){return this.wooAddonActive?window.wappointment_woocommerce.currency_text:""},currencySeparator:function(){return this.wooAddonActive?window.wappointment_woocommerce.decimals_sep:window.apiWappointment.currency.decimals_sep},thousandSeparator:function(){return this.wooAddonActive?window.wappointment_woocommerce.thousand_sep:window.apiWappointment.currency.thousand_sep},wappoCurrencyText:function(){return window.apiWappointment.currency.code+" - "+this.wappoCurrency},wappoCurrency:function(){return window.apiWappointment.currency.symbol},currencyCode:function(){return window.apiWappointment.currency.code},currencySymb:function(){return this.wooAddonActive?this.wooCurrency:this.wappoCurrency},currencyText:function(){return this.wooAddonActive?this.wooCurrencyText:this.wappoCurrencyText}},methods:{canSell:function(e){return this.sellable&&""!==this.currency&&-1===[void 0,""].indexOf(e.woo_price)},formatPrice:function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];return this.priceFormat.replace("[currency]",this.currency).replace("[price]",this.formatThousands(this.displayCents(e*(t?1:100))))},formatCentsPrice:function(e){return this.formatPrice(e,!0)},displayCents:function(e){var t=e/100;return!1===this.currencySeparator?Math.floor(t):t.toFixed(2).replace(".",this.currencySeparator)},formatThousands:function(e){for(var t=e.toString(),r="",i=(!1===this.currencySeparator?t:t.split(this.currencySeparator)[0]).split("").reverse().join(""),n=0;n<i.length;n++)r+=i[n]+((n+1)%3==0&&i.length>n+1?this.thousandSeparator:"");return r.split("").reverse().join("")+(!1===this.currencySeparator?"":this.currencySeparator+t.split(this.currencySeparator)[1])}}},n=r("KHd+"),o=Object(n.a)(i,void 0,void 0,!1,null,null,null);t.a=o.exports},VJs4:function(e,t,r){"use strict";var i=r("5pyh"),n=r("XfSa"),o=r("OiRY");var s,a,c={extends:n.a,mixins:[o.a],props:["dataPassed","servicesService","extraOptions","buttons","minimal","params"],data:function(){return{serviceService:null,modelHolder:{name:"",duration:60,type:"",address:"",options:{countries:[],phone_required:!1,video:""}},errors:{},formKey:"form"}},created:function(){void 0!==this.dataPassed&&(this.modelHolder=Object.assign({},this.dataPassed))},computed:{errorsPassed:function(){return this.errors},schemai18n:function(){return[{type:"row",class:"d-flex flex-wrap flex-sm-nowrap align-items-top fieldthumb",classEach:"mr-2",fields:[{type:"opt-imageselect",model:"options.icon",cast:String},{type:"input",label:this.get_i18n("service_f_name","settings"),model:"name",cast:String,class:"input-360"}]},{type:"checkbox",label:this.get_i18n("service_f_sell","settings"),model:"options.woo_sellable",cast:Boolean,default:!1},{type:"address",label:this.get_i18n("service_f_sdecs","settings"),model:"options.description",address:!1,cast:String},{type:"opt-multidurations",label:this.get_i18n("service_f_duration","settings"),model:"options.durations",cast:String,class:"w-100",default:[{duration:60}],min:5,max:240,step:5,int:!0,unit:"min",required_options_props:{woo_sellable:"woo_sellable"}},{type:"opt-modality",label:this.get_i18n("service_f_modality","settings"),model:"locations_id",cast:Array,checklistOptions:{value:"id"}},{type:"opt-customfields",label:this.get_i18n("service_f_cfield","settings"),model:"options.fields",bus:!0,listenBus:!0,cast:Array,checklistOptions:{value:"namekey"}},{type:"countryselector",label:this.get_i18n("service_f_countries","settings"),model:"options.countries",cast:Array,conditions:[{model:"options.fields",values:["phone"]}],validation:["required"]}]}},methods:{schemaParsed:function(){return window.wappointmentExtends.filter("ServiceFormSchema",this.addPriceField(this.schemai18n),this.params)},generatePriceField:function(){return{type:"input",label:this.sprintf_i18n("service_f_price","settings",this.currencyText),model:"options.woo_price",cast:String,conditions:[{model:"options.woo_sellable",values:[!0]}],liveParse:function(e){var t=(e=(e=e.replace(",",".")).replace(/[^0-9.]/g,"")).indexOf(".");return-1!==t&&t<e.length-2&&(e=Number.parseFloat(e).toFixed(2)),e}}},addPriceField:function(e){for(var t=0;t<e.length;t++)"opt-multidurations"==e[t].type&&(e[t].woo_price_field=this.generatePriceField());return e},isReady:function(e){this.$emit("ready",e)},initMethod:function(){this.serviceService=void 0!==this.servicesService?this.servicesService:this.$vueService(new i.a)},saveExternal:function(){this.$refs.formgenerator.submitTrigger(!0)},save:function(e){this.modelHolder=e,this.request(this.saveServiceRequest,void 0,void 0,!1,this.saved,this.failedValidation)},failedValidation:function(e){void 0!==e.response&&void 0!==e.response.data&&void 0!==e.response.data.data&&void 0!==e.response.data.data.errors&&void 0!==e.response.data.data.errors.validations&&(this.errors=e.response.data.data.errors.validations,this.formKey="form"+(new Date).getTime()),this.$refs.fgaddservice.reRender(),this.serviceError(e)},saveServiceRequest:(s=regeneratorRuntime.mark(function e(){return regeneratorRuntime.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,this.serviceService.call("save",this.modelHolder);case 2:return e.abrupt("return",e.sent);case 3:case"end":return e.stop()}},e,this)}),a=function(){var e=s.apply(this,arguments);return new Promise(function(t,r){return function i(n,o){try{var s=e[n](o),a=s.value}catch(e){return void r(e)}if(!s.done)return Promise.resolve(a).then(function(e){i("next",e)},function(e){i("throw",e)});t(a)}("next")})},function(){return a.apply(this,arguments)}),saved:function(e){this.$emit("saved",e)}}},d=r("KHd+"),u=Object(d.a)(c,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",[r("WAPFormGenerator",e._b({key:e.formKey,ref:"fgaddservice",attrs:{buttons:e.buttons,schema:e.schemaParsed(),data:e.modelHolder,errors:e.errorsPassed,minimal:e.minimal},on:{submit:e.save,back:function(t){e.$emit("back")},ready:e.isReady}},"WAPFormGenerator",e.extraOptions,!1))],1)},[],!1,null,null,null);t.a=u.exports},eErJ:function(e,t,r){"use strict";var i=r("5pyh");var n,o,s={extends:r("XfSa").a,props:["dataPassed","servicesService","extraOptions","buttons"],data:function(){return{serviceService:null,modelHolder:{name:"",duration:60,type:"",address:"",options:{countries:[],phone_required:!1,video:""}},errors:{},formKey:"form"}},created:function(){void 0!==this.dataPassed&&(this.modelHolder=Object.assign({},this.dataPassed))},computed:{schemai18n:function(){return[{type:"row",class:"d-flex flex-wrap flex-sm-nowrap align-items-center",classEach:"mr-2 w-100",fields:[{type:"input",label:this.get_i18n("wizard_3_servicename","wizard"),model:"name",cast:String,styles:{"max-width":"200px"},validation:["required"]},{type:"duration",label:this.get_i18n("wizard_3_duration","wizard"),model:"duration",cast:String,class:"w-100",default:60,min:5,max:240,step:5,int:!0,unit:"min",validation:["required"]}]},{type:"checkimages",label:this.get_i18n("wizard_3_delivery","wizard"),model:"type",cast:Array,images:[{value:"zoom",name:this.get_i18n("wizard_3_delivery_video","wizard"),subname:"(Zoom, Google meet, ...)",icon:["fas","video"]},{value:"physical",name:this.get_i18n("wizard_3_delivery_address","wizard"),icon:"map-marked-alt"},{value:"phone",name:this.get_i18n("wizard_3_delivery_byphone","wizard"),icon:"phone"},{value:"skype",name:this.get_i18n("wizard_3_delivery_byskype","wizard"),icon:["fab","skype"]}],validation:["required"]},{type:"checkimages",label:this.get_i18n("wizard_3_select_video","wizard"),radioMode:!0,model:"options.video",cast:Array,images:[{value:"zoom",name:"Zoom",icon:"zoom.png",icontype:"img",realsize:!0},{value:"googlemeet",name:"Google Meet",icon:"google-meet.png",icontype:"img",realsize:!0}],conditions:[{model:"type",values:["zoom"]}],validation:["required"]},{type:"address",label:this.get_i18n("wizard_3_address","wizard"),model:"address",cast:String,conditions:[{model:"type",values:["physical"]}],validation:["required"]},{type:"checkbox",label:this.get_i18n("wizard_3_delivery_phone_require","wizard"),model:"options.phone_required",cast:Boolean},{type:"countryselector",label:this.get_i18n("wizard_3_accepted_countries","wizard"),model:"options.countries",cast:Array,conditions:[{type:"or",conda:{model:"type",values:["phone"]},condb:{model:"options.phone_required",values:[!0]}}],validation:["required"]}]},schemaParsed:function(){return window.wappointmentExtends.filter("serviceFormSchema",this.schemai18n,this.modelHolder)},errorsPassed:function(){return this.errors}},methods:{isReady:function(e){this.$emit("ready",e)},initMethod:function(){this.serviceService=void 0!==this.servicesService?this.servicesService:this.$vueService(new i.a)},saveExternal:function(){this.$refs.formgenerator.submitTrigger(!0)},save:function(e){this.modelHolder=e,this.request(this.saveServiceRequest,void 0,void 0,!1,this.saved,this.failedValidation)},failedValidation:function(e){void 0!==e.response&&void 0!==e.response.data&&void 0!==e.response.data.data&&void 0!==e.response.data.data.errors&&void 0!==e.response.data.data.errors.validations&&(this.errors=e.response.data.data.errors.validations,this.formKey="form"+(new Date).getTime()),this.serviceError(e)},saveServiceRequest:(n=regeneratorRuntime.mark(function e(){return regeneratorRuntime.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,this.serviceService.call("save",this.modelHolder);case 2:return e.abrupt("return",e.sent);case 3:case"end":return e.stop()}},e,this)}),o=function(){var e=n.apply(this,arguments);return new Promise(function(t,r){return function i(n,o){try{var s=e[n](o),a=s.value}catch(e){return void r(e)}if(!s.done)return Promise.resolve(a).then(function(e){i("next",e)},function(e){i("throw",e)});t(a)}("next")})},function(){return o.apply(this,arguments)}),saved:function(e){this.$emit("saved"),this.serviceSuccess(e)}}},a=r("KHd+"),c=Object(a.a)(s,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",[r("WAPFormGenerator",e._b({key:e.formKey,ref:"formgenerator",attrs:{buttons:e.buttons,schema:e.schemaParsed,data:e.modelHolder,errors:e.errorsPassed},on:{submit:e.save,back:function(t){e.$emit("back")},ready:e.isReady}},"WAPFormGenerator",e.extraOptions,!1))],1)},[],!1,null,null,null);t.a=c.exports},jwg7:function(e,t,r){"use strict";r.r(t);var i=r("XfSa"),n=r("VJs4"),o=r("eErJ"),s={extends:i.a,props:["legacy"],data:function(){return{viewName:"service",parentLoad:!1,model:{name:"",duration:60,type:"",address:"",options:{countries:[]}}}},components:{ServiceModulable:n.a,ServiceLegacy:o.a},methods:{initMethod:function(){this.request(this.initValueRequest,void 0,void 0,!1,this.loaded)},loaded:function(e){this.viewData=e.data,this.model=e.data.service,this.model.duration=parseInt(this.model.duration),-1!==["",null,void 0].indexOf(this.model.options)&&(this.model.options={countries:[]}),this.$emit("fullyLoaded")},saveTransmit:function(e){this.$emit("saved",e)}}},a=r("KHd+"),c=Object(a.a)(s,function(){var e=this,t=e.$createElement,r=e._self._c||t;return e.dataLoaded?r("div",{staticClass:"container-fluid"},[e.legacy?r("ServiceLegacy",{attrs:{dataPassed:e.model,buttons:!0},on:{saved:function(t){e.$emit("saved")}}}):r("ServiceModulable",{attrs:{dataPassed:e.model,buttons:!0},on:{saved:e.saveTransmit}})],1):e._e()},[],!1,null,null,null);t.default=c.exports}}]);