(window.webpackJsonp=window.webpackJsonp||[]).push([[69],{362:function(e,t,n){"use strict";var r=n(46),a=n(0),o=n(4),c=n(17),i=n(18),s=n(176),l=n(7),u=function(e,t,n){return decodeURIComponent(Object(c.f)(e,t,n))};t.a=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n=arguments.length>2&&void 0!==arguments[2]&&arguments[2],c=arguments.length>3&&void 0!==arguments[3]&&arguments[3],p=arguments.length>4&&void 0!==arguments[4]?arguments[4]:"",f=arguments.length>5&&void 0!==arguments[5]?arguments[5]:1,b=location&&location.search?Object(i.parse)(location.search.substring(1)):{},m=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},y=m.broadcasts_nav,d=s.a.getCampaignData(),O=s.a.getCampaignId(),v={overview:{name:Object(o.__)("Overview","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(O,"/overview"),b)},analytics:{name:Object(o.__)("Analytics","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(O,"/analytics"),b)},recipient:{name:Object(o.__)("Engagements","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(O,"/engagements"),b)}};Object(l.X)()&&(v.orders={name:Object(o.__)("Orders","wp-marketing-automations-crm"),link:u({},"/broadcast/".concat(O,"/orders"),b)});var h=Object(r.a)(),g=h.setActiveMultiple,w=h.resetHeaderMenu,j=h.setL2NavType,_=h.setL2Nav,k=h.setBackLink,E=h.setL2Title,S=h.setL2Content,P=h.setBackLinkLabel,C=h.setL2NavAlign,N=h.setPageHeader;return Object(a.useEffect)((function(){w(),c?(j("menu"),_(v)):(!n&&j("menu"),!n&&_(y)),g({leftNav:"broadcasts",rightNav:e}),n&&1===f&&(P("Email Broadcasts"),k(u({},"/broadcasts/email",b))),n&&2===f&&(P("SMS Broadcasts"),k(u({},"/broadcasts/sms",b))),n&&3===f&&(P("WhatsApp Broadcasts"),k(u({},"/broadcasts/whatsapp",b))),t?E(t):c&&E(d.title),c&&C("left"),!n&&p&&S(p),N("Broadcasts")}),[e,f]),!0}},486:function(e,t,n){"use strict";var r=n(0),a=n(15),o=n.n(a),c=n(8),i=n.n(c);n(494);function s(e){return(s="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function l(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function u(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function p(e,t){return(p=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function f(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=m(e);if(t){var a=m(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return b(this,n)}}function b(e,t){return!t||"object"!==s(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function m(e){return(m=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var y=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&p(e,t)}(i,e);var t,n,a,c=f(i);function i(){return l(this,i),c.apply(this,arguments)}return t=i,(n=[{key:"render",value:function(){var e=this.props.className,t=o()("bwf-spinner",e);return Object(r.createElement)("svg",{className:t,viewBox:"0 0 100 100",xmlns:"http://www.w3.org/2000/svg"},Object(r.createElement)("circle",{className:"bwf-spinner__circle",fill:"none",strokeWidth:"5",strokeLinecap:"round",cx:"50",cy:"50",r:"30"}))}}])&&u(t.prototype,n),a&&u(t,a),i}(r.Component);y.propTypes={className:i.a.string};var d=y,O=function(){return Object(r.createElement)("svg",{role:"img","aria-hidden":"true",focusable:"false",width:"18",height:"18",viewBox:"0 0 18 18",fill:"none",xmlns:"http://www.w3.org/2000/svg"},Object(r.createElement)("mask",{id:"mask0","mask-type":"alpha",maskUnits:"userSpaceOnUse",x:"2",y:"3",width:"14",height:"12"},Object(r.createElement)("path",{d:"M6.59631 11.9062L3.46881 8.77875L2.40381 9.83625L6.59631 14.0287L15.5963 5.02875L14.5388 3.97125L6.59631 11.9062Z",fill:"white"})),Object(r.createElement)("g",{mask:"url(#mask0)"},Object(r.createElement)("rect",{width:"18",height:"18",fill:"white"})))};n(495);function v(e){return(v="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function h(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function g(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function w(e,t){return(w=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function j(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=k(e);if(t){var a=k(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return _(this,n)}}function _(e,t){return!t||"object"!==v(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function k(e){return(k=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var E=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&w(e,t)}(i,e);var t,n,a,c=j(i);function i(){return h(this,i),c.apply(this,arguments)}return t=i,(n=[{key:"renderCurrentStepContent",value:function(){var e=this.props,t=e.currentStep,n=e.steps.find((function(e){return t===e.key}));return n.content?Object(r.createElement)("div",{className:"bwf-stepper_content"},n.content):null}},{key:"render",value:function(){var e=this,t=this.props,n=t.className,a=t.currentStep,c=t.steps,i=t.isVertical,s=t.isPending,l=c.findIndex((function(e){return a===e.key})),u=o()("bwf-stepper",n,{"is-vertical":i});return Object(r.createElement)("div",{className:u},Object(r.createElement)("div",{className:"bwf-stepper__steps"},c.map((function(t,n){var c=t.key,u=t.label,p=t.description,f=t.isComplete,b=t.onClick,m=c===a,y=o()("bwf-stepper__step",{"is-active":m,"is-complete":void 0!==f?f:l>n}),v=m&&s?Object(r.createElement)("div",{className:"bwf-stepper__step-icon"},Object(r.createElement)("span",{className:"bwf-stepper__step-number is-pending"},Object(r.createElement)(d,null))):Object(r.createElement)("div",{className:"bwf-stepper__step-icon"},Object(r.createElement)("span",{className:"bwf-stepper__step-number"},n+1),Object(r.createElement)(O,null)),h="function"==typeof b?"button":"div";return Object(r.createElement)(r.Fragment,{key:c},Object(r.createElement)("div",{className:y},Object(r.createElement)(h,{className:"bwf-stepper__step-label-wrapper",onClick:"function"==typeof b?function(){return b(c)}:null},v,Object(r.createElement)("div",{className:"bwf-stepper__step-text"},Object(r.createElement)("span",{className:"bwf-stepper__step-label"},u),p&&Object(r.createElement)("span",{className:"bwf-stepper__step-description"},p))),m&&i&&e.renderCurrentStepContent()))}))),!i&&this.renderCurrentStepContent())}}])&&g(t.prototype,n),a&&g(t,a),i}(r.Component);E.propTypes={className:i.a.string,currentStep:i.a.string.isRequired,steps:i.a.arrayOf(i.a.shape({content:i.a.node,description:i.a.oneOfType([i.a.string,i.a.array]),isComplete:i.a.bool,key:i.a.string.isRequired,label:i.a.string.isRequired,onClick:i.a.func})).isRequired,isVertical:i.a.bool,isPending:i.a.bool},E.defaultProps={isVertical:!1,isPending:!1};t.a=E},494:function(e,t,n){},495:function(e,t,n){},715:function(e,t,n){"use strict";n.r(t);var r=n(0),a=n(4),o=n(486),c=n(176),i=(n(716),n(362)),s=n(111);function l(){return(l=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e}).apply(this,arguments)}function u(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],r=!0,a=!1,o=void 0;try{for(var c,i=e[Symbol.iterator]();!(r=(c=i.next()).done)&&(n.push(c.value),!t||n.length!==t);r=!0);}catch(e){a=!0,o=e}finally{try{r||null==i.return||i.return()}finally{if(a)throw o}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return p(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return p(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function p(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var f=Object(r.lazy)((function(){return n.e(70).then(n.bind(null,719))})),b=Object(r.lazy)((function(){return Promise.all([n.e(0),n.e(3),n.e(4),n.e(5),n.e(40)]).then(n.bind(null,740))})),m=Object(r.lazy)((function(){return Promise.all([n.e(11),n.e(15),n.e(66)]).then(n.bind(null,732))})),y=Object(r.lazy)((function(){return Promise.all([n.e(0),n.e(7),n.e(10),n.e(76)]).then(n.bind(null,722))}));t.default=function(e){var t=u(Object(r.useState)(!1),2),n=t[0],p=t[1],d=c.a.getStep(),O=c.a.getLoading(),v=c.a.getCampaignId(),h=c.a.getCampaignData();Object(i.a)("","title"in h?h.title:Object(a.__)("New Broadcast","wp-marketing-automations-crm"),!0,!1,Object(r.createElement)(r.Fragment,null),h&&h.type?parseInt(h.type):1);var g=function(t){switch(t){case 1:return Object(r.createElement)(f,{campaignId:v,setPending:p});case 2:return Object(r.createElement)(b,l({},e,{setPending:p}));case 3:return Object(r.createElement)(m,{setPending:p});case 4:return Object(r.createElement)(y,{setPending:p});default:return Object(r.createElement)(r.Fragment,null,Object(a.__)("There is some error","wp-marketing-automations-crm"))}},w=[{key:1,label:Object(a.__)("Information","wp-marketing-automations-crm"),content:g(1)},{key:2,label:Object(a.__)("Contacts","wp-marketing-automations-crm"),content:g(2)},{key:3,label:Object(a.__)("Content","wp-marketing-automations-crm"),content:g(3)},{key:4,label:Object(a.__)("Review","wp-marketing-automations-crm"),content:g(4)}];return Object(r.createElement)(r.Fragment,null,O||d<1?Object(r.createElement)(s.a,null):Object(r.createElement)("div",{"data-step":d},Object(r.createElement)(r.Suspense,{fallback:Object(r.createElement)(s.a,null)},Object(r.createElement)(o.a,{steps:w,currentStep:parseInt(d)>0?d:1,isPending:n}))))}},716:function(e,t,n){}}]);