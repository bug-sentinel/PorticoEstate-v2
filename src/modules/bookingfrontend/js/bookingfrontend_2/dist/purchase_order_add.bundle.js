(()=>{var e={940:()=>{if(!globalThis.trans){globalThis.translations=ko.observable({}),globalThis.translationsLoaded=ko.observable(!1);const e=phpGWLink("bookingfrontend/lang",null,!0);fetch(e).then((e=>e.json())).then((e=>{globalThis.translations(e),globalThis.translationsLoaded(!0)})),globalThis.trans=(e,t,...n)=>{const a=globalThis.translations(),i=e=>a[e]&&a[e][t]?a[e][t].replace(/%\d+/g,(e=>{const t=parseInt(e.substring(1))-1;return void 0!==n[t]?n[t]:e})):null;if(Array.isArray(e))for(let t=0;t<e.length;t++){const n=i(e[t]);if(null!==n)return n}else{const t=i(e);if(null!==t)return t}if(!Array.isArray(e)||!e.includes("common")){const e=i("common");if(null!==e)return e}return`Missing translation for [${Array.isArray(e)?e.join(", "):e}][${t}]`};class t{constructor(e,t){let n=t.templateNodes.map((e=>e.textContent));1===n.length&&n[0].includes(":")&&(n=n[0].split(":")),n=n.map((e=>e.trim())),n.length>=2?(this.tag=ko.observable(n[1]),this.group=ko.observable(n[0])):(this.tag="function"==typeof e.tag?e.tag:ko.observable(e.tag),this.group="function"==typeof e.group?e.group:ko.observable(e.group)),this.suffix=ko.observable(e.suffix||""),this.args=ko.observable(e.args),this.translations=globalThis.translations,this.translated=ko.computed((()=>{if(self.translations&&self.translations()&&Object.keys(self.translations()).length>0&&this.group&&this.tag)return globalThis.trans(this.group(),this.tag(),this.args())+this.suffix()}))}}ko.components.register("trans",{viewModel:{createViewModel:function(e,n){return new t(e,n)}},template:"\x3c!--ko text: translated()--\x3e\x3c!--/ko--\x3e"})}}},t={};function n(a){var i=t[a];if(void 0!==i)return i.exports;var s=t[a]={exports:{}};return e[a](s,s.exports,n),s.exports}(()=>{"use strict";function e(e,t,n,a){if(a){const e=a.split("/").filter((e=>""!==e&&!e.includes("http")));a="//"+e.slice(0,e.length-1).join("/")+"/"}const i=(a||strBaseURL).split("?");let s=i[0]+e+"?";null==t&&(t=new Object);for(const e in t)s+=e+"="+t[e]+"&";return i[1]&&(s+=i[1]),n&&(s+="&phpgw_return_as=json"),s}n(940),$(document).ready((function(){$("input[type=radio][name=select_template]").change((function(){var t=$(this).val(),n=e("bookingfrontend/",{menuaction:"bookingfrontend.preferences.set"},!0);$.ajax({type:"POST",dataType:"json",data:{template_set:t},url:n,success:function(e){location.reload(!0)}})})),$("input[type=radio][name=select_language]").change((function(){var t=$(this).val(),n=e("bookingfrontend/",{menuaction:"bookingfrontend.preferences.set"},!0);$.ajax({type:"POST",dataType:"json",data:{lang:t},url:n,success:function(e){location.reload(!0)}})}))})),ko.bindingHandlers.collapse={init:function(e,t){var n=t();$(e).collapse(ko.unwrap(n)?"show":"hide")},update:function(e,t){var n=t();$(e).collapse(ko.unwrap(n)?"show":"hide")}};class t{constructor(e){this.dates=e.date,this.selectedResources=e.selectedResources,this.articles=ko.observableArray([]),this.dateSubscription=this.dates.subscribe(this.updateMandatoryQuantities.bind(this)),this.selectedResourcesSubscription=this.selectedResources.subscribe(this.handleResourceChange.bind(this)),this.isLoading=ko.observable(!1),this.fetchArticles()}handleResourceChange(e){this.fetchArticles()}toggleCollapse(e){e.isCollapsed(!e.isCollapsed())}getPriceUnit(e){switch(e.info.unit){case"minute":return"minute_rate";case"hour":return"hourly_rate";case"day":return"daily_rate";case"each":return"each";default:return console.error("Unknown unit type for mandatory item:",e.info.unit),e.info.unit}}getPriceName(e){switch(e.info.selected_quantity(),e.info.unit){case"minute":return"minute";case"hour":return"hours";case"day":return"days";default:console.error("Unknown unit type for mandatory item:",e.info.unit)}}incrementQuantity(e){e.selected_quantity(e.selected_quantity()+1)}decrementQuantity(e){const t=e.selected_quantity()-1;e.selected_quantity(t<0?0:t)}updateMandatoryQuantities(e){let t=0;e.forEach((e=>{const n=luxon.DateTime.fromFormat(e.from_,"dd/MM/yyyy HH:mm"),a=luxon.DateTime.fromFormat(e.to_,"dd/MM/yyyy HH:mm");n.isValid&&a.isValid?t+=a-n:console.error("Invalid date range:",e)}));const n=Math.floor(t/6e4),a=Math.floor(n/60),i=Math.floor(a/24);this.articles().forEach((e=>{if(e.info.mandatory){var t=0;switch(e.info.unit){case"minute":t=n;break;case"hour":t=a;break;case"day":t=i;break;default:console.error("Unknown unit type for mandatory item:",e.info.unit),t=0}e.info.selected_quantity(t)}Object.values(e.groups).forEach((function(e){e.forEach((function(e){if(e.mandatory){var t=0;switch(e.unit){case"minute":t=n;break;case"hour":t=a;break;case"day":t=i;break;default:console.error("Unknown unit type for mandatory item:",e.unit),t=0}e.selected_quantity(t)}}))}))}))}calculateTotal(e){var t=parseFloat(e.info.price)*(e.info.selected_quantity()||0);Object.values(e.groups).forEach((function(e){e.forEach((function(e){t+=parseFloat(e.price)*(e.selected_quantity()||0)}))}));const n=t%1!=0,a={maximumFractionDigits:2,minimumFractionDigits:n?2:0},i=t.toLocaleString("nb-NO",a);return n?i:`${i},-`}toLocale(e,t){"string"==typeof e&&(e=+e);const n=e%1!=0,a={minimumFractionDigits:n?void 0!==t?t:2:0},i=e.toLocaleString("nb-NO",a);return n?i:`${i},-`}structureTableData(e){let t={};return e.forEach((function(e){e.parent_mapping_id||(t[e.id]={info:e,groups:{},isCollapsed:ko.observable(!1)})})),e.forEach((function(e){e.name=e.name.replace("- ",""),e.selected_quantity=ko.observable(Math.max(e.selected_quantity||0,0)),e.selected_sum=ko.pureComputed((function(){return(e.selected_quantity()*parseFloat(e.price)).toFixed(2)})),e.parent_mapping_id&&(t[e.parent_mapping_id]?(t[e.parent_mapping_id].groups[e.article_group_name]||(t[e.parent_mapping_id].groups[e.article_group_name]=[]),t[e.parent_mapping_id].groups[e.article_group_name].push(e)):console.error("Parent resource with ID "+e.parent_mapping_id+" does not exist.")),e.computed_selected_article=ko.pureComputed((function(){return e.id,e.selected_quantity(),e.parent_mapping_id,`${e.id}_${e.selected_quantity()}_x_x_${e.parent_mapping_id||"null"}`}))})),t}getRemark(e){const t=e.find((e=>e.article_group_remark));return t?`<br/> <span class="remark">*${t.article_group_remark}</span>`:""}async fetchArticles(){window.application_id=void 0===window.application_id?"":window.application_id,window.reservation_type=void 0===window.reservation_type?"":window.reservation_type,window.reservation_id=void 0===window.reservation_id?"":window.reservation_id;const e={menuaction:"bookingfrontend.uiarticle_mapping.get_articles",sort:"name",application_id,reservation_type,reservation_id,alloc_template_id:null};let t=phpGWLink("bookingfrontend/",e,!0);for(const e of this.selectedResources())t+="&resources[]="+e;this.isLoading(!0);try{const e=await fetch(t);if(!e.ok)throw new Error(`HTTP error! status: ${e.status}`);const n=(await e.json()).data,a=this.structureTableData(n);this.articles(Object.values(a))}catch(e){console.error("Fetching articles failed:",e)}finally{this.updateMandatoryQuantities(this.dates()),this.isLoading(!1)}}cleanText(e){return e.replace(/<\/?[^>]+(>|$)/g,"")}dispose(){this.dateSubscription.dispose(),this.selectedResourcesSubscription.dispose()}}ko.components.register("article-table",{viewModel:{createViewModel:e=>new t(e)},template:'\n        \x3c!-- ko foreach: { data: articles, as: \'resource\' } --\x3e\n        <div class="article-table-wrapper">\n            <div class="article-table-header" data-bind="css: { \'collapsed-head\': resource.isCollapsed() }">\n                \x3c!--                <div class="table article-table resource-table" data-bind="css: { \'collapsed-head\': resource.isCollapsed() }">--\x3e\n                <div class="resource-name" data-bind="text: resource.info.name"></div>\n                <div class="resource-price">\n                    <trans params="group: \'bookingfrontend\',tag: $parent.getPriceUnit(resource), suffix: \':\'"></trans>\n                    \x3c!--ko text: $parent.toLocale(resource.info.price)--\x3e\x3c!--/ko--\x3e\n                </div>\n                <div class="resource-hours">\n                    <trans params="group: \'bookingfrontend\',tag: $parent.getPriceName(resource), suffix: \':\'"></trans>\n                    \x3c!--ko text: resource.info.selected_quantity()--\x3e\x3c!--/ko--\x3e\n                </div>\n                <div class="resource-total">\n                    <trans params="group: \'bookingfrontend\',tag: \'total\', suffix: \':\'"></trans>\n                    \x3c!--ko text:  $parent.calculateTotal(resource)--\x3e\x3c!--/ko--\x3e\n                </div>\n                <div class="resource-expand"\n                     data-bind="click: function() { $parent.toggleCollapse(resource) }">\n                    <button class="btn btn-subtle" type="button" data-toggle="collapse"\n                            data-bind="//click: function() { $parent.toggleCollapse(resource) }"\n                            aria-expanded="true">\n                        \x3c!-- ko if: resource.isCollapsed() --\x3e\n                        <div><i class="fas fa-angle-down"></i></div>\n                        \x3c!-- /ko --\x3e\n                        \x3c!-- ko ifnot: resource.isCollapsed() --\x3e\n                        <div><i class="fas fa-angle-up"></i></div>\n                        \x3c!-- /ko --\x3e\n                    </button>\n                </div>\n            </div>\n            <div style="display: none;">\n                <td colspan="8">\n                    \x3c!-- Hidden inputs for resource --\x3e\n                    <input type="hidden" data-bind="value: resource.info.id" name="resource_ids[]">\n                    <input type="hidden" data-bind="value: resource.info.selected_quantity"\n                           name="resource_quantities[]">\n                    <input type="hidden" data-bind="value: resource.info.mandatory" name="resource_mandatory[]">\n                    <input type="hidden" name="selected_articles[]"\n                           data-bind="value: resource.info.computed_selected_article">\n                    \x3c!-- Add other hidden fields as needed --\x3e\n                </td>\n            </div>\n            <div data-bind="visible: !resource.isCollapsed(), attr: {id: \'resource\' + resource.info.resource_id}"\n                 class="collapsible-part">\n                \x3c!-- ko foreach: { data: Object.keys(resource.groups), as: \'groupName\' } --\x3e\n                <div class="category-table ">\n                    <div class="category-header">\n                        <div class="category-name">\n                            <span class="category-name-title" data-bind="text: groupName"></span>\n                            <span data-bind="html: $parents[1].getRemark(resource.groups[groupName])"></span>\n                        </div>\n                        <div class="category-header-description"><trans params="group: \'bookingfrontend\',tag: \'description\'"></trans></div>\n                        <div class="category-header-unit-price"><trans params="group: \'bookingfrontend\',tag: \'price_unit\'"></trans></div>\n                        <div class="category-header-count"><trans params="group: \'bookingfrontend\',tag: \'amount\'"></div>\n                        <div class="category-header-total"><trans params="group: \'bookingfrontend\',tag: \'total\'"></trans></div>\n                    </div>\n                    <div class="category-articles">\n                        \x3c!-- ko foreach: { data: resource.groups[groupName], as: \'item\' } --\x3e\n                        <div class="category-article-row">\n                            <div class="item-name" data-bind="text: item.name"></div>\n                            <div class="desc-title"><trans params="group: \'bookingfrontend\',tag: \'description\'"></div>\n\n                            <div class="item-description"\n                                 data-bind="text: $parents[2].cleanText(item.article_remark)"></div>\n                            <div class="price-title"><trans params="group: \'bookingfrontend\',tag: \'price_unit\'"></trans></div>\n\n                            <div class="item-price"\n                                 data-bind="text: $parents[2].toLocale(item.price) + (item.unit === \'each\' ? \'/stk\' : \'/\' + item.unit)"></div>\n                            \x3c!--                            <td class="item-quantity">--\x3e\n                            \x3c!--                                <input type="number" class="form-control" min="0"--\x3e\n                            \x3c!--                                       data-bind="value: item.selected_quantity, event: { change: $parent.updateQuantity }">--\x3e\n                            \x3c!--                            </td>--\x3e\n                            <div class="item-quantity">\n                                <button type="button" class=" pe-btn pe-btn-secondary pe-btn--small-circle "\n                                        data-bind="click: function(data, event) { $parents[2].decrementQuantity(item)  }">\n                                    <svg viewBox="0 0 48 48"\n                                         xmlns="http://www.w3.org/2000/svg" ml-update="aware">\n                                        <path class="horizontal" d="M32,26H16a2,2,0,0,1,0-4H32A2,2,0,0,1,32,26Z"/>\n                                    </svg>\n                                </button>\n                                <span style="display: inline-block;min-width: 20px; text-align: center"\n                                      data-bind="text: item.selected_quantity"></span>\n                                <button type="button" class=" pe-btn pe-btn-secondary pe-btn--small-circle "\n                                        data-bind="click: function() { $parents[2].incrementQuantity(item) }">\n                                    <svg viewBox="0 0 48 48"\n                                         xmlns="http://www.w3.org/2000/svg" ml-update="aware">\n                                        <path class="horizontal" d="M32,26H16a2,2,0,0,1,0-4H32A2,2,0,0,1,32,26Z"/>\n                                        <path class="vertical"\n                                              d="M24,34a2,2,0,0,1-2-2V16a2,2,0,0,1,4,0V32A2,2,0,0,1,24,34Z"\n                                        />\n                                    </svg>\n                                </button>\n                            </div>\n                            <div class="sum-title">Total</div>\n                            <div class="item-sum" data-bind="text: $parents[2].toLocale(item.selected_sum(), 2)"></div>\n                            <div class="hidden-inputs" style="display: none;">\n                                \x3c!-- Hidden inputs for each item --\x3e\n                                <input type="hidden" data-bind="value: item.id">\n                                <input type="hidden" data-bind="value: item.mandatory" name="mandatory_items[]">\n                                <input type="hidden" data-bind="value: item.selected_quantity"\n                                       name="selected_quantities[]">\n                                <input type="hidden" data-bind="value: item.parent_mapping_id"\n                                       name="parent_mapping_ids[]">\n                                <input type="text" name="selected_articles[]"\n                                       data-bind="value: item.computed_selected_article">\n\n                            </div>\n                        </div>\n                        \x3c!-- /ko --\x3e\n                    </div>\n                </div>\n                \x3c!-- /ko --\x3e\n            </div>\n        </div>\n        \x3c!-- /ko --\x3e\n    '})})()})();