(window.webpackJsonp=window.webpackJsonp||[]).push([[60],{113:function(e,t,a){"use strict";var n=a(0),r=a(4),c=a(15),o=a.n(c),i=a(9),s=a(34),l=a(8),u=a.n(l),m=a(96),f=(a(138),function(e){var t=e.id,a=e.instanceId,c=e.isVisible,l=e.label,u=e.popoverContents,m=e.remove,f=e.screenReaderLabel,b=e.setState,p=e.className;if(f=f||l,!l)return null;l=Object(s.decodeEntities)(l);var d=o()("bwf-tag",p,{"has-remove":!!m}),O="bwf-tag-label-".concat(a),w=Object(n.createElement)(n.Fragment,null,Object(n.createElement)("span",{className:"screen-reader-text"},f),Object(n.createElement)("span",{"aria-hidden":"true"},l));return Object(n.createElement)("span",{className:d},u?Object(n.createElement)(i.Button,{className:"bwf-tag-text",id:O,onClick:function(){return b((function(){return{isVisible:!0}}))}},w):Object(n.createElement)("span",{className:"bwf-tag-text",id:O},w),u&&c&&Object(n.createElement)(i.Popover,{onClose:function(){return b((function(){return{isVisible:!1}}))}},u),m&&Object(n.createElement)(i.Button,{className:"bwf-tag-remove",onClick:function(){return m(t,l)},label:Object(r.sprintf)(Object(r.__)("Remove %s","wp-marketing-automations-crm"),l),"aria-describedby":O},Object(n.createElement)(i.Dashicon,{icon:"dismiss",size:20})))});f.propTypes={id:u.a.oneOfType([u.a.number,u.a.string]),label:u.a.string.isRequired,popoverContents:u.a.node,remove:u.a.func,screenReaderLabel:u.a.string},t.a=Object(m.withState)({isVisible:!1})(Object(m.withInstanceId)(f))},117:function(e,t,a){"use strict";var n=a(0),r=a(8),c=a.n(r);function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function i(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function s(e,t){return(s=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function l(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var a,n=f(e);if(t){var r=f(this).constructor;a=Reflect.construct(n,arguments,r)}else a=n.apply(this,arguments);return u(this,a)}}function u(e,t){return!t||"object"!==o(t)&&"function"!=typeof t?m(e):t}function m(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function f(e){return(f=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var b=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&s(e,t)}(o,e);var t,a,r,c=l(o);function o(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,o),(t=c.call(this,e)).scrollTo=t.scrollTo.bind(m(t)),t}return t=o,(a=[{key:"componentDidMount",value:function(){setTimeout(this.scrollTo,250)}},{key:"scrollTo",value:function(){var e=this.props.offset;this.ref.current?window.scrollTo(0,parseInt(e,10)):setTimeout(this.scrollTo,250)}},{key:"render",value:function(){var e=this.props.children;return this.ref=Object(n.createRef)(),Object(n.createElement)("div",{ref:this.ref},e)}}])&&i(t.prototype,a),r&&i(t,r),o}(n.Component);b.propTypes={offset:c.a.string},b.defaultProps={offset:"0"},t.a=b},138:function(e,t,a){},173:function(e,t,a){"use strict";var n=a(0),r=a(15),c=a.n(r),o=a(18),i=a(8),s=a.n(i),l=a(320),u=a.n(l),m=a(5),f=a(321),b=a.n(f),p=(a(184),function(e){var t,a,r,i,s=e.alt,l=e.title,f=e.size,p=e.user,d=e.className,O=c()("bwf-gravatar",d,{"is-placeholder":!p}),w=s||p&&(p.display_name||p.name)||"",v="https://www.gravatar.com/avatar/0?s="+f+"&d=blank";return p&&(t=Object(m.isString)(p)?(i=p,"https://www.gravatar.com/avatar/"+b()(i)):p.avatar_URLs[96],a=u.a.parse(t),(r=Object(o.parse)(a.query)).s=f,r.d="blank",a.search=Object(o.stringify)(r),v=u.a.format(a)),Object(n.createElement)("img",{alt:w,title:l,className:O,src:v,width:f,height:f})});p.propTypes={user:s.a.oneOfType([s.a.object,s.a.string]),alt:s.a.string,title:s.a.string,size:s.a.number,className:s.a.string},p.defaultProps={size:60},t.a=p},181:function(e,t,a){"use strict";var n=a(0),r=a(7),c=a(5),o=a(159),i=a.n(o),s=(a(200),function(e){var t=e.first_name,a=e.last_name;return Object(n.createElement)("div",{className:"bwf-c-name-initials"},Object(n.createElement)("span",null,Object(c.isEmpty)(i()(t+" "+a))?"-":Object(r.F)(t,a)))});s.defaultProps={first_name:"",last_name:""};var l=s,u=a(32),m=a(4),f=a(115),b=a.n(f),p=(a(201),a(173)),d=function(e){var t=e.dateText,a=e.date,c=e.contact,o=c.f_name,s=void 0===o?"":o,f=c.l_name,d=void 0===f?"":f,O=(c.id,c.creation_date),w=c.email,v=e.hideJoiningDate,y=void 0!==v&&v,j=e.lowerText,g=Object(r.Y)([s,d]," "),h=Object(r.B)(a||O);return Object(n.createElement)(u.a,{className:"bwf-c-contact-basic-info-cell",justify:"flex-start"},Object(n.createElement)(u.c,{className:"bwf-c-avatar"},w&&Object(n.createElement)(p.a,{user:w,size:40}),Object(n.createElement)(l,{first_name:s,last_name:d})),Object(n.createElement)(u.c,null,Object(n.createElement)(u.a,{style:{flexDirection:"column"},align:"flex-start"},Object(n.createElement)(u.c,{style:{padding:0}},Object(n.createElement)("span",{className:"bwf-c-contact-name"},b()(i()(g))?Object(m.__)("-","wp-marketing-automations-crm"):g)),!y&&h&&Object(n.createElement)(u.c,null,Object(n.createElement)("span",{className:"bwf-c-contact-creation-date"},t||Object(m.__)("Joined","wp-marketing-automations-crm")," ",j||h)))))};d.defaultProps={contact:{f_name:"",l_name:"",id:0,creation_date:""}};t.a=d},184:function(e,t,a){},200:function(e,t,a){},201:function(e,t,a){},243:function(e,t,a){"use strict";var n=a(0),r=a(4),c=a(8),o=a.n(c),i=a(113),s=(a(256),a(13)),l=function(e){var t=e.items;return Object(n.createElement)(i.a,{className:"bwf-view-more-list",label:Object(n.createElement)("span",{className:"bwf-display-flex"},Object(n.createElement)("span",{className:"bwf-view-more-label"},Object(r.sprintf)(Object(r.__)("+%d ","bwf-admin"),t.length-1)),Object(n.createElement)(s.a,{icon:"tailless-arrow-down"})),popoverContents:Object(n.createElement)("ul",{className:"bwf-view-more-list__popover"},t.map((function(e,t){return Object(n.createElement)("li",{key:t,className:"bwf-view-more-list__popover__item"},e)})))})};l.propTypes={items:o.a.arrayOf(o.a.node)},l.defaultProps={items:[]},t.a=l},256:function(e,t,a){},362:function(e,t,a){"use strict";var n=a(46),r=a(0),c=a(4),o=a(17),i=a(18),s=a(176),l=a(7),u=function(e,t,a){return decodeURIComponent(Object(o.f)(e,t,a))};t.a=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",a=arguments.length>2&&void 0!==arguments[2]&&arguments[2],o=arguments.length>3&&void 0!==arguments[3]&&arguments[3],m=arguments.length>4&&void 0!==arguments[4]?arguments[4]:"",f=arguments.length>5&&void 0!==arguments[5]?arguments[5]:1,b=location&&location.search?Object(i.parse)(location.search.substring(1)):{},p=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},d=p.broadcasts_nav,O=s.a.getCampaignData(),w=s.a.getCampaignId(),v={overview:{name:Object(c.__)("Overview","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(w,"/overview"),b)},analytics:{name:Object(c.__)("Analytics","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(w,"/analytics"),b)},recipient:{name:Object(c.__)("Engagements","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(w,"/engagements"),b)}};Object(l.X)()&&(v.orders={name:Object(c.__)("Orders","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(w,"/orders"),b)});var y=Object(n.a)(),j=y.setActiveMultiple,g=y.resetHeaderMenu,h=y.setL2NavType,_=y.setL2Nav,E=y.setBackLink,k=y.setL2Title,N=y.setL2Content,C=y.setBackLinkLabel,P=y.setL2NavAlign,T=y.setPageHeader;return Object(r.useEffect)((function(){g(),o?(h("menu"),_(v)):(!a&&h("menu"),!a&&_(d)),j({leftNav:"broadcasts",rightNav:e}),a&&1===f&&(C("Email Broadcasts"),E(u({},"/broadcasts/email",b))),a&&2===f&&(C("SMS Broadcasts"),E(u({},"/broadcasts/sms",b))),a&&3===f&&(C("WhatsApp Broadcasts"),E(u({},"/broadcasts/whatsapp",b))),t?k(t):o&&k(O.title),o&&P("left"),!a&&m&&N(m),T("Broadcasts")}),[e,f]),!0}},727:function(e,t,a){"use strict";a.r(t);var n=a(0),r=a(4),c=a(130),o=a(459),i=a(176),s=a(125),l=a(7),u=a(243),m=a(5),f=(a(728),a(362)),b=a(117),p=a(19),d=a(181);function O(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var a=[],n=!0,r=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(a.push(o.value),!t||a.length!==t);n=!0);}catch(e){r=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(r)throw c}}return a}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return w(e,t);var a=Object.prototype.toString.call(e).slice(8,-1);"Object"===a&&e.constructor&&(a=e.constructor.name);if("Map"===a||"Set"===a)return Array.from(e);if("Arguments"===a||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(a))return w(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function w(e,t){(null==t||t>e.length)&&(t=e.length);for(var a=0,n=new Array(t);a<t;a++)n[a]=e[a];return n}t.default=function(e){var t=e.campaignType;Object(f.a)("orders","",!0,!0,!1,t);var a=e.campaignId,w=i.a.getConversionData(),v=i.a.getConversionLoading(),y=i.a.getConversionLimit(),j=i.a.getConversionOffset(),g=i.a.getConversionTotal(),h=Object(o.a)(),_=h.fetchConversion,E=h.setConversionValues,k=Object(s.a)(Object(l.H)()).formatAmount;Object(n.useEffect)((function(){_(a,y,j)}),[y,j]);var N=[{key:"orderid",label:Object(r.__)("Order ID","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m"},{key:"contact",label:Object(r.__)("Contact","wp-marketing-automations-crm"),required:!0,cellClassName:"bwf-crm-col-contact"},{key:"purchaseitems",label:Object(r.__)("Purchased Items","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-contact-details"},{key:"revenue",label:Object(r.__)("Revenue","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m"},{key:"date",label:Object(r.__)("Date","wp-marketing-automations-crm"),cellClassName:"bwf-crm-col-stats-m"}],C=i.a.getConversionPage(),P=function(e){e!==y&&(E("limit",e),E("offset",0))},T=function(e){return Object(n.createElement)(p.a,{href:"admin.php?page=autonami&path=/contact/"+e.cid,type:"bwf-crm",className:"bwf-crm-campaign-order-contact-link bwf-a-no-underline",key:e.cid},Object(n.createElement)(d.a,{contact:e,date:e.date,dateText:Object(r.__)("Placed on","wp-marketing-automations-crm")}))},S=function(e){return e.hasOwnProperty("order_deleted")&&e.order_deleted?Object(n.createElement)(n.Fragment,null,"#"+e.wcid):Object(n.createElement)("a",{target:"_blank",className:"bwf-a-no-underline",href:"post.php?post="+e.wcid+"&action=edit"},"#"+e.wcid)},R=w.map((function(e){return[{display:e.hasOwnProperty("wcid")?S(e):"-",value:e.hasOwnProperty("wcid")?e.wcid:"-"},{display:T(e),value:e.hasOwnProperty("contact_name")?e.contact_name:"-"},{display:e.hasOwnProperty("items")?(t=e.items,a="",c=[],Object.entries(t).map((function(e){var t=O(e,2),r=t[0],o=t[1];Object(m.isEmpty)(a)&&(a=Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"post.php?action=edit&post="+r,rel:"noreferrer"},o)),c.push(Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"post.php?action=edit&post="+r},o))})),Object(n.createElement)(n.Fragment,null,Object(m.isEmpty)(a)?"-":a,!Object(m.isEmpty)(c)&&c.length>1&&Object(n.createElement)(u.a,{items:c}))):Object(r.__)("Order Deleted","wp-marketing-automations-crm"),value:"purchase_item"},{display:e.hasOwnProperty("wctotal")?k(e.wctotal):"-",value:e.hasOwnProperty("wctotal")?e.wctotal:0},{display:e.hasOwnProperty("date")?Object(l.B)(e.date):"-",value:e.hasOwnProperty("date")?e.date:"-"}];var t,a,c}));return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(b.a,null),Object(n.createElement)(c.a,{className:"bwf-crm-campaign-report-conversion",rows:R,headers:N,query:{paged:C},rowsPerPage:y,totalRows:g,isLoading:v,onPageChange:function(e,t){E("offset",(e-1)*y)},onQueryChange:function(e){return"per_page"!==e?function(){}:P},rowHeader:!0,showMenu:!1,emptyMessage:Object(r.__)("No orders found","wp-marketing-automations-crm"),hideHeader:"yes"}))}},728:function(e,t,a){}}]);