(window.webpackJsonp=window.webpackJsonp||[]).push([[79],{718:function(e,t,n){"use strict";n.r(t);var a=n(0),r=n(17),c=n(106),o=n(20),i=n(109),l=n(9),u=n(4),s=n(7),m=n(176),b=n(18);function p(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],a=!0,r=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(a=(o=i.next()).done)&&(n.push(o.value),!t||n.length!==t);a=!0);}catch(e){r=!0,c=e}finally{try{a||null==i.return||i.return()}finally{if(r)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return d(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return d(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function d(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}var f=Object(a.lazy)((function(){return n.e(76).then(n.bind(null,724))})),y=Object(a.lazy)((function(){return Promise.all([n.e(0),n.e(3),n.e(7),n.e(10),n.e(64)]).then(n.bind(null,738))})),j=Object(a.lazy)((function(){return Promise.all([n.e(4),n.e(13),n.e(2),n.e(61)]).then(n.bind(null,739))})),O=Object(a.lazy)((function(){return Promise.all([n.e(4),n.e(43),n.e(2),n.e(60)]).then(n.bind(null,728))}));t.default=function(e){var t=location&&location.search?Object(r.parse)(location.search.substring(1)):{},n=e.campaignId,d=m.a.getCampaignData(),g=(d&&d.status&&parseInt(d.status),d.hasOwnProperty("type")?parseInt(d.type):1),h=Object(s.o)(),w=h.reached,E=h.daily_limit,v=p(Object(a.useState)(!0),2),I=v[0],S=v[1];return Object(a.createElement)(a.Fragment,null,"1"===d.type&&!!w&&!!I&&Object(a.createElement)(l.Notice,{className:"bwf-error-notice",status:"warning",onRemove:function(){return S(!1)}},"".concat(Object(u.__)("Daily sending limit of","wp-marketing-automations-crm")," ").concat(E," ").concat(Object(u.__)(" emails has been reached today. To send out more emails, go to ","wp-marketing-automations-crm")," "),Object(a.createElement)(b.a,{href:"admin.php?page=autonami&path=/settings",className:"bwf-a-no-underline"},Object(u.__)("Settings > Emails","wp-marketing-automations")),Object(u.__)(" to increase the limit.","wp-marketing-automations-crm")),Object(a.createElement)(c.b,{history:Object(o.d)()},Object(a.createElement)(a.Suspense,{fallback:Object(a.createElement)(i.a,null)},Object(a.createElement)(c.c,null,Object(a.createElement)(c.a,{exact:!0,path:["/broadcast/".concat(n,"/overview"),"/broadcast/".concat(n)],render:function(){return Object(a.createElement)(y,{campaignType:g})}}),Object(a.createElement)(c.a,{exact:!0,path:"/broadcast/".concat(n,"/analytics"),render:function(){return Object(a.createElement)(f,{query:t,campaignId:n,campaignType:g})}}),Object(a.createElement)(c.a,{exact:!0,path:"/broadcast/".concat(n,"/engagements"),render:function(){return Object(a.createElement)(j,{campaignId:n,campaignType:g})}}),Object(a.createElement)(c.a,{exact:!0,path:"/broadcast/".concat(n,"/orders"),render:function(){return Object(a.createElement)(O,{campaignId:n,campaignType:g})}})))))}}}]);