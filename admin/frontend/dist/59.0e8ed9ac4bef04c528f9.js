(window.webpackJsonp=window.webpackJsonp||[]).push([[59],{112:function(e,t,n){"use strict";var a=n(24),r=n(48);t.a=function(e){var t=Object(a.b)(),n=Object(r.a)(e),c=n.setLoading,i=n.fetch,o=n.clearError,s=n.setStateProp;return{setLoading:function(e){return t(c(e))},fetch:function(e,n,a){var r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};return t(i(e,n,a,r))},clearError:function(){return t(o())},setStateProp:function(e,n){return t(s(e,n))}}}},114:function(e,t,n){"use strict";var a=n(112),r=n(44);function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){o(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function o(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}t.a=function(){var e=Object(a.a)("menu").setStateProp,t=(0,Object(r.a)().getActive)();return{setActive:function(n,a){return e("active",i(i({},t),{},o({},n,a)))},setActiveMultiple:function(t){return e("active",t)},setBackLink:function(t){return e("backLink",t)},setL2Title:function(t){return e("l2Title",t)},setL2PostTitle:function(t){return e("l2PostTitle",t)},setL2Nav:function(t){return e("l2Nav",t)},setL2NavType:function(t){return e("l2NavType",t)},setL2Content:function(t){return e("l2Content",t)},setL2NavAlign:function(t){return e("l2NavAlign",t)},setPageHeader:function(t){return e("pageHeader",t)},setBackLinkLabel:function(t){return e("backLinkLabel",t)},setPageCountData:function(t){return e("pageCountData",t)},resetHeaderMenu:function(){e("backLink",""),e("l2Title",""),e("l2PostTitle",""),e("l2Nav",{}),e("l2NavType",""),e("active",{leftNav:"",rightNav:""}),e("l2Content",""),e("l2NavAlign","left"),e("pageHeader","")},setContactL2Menu:function(){return e("l2Nav")}}}},117:function(e,t,n){"use strict";var a=n(0),r=n(8),c=n.n(r);function i(e){return(i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function o(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function s(e,t){return(s=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function l(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,a=b(e);if(t){var r=b(this).constructor;n=Reflect.construct(a,arguments,r)}else n=a.apply(this,arguments);return u(this,n)}}function u(e,t){return!t||"object"!==i(t)&&"function"!=typeof t?m(e):t}function m(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function b(e){return(b=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var f=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&s(e,t)}(i,e);var t,n,r,c=l(i);function i(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),(t=c.call(this,e)).scrollTo=t.scrollTo.bind(m(t)),t}return t=i,(n=[{key:"componentDidMount",value:function(){setTimeout(this.scrollTo,250)}},{key:"scrollTo",value:function(){var e=this.props.offset;this.ref.current?window.scrollTo(0,parseInt(e,10)):setTimeout(this.scrollTo,250)}},{key:"render",value:function(){var e=this.props.children;return this.ref=Object(a.createRef)(),Object(a.createElement)("div",{ref:this.ref},e)}}])&&o(t.prototype,n),r&&o(t,r),i}(a.Component);f.propTypes={offset:c.a.string},f.defaultProps={offset:"0"},t.a=f},173:function(e,t,n){"use strict";var a=n(0),r=n(15),c=n.n(r),i=n(17),o=n(8),s=n.n(o),l=n(320),u=n.n(l),m=n(5),b=n(321),f=n.n(b),p=(n(184),function(e){var t,n,r,o,s=e.alt,l=e.title,b=e.size,p=e.user,O=e.className,d=c()("bwf-gravatar",O,{"is-placeholder":!p}),j=s||p&&(p.display_name||p.name)||"",v="https://www.gravatar.com/avatar/0?s="+b+"&d=blank";return p&&(t=Object(m.isString)(p)?(o=p,"https://www.gravatar.com/avatar/"+f()(o)):p.avatar_URLs[96],n=u.a.parse(t),(r=Object(i.parse)(n.query)).s=b,r.d="blank",n.search=Object(i.stringify)(r),v=u.a.format(n)),Object(a.createElement)("img",{alt:j,title:l,className:d,src:v,width:b,height:b})});p.propTypes={user:s.a.oneOfType([s.a.object,s.a.string]),alt:s.a.string,title:s.a.string,size:s.a.number,className:s.a.string},p.defaultProps={size:60},t.a=p},181:function(e,t,n){"use strict";var a=n(0),r=n(7),c=n(5),i=n(159),o=n.n(i),s=(n(200),function(e){var t=e.first_name,n=e.last_name;return Object(a.createElement)("div",{className:"bwf-c-name-initials"},Object(a.createElement)("span",null,Object(c.isEmpty)(o()(t+" "+n))?"-":Object(r.D)(t,n)))});s.defaultProps={first_name:"",last_name:""};var l=s,u=n(31),m=n(4),b=n(116),f=n.n(b),p=(n(201),n(173)),O=function(e){var t=e.dateText,n=e.date,c=e.contact,i=c.f_name,s=void 0===i?"":i,b=c.l_name,O=void 0===b?"":b,d=(c.id,c.creation_date),j=c.email,v=e.hideJoiningDate,w=void 0!==v&&v,g=e.lowerText,y=Object(r.W)([s,O]," "),E=Object(r.z)(n||d);return Object(a.createElement)(u.a,{className:"bwf-c-contact-basic-info-cell",justify:"flex-start"},Object(a.createElement)(u.c,{className:"bwf-c-avatar"},j&&Object(a.createElement)(p.a,{user:j,size:40}),Object(a.createElement)(l,{first_name:s,last_name:O})),Object(a.createElement)(u.c,null,Object(a.createElement)(u.a,{style:{flexDirection:"column"},align:"flex-start"},Object(a.createElement)(u.c,{style:{padding:0}},Object(a.createElement)("span",{className:"bwf-c-contact-name"},f()(o()(y))?Object(m.__)("-","wp-marketing-automations-crm"):y)),!w&&E&&Object(a.createElement)(u.c,null,Object(a.createElement)("span",{className:"bwf-c-contact-creation-date"},t||Object(m.__)("Joined","wp-marketing-automations-crm")," ",g||E)))))};O.defaultProps={contact:{f_name:"",l_name:"",id:0,creation_date:""}};t.a=O},184:function(e,t,n){},200:function(e,t,n){},201:function(e,t,n){},461:function(e,t,n){"use strict";var a=n(114),r=n(0),c=n(4),i=n(7),o=function(e){var t={workflow:{name:Object(c.__)("Workflow","wp-marketing-automations"),link:"admin.php?page=autonami-automations&edit=".concat(e)},engagement:{name:Object(c.__)("Engagement","wp-marketing-automations"),link:"admin.php?page=autonami&path=/automation/".concat(e,"/engagement")}};return Object(i.V)()&&(t.orders={name:Object(c.__)("Orders","wp-marketing-automations"),link:"admin.php?page=autonami&path=/automation/".concat(e,"/orders")}),t};t.a=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",i=arguments.length>3&&void 0!==arguments[3]&&arguments[3],s=arguments.length>4&&void 0!==arguments[4]?arguments[4]:"",l=arguments.length>5&&void 0!==arguments[5]?arguments[5]:0,u=arguments.length>6&&void 0!==arguments[6]&&arguments[6],m=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},b=m.automation_nav,f=Object(a.a)(),p=f.setActiveMultiple,O=f.resetHeaderMenu,d=f.setL2NavType,j=f.setL2Nav,v=f.setBackLink,w=f.setL2Title,g=f.setL2Content,y=f.setBackLinkLabel,E=f.setL2NavAlign,h=f.setPageHeader;return Object(r.useEffect)((function(){O(),!i&&d("menu"),j(l?o(l):b),p({leftNav:"automations",rightNav:e}),n&&v(n),n&&l&&v("admin.php?page=autonami-automations&edit=".concat(l)),i&&y(Object(c.__)("All Automations","wp-marketing-automations-crm")),l&&y(Object(c.__)("Back to Automation","wp-marketing-automations-crm")),l&&E("right"),t&&w(t),!n&&s&&g(s),h("Automations"),u&&j({})}),[e]),!0}},503:function(e,t,n){"use strict";var a=n(46),r=n(55);function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){o(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function o(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function s(e,t){if(null==e)return{};var n,a,r=function(e,t){if(null==e)return{};var n,a,r={},c=Object.keys(e);for(a=0;a<c.length;a++)n=c[a],t.indexOf(n)>=0||(r[n]=e[n]);return r}(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(a=0;a<c.length;a++)n=c[a],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(r[n]=e[n])}return r}t.a=function(){var e=Object(a.a)("automationList"),t=e.getStateProp,n=s(e,["getStateProp"]),c=Object(a.a)(r.a.recipient).getStateProp,o=Object(a.a)(r.a.conversion).getStateProp;return i(i({},n),{},{getAutomations:function(){return t("automations")},getPageNumber:function(){return parseInt(t("offset"))/parseInt(t("limit"))+1},getPerPageCount:function(){return parseInt(t("limit"))},getOffset:function(){return parseInt(t("offset"))},getTotalCount:function(){return parseInt(t("total"))},getLoadingStatus:function(){return t("isLoading")},getRecipientData:function(){return c("data")},getRecipientLoading:function(){return c("isLoading")},getRecipientOffset:function(){return c("offset")},getRecipientAutomationId:function(){return c("automationId")},getRecipientTotal:function(){return c("total")},getRecipientPage:function(){return parseInt(c("offset"))/parseInt(c("limit"))+1},getRecipientLimit:function(){return c("limit")},getConversionData:function(){return o("data")},getConversionLoading:function(){return o("isLoading")},getConversionOffset:function(){return o("offset")},getConversionAutomationId:function(){return o("automationId")},getConversionTotal:function(){return o("total")},getConversionPage:function(){return parseInt(o("offset"))/parseInt(o("limit"))+1},getConversionLimit:function(){return o("limit")},getCountData:function(){return t("countData")}})}},504:function(e,t,n){"use strict";var a=n(112),r=n(7),c=n(55);function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function o(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){s(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function s(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function l(e,t){if(null==e)return{};var n,a,r=function(e,t){if(null==e)return{};var n,a,r={},c=Object.keys(e);for(a=0;a<c.length;a++)n=c[a],t.indexOf(n)>=0||(r[n]=e[n]);return r}(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(a=0;a<c.length;a++)n=c[a],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(r[n]=e[n])}return r}t.a=function(){var e=Object(a.a)("automationList"),t=e.fetch,n=e.setStateProp,i=l(e,["fetch","setStateProp"]),s=Object(a.a)(c.a.recipient),u=s.fetch,m=s.setStateProp,b=Object(a.a)(c.a.conversion),f=b.fetch,p=b.setStateProp;return o(o({},i),{},{fetch:function(e,n,a,c){var i=n.s,o=(n.page,n.filter,n.path,{offset:a,limit:c,status:e,search:i,filters:l(n,["s","page","filter","path"])});t("GET",Object(r.f)("/automations"),o)},setAutomationListValues:function(e,t){n(e,t)},fetchRecipient:function(e,t,n){u("GET",Object(r.f)("/automation/".concat(e,"/recipients?limit=").concat(t,"&offset=").concat(n)))},setRecipientsValues:function(e,t){m(e,t)},fetchConversion:function(e,t,n){f("GET",Object(r.f)("/automation/".concat(e,"/conversions?limit=").concat(t,"&offset=").concat(n)))},setConversionValues:function(e,t){p(e,t)}})}},552:function(e,t,n){"use strict";var a=n(0);n(553);t.a=function(e){var t=e.size,n=e.isOpen,r=e.onRequestClose,c=e.children,i=n?"is-open":"",o=Object(a.useRef)(),s=Object(a.useRef)(n);return Object(a.useEffect)((function(){s.current=n}),[n]),Object(a.useEffect)((function(){jQuery("body").click((function(e){!(jQuery(o.current).find(e.target).length>0)&&s.current&&r()}))}),[]),Object(a.createElement)("div",{className:"bwf-crm-side-panel "+i,ref:o,style:{width:t+"px"}},c)}},553:function(e,t,n){},698:function(e,t,n){},699:function(e,t,n){},798:function(e,t,n){"use strict";n.r(t);var a=n(0),r=n(4),c=n(130),i=n(552),o=function(){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status is-preview"})," ",Object(a.createElement)("span",{className:"is-placeholder"})),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle"},Object(a.createElement)("span",{className:"is-placeholder long"})))),Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status is-preview"})," ",Object(a.createElement)("span",{className:"is-placeholder"})),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle"},Object(a.createElement)("span",{className:"is-placeholder long"})))))},s=n(7),l=(n(9),n(124)),u=n(16),m=n.n(u),b=n(5);function f(e,t,n,a,r,c,i){try{var o=e[c](i),s=o.value}catch(e){return void n(e)}o.done?t(s):Promise.resolve(s).then(a,r)}function p(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],a=!0,r=!1,c=void 0;try{for(var i,o=e[Symbol.iterator]();!(a=(i=o.next()).done)&&(n.push(i.value),!t||n.length!==t);a=!0);}catch(e){r=!0,c=e}finally{try{a||null==o.return||o.return()}finally{if(r)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return O(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return O(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function O(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}var d=Object(l.a)(Object(s.F)()).formatAmount,j=function(e){var t=e.contact,n=e.automationId,c=p(Object(a.useState)(!0),2),i=c[0],l=c[1],u=p(Object(a.useState)([]),2),O=u[0],j=u[1],v=function(){var e,a=(e=regeneratorRuntime.mark((function e(){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.prev=0,e.next=3,m()({method:"GET",path:Object(s.f)("/automation/".concat(n,"/recipients/").concat(t.cid,"/timeline?mode=").concat(t.mode))}).then((function(e){200===e.code&&e.hasOwnProperty("result")&&!Object(b.isEmpty)(e.result)?j(e.result):j([]),l(!1)}));case 3:e.next=8;break;case 5:e.prev=5,e.t0=e.catch(0),l(!1);case 8:case"end":return e.stop()}}),e,null,[[0,5]])})),function(){var t=this,n=arguments;return new Promise((function(a,r){var c=e.apply(t,n);function i(e){f(c,a,r,i,o,"next",e)}function o(e){f(c,a,r,i,o,"throw",e)}i(void 0)}))});return function(){return a.apply(this,arguments)}}();Object(a.useEffect)((function(){l(!0),Object(b.isEmpty)(t.cid)||v()}),[t]);return i?Object(a.createElement)(o,null):Object(a.createElement)(a.Fragment,null,Object(b.isEmpty)(O)?Object(a.createElement)("p",null,Object(r.__)("No timeline found","wp-marketing-automations-crm")):O.map((function(e){switch(e.type){case"conversion":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,Object(r.__)("Conversion Recorded","wp-marketing-automations-crm"),Object(a.createElement)("a",{href:"post.php?post="+e.order_id+"&action=edit",className:"bwf-a-no-underline"}," (#".concat(e.order_id,")")))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.A)(e.date,!0)},Object(s.C)(e.date).fromNow())),Object(a.createElement)("div",{className:"bwf-crm-card-r"},Object(a.createElement)("div",{className:"bwf-crm-card-date"},d(parseFloat(e.revenue))))))}(e);case"open":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,Object(r.__)("Email Opened","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.A)(e.date,!0)},Object(s.C)(e.date).fromNow()))))}(e);case"click":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,1===parseInt(e.mode)?Object(r.__)("Email Clicked","wp-marketing-automations-crm"):Object(r.__)("SMS Clicked","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.A)(e.date,!0)},Object(s.C)(e.date).fromNow()))))}(e);case"sent":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,1===parseInt(e.mode)?Object(r.__)("Email Sent","wp-marketing-automations-crm"):Object(r.__)("SMS Sent","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.A)(e.date,!0)},Object(s.C)(e.date).fromNow()))))}(e);case"failed":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-failed"}),Object(a.createElement)("span",null,1===parseInt(e.mode)?Object(r.__)("Sent Failed","wp-marketing-automations-crm"):Object(r.__)("SMS Sent Failed","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.A)(e.date,!0)},Object(s.C)(e.date).fromNow()))))}(e)}})))},v=(n(698),n(173)),w=n(159),g=n.n(w),y=n(18),E=Object(l.a)(Object(s.F)()).formatAmount,h=function(e){var t=e.isOpen,n=e.automationId,c=e.onRequestClose,o=e.contact;return Object(a.createElement)(i.a,{size:400,isOpen:t,onRequestClose:c},Object(a.createElement)("div",{className:"bwf-crm-body"},Object(a.createElement)("div",{className:"bwf-crm-details"},Object(a.createElement)("div",{className:"bwf-crm-d-head bwf-crm-gap-border"},Object(a.createElement)("div",{className:"bwf-gravatar-wrapper"},o.send_to&&Object(a.createElement)(v.a,{user:o.send_to,size:60}),Object(a.createElement)("div",{className:"bwf-crm-gravatar"},Object(a.createElement)("span",null,Object(b.isEmpty)(g()(o.f_name+" "+o.l_name))?"-":Object(s.D)(o.f_name,o.l_name)))),Object(a.createElement)("div",{className:"bwf-crm-name"},o&&o.f_name&&o.l_name&&o.f_name.charAt(0).toUpperCase()+o.f_name.slice(1)+" "+(o.l_name.charAt(0).toUpperCase()+o.l_name.slice(1))),Object(a.createElement)("div",{className:"bwf-crm-email"},o.send_to),Object(a.createElement)("div",{className:"bwf-t-center bwf-pt-15"},Object(a.createElement)(y.a,{href:"admin.php?page=autonami&path=/contact/"+o.cid,type:"bwf-crm",className:"bwf-a-no-underline button is-secondary"},Object(r.__)("View Contact")))),Object(a.createElement)("div",{className:"bwf-crm-d-data bwf-crm-gap-border"},Object(a.createElement)("div",{className:"bwf-crm-head"},Object(r.__)("Details","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-col2",key:1},o.is_unsubscribe&&!Object(b.isEmpty)(o.unsubscribe)&&Object(a.createElement)("div",{className:"bwf-crm-list",key:2},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(r.__)("Unsubscribe","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},Object(s.z)(o.unsubscribe)))),Object(a.createElement)("div",{className:"bwf-crm-col2",key:2},Object(a.createElement)("div",{className:"bwf-crm-list",key:2},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(r.__)("Total Click","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},parseInt(o.click))),Object(a.createElement)("div",{className:"bwf-crm-list",key:1},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(r.__)("Total Open","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},parseInt(o.open)))),Object(a.createElement)("div",{className:"bwf-crm-col2",key:2},Object(a.createElement)("div",{className:"bwf-crm-list",key:1},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(r.__)("Is Converted","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},parseInt(o.conversions)>0?Object(r.__)("Yes","wp-marketing-automations-crm"):Object(r.__)("No","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-list",key:2},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(r.__)("Total Revenue","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},E(parseInt(o.revenue))))))),Object(a.createElement)("div",{className:"bwf-crm-timeline"},Object(a.createElement)("div",{className:"bwf-crm-head"},Object(r.__)("Timeline","wp-marketing-automations-crm")),Object(a.createElement)(j,{contact:o,automationId:n}))))},_=n(181),N=n(31),k=n(504),P=n(503),S=(n(699),n(117)),C=n(13),I=n(461),T=n(114);function A(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],a=!0,r=!1,c=void 0;try{for(var i,o=e[Symbol.iterator]();!(a=(i=o.next()).done)&&(n.push(i.value),!t||n.length!==t);a=!0);}catch(e){r=!0,c=e}finally{try{a||null==o.return||o.return()}finally{if(r)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return L(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return L(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function L(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}t.default=function(e){var t=e.match.params.automationId,n=Object(P.a)(),i=A(Object(a.useState)(!1),2),o=i[0],u=i[1],m=A(Object(a.useState)({}),2),b=m[0],f=m[1];Object(s.d)("Automation #"+t);var p=n.getRecipientData(),O=n.getRecipientLoading(),d=n.getRecipientLimit(),j=n.getRecipientOffset(),v=n.getRecipientTotal(),w=Object(k.a)(),g=w.fetchRecipient,E=w.setRecipientsValues,L=Object(l.a)(Object(s.F)()).formatAmount,R=Object(T.a)().setL2NavAlign;Object(I.a)("engagement","",!0,!1,null,t),R("left"),Object(a.useEffect)((function(){g(t,d,j)}),[d,j]);var D=[{key:"contact",label:Object(r.__)("Contact","wp-marketing-automations-crm"),required:!0,cellClassName:"bwf-crm-col-contact"},{key:"contact_details",label:Object(r.__)("Details","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-contact-details"},{key:"sent",label:Object(r.__)("Sent","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},{key:"open",label:Object(r.__)("Open","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},{key:"click",label:Object(r.__)("Click","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},{key:"unsubscribe",label:Object(r.__)("Unsubscribe","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},Object(s.V)()?{key:"converted",label:Object(r.__)("Converted","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"}:{},Object(s.V)()?{key:"revenue",label:Object(r.__)("Revenue","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m"}:{}],x=n.getRecipientPage(),F=function(e){e!==d&&(E("limit",e),E("offset",0))},M=function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],n="";switch(e){case"null":case null:n="";break;case!0:case"yes":n=t?" bwf-crm-status-failed":" bwf-crm-status-success";break;case!1:case"no":n=t?" bwf-crm-status-success":""}return Object(a.createElement)("div",{className:"bwf-crm-status bwf-crm-status-s"+n})},z=function(e){var t=e;return t.email=e.send_to,Object(a.createElement)("div",{onClick:function(){return function(e){f(e),u(!0)}(e)}},Object(a.createElement)(_.a,{contact:t,dateText:Object(r.__)("Sent","wp-marketing-automations-crm"),date:e.sent_time}))},V=function(e){return Object(a.createElement)("div",{className:"bwf-crm-contact-details-cell"},e.send_to&&Object(a.createElement)(N.a,{justify:"start"},Object(a.createElement)(N.c,null,Object(a.createElement)(C.a,{icon:1===parseInt(e.mode)?"mail":"phone"})),Object(a.createElement)(N.c,null,0!==e.cid?Object(a.createElement)(y.a,{className:"bwf-a-no-underline",href:"mailto:"+e.send_to,type:"external"},e.send_to):e.send_to)))},H=p.map((function(e){return[{display:z(e),value:e.f_name+" "+e.l_name},{display:V(e),value:e.email},{display:M(parseInt(e.sent)>0),value:e.open},{display:M(parseInt(e.open)>0),value:e.open},{display:M(parseInt(e.click)>0),value:e.click},{display:M(0!=parseInt(e.unsubscribed)||null,!0),value:e.unsubscribe},Object(s.V)()?{display:M(parseInt(e.conversions)>0),value:e.is_converted}:{},Object(s.V)()?{display:L(parseFloat(e.revenue)),value:e.revenue}:{}]}));return Object(a.createElement)(a.Fragment,null,Object(a.createElement)(S.a,null),Object(a.createElement)(c.a,{className:"bwf-crm-campaign-report-recipients",rows:H,headers:D,query:{paged:x},rowsPerPage:d,totalRows:v,isLoading:O,onPageChange:function(e){E("offset",(e-1)*d)},onQueryChange:function(e){return"per_page"!==e?function(){}:F},emptyMessage:Object(r.__)("No engagements found","wp-marketing-automations-crm"),rowHeader:!0,showMenu:!1,hideHeader:"yes"}),Object(a.createElement)(h,{contact:b,size:400,automationId:t,isOpen:o,onRequestClose:function(){return u(!1)}}))}}}]);