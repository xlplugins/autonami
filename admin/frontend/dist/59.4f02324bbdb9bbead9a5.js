(window.webpackJsonp=window.webpackJsonp||[]).push([[59],{117:function(e,t,n){"use strict";var a=n(0),c=n(8),r=n.n(c);function i(e){return(i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function o(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function s(e,t){return(s=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function l(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,a=b(e);if(t){var c=b(this).constructor;n=Reflect.construct(a,arguments,c)}else n=a.apply(this,arguments);return m(this,n)}}function m(e,t){return!t||"object"!==i(t)&&"function"!=typeof t?u(e):t}function u(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function b(e){return(b=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var f=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&s(e,t)}(i,e);var t,n,c,r=l(i);function i(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),(t=r.call(this,e)).scrollTo=t.scrollTo.bind(u(t)),t}return t=i,(n=[{key:"componentDidMount",value:function(){setTimeout(this.scrollTo,250)}},{key:"scrollTo",value:function(){var e=this.props.offset;this.ref.current?window.scrollTo(0,parseInt(e,10)):setTimeout(this.scrollTo,250)}},{key:"render",value:function(){var e=this.props.children;return this.ref=Object(a.createRef)(),Object(a.createElement)("div",{ref:this.ref},e)}}])&&o(t.prototype,n),c&&o(t,c),i}(a.Component);f.propTypes={offset:r.a.string},f.defaultProps={offset:"0"},t.a=f},173:function(e,t,n){"use strict";var a=n(0),c=n(15),r=n.n(c),i=n(18),o=n(8),s=n.n(o),l=n(320),m=n.n(l),u=n(5),b=n(321),f=n.n(b),p=(n(184),function(e){var t,n,c,o,s=e.alt,l=e.title,b=e.size,p=e.user,O=e.className,d=r()("bwf-gravatar",O,{"is-placeholder":!p}),j=s||p&&(p.display_name||p.name)||"",w="https://www.gravatar.com/avatar/0?s="+b+"&d=blank";return p&&(t=Object(u.isString)(p)?(o=p,"https://www.gravatar.com/avatar/"+f()(o)):p.avatar_URLs[96],n=m.a.parse(t),(c=Object(i.parse)(n.query)).s=b,c.d="blank",n.search=Object(i.stringify)(c),w=m.a.format(n)),Object(a.createElement)("img",{alt:j,title:l,className:d,src:w,width:b,height:b})});p.propTypes={user:s.a.oneOfType([s.a.object,s.a.string]),alt:s.a.string,title:s.a.string,size:s.a.number,className:s.a.string},p.defaultProps={size:60},t.a=p},181:function(e,t,n){"use strict";var a=n(0),c=n(7),r=n(5),i=n(159),o=n.n(i),s=(n(200),function(e){var t=e.first_name,n=e.last_name;return Object(a.createElement)("div",{className:"bwf-c-name-initials"},Object(a.createElement)("span",null,Object(r.isEmpty)(o()(t+" "+n))?"-":Object(c.F)(t,n)))});s.defaultProps={first_name:"",last_name:""};var l=s,m=n(32),u=n(4),b=n(115),f=n.n(b),p=(n(201),n(173)),O=function(e){var t=e.dateText,n=e.date,r=e.contact,i=r.f_name,s=void 0===i?"":i,b=r.l_name,O=void 0===b?"":b,d=(r.id,r.creation_date),j=r.email,w=e.hideJoiningDate,v=void 0!==w&&w,g=e.lowerText,y=Object(c.Y)([s,O]," "),E=Object(c.B)(n||d);return Object(a.createElement)(m.a,{className:"bwf-c-contact-basic-info-cell",justify:"flex-start"},Object(a.createElement)(m.c,{className:"bwf-c-avatar"},j&&Object(a.createElement)(p.a,{user:j,size:40}),Object(a.createElement)(l,{first_name:s,last_name:O})),Object(a.createElement)(m.c,null,Object(a.createElement)(m.a,{style:{flexDirection:"column"},align:"flex-start"},Object(a.createElement)(m.c,{style:{padding:0}},Object(a.createElement)("span",{className:"bwf-c-contact-name"},f()(o()(y))?Object(u.__)("-","wp-marketing-automations-crm"):y)),!v&&E&&Object(a.createElement)(m.c,null,Object(a.createElement)("span",{className:"bwf-c-contact-creation-date"},t||Object(u.__)("Joined","wp-marketing-automations-crm")," ",g||E)))))};O.defaultProps={contact:{f_name:"",l_name:"",id:0,creation_date:""}};t.a=O},184:function(e,t,n){},200:function(e,t,n){},201:function(e,t,n){},460:function(e,t,n){"use strict";var a=n(46),c=n(0),r=n(4),i=n(7),o=function(e){var t={workflow:{name:Object(r.__)("Workflow","wp-marketing-automations"),link:"admin.php?page=autonami-automations&edit=".concat(e)},engagement:{name:Object(r.__)("Engagement","wp-marketing-automations"),link:"admin.php?page=autonami&path=/automation/".concat(e,"/engagement")}};return Object(i.X)()&&(t.orders={name:Object(r.__)("Orders","wp-marketing-automations"),link:"admin.php?page=autonami&path=/automation/".concat(e,"/orders")}),t};t.a=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",i=arguments.length>3&&void 0!==arguments[3]&&arguments[3],s=arguments.length>4&&void 0!==arguments[4]?arguments[4]:"",l=arguments.length>5&&void 0!==arguments[5]?arguments[5]:0,m=arguments.length>6&&void 0!==arguments[6]&&arguments[6],u=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},b=u.automation_nav,f=Object(a.a)(),p=f.setActiveMultiple,O=f.resetHeaderMenu,d=f.setL2NavType,j=f.setL2Nav,w=f.setBackLink,v=f.setL2Title,g=f.setL2Content,y=f.setBackLinkLabel,E=f.setL2NavAlign,_=f.setPageHeader;return Object(c.useEffect)((function(){O(),!i&&d("menu"),j(l?o(l):b),p({leftNav:"automations",rightNav:e}),n&&w(n),n&&l&&w("admin.php?page=autonami-automations&edit=".concat(l)),i&&y(Object(r.__)("All Automations","wp-marketing-automations-crm")),l&&y(Object(r.__)("Back to Automation","wp-marketing-automations-crm")),l&&E("right"),t&&v(t),!n&&s&&g(s),_("Automations"),m&&j({})}),[e]),!0}},502:function(e,t,n){"use strict";var a=n(48),c=n(57);function r(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?r(Object(n),!0).forEach((function(t){o(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):r(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function o(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function s(e,t){if(null==e)return{};var n,a,c=function(e,t){if(null==e)return{};var n,a,c={},r=Object.keys(e);for(a=0;a<r.length;a++)n=r[a],t.indexOf(n)>=0||(c[n]=e[n]);return c}(e,t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);for(a=0;a<r.length;a++)n=r[a],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(c[n]=e[n])}return c}t.a=function(){var e=Object(a.a)("automationList"),t=e.getStateProp,n=s(e,["getStateProp"]),r=Object(a.a)(c.a.recipient).getStateProp,o=Object(a.a)(c.a.conversion).getStateProp;return i(i({},n),{},{getAutomations:function(){return t("automations")},getPageNumber:function(){return parseInt(t("offset"))/parseInt(t("limit"))+1},getPerPageCount:function(){return parseInt(t("limit"))},getOffset:function(){return parseInt(t("offset"))},getTotalCount:function(){return parseInt(t("total"))},getLoadingStatus:function(){return t("isLoading")},getRecipientData:function(){return r("data")},getRecipientLoading:function(){return r("isLoading")},getRecipientOffset:function(){return r("offset")},getRecipientAutomationId:function(){return r("automationId")},getRecipientTotal:function(){return r("total")},getRecipientPage:function(){return parseInt(r("offset"))/parseInt(r("limit"))+1},getRecipientLimit:function(){return r("limit")},getConversionData:function(){return o("data")},getConversionLoading:function(){return o("isLoading")},getConversionOffset:function(){return o("offset")},getConversionAutomationId:function(){return o("automationId")},getConversionTotal:function(){return o("total")},getConversionPage:function(){return parseInt(o("offset"))/parseInt(o("limit"))+1},getConversionLimit:function(){return o("limit")},getCountData:function(){return t("countData")}})}},503:function(e,t,n){"use strict";var a=n(50),c=n(7),r=n(57);function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function o(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){s(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function s(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function l(e,t){if(null==e)return{};var n,a,c=function(e,t){if(null==e)return{};var n,a,c={},r=Object.keys(e);for(a=0;a<r.length;a++)n=r[a],t.indexOf(n)>=0||(c[n]=e[n]);return c}(e,t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);for(a=0;a<r.length;a++)n=r[a],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(c[n]=e[n])}return c}t.a=function(){var e=Object(a.a)("automationList"),t=e.fetch,n=e.setStateProp,i=l(e,["fetch","setStateProp"]),s=Object(a.a)(r.a.recipient),m=s.fetch,u=s.setStateProp,b=Object(a.a)(r.a.conversion),f=b.fetch,p=b.setStateProp;return o(o({},i),{},{fetch:function(e,n,a,r){var i=n.s,o=(n.page,n.filter,n.path,{offset:a,limit:r,status:e,search:i,filters:l(n,["s","page","filter","path"])});t("GET",Object(c.g)("/automations"),o)},setAutomationListValues:function(e,t){n(e,t)},fetchRecipient:function(e,t,n){m("GET",Object(c.g)("/automation/".concat(e,"/recipients?limit=").concat(t,"&offset=").concat(n)))},setRecipientsValues:function(e,t){u(e,t)},fetchConversion:function(e,t,n){f("GET",Object(c.g)("/automation/".concat(e,"/conversions?limit=").concat(t,"&offset=").concat(n)))},setConversionValues:function(e,t){p(e,t)}})}},550:function(e,t,n){"use strict";var a=n(0);n(551);t.a=function(e){var t=e.size,n=e.isOpen,c=e.onRequestClose,r=e.children,i=n?"is-open":"",o=Object(a.useRef)(),s=Object(a.useRef)(n);return Object(a.useEffect)((function(){s.current=n}),[n]),Object(a.useEffect)((function(){jQuery("body").click((function(e){!(jQuery(o.current).find(e.target).length>0)&&s.current&&c()}))}),[]),Object(a.createElement)("div",{className:"bwf-crm-side-panel "+i,ref:o,style:{width:t+"px"}},r)}},551:function(e,t,n){},697:function(e,t,n){},698:function(e,t,n){},797:function(e,t,n){"use strict";n.r(t);var a=n(0),c=n(4),r=n(130),i=n(550),o=function(){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status is-preview"})," ",Object(a.createElement)("span",{className:"is-placeholder"})),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle"},Object(a.createElement)("span",{className:"is-placeholder long"})))),Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status is-preview"})," ",Object(a.createElement)("span",{className:"is-placeholder"})),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle"},Object(a.createElement)("span",{className:"is-placeholder long"})))))},s=n(7),l=(n(9),n(125)),m=n(16),u=n.n(m),b=n(5);function f(e,t,n,a,c,r,i){try{var o=e[r](i),s=o.value}catch(e){return void n(e)}o.done?t(s):Promise.resolve(s).then(a,c)}function p(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],a=!0,c=!1,r=void 0;try{for(var i,o=e[Symbol.iterator]();!(a=(i=o.next()).done)&&(n.push(i.value),!t||n.length!==t);a=!0);}catch(e){c=!0,r=e}finally{try{a||null==o.return||o.return()}finally{if(c)throw r}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return O(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return O(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function O(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}var d=Object(l.a)(Object(s.H)()).formatAmount,j=function(e){var t=e.contact,n=e.automationId,r=p(Object(a.useState)(!0),2),i=r[0],l=r[1],m=p(Object(a.useState)([]),2),O=m[0],j=m[1],w=function(){var e,a=(e=regeneratorRuntime.mark((function e(){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.prev=0,e.next=3,u()({method:"GET",path:Object(s.g)("/automation/".concat(n,"/recipients/").concat(t.cid,"/timeline?mode=").concat(t.mode))}).then((function(e){200===e.code&&e.hasOwnProperty("result")&&!Object(b.isEmpty)(e.result)?j(e.result):j([]),l(!1)}));case 3:e.next=8;break;case 5:e.prev=5,e.t0=e.catch(0),l(!1);case 8:case"end":return e.stop()}}),e,null,[[0,5]])})),function(){var t=this,n=arguments;return new Promise((function(a,c){var r=e.apply(t,n);function i(e){f(r,a,c,i,o,"next",e)}function o(e){f(r,a,c,i,o,"throw",e)}i(void 0)}))});return function(){return a.apply(this,arguments)}}();Object(a.useEffect)((function(){l(!0),Object(b.isEmpty)(t.cid)||w()}),[t]);return i?Object(a.createElement)(o,null):Object(a.createElement)(a.Fragment,null,Object(b.isEmpty)(O)?Object(a.createElement)("p",null,Object(c.__)("No timeline found","wp-marketing-automations-crm")):O.map((function(e){switch(e.type){case"conversion":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,Object(c.__)("Conversion Recorded","wp-marketing-automations-crm"),Object(a.createElement)("a",{href:"post.php?post="+e.order_id+"&action=edit",className:"bwf-a-no-underline"}," (#".concat(e.order_id,")")))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.C)(e.date,!0)},Object(s.E)(e.date).fromNow())),Object(a.createElement)("div",{className:"bwf-crm-card-r"},Object(a.createElement)("div",{className:"bwf-crm-card-date"},d(parseFloat(e.revenue))))))}(e);case"open":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,Object(c.__)("Email Opened","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.C)(e.date,!0)},Object(s.E)(e.date).fromNow()))))}(e);case"click":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,1===parseInt(e.mode)?Object(c.__)("Email Clicked","wp-marketing-automations-crm"):Object(c.__)("SMS Clicked","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.C)(e.date,!0)},Object(s.E)(e.date).fromNow()))))}(e);case"sent":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-success"}),Object(a.createElement)("span",null,1===parseInt(e.mode)?Object(c.__)("Email Sent","wp-marketing-automations-crm"):Object(c.__)("SMS Sent","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.C)(e.date,!0)},Object(s.E)(e.date).fromNow()))))}(e);case"failed":return function(e){return Object(a.createElement)(a.Fragment,null,Object(a.createElement)("section",{className:"bwf-crm-card"},Object(a.createElement)("div",{className:"bwf-crm-card-l"},Object(a.createElement)("div",{className:"bwf-crm-card-title"},Object(a.createElement)("span",{className:"bwf-crm-status bwf-crm-status-failed"}),Object(a.createElement)("span",null,1===parseInt(e.mode)?Object(c.__)("Sent Failed","wp-marketing-automations-crm"):Object(c.__)("SMS Sent Failed","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-card-subtitle",title:Object(s.C)(e.date,!0)},Object(s.E)(e.date).fromNow()))))}(e)}})))},w=(n(697),n(173)),v=n(159),g=n.n(v),y=n(19),E=Object(l.a)(Object(s.H)()).formatAmount,_=function(e){var t=e.isOpen,n=e.automationId,r=e.onRequestClose,o=e.contact;return Object(a.createElement)(i.a,{size:400,isOpen:t,onRequestClose:r},Object(a.createElement)("div",{className:"bwf-crm-body"},Object(a.createElement)("div",{className:"bwf-crm-details"},Object(a.createElement)("div",{className:"bwf-crm-d-head bwf-crm-gap-border"},Object(a.createElement)("div",{className:"bwf-gravatar-wrapper"},o.send_to&&Object(a.createElement)(w.a,{user:o.send_to,size:60}),Object(a.createElement)("div",{className:"bwf-crm-gravatar"},Object(a.createElement)("span",null,Object(b.isEmpty)(g()(o.f_name+" "+o.l_name))?"-":Object(s.F)(o.f_name,o.l_name)))),Object(a.createElement)("div",{className:"bwf-crm-name"},o&&o.f_name&&o.l_name&&o.f_name.charAt(0).toUpperCase()+o.f_name.slice(1)+" "+(o.l_name.charAt(0).toUpperCase()+o.l_name.slice(1))),Object(a.createElement)("div",{className:"bwf-crm-email"},o.send_to),Object(a.createElement)("div",{className:"bwf-t-center bwf-pt-15"},Object(a.createElement)(y.a,{href:"admin.php?page=autonami&path=/contact/"+o.cid,type:"bwf-crm",className:"bwf-a-no-underline button is-secondary"},Object(c.__)("View Contact")))),Object(a.createElement)("div",{className:"bwf-crm-d-data bwf-crm-gap-border"},Object(a.createElement)("div",{className:"bwf-crm-head"},Object(c.__)("Details","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-col2",key:1},o.is_unsubscribe&&!Object(b.isEmpty)(o.unsubscribe)&&Object(a.createElement)("div",{className:"bwf-crm-list",key:2},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(c.__)("Unsubscribe","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},Object(s.B)(o.unsubscribe)))),Object(a.createElement)("div",{className:"bwf-crm-col2",key:2},Object(a.createElement)("div",{className:"bwf-crm-list",key:2},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(c.__)("Total Click","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},parseInt(o.click))),Object(a.createElement)("div",{className:"bwf-crm-list",key:1},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(c.__)("Total Open","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},parseInt(o.open)))),Object(a.createElement)("div",{className:"bwf-crm-col2",key:2},Object(a.createElement)("div",{className:"bwf-crm-list",key:1},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(c.__)("Is Converted","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},parseInt(o.conversions)>0?Object(c.__)("Yes","wp-marketing-automations-crm"):Object(c.__)("No","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf-crm-list",key:2},Object(a.createElement)("div",{className:"bwf-crm-label"},Object(c.__)("Total Revenue","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf-crm-value"},E(parseInt(o.revenue))))))),Object(a.createElement)("div",{className:"bwf-crm-timeline"},Object(a.createElement)("div",{className:"bwf-crm-head"},Object(c.__)("Timeline","wp-marketing-automations-crm")),Object(a.createElement)(j,{contact:o,automationId:n}))))},h=n(181),N=n(32),k=n(503),P=n(502),S=(n(698),n(117)),C=n(13),I=n(460),R=n(46);function T(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],a=!0,c=!1,r=void 0;try{for(var i,o=e[Symbol.iterator]();!(a=(i=o.next()).done)&&(n.push(i.value),!t||n.length!==t);a=!0);}catch(e){c=!0,r=e}finally{try{a||null==o.return||o.return()}finally{if(c)throw r}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return A(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return A(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function A(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}t.default=function(e){var t=e.match.params.automationId,n=Object(P.a)(),i=T(Object(a.useState)(!1),2),o=i[0],m=i[1],u=T(Object(a.useState)({}),2),b=u[0],f=u[1];Object(s.d)("Automation #"+t);var p=n.getRecipientData(),O=n.getRecipientLoading(),d=n.getRecipientLimit(),j=n.getRecipientOffset(),w=n.getRecipientTotal(),v=Object(k.a)(),g=v.fetchRecipient,E=v.setRecipientsValues,A=Object(l.a)(Object(s.H)()).formatAmount,L=Object(R.a)().setL2NavAlign;Object(I.a)("engagement","",!0,!1,null,t),L("left"),Object(a.useEffect)((function(){g(t,d,j)}),[d,j]);var x=[{key:"contact",label:Object(c.__)("Contact","wp-marketing-automations-crm"),required:!0,cellClassName:"bwf-crm-col-contact"},{key:"contact_details",label:Object(c.__)("Details","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-contact-details"},{key:"sent",label:Object(c.__)("Sent","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},{key:"open",label:Object(c.__)("Open","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},{key:"click",label:Object(c.__)("Click","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},{key:"unsubscribe",label:Object(c.__)("Unsubscribe","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"},Object(s.X)()?{key:"converted",label:Object(c.__)("Converted","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m bwf-t-center"}:{},Object(s.X)()?{key:"revenue",label:Object(c.__)("Revenue","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m"}:{}],D=n.getRecipientPage(),F=function(e){e!==d&&(E("limit",e),E("offset",0))},M=function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],n="";switch(e){case"null":case null:n="";break;case!0:case"yes":n=t?" bwf-crm-status-failed":" bwf-crm-status-success";break;case!1:case"no":n=t?" bwf-crm-status-success":""}return Object(a.createElement)("div",{className:"bwf-crm-status bwf-crm-status-s"+n})},z=function(e){var t=e;return t.email=e.send_to,Object(a.createElement)("div",{onClick:function(){return function(e){f(e),m(!0)}(e)}},Object(a.createElement)(h.a,{contact:t,dateText:Object(c.__)("Sent","wp-marketing-automations-crm"),date:e.sent_time}))},q=function(e){return Object(a.createElement)("div",{className:"bwf-crm-contact-details-cell"},e.send_to&&Object(a.createElement)(N.a,{justify:"start"},Object(a.createElement)(N.c,null,Object(a.createElement)(C.a,{icon:1===parseInt(e.mode)?"mail":"phone"})),Object(a.createElement)(N.c,null,0!==e.cid?Object(a.createElement)(y.a,{className:"bwf-a-no-underline",href:"mailto:"+e.send_to,type:"external"},e.send_to):e.send_to)))},B=p.map((function(e){return[{display:z(e),value:e.f_name+" "+e.l_name},{display:q(e),value:e.email},{display:M(parseInt(e.sent)>0),value:e.open},{display:M(parseInt(e.open)>0),value:e.open},{display:M(parseInt(e.click)>0),value:e.click},{display:M(0!=parseInt(e.unsubscribed)||null,!0),value:e.unsubscribe},Object(s.X)()?{display:M(parseInt(e.conversions)>0),value:e.is_converted}:{},Object(s.X)()?{display:A(parseFloat(e.revenue)),value:e.revenue}:{}]}));return Object(a.createElement)(a.Fragment,null,Object(a.createElement)(S.a,null),Object(a.createElement)(r.a,{className:"bwf-crm-campaign-report-recipients",rows:B,headers:x,query:{paged:D},rowsPerPage:d,totalRows:w,isLoading:O,onPageChange:function(e){E("offset",(e-1)*d)},onQueryChange:function(e){return"per_page"!==e?function(){}:F},emptyMessage:Object(c.__)("No engagements found","wp-marketing-automations-crm"),rowHeader:!0,showMenu:!1,hideHeader:"yes"}),Object(a.createElement)(_,{contact:b,size:400,automationId:t,isOpen:o,onRequestClose:function(){return m(!1)}}))}}}]);