(window.webpackJsonp=window.webpackJsonp||[]).push([[35],{109:function(e,t,n){"use strict";var r=n(0);n(133);t.a=function(e){var t=e.size,n=void 0===t?"xl":t;return Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)("div",{className:"bwf_clear_30"}),Object(r.createElement)("div",{className:"bwf-spin-loader bwf-spin-loader-".concat(n)}),Object(r.createElement)("div",{className:"bwf_clear_30"}))}},111:function(e,t,n){"use strict";var r=n(0),a=n(4),c=n(15),o=n.n(c),i=n(9),s=n(33),u=n(8),l=n.n(u),f=n(94),b=(n(138),function(e){var t=e.id,n=e.instanceId,c=e.isVisible,u=e.label,l=e.popoverContents,f=e.remove,b=e.screenReaderLabel,m=e.setState,p=e.className;if(b=b||u,!u)return null;u=Object(s.decodeEntities)(u);var O=o()("bwf-tag",p,{"has-remove":!!f}),d="bwf-tag-label-".concat(n),g=Object(r.createElement)(r.Fragment,null,Object(r.createElement)("span",{className:"screen-reader-text"},b),Object(r.createElement)("span",{"aria-hidden":"true"},u));return Object(r.createElement)("span",{className:O},l?Object(r.createElement)(i.Button,{className:"bwf-tag-text",id:d,onClick:function(){return m((function(){return{isVisible:!0}}))}},g):Object(r.createElement)("span",{className:"bwf-tag-text",id:d},g),l&&c&&Object(r.createElement)(i.Popover,{onClose:function(){return m((function(){return{isVisible:!1}}))}},l),f&&Object(r.createElement)(i.Button,{className:"bwf-tag-remove",onClick:function(){return f(t,u)},label:Object(a.sprintf)(Object(a.__)("Remove %s","wp-marketing-automations-crm"),u),"aria-describedby":d},Object(r.createElement)(i.Dashicon,{icon:"dismiss",size:20})))});b.propTypes={id:l.a.oneOfType([l.a.number,l.a.string]),label:l.a.string.isRequired,popoverContents:l.a.node,remove:l.a.func,screenReaderLabel:l.a.string},t.a=Object(f.withState)({isVisible:!1})(Object(f.withInstanceId)(b))},112:function(e,t,n){"use strict";var r=n(24),a=n(48);t.a=function(e){var t=Object(r.b)(),n=Object(a.a)(e),c=n.setLoading,o=n.fetch,i=n.clearError,s=n.setStateProp;return{setLoading:function(e){return t(c(e))},fetch:function(e,n,r){var a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};return t(o(e,n,r,a))},clearError:function(){return t(i())},setStateProp:function(e,n){return t(s(e,n))}}}},114:function(e,t,n){"use strict";var r=n(112),a=n(44);function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function o(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){i(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function i(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}t.a=function(){var e=Object(r.a)("menu").setStateProp,t=(0,Object(a.a)().getActive)();return{setActive:function(n,r){return e("active",o(o({},t),{},i({},n,r)))},setActiveMultiple:function(t){return e("active",t)},setBackLink:function(t){return e("backLink",t)},setL2Title:function(t){return e("l2Title",t)},setL2PostTitle:function(t){return e("l2PostTitle",t)},setL2Nav:function(t){return e("l2Nav",t)},setL2NavType:function(t){return e("l2NavType",t)},setL2Content:function(t){return e("l2Content",t)},setL2NavAlign:function(t){return e("l2NavAlign",t)},setPageHeader:function(t){return e("pageHeader",t)},setBackLinkLabel:function(t){return e("backLinkLabel",t)},setPageCountData:function(t){return e("pageCountData",t)},resetHeaderMenu:function(){e("backLink",""),e("l2Title",""),e("l2PostTitle",""),e("l2Nav",{}),e("l2NavType",""),e("active",{leftNav:"",rightNav:""}),e("l2Content",""),e("l2NavAlign","left"),e("pageHeader","")},setContactL2Menu:function(){return e("l2Nav")}}}},117:function(e,t,n){"use strict";var r=n(0),a=n(8),c=n.n(a);function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function s(e,t){return(s=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function u(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=b(e);if(t){var a=b(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return l(this,n)}}function l(e,t){return!t||"object"!==o(t)&&"function"!=typeof t?f(e):t}function f(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function b(e){return(b=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var m=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&s(e,t)}(o,e);var t,n,a,c=u(o);function o(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,o),(t=c.call(this,e)).scrollTo=t.scrollTo.bind(f(t)),t}return t=o,(n=[{key:"componentDidMount",value:function(){setTimeout(this.scrollTo,250)}},{key:"scrollTo",value:function(){var e=this.props.offset;this.ref.current?window.scrollTo(0,parseInt(e,10)):setTimeout(this.scrollTo,250)}},{key:"render",value:function(){var e=this.props.children;return this.ref=Object(r.createRef)(),Object(r.createElement)("div",{ref:this.ref},e)}}])&&i(t.prototype,n),a&&i(t,a),o}(r.Component);m.propTypes={offset:c.a.string},m.defaultProps={offset:"0"},t.a=m},118:function(e,t,n){"use strict";function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}n.d(t,"a",(function(){return r}))},120:function(e,t,n){"use strict";var r=n(0);n(135);t.a=function(e){return Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)("div",{className:"bwf_align_center"},Object(r.createElement)("div",{className:"bwf-w-100",dangerouslySetInnerHTML:{__html:bwfcrm_contacts_data.icons.success}})))}},121:function(e,t,n){"use strict";var r=n(0);n(136);t.a=function(e){return Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)("div",{className:"bwf_align_center"},Object(r.createElement)("div",{className:"bwf-w-100",dangerouslySetInnerHTML:{__html:bwfcrm_contacts_data.icons.error}})))}},123:function(e,t,n){"use strict";var r=n(0),a=n(4),c=n(5),o=n(8),i=n.n(o),s=n(15),u=n.n(s),l=(n(134),n(160));function f(e){return function(e){if(Array.isArray(e))return b(e)}(e)||function(e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return b(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return b(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function b(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function m(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}function p(e){return(p="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function O(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function d(e,t){return(d=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function g(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=h(e);if(t){var a=h(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return y(this,n)}}function y(e,t){return!t||"object"!==p(t)&&"function"!=typeof t?j(e):t}function j(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function h(e){return(h=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var v=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&d(e,t)}(i,e);var t,n,c,o=g(i);function i(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),(t=o.call(this,e)).state={options:[]},t.appendFreeTextSearch=t.appendFreeTextSearch.bind(j(t)),t.fetchOptions=t.fetchOptions.bind(j(t)),t.updateSelected=t.updateSelected.bind(j(t)),t}return t=i,(n=[{key:"getAutocompleter",value:function(){return this.props.autocompleter&&"object"===p(this.props.autocompleter)?this.props.autocompleter:{}}},{key:"getFormattedOptions",value:function(e,t){var n=this.getAutocompleter(),r=[];return e.forEach((function(e){var a={key:n.getOptionIdentifier(e),label:n.getOptionLabel(e,t),keywords:n.getOptionKeywords(e).filter(Boolean),value:e};r.push(a)})),r}},{key:"fetchOptions",value:function(e,t){var n=this;return this.props.bwfEnableEmptySearch||t?this.getAutocompleter().options(t).then(function(){var e,r=(e=regeneratorRuntime.mark((function e(r){var a;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return a=n.getFormattedOptions(r,t),n.setState({options:a}),e.abrupt("return",a);case 3:case"end":return e.stop()}}),e)})),function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){m(c,r,a,o,i,"next",e)}function i(e){m(c,r,a,o,i,"throw",e)}o(void 0)}))});return function(e){return r.apply(this,arguments)}}()):[]}},{key:"updateSelected",value:function(e){var t=this.props.onChange,n=this.getAutocompleter();t(e.map((function(e){return!e.notFound&&(e.value?n.getOptionCompletion(e.value):e)})).filter(Boolean))}},{key:"appendFreeTextSearch",value:function(e,t){var n=this.props.allowFreeTextSearch;if(!t||!t.length)return[];if(!n)return e.length>0?e:[{label:Object(a.__)("Not Found","wp-marketing-automations-crm"),key:"",notFound:!0}];var r=this.getAutocompleter();return[].concat(f(r.getFreeTextOptions(t,e)),f(e))}},{key:"render",value:function(){var e=this.getAutocompleter(),t=this.props,n=t.className,a=t.inlineTags,c=t.placeholder,o=t.selected,i=t.showClearButton,s=t.staticResults,f=t.disabled,b=(t.multiple,this.state.options),m=e.inputType?e.inputType:"text";return Object(r.createElement)("div",null,Object(r.createElement)(l.a,{className:u()("bwf-search",n,{"is-static-results":s}),disabled:f,hideBeforeSearch:!1,inlineTags:a,isSearchable:!0,label:c,getSearchExpression:e.getSearchExpression,multiple:!0,placeholder:c,onChange:this.updateSelected,onFilter:this.appendFreeTextSearch,onSearch:this.fetchOptions,options:b,remove:this.props.onRemoveTag,searchDebounceTime:500,searchInputType:m,selected:o,showClearButton:i,bwfMaintainSingleTerm:this.props.bwfMaintainSingleTerm}))}}])&&O(t.prototype,n),c&&O(t,c),i}(r.Component);v.propTypes={allowFreeTextSearch:i.a.bool,className:i.a.string,onChange:i.a.func,autocompleter:i.a.object,placeholder:i.a.string,selected:i.a.arrayOf(i.a.shape({key:i.a.oneOfType([i.a.number,i.a.string]).isRequired,label:i.a.string})),inlineTags:i.a.bool,showClearButton:i.a.bool,staticResults:i.a.bool,disabled:i.a.bool,bwfMaintainSingleTerm:i.a.bool,bwfEnableEmptySearch:i.a.bool},v.defaultProps={allowFreeTextSearch:!1,onChange:c.noop,selected:[],inlineTags:!1,showClearButton:!1,staticResults:!1,disabled:!1,bwfMaintainSingleTerm:!1,bwfEnableEmptySearch:!1},t.a=v},133:function(e,t,n){},134:function(e,t,n){},135:function(e,t,n){},136:function(e,t,n){},138:function(e,t,n){},146:function(e,t){e.exports=function(e){return e.webpackPolyfill||(e.deprecate=function(){},e.paths=[],e.children||(e.children=[]),Object.defineProperty(e,"loaded",{enumerable:!0,get:function(){return e.l}}),Object.defineProperty(e,"id",{enumerable:!0,get:function(){return e.i}}),e.webpackPolyfill=1),e}},186:function(e,t,n){"use strict";var r=n(118),a=n(25),c=n(0);function o(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}t.a=function(e){var t=e.icon,n=e.size,i=void 0===n?24:n,s=Object(a.a)(e,["icon","size"]);return Object(c.cloneElement)(t,function(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?o(Object(n),!0).forEach((function(t){Object(r.a)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}({width:i,height:i},s))}},195:function(e,t,n){"use strict";var r=n(0),a=n(93),c=Object(r.createElement)(a.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(a.Path,{d:"M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"}));t.a=c},196:function(e,t,n){"use strict";var r=n(0),a=n(93),c=Object(r.createElement)(a.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(a.Path,{d:"M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"}));t.a=c},202:function(e,t,n){"use strict";var r=n(0),a=n(93),c=Object(r.createElement)(a.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(r.createElement)(a.Path,{d:"M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"}));t.a=c},203:function(e,t,n){"use strict";var r=n(0),a=n(93),c=Object(r.createElement)(a.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(r.createElement)(a.Path,{d:"M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"}));t.a=c},209:function(e,t,n){"use strict";var r=n(114),a=n(0),c=n(4);t.a=function(e,t,n){var o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"",i=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},s=bwfcrm_contacts_data&&bwfcrm_contacts_data.contacts_count?parseInt(bwfcrm_contacts_data.contacts_count):0,u=Object(r.a)(),l=u.setActiveMultiple,f=u.resetHeaderMenu,b=u.setL2NavType,m=u.setL2Nav,p=u.setBackLink,O=u.setL2Title,d=u.setL2Content,g=u.setBackLinkLabel,y=u.setPageHeader;return Object(a.useEffect)((function(){f(),!t&&s>0&&b("menu"),!t&&s>0&&m(i.contacts_nav),l({leftNav:"contacts",rightNav:e}),t&&p(t),t&&g("All Contacts"),n&&O(n),n&&"Export"===n&&(o&&d(o),b("menu"),m({export:{name:Object(c.__)("All","wp-marketing-automations"),link:"admin.php?page=autonami&path=/export"}})),!t&&s>0&&o&&d(o),y("Contacts")}),[e,n]),!0}},705:function(e,t,n){},706:function(e,t,n){},734:function(e,t,n){"use strict";n.r(t);var r=n(0),a=n(17),c=(n(705),n(4)),o=n(15),i=n.n(o),s=n(5),u=n(16),l=n.n(u),f=n(9),b=n(31),m=n(130),p=n(45),O=n(47),d=n(7),g=n(123),y=n(116),j=n.n(y),h=n(163),v=n.n(h),w=n(125),E=n.n(w),_=n(20),P=n(108),k=n.n(P);function S(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}var T=function(e){return e.name},N={name:"tags",className:"bwf-search-bwf-tags-result",options:function(e){return(t=regeneratorRuntime.mark((function e(t){var n,r;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(!j()(t)){e.next=2;break}return e.abrupt("return",[]);case 2:return n={search:t,limit:5,offset:0},e.next=5,l()({path:Object(d.f)("/tags?"+Object(a.stringify)(n)),method:"GET"});case 5:return r=e.sent,e.abrupt("return",E()(r,"result")?r.result:[]);case 7:case"end":return e.stop()}}),e)})),n=function(){var e=this,n=arguments;return new Promise((function(r,a){var c=t.apply(e,n);function o(e){S(c,r,a,o,i,"next",e)}function i(e){S(c,r,a,o,i,"throw",e)}o(void 0)}))},function(e){return n.apply(this,arguments)})(e);var t,n},isDebounced:!0,getOptionIdentifier:function(e){return e.ID},getOptionKeywords:function(e){return[e.name]},getFreeTextOptions:function(e,t){return[{key:"name",label:Object(r.createElement)("span",{key:"name",className:"bwf-search-result-name"},k()({mixedString:Object(c.__)("All tags with names that include {{query /}}","wp-marketing-automations-crm"),components:{query:Object(r.createElement)("strong",{className:"components-form-token-field__suggestion-match"},e)}})),value:{id:e,name:e,tags:t.map((function(e){return E()(e,"value")?e.value:e})),searchTerm:e}}]},getOptionLabel:function(e,t){var n=Object(d.e)(T(e),t)||{};return Object(r.createElement)("span",{key:"name",className:"bwf-search-result-name","aria-label":T(e)},n.suggestionBeforeMatch,Object(r.createElement)("strong",{className:"components-form-token-field__suggestion-match"},n.suggestionMatch),n.suggestionAfterMatch)},getOptionCompletion:function(e){return e}};function x(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}var C=function(e){var t=e.query,n=t.hasOwnProperty("s")?t.s:"",a=j()(n)?[]:[{key:n,label:Object(c.__)("Search Tag: ","wp-marketing-automations-crm")+n,bwfLabelSource:"bwfcrm_tags",isSearchTerm:!0}],o=function(){var e,n=(e=regeneratorRuntime.mark((function e(n){var r,a,c,o;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(v()(n)){e.next=2;break}return e.abrupt("return");case 2:if(j()(n)||(r=n[n.length-1],(n=[])[0]=r),a=n.find((function(e){return E()(e,"searchTerm")})),!(Object(s.isUndefined)(a)&&n.length>0&&n[0].hasOwnProperty("name"))){e.next=7;break}return Object(_.j)({s:n[0].name},"/manage/tags",t),e.abrupt("return");case 7:if(c=Object(s.isUndefined)(a)?void 0:a.searchTerm,o=E()(t,"s")&&!j()(t.s)?t.s:"",c!==o){e.next=11;break}return e.abrupt("return");case 11:Object(_.j)({s:c},"/manage/tags",t);case 12:case"end":return e.stop()}}),e)})),function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){x(c,r,a,o,i,"next",e)}function i(e){x(c,r,a,o,i,"throw",e)}o(void 0)}))});return function(e){return n.apply(this,arguments)}}();return Object(r.createElement)(g.a,{autocompleter:N,multiple:!1,allowFreeTextSearch:!0,inlineTags:!0,selected:a,onChange:o,placeholder:Object(c.__)("Search by name","wp-marketing-automations-crm"),showClearButton:!0,disabled:!1})},D=n(46);function I(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function L(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?I(Object(n),!0).forEach((function(t){A(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):I(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function A(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function R(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},c=Object.keys(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var B=function(){var e=Object(D.a)("taglist"),t=e.getStateProp;return L(L({},R(e,["getStateProp"])),{},{getTags:function(){return t("tags")},getPageNumber:function(){return parseInt(t("offset"))/parseInt(t("limit"))+1},getPerPageCount:function(){return parseInt(t("limit"))},getTotalCount:function(){return parseInt(t("total"))},getCountData:function(){return t("countData")},getContactCountData:function(){return t("contactCountData")}})},M=n(112);function F(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function V(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?F(Object(n),!0).forEach((function(t){q(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):F(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function q(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function H(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},c=Object.keys(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var z=function(){var e=Object(M.a)("taglist"),t=e.fetch,n=e.setStateProp;return V(V({},H(e,["fetch","setStateProp"])),{},{fetch:function(e,n,r){var a=arguments.length>3&&void 0!==arguments[3]&&arguments[3],c=e.s,o=(e.page,e.filter,e.path,H(e,["s","page","filter","path"])),i={offset:n,limit:r,search:c,filters:o,get_wc:Object(d.V)(),grab_totals:a};t("GET",Object(d.f)("/tags"),i)},setStateTagValues:function(e){n("tags",e)},setStateTagValuesByKey:function(e,t){n(e,t)}})},G=(n(706),n(120)),K=n(121),J=n(109),U=n(13),Q=n(209),$=n(44),X=n(114);function W(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}function Y(e){return function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){W(c,r,a,o,i,"next",e)}function i(e){W(c,r,a,o,i,"throw",e)}o(void 0)}))}}function Z(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function ee(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?Z(Object(n),!0).forEach((function(t){te(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):Z(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function te(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function ne(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],r=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(r=(o=i.next()).done)&&(n.push(o.value),!t||n.length!==t);r=!0);}catch(e){a=!0,c=e}finally{try{r||null==i.return||i.return()}finally{if(a)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return re(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return re(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function re(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var ae=function(e){var t=e.query,n=B(),o=z(),u=ne(Object(r.useState)(!1),2),g=u[0],y=u[1],j=ne(Object(r.useState)(!1),2),h=j[0],v=j[1],w=ne(Object(r.useState)({}),2),E=w[0],P=w[1],k=o.fetch,S=o.setStateTagValues,T=o.setStateTagValuesByKey,N=ne(Object(r.useState)(!1),2),x=N[0],D=N[1],I=n.getTags,L=n.getPageNumber,A=n.getPerPageCount,R=n.getLoading,M=n.getTotalCount,F=n.getCountData,V=n.getContactCountData,q=Object(r.useContext)(d.b),H=L(),W=A(),Z=M(),te=R(),re=F(),ae=I(),ce=V(),oe=ne(Object(r.useState)({}),2),ie=oe[0],se=oe[1];Object(r.useEffect)((function(){se(ce)}),[ce]);var ue=Object(r.createElement)(f.Button,{isPrimary:!0,key:"add",className:"bwf-display-flex",onClick:function(){P({}),v(!0),D(!1)}},Object(c.__)("Add New","wp-marketing-automations-crm")),le=Object($.a)().getPageCountData,fe=Object(X.a)().setPageCountData,be=le();Object(r.useEffect)((function(){fe(ee(ee({},be),re))}),[re]),Object(Q.a)("manage_tags","",Object(c.__)("ALL TAGS","wp-marketing-automations-crm"),ue),Object(r.useEffect)((function(){k(t,0,25,!0),y(!1)}),[t.s]),Object(r.useEffect)((function(){if(!g&&!Object(s.isEmpty)(ae)&&!te)try{var e={tag_ids:[]};ae.map((function(t){e.tag_ids.push(t.ID)})),l()({method:"GET",path:Object(d.f)("/tags/contacts?"+Object(a.stringify)(e))}).then((function(e){200==e.code&&(T("contactCountData",ee(ee({},e.result),ce)),y(!0))}))}catch(e){console.log(e)}}),[ae]);var me=i()("bwfcrm-contacts-tags",{"has-search":!0}),pe=[{key:"actions",label:"",isLeftAligned:!1,cellClassName:"bwf-col-action bwf-w-30"},{key:"tags",label:Object(c.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"createdon",label:Object(c.__)("Created On","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"contacts",label:Object(c.__)("Contacts","wp-marketing-automations-crm"),isLeftAligned:!0}],Oe=function(e){e!==W&&(k(t,0,e),y(!1))},de=function(e){return Object(r.createElement)(p.a,{label:Object(c.__)("Quick Actions","wp-marketing-automations-crm"),menuPosition:"bottom right",renderContent:function(t){var n=t.onToggle;return Object(r.createElement)(r.Fragment,null,Object(r.createElement)(O.a,{isClickable:!0,onInvoke:function(){Object(_.j)({filter:"advanced",path:"/contacts","tags_any[]":e.ID},"/",{}),n()}},Object(r.createElement)(b.a,{justify:"flex-start"},Object(r.createElement)(b.c,null,Object(r.createElement)(U.a,{icon:"view"})),Object(r.createElement)(b.c,null,Object(c.__)("View Contacts","wp-marketing-automations-crm")))),Object(r.createElement)(O.a,{isClickable:!0,onInvoke:function(){P(e),v(!0),D(!1),n()}},Object(r.createElement)(b.a,{justify:"flex-start"},Object(r.createElement)(b.c,null,Object(r.createElement)(U.a,{icon:"edit"})),Object(r.createElement)(b.c,null,Object(c.__)("Edit","wp-marketing-automations-crm")))),Object(r.createElement)(O.a,{isClickable:!0,onInvoke:function(){P(ee(ee({},E),{},{loading:!0,delete:!0,deleteid:e.ID})),v(!0),n()}},Object(r.createElement)(b.a,{justify:"flex-start"},Object(r.createElement)(b.c,null,Object(r.createElement)(U.a,{icon:"trash"})),Object(r.createElement)(b.c,null,Object(c.__)("Delete","wp-marketing-automations-crm")))))}})},ge=ae.map((function(e){var t,n;return[{display:de(e),value:"action"},{display:e.name,value:e.ID},{display:(n=e.created_at,Object(r.createElement)("div",{className:"bwf-display-flex-column"},Object(r.createElement)("span",null,Object(d.A)(n)))),value:e.created_at},{display:(t=e,g?ie.hasOwnProperty(parseInt(t.ID))&&ie[parseInt(t.ID)].contact_count>0?Object(r.createElement)("div",{className:"bwf-display-flex-column"},Object(r.createElement)("a",{onClick:function(){Object(_.j)({filter:"advanced",path:"/contacts","tags_any[]":t.ID},"/",{})},className:"bwf-a-no-underline"},parseInt(ie[parseInt(t.ID)].subscribers_count)+" of "+ie[parseInt(t.ID)].contact_count),Object(r.createElement)("span",null,Object(c.__)("Subscribed","wp-marketing-automations-crm"))):"-":Object(r.createElement)("span",{className:"bwf-placeholder-temp bwf-w-150",title:"Loading"},"Loading")),value:""}]})),ye=function(){var e=Y(regeneratorRuntime.mark((function e(n){var r;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(!n.ID){e.next=13;break}return r=ae.map((function(e){return parseInt(e.ID)===parseInt(n.ID)?n:e})),e.prev=2,e.next=5,l()({path:Object(d.f)("/tags/".concat(n.ID,"/")),method:"POST",data:{tag_name:n.name},headers:{"Content-Type":"application/json"}}).then((function(e){200==e.code&&(v(!1),S(r),P({}),q(e.message),Object(d.J)(q,2e3))}));case 5:e.next=11;break;case 7:e.prev=7,e.t0=e.catch(2),P(ee(ee({},E),{},{error:!0,loading:!0,message:e.t0.message})),setTimeout((function(){v(!1),P({})}),2e3);case 11:e.next=22;break;case 13:return e.prev=13,e.next=16,l()({path:Object(d.f)("/tags/"),method:"POST",data:{tags:[n.name]},headers:{"Content-Type":"application/json"}}).then((function(e){200==e.code&&(v(!1),k(t,(H-1)*W,W),P({}),y(!1),q(e.message),Object(d.J)(q,2e3))}));case 16:e.next=22;break;case 18:e.prev=18,e.t1=e.catch(13),P(ee(ee({},E),{},{error:!0,loading:!0,message:e.t1.message})),setTimeout((function(){v(!1),P({})}),2e3);case 22:case"end":return e.stop()}}),e,null,[[2,7],[13,18]])})));return function(t){return e.apply(this,arguments)}}(),je=function(){var e=Y(regeneratorRuntime.mark((function e(n){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(!n){e.next=10;break}return e.prev=1,e.next=4,l()({path:Object(d.f)("/tags/".concat(n,"/")),method:"POST",data:{tag_id:parseInt(n)},headers:{"X-HTTP-Method-Override":"DELETE"}}).then((function(e){200==e.code&&(P(ee(ee({},E),{},{success:!0,loading:!0,message:e.message,delete:!0})),setTimeout((function(){v(!1),k(t,(H-1)*W,W),y(!1),P({})}),2e3))}));case 4:e.next=10;break;case 6:e.prev=6,e.t0=e.catch(1),P(ee(ee({},E),{},{error:!0,loading:!0,message:e.t0.message,delete:!0})),setTimeout((function(){v(!1),P({})}),2e3);case 10:case"end":return e.stop()}}),e,null,[[1,6]])})));return function(t){return e.apply(this,arguments)}}();return Object(r.createElement)("div",{className:"bwf-c-tag-list-section"},Object(r.createElement)(m.a,{className:me,rows:ge,headers:pe,query:{paged:H},rowsPerPage:W,totalRows:Z?parseInt(Z):0,isLoading:te,onPageChange:function(e,n){k(t,(e-1)*W,W),y(!1)},onQueryChange:function(e){return"per_page"!==e?function(){}:Oe},showMenu:!1,actions:[Object(r.createElement)(C,{key:"search",query:t})],rowHeader:!0,emptyMessage:Object(c.__)("No tags found","wp-marketing-automations-crm")}),h&&Object(r.createElement)(f.Modal,{title:!E.delete&&(E.ID?Object(c.__)("Edit Tags","wp-marketing-automations-crm"):Object(c.__)("Add Tag","wp-marketing-automations-crm")),onRequestClose:function(){return v(!1)},className:"bwf-admin-modal "+(E.loading?"bwf-admin-modal-no-header ":" ")+(E.delete?" bwf-admin-modal-small ":"bwf-admin-modal-medium")},E.loading?E.success?Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)(G.a,null),Object(r.createElement)("div",{className:"bwf-h1"},E.message))):E.error?Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)(K.a,null),Object(r.createElement)("div",{className:"bwf-h1"},E.message))):E.delete&&!E.deleteconfirm?Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-h4"},Object(c.__)("Delete","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf-t-center bwf-form-buttons"},Object(r.createElement)("div",{className:"bwf-h2"},Object(c.__)("Are you sure?","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf_clear_15"}),Object(r.createElement)("div",{className:"bwf-h4 bwf-h4-grey"},Object(c.__)("Once you delete this item. It will no longer available.","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf_clear_20"}),Object(r.createElement)("div",{className:"bwf_text_right"},Object(r.createElement)(f.Button,{isTertiary:!0,onClick:function(){P({}),v(!1)}},Object(c.__)("Cancel","wp-marketing-automations-crm")),Object(r.createElement)(f.Button,{isPrimary:!0,className:"bwf-delete-btn",onClick:function(){P(ee(ee({},E),{},{loading:!0,deleteconfirm:!0,deleteid:E.deleteid,delete:!0})),je(E.deleteid)}},Object(c.__)("Delete","wp-marketing-automations-crm"))))):Object(r.createElement)(J.a,null):Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-form-fields",onKeyPress:function(e){"Enter"===e.key&&(Object(s.isEmpty)(E.name)||(P(ee(ee({},E),{},{loading:!0})),ye(E)))}},x&&Object(r.createElement)(f.Notice,{status:"error",onRemove:function(){return D(!1)}},Object(c.__)("Name is required","wp-marketing-automations-crm")),Object(r.createElement)(f.TextControl,{label:"",type:"text",value:E.name?E.name:"",autoFocus:!0,placeholder:Object(c.__)("Add Tag Name","wp-marketing-automations-crm"),onChange:function(e){P(ee(ee({},E),{},{name:e}))}}),Object(r.createElement)("div",{className:"bwf_clear_10"}),Object(r.createElement)("div",{className:"bwf_text_right"},Object(r.createElement)(f.Button,{isTertiary:!0,className:"bwf-mr-5",onClick:function(){return v(!1)}},Object(c.__)("Cancel","wp-marketing-automations-crm")),Object(r.createElement)(f.Button,{isPrimary:!0,onClick:function(){Object(s.isEmpty)(E.name)?D(!0):(P(ee(ee({},E),{},{loading:!0})),ye(E))},className:"bwf-ml-0"},E.ID?Object(c.__)("Save","wp-marketing-automations-crm"):Object(c.__)("Add","wp-marketing-automations-crm")))))))},ce=n(117);t.default=function(){var e=location&&location.search?Object(a.parse)(location.search.substring(1)):{};return Object(d.d)("Tags"),Object(r.createElement)(r.Fragment,null,Object(r.createElement)(ce.a,null),Object(r.createElement)(ae,{query:e}))}}}]);