(window.webpackJsonp=window.webpackJsonp||[]).push([[58],{109:function(e,t,n){"use strict";var a=n(0);n(133);t.a=function(e){var t=e.size,n=void 0===t?"xl":t;return Object(a.createElement)("div",{className:"bwf-t-center"},Object(a.createElement)("div",{className:"bwf_clear_30"}),Object(a.createElement)("div",{className:"bwf-spin-loader bwf-spin-loader-".concat(n)}),Object(a.createElement)("div",{className:"bwf_clear_30"}))}},112:function(e,t,n){"use strict";var a=n(24),r=n(48);t.a=function(e){var t=Object(a.b)(),n=Object(r.a)(e),c=n.setLoading,i=n.fetch,o=n.clearError,s=n.setStateProp;return{setLoading:function(e){return t(c(e))},fetch:function(e,n,a){var r=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};return t(i(e,n,a,r))},clearError:function(){return t(o())},setStateProp:function(e,n){return t(s(e,n))}}}},114:function(e,t,n){"use strict";var a=n(112),r=n(44);function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){o(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function o(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}t.a=function(){var e=Object(a.a)("menu").setStateProp,t=(0,Object(r.a)().getActive)();return{setActive:function(n,a){return e("active",i(i({},t),{},o({},n,a)))},setActiveMultiple:function(t){return e("active",t)},setBackLink:function(t){return e("backLink",t)},setL2Title:function(t){return e("l2Title",t)},setL2PostTitle:function(t){return e("l2PostTitle",t)},setL2Nav:function(t){return e("l2Nav",t)},setL2NavType:function(t){return e("l2NavType",t)},setL2Content:function(t){return e("l2Content",t)},setL2NavAlign:function(t){return e("l2NavAlign",t)},setPageHeader:function(t){return e("pageHeader",t)},setBackLinkLabel:function(t){return e("backLinkLabel",t)},setPageCountData:function(t){return e("pageCountData",t)},resetHeaderMenu:function(){e("backLink",""),e("l2Title",""),e("l2PostTitle",""),e("l2Nav",{}),e("l2NavType",""),e("active",{leftNav:"",rightNav:""}),e("l2Content",""),e("l2NavAlign","left"),e("pageHeader","")},setContactL2Menu:function(){return e("l2Nav")}}}},118:function(e,t,n){"use strict";function a(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}n.d(t,"a",(function(){return a}))},120:function(e,t,n){"use strict";var a=n(0);n(135);t.a=function(e){return Object(a.createElement)("div",{className:"bwf-t-center"},Object(a.createElement)("div",{className:"bwf_align_center"},Object(a.createElement)("div",{className:"bwf-w-100",dangerouslySetInnerHTML:{__html:bwfcrm_contacts_data.icons.success}})))}},121:function(e,t,n){"use strict";var a=n(0);n(136);t.a=function(e){return Object(a.createElement)("div",{className:"bwf-t-center"},Object(a.createElement)("div",{className:"bwf_align_center"},Object(a.createElement)("div",{className:"bwf-w-100",dangerouslySetInnerHTML:{__html:bwfcrm_contacts_data.icons.error}})))}},133:function(e,t,n){},135:function(e,t,n){},136:function(e,t,n){},137:function(e,t,n){"use strict";var a=n(0),r=n(109),c=n(120),i=n(121),o=n(9),s=n(4);t.a=function(e){var t=e.confirmText,n=void 0===t?"":t,l=e.confirmButtonText,u=void 0===l?"":l,m=e.cancelButtonText,b=void 0===m?"":m,f=e.onConfirm,O=void 0===f?function(){}:f,d=e.isLoading,j=void 0!==d&&d,p=e.successMessage,w=void 0===p?"":p,g=e.errorMessage,v=void 0===g?"":g,_=e.onRequestClose,y=e.isOpen,h=void 0!==y&&y,k=e.confirmDescription,E=void 0===k?"":k,N=e.isDelete,P=void 0!==N&&N;Object(a.useEffect)((function(){(w||v)&&setTimeout((function(){_&&_()}),2e3)}),[w,v]);return h?Object(a.createElement)(o.Modal,{title:"",onRequestClose:function(){return _()},className:"bwf-c-s-delete-model bwf-admin-modal bwf-admin-modal-small bwf-admin-modal-no-header"},P&&Object(a.createElement)("div",{className:"bwf-h4"},Object(s.__)("Delete","wp-marketing-automations-crm")),j&&Object(a.createElement)("div",{className:"bwf-t-center"},Object(a.createElement)("div",{className:"bwf_clear_20"}),Object(a.createElement)(r.a,null)),!!w&&Object(a.createElement)("div",{className:"bwf-t-center"},Object(a.createElement)(c.a,null),Object(a.createElement)("div",{className:"bwf-h1"},w)),!!v&&Object(a.createElement)("div",{className:"bwf-t-center"},Object(a.createElement)(i.a,null),Object(a.createElement)("div",{className:"bwf-h1"},v)),!j&&!w&&!v&&Object(a.createElement)("div",{className:"bwf-t-center bwf-form-buttons"},P?Object(a.createElement)(a.Fragment,null,Object(a.createElement)("div",{className:"bwf-h2"},Object(s.__)("Are you sure?","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf_clear_15"}),Object(a.createElement)("div",{className:"bwf-h4 bwf-h4-grey"},Object(s.__)("Once you delete this item. It will no longer available.","wp-marketing-automations-crm"))):Object(a.createElement)(a.Fragment,null,Object(a.createElement)("div",{className:"bwf-h1"},n),!!E&&Object(a.createElement)("p",null,E)),Object(a.createElement)("div",{className:"bwf_clear_20"}),Object(a.createElement)("div",{className:P?"bwf_text_right":""},Object(a.createElement)(o.Button,{isTertiary:!0,style:{marginRight:"20px"},onClick:function(){_&&_()}},b),Object(a.createElement)(o.Button,{isPrimary:!0,onClick:function(){O&&O()},className:P?"bwf-delete-btn":""},u)))):null}},186:function(e,t,n){"use strict";var a=n(118),r=n(25),c=n(0);function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}t.a=function(e){var t=e.icon,n=e.size,o=void 0===n?24:n,s=Object(r.a)(e,["icon","size"]);return Object(c.cloneElement)(t,function(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){Object(a.a)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}({width:o,height:o},s))}},195:function(e,t,n){"use strict";var a=n(0),r=n(93),c=Object(a.createElement)(r.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(a.createElement)(r.Path,{d:"M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"}));t.a=c},196:function(e,t,n){"use strict";var a=n(0),r=n(93),c=Object(a.createElement)(r.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(a.createElement)(r.Path,{d:"M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"}));t.a=c},202:function(e,t,n){"use strict";var a=n(0),r=n(93),c=Object(a.createElement)(r.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(a.createElement)(r.Path,{d:"M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"}));t.a=c},203:function(e,t,n){"use strict";var a=n(0),r=n(93),c=Object(a.createElement)(r.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(a.createElement)(r.Path,{d:"M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"}));t.a=c},478:function(e,t,n){"use strict";var a=n(114),r=n(0),c=n(4),i=n(7),o=n(44);function s(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function l(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?s(Object(n),!0).forEach((function(t){u(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):s(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function u(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}t.a=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",s=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"",u=arguments.length>4&&void 0!==arguments[4]?arguments[4]:"left",m=!(arguments.length>5&&void 0!==arguments[5])||arguments[5],b=bwfcrm_contacts_data&&bwfcrm_contacts_data.header_data?bwfcrm_contacts_data.header_data:{},f=b.carts_nav,O=Object(a.a)(),d=O.setActiveMultiple,j=O.resetHeaderMenu,p=O.setL2NavType,w=O.setL2Nav,g=O.setBackLink,v=O.setL2Title,_=O.setL2NavAlign,y=O.setPageHeader,h=O.setPageCountData,k=Object(o.a)(),E=k.getPageCountData,N=E();return Object(r.useEffect)((function(){j(),Object(i.K)()&&p("menu"),Object(i.K)()&&m&&w(f),d({leftNav:"carts",rightNav:e}),s&&g(s),n&&v(n),_(u),!Object(i.K)()&&v(Object(c.__)("Cart Tracking","wp-marketing-automations-crm")),y("Carts"),h(l(l({},N),t))}),[e,t]),!0}},689:function(e,t,n){},762:function(e,t,n){"use strict";n.r(t);var a=n(0),r=n(478),c=n(16),i=n.n(c),o=n(7),s=n(4),l=n(9),u=n(130),m=n(15),b=n.n(m),f=n(45),O=n(47),d=n(31),j=n(137),p=(n(689),n(5)),w=n(13);function g(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function v(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?g(Object(n),!0).forEach((function(t){_(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):g(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function _(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function y(e,t,n,a,r,c,i){try{var o=e[c](i),s=o.value}catch(e){return void n(e)}o.done?t(s):Promise.resolve(s).then(a,r)}function h(e){return function(){var t=this,n=arguments;return new Promise((function(a,r){var c=e.apply(t,n);function i(e){y(c,a,r,i,o,"next",e)}function o(e){y(c,a,r,i,o,"throw",e)}i(void 0)}))}}function k(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],a=!0,r=!1,c=void 0;try{for(var i,o=e[Symbol.iterator]();!(a=(i=o.next()).done)&&(n.push(i.value),!t||n.length!==t);a=!0);}catch(e){r=!0,c=e}finally{try{a||null==o.return||o.return()}finally{if(r)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return E(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return E(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function E(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}t.default=function(e){var t=e.match.params.cartType,n=e.match.params.id;Object(r.a)(t,"","Card #"+n,"admin.php?page=autonami&path=/carts/".concat(t),"left",!1);var c=k(Object(a.useState)(25),2),m=c[0],g=c[1],_=k(Object(a.useState)(0),2),y=_[0],E=_[1],N=k(Object(a.useState)(0),2),P=N[0],L=N[1],S=k(Object(a.useState)([]),2),T=S[0],x=S[1],A=k(Object(a.useState)(!1),2),C=A[0],D=A[1],M=k(Object(a.useState)({}),2),I=M[0],B=M[1],H=k(Object(a.useState)(!1),2),R=H[0],z=H[1],F=k(Object(a.useState)(),2),q=F[0],G=F[1],V=k(Object(a.useState)({status:!1,loading:!1}),2),K=V[0],J=V[1],Q=function(){var e=h(regeneratorRuntime.mark((function e(){var t,a,r,c,l,u,b,f=arguments;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return t=f.length>0&&void 0!==f[0]?f[0]:{},z(!0),e.prev=2,a="",t&&t.search&&(a="search=".concat(t.search)),e.next=7,i()({method:"GET",path:Object(o.f)("/carts/".concat(n,"/tasks/?offset=").concat(y,"&limit=").concat(m,"&").concat(a))});case 7:if((r=e.sent)&&r.result&&Array.isArray(r.result)){e.next=11;break}return G(Object(s.__)("Blank response returned","wp-marketing-automations-crm")),e.abrupt("return");case 11:c=r.total_count,l=r.result,u=r.limit,b=r.offset,c&&L(parseInt(c)),u&&g(parseInt(u)),b&&E(parseInt(b)),l&&x(l),e.next=21;break;case 18:e.prev=18,e.t0=e.catch(2),G(e.t0&&e.t0.message?e.t0.message:Object(s.__)("Unknown Error Occurred","wp-marketing-automations-crm"));case 21:z(!1);case 22:case"end":return e.stop()}}),e,null,[[2,18]])})));return function(){return e.apply(this,arguments)}}();Object(a.useEffect)((function(){Object(o.d)("Cart Task #"+n),Q()}),[]);var U=[{key:"action",label:"",isLeftAligned:!0,required:!0,cellClassName:"bwf-col-action bwf-w-30"},{key:"task",label:Object(s.__)("Task","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"actions",label:Object(s.__)("Action","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"data",label:Object(s.__)("Data","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"automation",label:Object(s.__)("Automation","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"date",label:Object(s.__)("Date","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"show",label:"",isLeftAligned:!0},{key:"status",label:Object(s.__)("Status","wp-marketing-automations-crm"),isLeftAligned:!0,cellClassName:"bwf-col-center"}],$=function(e){e!==m&&(g(m),Q())},W=function(){var e=h(regeneratorRuntime.mark((function e(t){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(J({status:!0,loading:!0}),Object(p.isEmpty)(t)){e.next=12;break}return e.prev=2,e.next=5,i()({path:Object(o.f)("/automations/run-tasks"),method:"POST",data:{task_ids:t}}).then((function(e){200===e.code?(J({status:!0,success:e.message}),setTimeout((function(){Q()}),1e3)):J({status:!0,error:e.message})}));case 5:e.next=10;break;case 7:e.prev=7,e.t0=e.catch(2),J({status:!0,error:e.t0.message});case 10:e.next=13;break;case 12:J({status:!0,error:Object(s.__)("No Task Selected","wp-marketing-automations-crm")});case 13:case"end":return e.stop()}}),e,null,[[2,7]])})));return function(t){return e.apply(this,arguments)}}(),X=function(e){return Object(a.createElement)(f.a,{label:Object(s.__)("Quick Actions","wp-marketing-automations-crm"),menuPosition:"bottom right",renderContent:function(t){var n=t.onToggle;return Object(a.createElement)(a.Fragment,null,"t_0"==e.status&&Object(a.createElement)(O.a,{isClickable:!0,onInvoke:function(){W([e.id]),n()}},Object(a.createElement)(d.a,{justify:"flex-start"},Object(a.createElement)(d.c,null,Object(a.createElement)(w.a,{icon:"play"})),Object(a.createElement)(d.c,null,Object(s.__)("Run Now","wp-marketing-automations-crm")))),Object(a.createElement)(O.a,{isClickable:!0,onInvoke:function(){J({status:!0,taskId:e.id}),n()}},Object(a.createElement)(d.a,{justify:"flex-start"},Object(a.createElement)(d.c,null,Object(a.createElement)(w.a,{icon:"trash"})),Object(a.createElement)(d.c,null,Object(s.__)("Delete","wp-marketing-automations-crm")))))}})},Y={l_0:Object(s.__)("Failed","wp-marketing-automations-crm"),l_1:Object(s.__)("Completed","wp-marketing-automations-crm"),t_0:Object(s.__)("Scheduled","wp-marketing-automations-crm"),t_1:Object(s.__)("Paused","wp-marketing-automations-crm")},Z=Array.isArray(T)?T.map((function(e){return[{display:X(e),value:""},{display:"#".concat(e.id),value:""},{display:e.task_integration+(e.task_integration_action?" : ".concat(e.task_integration_action):""),value:""},{display:Object(a.createElement)("ul",{dangerouslySetInnerHTML:{__html:e.task_details}}),value:""},{display:e.automation_name,value:""},{display:Object(a.createElement)("span",{dangerouslySetInnerHTML:{__html:e.task_date}}),value:""},{display:Object(a.createElement)(l.Button,{icon:Object(a.createElement)(w.a,{icon:"view",size:30}),onClick:function(){B(e),D(!0)}}),value:""},{display:Y[e.status]?Y[e.status]:"-",value:e.status}]})):[],ee=function(){var e=h(regeneratorRuntime.mark((function e(){return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(J(v(v({},K),{},{loading:!0})),!K.hasOwnProperty("taskId")){e.next=10;break}return e.prev=2,e.next=5,i()({path:Object(o.f)("/automations/tasks/"),method:"DELETE",data:{task_ids:[K.taskId]}}).then((function(e){200==e.code&&(J(v(v({},K),{},{success:e.message})),setTimeout((function(){Q()}),1e3))}));case 5:e.next=10;break;case 7:e.prev=7,e.t0=e.catch(2),J(v(v({},K),{},{error:e.t0.message}));case 10:case"end":return e.stop()}}),e,null,[[2,7]])})));return function(){return e.apply(this,arguments)}}();return Object(a.createElement)(a.Fragment,null,q&&Object(a.createElement)(l.Notice,{status:"error"},q),Object(a.createElement)("div",{className:"bwf-flex"},Object(a.createElement)("div",{className:"bwf_main_heading_inline bwf-mr-15"},Object(s.__)("Tasks","wp-marketing-automations-crm"))),Object(a.createElement)("div",{className:"bwf_clear_20"}),Object(a.createElement)(u.a,{className:b()("bwfcrm-forms-list-table",{"has-search":!0}),rows:Z,headers:U,query:{paged:y/m+1},rowsPerPage:m,totalRows:P,isLoading:R,onPageChange:function(e,t){E((e-1)*m),Q()},onQueryChange:function(e){return"per_page"!==e?function(){}:$},rowHeader:!0,showMenu:!1,hideHeader:"yes",emptyMessage:Object(s.__)("No tasks found","wp-marketing-automations-crm")}),Object(a.createElement)(j.a,{confirmText:Object(s.__)("Are you sure you want to delete this task ?","wp-marketing-automations-crm"),confirmButtonText:Object(s.__)("Confirm","wp-marketing-automations-crm"),cancelButtonText:Object(s.__)("Cancel","wp-marketing-automations-crm"),onConfirm:ee,isLoading:K.loading,successMessage:K.success,errorMessage:K.error,onRequestClose:function(){return J({status:!1})},isOpen:K.status,isDelete:!0}),C&&Object(a.createElement)(l.Modal,{title:"Details",onRequestClose:function(){return D(!1)},className:"bwf-message-preview-modal bwf-admin-modal bwf-admin-modal-medium bwf-task-model"},Object(a.createElement)(a.Fragment,null,Object(a.createElement)("div",{className:"bwf-p-15 bwf-bb-grey"},Object(a.createElement)("div",{className:"bwf-w-150 bwf_bold bwf_align_center"},Object(s.__)("Automation","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf_align_center"},I.automation_name)),Object(a.createElement)("div",{className:"bwf-p-15 bwf-bb-grey"},Object(a.createElement)("div",{className:"bwf-w-150 bwf_bold bwf_align_center"},Object(s.__)("Event","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf_align_center"},I.automation_source+" : "+I.automation_event)),Object(a.createElement)("div",{className:"bwf-p-15 bwf-bb-grey"},Object(a.createElement)("div",{className:"bwf-w-150 bwf_bold bwf_align_center"},Object(s.__)("Action","wp-marketing-automations-crm")),Object(a.createElement)("div",{className:"bwf_align_center"},I.task_integration+(I.task_integration_action?" : ".concat(I.task_integration_action):""))),Object(a.createElement)("div",{className:"bwf-p-15 bwf-bb-grey bwf-task-details"},Object(a.createElement)("ul",{className:"bwf_align_center",dangerouslySetInnerHTML:{__html:I.task_details}})),!Object(p.isEmpty)(I.task_message)&&Object(a.createElement)(a.Fragment,null,Object(a.createElement)("div",{className:"bwf-h3 bwf-pt-15"},Object(s.__)("Notes","wp-marketing-automations-crm")),Object.entries(I.task_message).map((function(e){var t=k(e,2),n=t[0],r=t[1];return Object(a.createElement)("div",{className:"bwf-p-15"},Object(a.createElement)("div",{className:"bwfcrm-task_notes_card"},r),Object(a.createElement)("div",{className:"bwfcrm-task_notes_time"},Object(a.createElement)("i",null,n)))}))))))}}}]);