(window.webpackJsonp=window.webpackJsonp||[]).push([[26],{111:function(e,t,n){"use strict";var r=n(0);n(133);t.a=function(e){var t=e.size,n=void 0===t?"xl":t;return Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)("div",{className:"bwf_clear_30"}),Object(r.createElement)("div",{className:"bwf-spin-loader bwf-spin-loader-".concat(n)}),Object(r.createElement)("div",{className:"bwf_clear_30"}))}},113:function(e,t,n){"use strict";var r=n(0),a=n(4),c=n(15),o=n.n(c),i=n(9),s=n(34),l=n(8),u=n.n(l),f=n(96),m=(n(138),function(e){var t=e.id,n=e.instanceId,c=e.isVisible,l=e.label,u=e.popoverContents,f=e.remove,m=e.screenReaderLabel,b=e.setState,p=e.className;if(m=m||l,!l)return null;l=Object(s.decodeEntities)(l);var d=o()("bwf-tag",p,{"has-remove":!!f}),O="bwf-tag-label-".concat(n),g=Object(r.createElement)(r.Fragment,null,Object(r.createElement)("span",{className:"screen-reader-text"},m),Object(r.createElement)("span",{"aria-hidden":"true"},l));return Object(r.createElement)("span",{className:d},u?Object(r.createElement)(i.Button,{className:"bwf-tag-text",id:O,onClick:function(){return b((function(){return{isVisible:!0}}))}},g):Object(r.createElement)("span",{className:"bwf-tag-text",id:O},g),u&&c&&Object(r.createElement)(i.Popover,{onClose:function(){return b((function(){return{isVisible:!1}}))}},u),f&&Object(r.createElement)(i.Button,{className:"bwf-tag-remove",onClick:function(){return f(t,l)},label:Object(a.sprintf)(Object(a.__)("Remove %s","wp-marketing-automations-crm"),l),"aria-describedby":O},Object(r.createElement)(i.Dashicon,{icon:"dismiss",size:20})))});m.propTypes={id:u.a.oneOfType([u.a.number,u.a.string]),label:u.a.string.isRequired,popoverContents:u.a.node,remove:u.a.func,screenReaderLabel:u.a.string},t.a=Object(f.withState)({isVisible:!1})(Object(f.withInstanceId)(m))},117:function(e,t,n){"use strict";var r=n(0),a=n(8),c=n.n(a);function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function s(e,t){return(s=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function l(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=m(e);if(t){var a=m(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return u(this,n)}}function u(e,t){return!t||"object"!==o(t)&&"function"!=typeof t?f(e):t}function f(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function m(e){return(m=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var b=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&s(e,t)}(o,e);var t,n,a,c=l(o);function o(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,o),(t=c.call(this,e)).scrollTo=t.scrollTo.bind(f(t)),t}return t=o,(n=[{key:"componentDidMount",value:function(){setTimeout(this.scrollTo,250)}},{key:"scrollTo",value:function(){var e=this.props.offset;this.ref.current?window.scrollTo(0,parseInt(e,10)):setTimeout(this.scrollTo,250)}},{key:"render",value:function(){var e=this.props.children;return this.ref=Object(r.createRef)(),Object(r.createElement)("div",{ref:this.ref},e)}}])&&i(t.prototype,n),a&&i(t,a),o}(r.Component);b.propTypes={offset:c.a.string},b.defaultProps={offset:"0"},t.a=b},119:function(e,t,n){"use strict";function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}n.d(t,"a",(function(){return r}))},121:function(e,t,n){"use strict";var r=n(0);n(135);t.a=function(e){return Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)("div",{className:"bwf_align_center"},Object(r.createElement)("div",{className:"bwf-w-100",dangerouslySetInnerHTML:{__html:bwfcrm_contacts_data.icons.success}})))}},122:function(e,t,n){"use strict";var r=n(0);n(136);t.a=function(e){return Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)("div",{className:"bwf_align_center"},Object(r.createElement)("div",{className:"bwf-w-100",dangerouslySetInnerHTML:{__html:bwfcrm_contacts_data.icons.error}})))}},124:function(e,t,n){"use strict";var r=n(0),a=n(4),c=n(5),o=n(8),i=n.n(o),s=n(15),l=n.n(s),u=(n(134),n(160));function f(e){return function(e){if(Array.isArray(e))return m(e)}(e)||function(e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return m(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return m(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function m(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function b(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}function p(e){return(p="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function O(e,t){return(O=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function g(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=y(e);if(t){var a=y(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return h(this,n)}}function h(e,t){return!t||"object"!==p(t)&&"function"!=typeof t?j(e):t}function j(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function y(e){return(y=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var v=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&O(e,t)}(i,e);var t,n,c,o=g(i);function i(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),(t=o.call(this,e)).state={options:[]},t.appendFreeTextSearch=t.appendFreeTextSearch.bind(j(t)),t.fetchOptions=t.fetchOptions.bind(j(t)),t.updateSelected=t.updateSelected.bind(j(t)),t}return t=i,(n=[{key:"getAutocompleter",value:function(){return this.props.autocompleter&&"object"===p(this.props.autocompleter)?this.props.autocompleter:{}}},{key:"getFormattedOptions",value:function(e,t){var n=this.getAutocompleter(),r=[];return e.forEach((function(e){var a={key:n.getOptionIdentifier(e),label:n.getOptionLabel(e,t),keywords:n.getOptionKeywords(e).filter(Boolean),value:e};r.push(a)})),r}},{key:"fetchOptions",value:function(e,t){var n=this;return this.props.bwfEnableEmptySearch||t?this.getAutocompleter().options(t).then(function(){var e,r=(e=regeneratorRuntime.mark((function e(r){var a;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return a=n.getFormattedOptions(r,t),n.setState({options:a}),e.abrupt("return",a);case 3:case"end":return e.stop()}}),e)})),function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){b(c,r,a,o,i,"next",e)}function i(e){b(c,r,a,o,i,"throw",e)}o(void 0)}))});return function(e){return r.apply(this,arguments)}}()):[]}},{key:"updateSelected",value:function(e){var t=this.props.onChange,n=this.getAutocompleter();t(e.map((function(e){return!e.notFound&&(e.value?n.getOptionCompletion(e.value):e)})).filter(Boolean))}},{key:"appendFreeTextSearch",value:function(e,t){var n=this.props.allowFreeTextSearch;if(!t||!t.length)return[];if(!n)return e.length>0?e:[{label:Object(a.__)("Not Found","wp-marketing-automations-crm"),key:"",notFound:!0}];var r=this.getAutocompleter();return[].concat(f(r.getFreeTextOptions(t,e)),f(e))}},{key:"render",value:function(){var e=this.getAutocompleter(),t=this.props,n=t.className,a=t.inlineTags,c=t.placeholder,o=t.selected,i=t.showClearButton,s=t.staticResults,f=t.disabled,m=(t.multiple,this.state.options),b=e.inputType?e.inputType:"text";return Object(r.createElement)("div",null,Object(r.createElement)(u.a,{className:l()("bwf-search",n,{"is-static-results":s}),disabled:f,hideBeforeSearch:!1,inlineTags:a,isSearchable:!0,label:c,getSearchExpression:e.getSearchExpression,multiple:!0,placeholder:c,onChange:this.updateSelected,onFilter:this.appendFreeTextSearch,onSearch:this.fetchOptions,options:m,remove:this.props.onRemoveTag,searchDebounceTime:500,searchInputType:b,selected:o,showClearButton:i,bwfMaintainSingleTerm:this.props.bwfMaintainSingleTerm}))}}])&&d(t.prototype,n),c&&d(t,c),i}(r.Component);v.propTypes={allowFreeTextSearch:i.a.bool,className:i.a.string,onChange:i.a.func,autocompleter:i.a.object,placeholder:i.a.string,selected:i.a.arrayOf(i.a.shape({key:i.a.oneOfType([i.a.number,i.a.string]).isRequired,label:i.a.string})),inlineTags:i.a.bool,showClearButton:i.a.bool,staticResults:i.a.bool,disabled:i.a.bool,bwfMaintainSingleTerm:i.a.bool,bwfEnableEmptySearch:i.a.bool},v.defaultProps={allowFreeTextSearch:!1,onChange:c.noop,selected:[],inlineTags:!1,showClearButton:!1,staticResults:!1,disabled:!1,bwfMaintainSingleTerm:!1,bwfEnableEmptySearch:!1},t.a=v},133:function(e,t,n){},134:function(e,t,n){},135:function(e,t,n){},136:function(e,t,n){},138:function(e,t,n){},146:function(e,t){e.exports=function(e){return e.webpackPolyfill||(e.deprecate=function(){},e.paths=[],e.children||(e.children=[]),Object.defineProperty(e,"loaded",{enumerable:!0,get:function(){return e.l}}),Object.defineProperty(e,"id",{enumerable:!0,get:function(){return e.i}}),e.webpackPolyfill=1),e}},186:function(e,t,n){"use strict";var r=n(119),a=n(27),c=n(0);function o(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}t.a=function(e){var t=e.icon,n=e.size,i=void 0===n?24:n,s=Object(a.a)(e,["icon","size"]);return Object(c.cloneElement)(t,function(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?o(Object(n),!0).forEach((function(t){Object(r.a)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}({width:i,height:i},s))}},195:function(e,t,n){"use strict";var r=n(0),a=n(95),c=Object(r.createElement)(a.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(a.Path,{d:"M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"}));t.a=c},196:function(e,t,n){"use strict";var r=n(0),a=n(95),c=Object(r.createElement)(a.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(a.Path,{d:"M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"}));t.a=c},203:function(e,t,n){"use strict";var r=n(0),a=n(95),c=Object(r.createElement)(a.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(r.createElement)(a.Path,{d:"M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"}));t.a=c},204:function(e,t,n){"use strict";var r=n(0),a=n(95),c=Object(r.createElement)(a.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(r.createElement)(a.Path,{d:"M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"}));t.a=c},206:function(e,t,n){"use strict";var r=n(46),a=n(0),c=n(4);t.a=function(e,t,n){var o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"",i=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},s=bwfcrm_contacts_data&&bwfcrm_contacts_data.contacts_count?parseInt(bwfcrm_contacts_data.contacts_count):0,l=Object(r.a)(),u=l.setActiveMultiple,f=l.resetHeaderMenu,m=l.setL2NavType,b=l.setL2Nav,p=l.setBackLink,d=l.setL2Title,O=l.setL2Content,g=l.setBackLinkLabel,h=l.setPageHeader;return Object(a.useEffect)((function(){f(),!t&&s>0&&m("menu"),!t&&s>0&&b(i.contacts_nav),u({leftNav:"contacts",rightNav:e}),t&&p(t),t&&g("All Contacts"),n&&d(n),n&&"Export"===n&&(o&&O(o),m("menu"),b({export:{name:Object(c.__)("All","wp-marketing-automations"),link:"admin.php?page=autonami&path=/export"}})),!t&&s>0&&o&&O(o),h("Contacts")}),[e,n]),!0}},706:function(e,t,n){},707:function(e,t,n){},734:function(e,t,n){"use strict";n.r(t);var r=n(0),a=n(18),c=(n(706),n(4)),o=n(15),i=n.n(o),s=n(5),l=n(16),u=n.n(l),f=n(9),m=n(32),b=n(130),p=n(47),d=n(49),O=n(7),g=n(124),h=n(115),j=n.n(h),y=n(163),v=n.n(y),w=n(118),_=n.n(w),E=n(17),k=n(110),P=n.n(k);function S(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}var x=function(e){return e.name},T={name:"lists",className:"bwf-search-bwf-lists-result",options:function(e){return(t=regeneratorRuntime.mark((function e(t){var n,r;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(!j()(t)){e.next=2;break}return e.abrupt("return",[]);case 2:return n={search:t,limit:5,offset:0},e.next=5,u()({path:Object(O.g)("/lists?"+Object(a.stringify)(n)),method:"GET"});case 5:return r=e.sent,e.abrupt("return",_()(r,"result")?r.result:[]);case 7:case"end":return e.stop()}}),e)})),n=function(){var e=this,n=arguments;return new Promise((function(r,a){var c=t.apply(e,n);function o(e){S(c,r,a,o,i,"next",e)}function i(e){S(c,r,a,o,i,"throw",e)}o(void 0)}))},function(e){return n.apply(this,arguments)})(e);var t,n},isDebounced:!0,getOptionIdentifier:function(e){return e.ID},getOptionKeywords:function(e){return[e.name]},getFreeTextOptions:function(e,t){return[{key:"name",label:Object(r.createElement)("span",{key:"name",className:"bwf-search-result-name"},P()({mixedString:Object(c.__)("All list with names that include {{query /}}","wp-marketing-automations-crm"),components:{query:Object(r.createElement)("strong",{className:"components-form-token-field__suggestion-match"},e)}})),value:{id:e,name:e,lists:t.map((function(e){return _()(e,"value")?e.value:e})),searchTerm:e}}]},getOptionLabel:function(e,t){var n=Object(O.e)(x(e),t)||{};return Object(r.createElement)("span",{key:"name",className:"bwf-search-result-name","aria-label":x(e)},n.suggestionBeforeMatch,Object(r.createElement)("strong",{className:"components-form-token-field__suggestion-match"},n.suggestionMatch),n.suggestionAfterMatch)},getOptionCompletion:function(e){return e}};function C(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}var N=function(e){var t=e.query,n=t.hasOwnProperty("s")?t.s:"",a=j()(n)?[]:[{key:n,label:Object(c.__)("Search List: ","wp-marketing-automations-crm")+n,bwfLabelSource:"bwfcrm_lists",isSearchTerm:!0}],o=function(){var e,n=(e=regeneratorRuntime.mark((function e(n){var r,a,c,o;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(v()(n)){e.next=2;break}return e.abrupt("return");case 2:if(j()(n)||(r=n[n.length-1],(n=[])[0]=r),a=n.find((function(e){return _()(e,"searchTerm")})),!(Object(s.isUndefined)(a)&&n.length>0&&n[0].hasOwnProperty("name"))){e.next=7;break}return Object(E.j)({s:n[0].name},"/manage/lists",t),e.abrupt("return");case 7:if(c=Object(s.isUndefined)(a)?void 0:a.searchTerm,o=_()(t,"s")&&!j()(t.s)?t.s:"",c!==o){e.next=11;break}return e.abrupt("return");case 11:Object(E.j)({s:c},"/manage/lists",t);case 12:case"end":return e.stop()}}),e)})),function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){C(c,r,a,o,i,"next",e)}function i(e){C(c,r,a,o,i,"throw",e)}o(void 0)}))});return function(e){return n.apply(this,arguments)}}();return Object(r.createElement)(g.a,{autocompleter:T,multiple:!1,allowFreeTextSearch:!0,inlineTags:!0,selected:a,onChange:o,placeholder:Object(c.__)("Search by name","wp-marketing-automations-crm"),showClearButton:!0,disabled:!1})},I=n(48);function D(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function L(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?D(Object(n),!0).forEach((function(t){R(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):D(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function R(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function A(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},c=Object.keys(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var B=function(){var e=Object(I.a)("listdata"),t=e.getStateProp;return L(L({},A(e,["getStateProp"])),{},{getLists:function(){return t("lists")},getPageNumber:function(){return parseInt(t("offset"))/parseInt(t("limit"))+1},getPerPageCount:function(){return parseInt(t("limit"))},getTotalCount:function(){return parseInt(t("total"))},getCountData:function(){return t("countData")},getContactCountData:function(){return t("contactCountData")}})},F=n(50);function M(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function V(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?M(Object(n),!0).forEach((function(t){q(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):M(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function q(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function z(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},c=Object.keys(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(r=0;r<c.length;r++)n=c[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var G=function(){var e=Object(F.a)("listdata"),t=e.fetch,n=e.setStateProp;return V(V({},z(e,["fetch","setStateProp"])),{},{fetch:function(e,n,r){var a=arguments.length>3&&void 0!==arguments[3]&&arguments[3],c=e.s,o=(e.page,e.filter,e.path,z(e,["s","page","filter","path"])),i={offset:n,limit:r,search:c,filters:o,get_wc:Object(O.Y)(),grab_totals:a};t("GET",Object(O.g)("/lists"),i)},setStateListValues:function(e){n("lists",e)},setStateListValuesByKey:function(e,t){n(e,t)}})},H=(n(707),n(121)),K=n(122),U=n(111),J=n(13),Q=n(206),$=n(35),X=n(46);function Y(e,t,n,r,a,c,o){try{var i=e[c](o),s=i.value}catch(e){return void n(e)}i.done?t(s):Promise.resolve(s).then(r,a)}function W(e){return function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){Y(c,r,a,o,i,"next",e)}function i(e){Y(c,r,a,o,i,"throw",e)}o(void 0)}))}}function Z(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function ee(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?Z(Object(n),!0).forEach((function(t){te(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):Z(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function te(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function ne(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],r=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(r=(o=i.next()).done)&&(n.push(o.value),!t||n.length!==t);r=!0);}catch(e){a=!0,c=e}finally{try{r||null==i.return||i.return()}finally{if(a)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return re(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return re(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function re(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var ae=function(e){var t=e.query,n=B(),o=G(),l=ne(Object(r.useState)(!1),2),g=l[0],h=l[1],j=ne(Object(r.useState)(!1),2),y=j[0],v=j[1],w=ne(Object(r.useState)({}),2),_=w[0],k=w[1],P=o.fetch,S=o.setStateListValues,x=o.setStateListValuesByKey,T=ne(Object(r.useState)(!1),2),C=T[0],I=T[1],D=n.getLists,L=n.getPageNumber,R=n.getPerPageCount,A=n.getLoading,F=n.getTotalCount,M=n.getCountData,V=n.getContactCountData,q=Object(r.useContext)(O.b),z=D(),Y=L(),Z=R(),te=F(),re=A(),ae=M(),ce=V(),oe=ne(Object(r.useState)({}),2),ie=oe[0],se=oe[1];Object(r.useEffect)((function(){se(ce)}),[ce]),Object(r.useEffect)((function(){P(t,0,25,!0),h(!1)}),[t.s]);var le=Object(r.createElement)(f.Button,{isPrimary:!0,key:"add",className:"bwf-display-flex",onClick:function(){k({}),v(!0),I(!1)}},Object(c.__)("Add New","wp-marketing-automations-crm")),ue=Object($.a)().getPageCountData,fe=Object(X.a)().setPageCountData,me=ue();Object(r.useEffect)((function(){fe(ee(ee({},me),ae))}),[ae]),Object(Q.a)("manage_lists","",Object(c.__)("ALL LISTS","wp-marketing-automations-crm"),le),Object(r.useEffect)((function(){if(!g&&!Object(s.isEmpty)(z)&&!re)try{var e={list_ids:[]};z.map((function(t){e.list_ids.push(t.ID)})),u()({method:"GET",path:Object(O.g)("/lists/contacts?"+Object(a.stringify)(e))}).then((function(e){200==e.code&&(x("contactCountData",ee(ee({},e.result),ce)),h(!0))}))}catch(e){console.log(e)}}),[z]);var be=i()("bwfcrm-contacts-lists",{"has-search":!0}),pe=[{key:"actions",label:"",isLeftAligned:!1,cellClassName:"bwf-col-action bwf-w-30"},{key:"lists",label:Object(c.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"createdon",label:Object(c.__)("Created On","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"contacts",label:Object(c.__)("Contacts","wp-marketing-automations-crm"),isLeftAligned:!0}],de=function(e){e!==Z&&(P(t,0,e),h(!1))},Oe=function(e){return Object(r.createElement)(p.a,{label:Object(c.__)("Quick Actions","wp-marketing-automations-crm"),menuPosition:"bottom right",renderContent:function(t){var n=t.onToggle;return Object(r.createElement)(r.Fragment,null,Object(r.createElement)(d.a,{isClickable:!0,onInvoke:function(){Object(E.j)({filter:"advanced",path:"/contacts","lists_any[]":e.ID},"/",{}),n()}},Object(r.createElement)(m.a,{justify:"flex-start"},Object(r.createElement)(m.c,null,Object(r.createElement)(J.a,{icon:"view"})),Object(r.createElement)(m.c,null,Object(c.__)("View Contacts","wp-marketing-automations-crm")))),Object(r.createElement)(d.a,{isClickable:!0,onInvoke:function(){k(e),v(!0),I(!1),n()}},Object(r.createElement)(m.a,{justify:"flex-start"},Object(r.createElement)(m.c,null,Object(r.createElement)(J.a,{icon:"edit"})),Object(r.createElement)(m.c,null,Object(c.__)("Edit","wp-marketing-automations-crm")))),Object(r.createElement)(d.a,{isClickable:!0,onInvoke:function(){k(ee(ee({},_),{},{loading:!0,delete:!0,deleteid:e.ID})),v(!0),n()}},Object(r.createElement)(m.a,{justify:"flex-start"},Object(r.createElement)(m.c,null,Object(r.createElement)(J.a,{icon:"trash"})),Object(r.createElement)(m.c,null,Object(c.__)("Delete","wp-marketing-automations-crm")))))}})},ge=z.map((function(e){var t,n;return[{display:Oe(e),value:"action"},{display:e.name,value:e.ID},{display:(n=e.created_at,Object(r.createElement)("div",{className:"bwf-display-flex-column"},Object(r.createElement)("span",null,Object(O.C)(n)))),value:e.created_at},{display:(t=e,g?ie.hasOwnProperty(parseInt(t.ID))&&ie[parseInt(t.ID)].contact_count>0?Object(r.createElement)("div",{className:"bwf-display-flex-column"},Object(r.createElement)("a",{onClick:function(){Object(E.j)({filter:"advanced",path:"/contacts","lists_any[]":t.ID},"/",{})},className:"bwf-a-no-underline"},parseInt(ie[parseInt(t.ID)].subscribers_count)+" of "+ie[parseInt(t.ID)].contact_count),Object(r.createElement)("span",null,Object(c.__)("Subscribed","wp-marketing-automations-crm"))):"-":Object(r.createElement)("span",{className:"bwf-placeholder-temp bwf-w-150",title:"Loading"},"Loading")),value:""}]})),he=function(){var e=W(regeneratorRuntime.mark((function e(n){var r;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(!n.ID){e.next=13;break}return r=z.map((function(e){return parseInt(e.ID)===parseInt(n.ID)?n:e})),e.prev=2,e.next=5,u()({path:Object(O.g)("/lists/".concat(n.ID,"/")),method:"POST",data:{list_name:n.name,description:n.description},headers:{"Content-Type":"application/json"}}).then((function(e){200==e.code&&(v(!1),S(r),k({}),q(e.result),Object(O.N)(q,2e3))}));case 5:e.next=11;break;case 7:e.prev=7,e.t0=e.catch(2),k(ee(ee({},_),{},{error:!0,loading:!0,message:e.t0.message})),setTimeout((function(){v(!1),k({})}),2e3);case 11:e.next=22;break;case 13:return e.prev=13,e.next=16,u()({path:Object(O.g)("/list/"),method:"POST",data:{name:n.name,description:n.description},headers:{"Content-Type":"application/json"}}).then((function(e){200==e.code&&(v(!1),P(t,(Y-1)*Z,Z),k({}),h(!1),q(e.message),Object(O.N)(q,2e3))}));case 16:e.next=22;break;case 18:e.prev=18,e.t1=e.catch(13),k(ee(ee({},_),{},{error:!0,loading:!0,message:e.t1.message})),setTimeout((function(){v(!1),k({})}),2e3);case 22:case"end":return e.stop()}}),e,null,[[2,7],[13,18]])})));return function(t){return e.apply(this,arguments)}}(),je=function(){var e=W(regeneratorRuntime.mark((function e(n){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(!n){e.next=10;break}return e.prev=1,e.next=4,u()({path:Object(O.g)("/lists/".concat(n,"/")),method:"POST",data:{list_id:parseInt(n)},headers:{"X-HTTP-Method-Override":"DELETE"}}).then((function(e){200==e.code&&(k(ee(ee({},_),{},{success:!0,loading:!0,message:e.message,delete:!0})),setTimeout((function(){v(!1),P(t,(Y-1)*Z,Z),k({}),h(!1)}),2e3))}));case 4:e.next=10;break;case 6:e.prev=6,e.t0=e.catch(1),k(ee(ee({},_),{},{error:!0,loading:!0,message:e.t0.message,delete:!0})),setTimeout((function(){v(!1),k({})}),2e3);case 10:case"end":return e.stop()}}),e,null,[[1,6]])})));return function(t){return e.apply(this,arguments)}}();return Object(r.createElement)("div",{className:"bwf-c-list-section"},Object(r.createElement)(b.a,{className:be,rows:ge,headers:pe,query:{paged:Y},rowsPerPage:Z,totalRows:te?parseInt(te):0,isLoading:re,onPageChange:function(e,n){P(t,(e-1)*Z,Z),h(!1)},onQueryChange:function(e){return"per_page"!==e?function(){}:de},showMenu:!1,actions:[Object(r.createElement)(N,{key:"search",query:t})],rowHeader:!0,emptyMessage:Object(c.__)("No lists found","wp-marketing-automations-crm")}),y&&Object(r.createElement)(f.Modal,{title:!_.delete&&(_.ID?Object(c.__)("Edit List","wp-marketing-automations-crm"):Object(c.__)("Add List","wp-marketing-automations-crm")),onRequestClose:function(){return v(!1)},className:"bwf-admin-modal "+(_.loading?"bwf-admin-modal-no-header ":" ")+(_.delete?" bwf-admin-modal-small ":"bwf-admin-modal-medium")},_.loading?_.success?Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)(H.a,null),Object(r.createElement)("div",{className:"bwf-h1"},_.message))):_.error?Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-t-center"},Object(r.createElement)(K.a,null),Object(r.createElement)("div",{className:"bwf-h1"},_.message))):_.delete&&!_.deleteconfirm?Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-h4"},Object(c.__)("Delete","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf-t-center bwf-form-buttons"},Object(r.createElement)("div",{className:"bwf-h2"},Object(c.__)("Are you sure?","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf_clear_15"}),Object(r.createElement)("div",{className:"bwf-h4 bwf-h4-grey"},Object(c.__)("Once you delete this item. It will no longer available.","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf_clear_20"}),Object(r.createElement)("div",{className:"bwf_text_right"},Object(r.createElement)(f.Button,{isTertiary:!0,onClick:function(){k({}),v(!1)}},Object(c.__)("Cancel","wp-marketing-automations-crm")),Object(r.createElement)(f.Button,{isPrimary:!0,className:"bwf-delete-btn",onClick:function(){k(ee(ee({},_),{},{loading:!0,deleteconfirm:!0,deleteid:_.deleteid,delete:!0})),je(_.deleteid)}},Object(c.__)("Delete","wp-marketing-automations-crm"))))):Object(r.createElement)(U.a,null):Object(r.createElement)("div",{className:"bwf-form-fields",onKeyPress:function(e){"Enter"===e.key&&(Object(s.isEmpty)(_.name)||(k(ee(ee({},_),{},{loading:!0})),he(_)))}},C&&Object(r.createElement)(f.Notice,{status:"error",onRemove:function(){return I(!1)}},Object(c.__)("Name is required","wp-marketing-automations-crm")),Object(r.createElement)(f.TextControl,{label:Object(c.__)("Name","wp-marketing-automations-crm"),autoFocus:!0,type:"text",value:_.name?_.name:"",placeholder:Object(c.__)("Enter List Name","wp-marketing-automations-crm"),onChange:function(e){k(ee(ee({},_),{},{name:e}))}}),Object(r.createElement)(f.TextareaControl,{label:Object(c.__)("Description","wp-marketing-automations-crm"),type:"text",value:_.description?_.description:"",placeholder:Object(c.__)("Enter List Description","wp-marketing-automations-crm"),onChange:function(e){k(ee(ee({},_),{},{description:e}))}}),Object(r.createElement)("div",{className:"bwf_clear_10"}),Object(r.createElement)("div",{className:"bwf_text_right"},Object(r.createElement)(f.Button,{isTertiary:!0,className:"bwf-mr-5",onClick:function(){return v(!1)}},Object(c.__)("Cancel","wp-marketing-automations-crm")),Object(r.createElement)(f.Button,{isPrimary:!0,onClick:function(){Object(s.isEmpty)(_.name)?I(!0):(k(ee(ee({},_),{},{loading:!0})),he(_))},className:"bwf-ml-0"},_.ID?Object(c.__)("Save","wp-marketing-automations-crm"):Object(c.__)("Add","wp-marketing-automations-crm"))))))},ce=n(117);t.default=function(){var e=location&&location.search?Object(a.parse)(location.search.substring(1)):{};return Object(O.d)("Lists"),Object(r.createElement)(r.Fragment,null,Object(r.createElement)(ce.a,null),Object(r.createElement)(ae,{query:e}))}}}]);