(window.webpackJsonp=window.webpackJsonp||[]).push([[15],{178:function(e,t,r){"use strict";var n=r(0),a=r(4),c=r(9),o=r(15),i=r.n(o),l=r(8),s=r.n(l),u=r(5),m=r(186),b=r(195),p=r(196);r(182);function f(e){return(f="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){for(var r=0;r<t.length;r++){var n=t[r];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function g(e,t){return(g=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function O(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var r,n=y(e);if(t){var a=y(this).constructor;r=Reflect.construct(n,arguments,a)}else r=n.apply(this,arguments);return j(this,r)}}function j(e,t){return!t||"object"!==f(t)&&"function"!=typeof t?w(e):t}function w(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function y(e){return(y=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var h=[10,25,50,75,100],v=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&g(e,t)}(s,e);var t,r,o,l=O(s);function s(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,s),(t=l.call(this,e)).state={inputValue:t.props.page},t.previousPage=t.previousPage.bind(w(t)),t.nextPage=t.nextPage.bind(w(t)),t.onInputChange=t.onInputChange.bind(w(t)),t.onInputBlur=t.onInputBlur.bind(w(t)),t.perPageChange=t.perPageChange.bind(w(t)),t.selectInputValue=t.selectInputValue.bind(w(t)),t}return t=s,(r=[{key:"previousPage",value:function(e){e.stopPropagation();var t=this.props,r=t.page,n=t.onPageChange;r-1<1||n(r-1,"previous")}},{key:"nextPage",value:function(e){e.stopPropagation();var t=this.props,r=t.page,n=t.onPageChange;r+1>this.pageCount||n(r+1,"next")}},{key:"perPageChange",value:function(e){var t=this.props,r=t.onPerPageChange,n=t.onPageChange,a=t.total,c=t.page;r(parseInt(e,10));var o=Math.ceil(a/parseInt(e,10));c>o&&n(o)}},{key:"onInputChange",value:function(e){this.setState({inputValue:e.target.value})}},{key:"onInputBlur",value:function(e){var t=this.props,r=t.onPageChange,n=t.page,a=parseInt(e.target.value,10);a!==n&&Number.isFinite(a)&&a>0&&this.pageCount&&this.pageCount>=a&&r(a,"goto")}},{key:"selectInputValue",value:function(e){e.target.select()}},{key:"renderPageArrows",value:function(){var e=this.props,t=e.page,r=e.showPageArrowsLabel;if(this.pageCount<=1)return null;var o=i()("bwf-pagination-link",{"is-active":t>1}),l=i()("bwf-pagination-link",{"is-active":t<this.pageCount});return Object(n.createElement)("div",{className:"bwf-pagination-page-arrows"},r&&Object(n.createElement)("span",{className:"bwf-pagination-page-arrows-label",role:"status","aria-live":"polite"},Object(a.sprintf)(Object(a.__)("Page %d of %d","wp-marketing-automations-crm"),t,this.pageCount)),Object(n.createElement)("div",{className:"bwf-pagination-page-arrows-buttons"},Object(n.createElement)(c.Button,{className:o,disabled:!(t>1),onClick:this.previousPage,label:Object(a.__)("Previous Page","wp-marketing-automations-crm")},Object(n.createElement)(m.a,{icon:b.a})),Object(n.createElement)(c.Button,{className:l,disabled:!(t<this.pageCount),onClick:this.nextPage,label:Object(a.__)("Next Page","wp-marketing-automations-crm")},Object(n.createElement)(m.a,{icon:p.a}))))}},{key:"renderPagePicker",value:function(){var e=this.props.page,t=this.state.inputValue,r=e<1||e>this.pageCount,c=i()("bwf-pagination-page-picker-input",{"has-error":r}),o=Object(u.uniqueId)("bwf-pagination-page-picker-");return Object(n.createElement)("div",{className:"bwf-pagination-page-picker"},Object(n.createElement)("label",{htmlFor:o,className:"bwf-pagination-page-picker-label"},Object(a.__)("Go to page","wp-marketing-automations-crm"),Object(n.createElement)("input",{id:o,className:c,"aria-invalid":r,type:"number",onClick:this.selectInputValue,onChange:this.onInputChange,onBlur:this.onInputBlur,value:t,min:1,max:this.pageCount})))}},{key:"renderPerPagePicker",value:function(){var e=h.map((function(e){return{value:e,label:e}}));return Object(n.createElement)("div",{className:"bwf-pagination-per-page-picker"},Object(n.createElement)(c.SelectControl,{label:Object(a.__)("Rows per page","wp-marketing-automations-crm"),value:this.props.perPage,onChange:this.perPageChange,options:e,labelPosition:"side"}))}},{key:"render",value:function(){var e=this.props,t=e.total,r=e.perPage,a=e.className,c=e.showPagePicker,o=e.showPerPagePicker;this.pageCount=Math.ceil(t/r);var l=i()("bwf-pagination",a);return this.pageCount<=1?t>h[0]&&Object(n.createElement)("div",{className:l},this.renderPerPagePicker())||null:Object(n.createElement)("div",{className:l},this.renderPageArrows(),c&&this.renderPagePicker(),o&&this.renderPerPagePicker())}}])&&d(t.prototype,r),o&&d(t,o),s}(n.Component);v.propTypes={page:s.a.number.isRequired,onPageChange:s.a.func,perPage:s.a.number.isRequired,onPerPageChange:s.a.func,total:s.a.number.isRequired,className:s.a.string,showPagePicker:s.a.bool,showPerPagePicker:s.a.bool,showPageArrowsLabel:s.a.bool},v.defaultProps={onPageChange:u.noop,onPerPageChange:u.noop,showPagePicker:!0,showPerPagePicker:!0,showPageArrowsLabel:!0},t.a=v},182:function(e,t,r){},224:function(e,t,r){"use strict";var n=r(0);r(257);t.a=function(e){return Object(n.useEffect)((function(){setTimeout((function(){e.removeMessage()}),5e3)}),[e.message]),Object(n.createElement)(n.Fragment,null,e.message&&Object(n.createElement)("div",{id:"bwfcrm-message-notice"},Object(n.createElement)("span",{className:"bwfcrm-message-text"},e.message)))}},257:function(e,t,r){},323:function(e,t,r){"use strict";var n=r(0),a=r(15),c=r.n(a);r(324);t.a=function(e){var t=e.white,r=void 0!==t&&t,a=e.image,o=void 0!==a&&a,i=e.long,l=void 0!==i&&i,s=e.width,u=void 0===s?0:s,m=e.height,b=void 0===m?0:m,p=c()({"bwf-placeholder-loader":!0,"is-white":!!r,"is-image":!!o,"is-long":!!l}),f=Object(n.useCallback)((function(){var e={width:u?"".concat(u,"px"):"120px"};return b&&(e.height="".concat(b,"px")),e}),[u]);return Object(n.createElement)("p",{className:p,style:f()})}},324:function(e,t,r){},485:function(e,t,r){"use strict";var n=r(0),a=r(9);r(99);var c=r(4);var o=Object(a.createSlotFill)("StandAloneBlockEditorSidebarInspector"),i=o.Slot,l=o.Fill;function s(){return Object(n.createElement)("div",{className:"getdavesbe-sidebar",role:"region","aria-label":Object(c.__)("Standalone Block Editor advanced settings."),tabIndex:"-1"},Object(n.createElement)(a.Panel,{header:Object(c.__)("Inspector")},Object(n.createElement)(i,{bubblesVirtually:!0})))}s.InspectorFill=l;r(100),r(101),r(102);var u=r(103);Object(u.registerCoreBlocks)();var m=r(28),b=r(5);function p(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function f(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?p(Object(r),!0).forEach((function(t){d(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):p(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function d(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function g(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(r.push(o.value),!t||r.length!==t);n=!0);}catch(e){a=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(a)throw c}}return r}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return O(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return O(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function O(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}var j=function(e){var t=e.content,r=void 0===t?"":t,a=e.setContent,c=e.keyid,o=Object(n.useRef)(!1),i=Object(n.useRef)(r),l=g(Object(n.useState)(r),2),s=l[0],u=l[1];return Object(n.useEffect)((function(){if(o.current){var e=window.tinymce.get("editor-bwfcrm"+c);(null==e?void 0:e.getContent())!==r&&(e.setContent(r||""),i.current=r||"")}}),[r]),Object(n.useEffect)((function(){a(s)}),[s]),Object(n.useEffect)((function(){function e(){var e=window.wpEditorL10n.tinymce.settings;wp.oldEditor.initialize("editor-bwfcrm"+c,{tinymce:f(f({},e),{},{inline:!1,content_css:!1,setup:t,height:400})})}function t(e){var t=Object(b.debounce)((function(){var t=e.getContent();t!==e._lastChange&&(e._lastChange=t,u(t))}),250);e.on("Paste Change input Undo Redo",t),e.on("remove",t.cancel),e.on("keydown",(function(t){m.isKeyboardEvent.primary(t,"z")&&t.stopPropagation(),t.keyCode!==m.BACKSPACE&&t.keyCode!==m.DELETE||!function(e){var t=e.getBody();return!(t.childNodes.length>1)&&(0===t.childNodes.length||!(t.childNodes[0].childNodes.length>1)&&/^\n?$/.test(t.innerText||t.textContent))}(e)||(onReplace([]),t.preventDefault(),t.stopImmediatePropagation()),t.altKey&&t.keyCode===m.F10&&t.stopPropagation()})),e.on("init",(function(){e.setContent(i.current),u(i.current)}))}function r(){"complete"===document.readyState&&e()}return o.current=!0,"complete"===document.readyState?e():document.addEventListener("readystatechange",r),function(){document.removeEventListener("readystatechange",r),wp.oldEditor.remove("editor-bwfcrm"+c)}}),[]),Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",null,Object(n.createElement)("div",{key:"editor",id:"editor-bwfcrm"+c,className:"wp-block-freeform block-library-rich-text__tinymce"})))};function w(){return(w=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(e[n]=r[n])}return e}).apply(this,arguments)}t.a=function(e){return Object(n.createElement)(j,w({},e,{keyid:e.keyid}))}},516:function(e,t,r){"use strict";var n=r(0),a=r(9),c=r(4),o=r(5),i=r(16),l=r.n(i),s=r(7),u=(r(553),r(109)),m=r(13);function b(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function p(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?b(Object(r),!0).forEach((function(t){f(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):b(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function f(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function d(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(r.push(o.value),!t||r.length!==t);n=!0);}catch(e){a=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(a)throw c}}return r}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return g(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return g(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function g(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}t.a=function(e){var t=e.isOpen,r=e.setOpenTags,i=e.onMergeTagsLoaded,b=e.context,f=d(Object(n.useState)(""),2),g=f[0],O=f[1],j=d(Object(n.useState)("contact"),2),w=j[0],y=j[1],h=d(Object(n.useState)([]),2),v=h[0],E=h[1],P=d(Object(n.useState)(!0),2),k=P[0],_=P[1],C=Object(n.useContext)(s.b);Object(n.useEffect)((function(){var e=b?"?context=".concat(b):"";l()({path:Object(s.g)("/broadcast/merge-tags".concat(e)),method:"GET"}).then((function(e){200===e.code&&(E(e.result),i(e.result),_(!1))}))}),[]);Object(n.useEffect)((function(){O(""),y("general")}),[t]);var N=[{label:Object(c.__)("General","wp-marketing-automations-crm"),value:"general"},{label:Object(c.__)("Contacts","wp-marketing-automations-crm"),value:"contact"},{label:Object(c.__)("Fields","wp-marketing-automations-crm"),value:"contact_fields"}];return t?Object(n.createElement)(a.Modal,{title:Object(c.__)("Merge Tags","wp-marketing-automations-crm"),onRequestClose:function(){return r(!1)},className:"bwf-crm-merge-tag-model bwf-admin-modal bwf-admin-modal-xl bwf-admin-modal-no-header bwf-show-close"},Object(n.createElement)("div",{className:"bwf-merge-tag-header"},Object(n.createElement)("div",{className:"bwf-merge-tag-label bwf-h3"},Object(c.__)("Merge Tags","wp-marketing-automations-crm")),Object(n.createElement)(a.TextControl,{className:"bwf-merge-tag-search",value:g,onChange:function(e){return O(e)},autoComplete:"off",placeholder:Object(c.__)("Search by name","wp-marketing-automations-crm")})),Object(n.createElement)("div",{className:"bwf-merge-tab"},Object(n.createElement)("div",{className:"bwf-merge-header"},Object(n.createElement)("div",{className:"bwf-header-option "+(""===w?"active":""),onClick:function(){return y("")}},Object(n.createElement)(m.a,{icon:"merge-tag",size:18,color:""===w?"#0073aa":""}),Object(n.createElement)("span",{className:"bwf-filter-label"},Object(c.__)("All","wp-marketing-automations-crm")),""===w&&Object(n.createElement)("span",{className:"bwf-filter-arrow"},Object(n.createElement)(m.a,{icon:"tailless-arrow-forward"}))),Object(n.createElement)("div",{className:"bwf-category-label"},Object(c.__)("Filter By Category","wp-marketing-automations-crm")),N&&N.map((function(e){return Object(n.createElement)("div",{className:"bwf-header-option "+(e.value===w?"active":""),onClick:function(){return y(e.value)}},Object(n.createElement)(m.a,{icon:"merge-tag",size:18,color:w===e.value?"#0073aa":""}),Object(n.createElement)("span",{className:"bwf-filter-label"},e.label),e.value===w&&Object(n.createElement)("span",{className:"bwf-filter-arrow"},Object(n.createElement)(m.a,{icon:"tailless-arrow-forward"})))}))),k?Object(n.createElement)("div",{className:"bwf-merge-loading"},Object(n.createElement)(u.a,null)):function(){var e=[];if(Object(o.isEmpty)(w)?Object.entries(v).map((function(t){var r=d(t,2),n=(r[0],r[1]);e=p(p({},e),n)})):e=v[w],!Object(o.isEmpty)(g)){var t=[];Object.keys(e).map((function(r){if(-1!=e[r].toLowerCase().indexOf(g.toLowerCase())){var n={};n[r]=e[r],t=p(p({},t),n)}})),e=t}return Object(n.createElement)("div",{className:"bwf-merge-tag-list"},Object(n.createElement)("table",null,Object(n.createElement)("tbody",null,Object(o.isEmpty)(e)?Object(n.createElement)("tr",null,Object(n.createElement)("div",{className:"bwf-p-15 bwf-t-center"},Object(c.__)("No merge tags available","wp-marketing-automations-crm"))):Object.keys(e).map((function(t){return Object(n.createElement)("tr",null,Object(n.createElement)("td",null,Object(n.createElement)("span",{className:"bwf-tag-label"},e[t]),Object(n.createElement)("span",{className:"bwf-tag-slug"},"{{"+t+"}}")),Object(n.createElement)("td",null,Object(n.createElement)("span",{className:"bwf-tag-item-select",onClick:function(){var e,n;e="{{"+t+"}}",(n=document.createElement("textarea")).value=e,document.body.appendChild(n),n.select(),document.execCommand("copy"),document.body.removeChild(n),C(Object(c.__)("Merge tag copied.","wp-marketing-automations-crm")),Object(s.K)(C,2e3),r(!1)}},Object(c.__)("Copy","wp-marketing-automations-crm"))))})))))}())):""}},517:function(e,t,r){"use strict";var n=r(0),a=r(4),c=r(9),o=r(120),i=r(121),l=r(109),s=r(5),u=r(16),m=r.n(u),b=r(7);function p(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function f(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?p(Object(r),!0).forEach((function(t){d(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):p(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function d(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}function g(e,t,r,n,a,c,o){try{var i=e[c](o),l=i.value}catch(e){return void r(e)}i.done?t(l):Promise.resolve(l).then(n,a)}function O(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(r.push(o.value),!t||r.length!==t);n=!0);}catch(e){a=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(a)throw c}}return r}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return j(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return j(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function j(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}t.a=function(e){var t=e.templateData,r=e.type,u=O(Object(n.useState)(!1),2),p=u[0],d=u[1],j=O(Object(n.useState)(""),2),w=j[0],y=j[1],h=O(Object(n.useState)(!1),2),v=h[0],E=h[1],P=O(Object(n.useState)({loading:!1,success:!1,error:!1,message:""}),2),k=P[0],_=P[1],C={rich:1,wc:2,html:3,editor:4},N=function(){var e,n=(e=regeneratorRuntime.mark((function e(){var n;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(_(f(f({},k),{},{loading:!0})),!w){e.next=14;break}return n={template:"editor"===t.type?t.editor.body:t.body,title:w,subject:t.subject,type:r||2,mode:C[t.type],data:{preheader:t.hasOwnProperty("preheader")?t.preheader:"",utmEnabled:t.hasOwnProperty("utmEnabled")?t.utmEnabled:"",utm:t.hasOwnProperty("utm")?t.utm:{},design:"editor"===t.type?t.editor.design:""}},e.prev=3,e.next=6,m()({path:Object(b.g)("/template"),method:"POST",data:n}).then((function(e){200===e.code&&(_({loading:!0,success:!0,message:e.message}),setTimeout((function(){_({loading:!1,success:!1,error:!1,message:""}),d(!1)}),2e3))}));case 6:e.next=12;break;case 8:e.prev=8,e.t0=e.catch(3),_({loading:!0,error:!0,message:e.t0.message}),setTimeout((function(){_({loading:!1,success:!1,error:!1,message:""})}),2e3);case 12:e.next=16;break;case 14:E(!0),_({loading:!1,success:!1,error:!1,message:""});case 16:case"end":return e.stop()}}),e,null,[[3,8]])})),function(){var t=this,r=arguments;return new Promise((function(n,a){var c=e.apply(t,r);function o(e){g(c,n,a,o,i,"next",e)}function i(e){g(c,n,a,o,i,"throw",e)}o(void 0)}))});return function(){return n.apply(this,arguments)}}();return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(c.Button,{isSecondary:!0,className:"bwf-big-button",onClick:function(){y(""),_({loading:!1,success:!1,error:!1,message:""}),d(!0)}},Object(a.__)("Save As Template","wp-marketing-automations-crm")),p&&Object(n.createElement)(c.Modal,{title:"Template",onRequestClose:function(){return d(!1)},className:"bwf-admin-modal bwf-admin-modal-medium"},k.loading?k&&k.success&&k.message?Object(n.createElement)("div",{className:"bwf-t-center"},Object(n.createElement)(o.a,null),Object(n.createElement)("div",{className:"bwf-h1"},k.message)):k&&k.error&&k.message?Object(n.createElement)("div",{className:"bwf-t-center"},Object(n.createElement)(i.a,null),Object(n.createElement)("div",{className:"bwf-h1"},k.message)):Object(n.createElement)(l.a,null):Object(n.createElement)("div",{onKeyPress:function(e){"Enter"===e.key&&(Object(s.isEmpty)(w)?E(!0):N())}},v&&Object(n.createElement)(c.Notice,{status:"error",onRemove:function(){return E("")}},Object(a.__)("Title is required","wp-marketing-automations-crm")),Object(n.createElement)(c.TextControl,{value:w,onChange:function(e){y(e),Object(s.isEmpty)(e)?E(!0):E(!1)},placeholder:Object(a.__)("Enter template title","wp-marketing-automations-crm")}),Object(n.createElement)("div",{className:"bwf_text_right bwf-form-buttons"},Object(n.createElement)(c.Button,{isPrimary:!0,className:"bwf-big-button",onClick:function(){return N()}},Object(a.__)("Save","wp-marketing-automations-crm"))))))}},518:function(e,t,r){"use strict";var n=r(0),a=r(9),c=(r(556),r(4)),o=r(16),i=r.n(o),l=r(7),s=r(109),u=r(5),m=r(13);function b(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(r.push(o.value),!t||r.length!==t);n=!0);}catch(e){a=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(a)throw c}}return r}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return p(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return p(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function p(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}t.a=function(e){var t=e.applyTemplate,r=b(Object(n.useState)(!1),2),o=r[0],p=r[1],f=b(Object(n.useState)(""),2),d=f[0],g=f[1],O=b(Object(n.useState)(""),2),j=O[0],w=O[1],y=b(Object(n.useState)([]),2),h=y[0],v=y[1],E=b(Object(n.useState)(!0),2),P=E[0],k=E[1],_=b(Object(n.useState)(!1),2),C=_[0],N=(_[1],b(Object(n.useState)(0),2)),S=(N[0],N[1],["rich","html","editor"].concat(Object(l.H)())),A=(Object(n.useContext)(l.b),function(){try{i()({method:"GET",path:Object(l.g)("/templates?limit=0&offset=0")}).then((function(e){200===e.code&&(v(e.result),k(!1))}))}catch(e){k(!1)}}),x={rich:{label:Object(c.__)("Text","wp-marketing-automations-crm"),value:1},wc:{label:Object(c.__)("WooCommerce","wp-marketing-automations-crm"),value:2},html:{label:Object(c.__)("Raw HTML","wp-marketing-automations-crm"),value:3},editor:{label:Object(c.__)("Drag and Drop","wp-marketing-automations-crm"),value:4}},I={1:"rich",2:"wc",3:"html",4:"editor"},T=function(){var e=[];return h.map((function(t){!S.includes(I[parseInt(t.mode)])||j&&parseInt(t.mode)!==parseInt(j)||d&&(!t.title||-1===t.title.toLowerCase().indexOf(d.toLowerCase()))||Object(u.isEmpty)(t.template)||Object(u.isEmpty)(t.subject)||e.push(t)})),e};return Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-list-template-wrap"},Object(n.createElement)(a.Button,{isTertiary:!0,className:"bwf-display-flex",title:Object(c.__)("My Templates","wp-marketing-automations-crm"),onClick:function(){A(),g(""),w(""),p(!o)}},Object(c.__)("My Templates","wp-marketing-automations-crm"))),o&&Object(n.createElement)(a.Modal,{title:Object(c.__)("My Templates","wp-marketing-automations-crm"),onRequestClose:function(){return p(!1)},className:"bwf-admin-modal bwf-admin-modal-xl bwf-template-modal bwf-admin-modal-no-header bwf-show-close"},P?Object(n.createElement)(s.a,null):Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-template-header"},Object(n.createElement)("div",{className:"bwf-template-label bwf-h4"},Object(c.__)("My Templates","wp-marketing-automations-crm")),Object(n.createElement)(a.TextControl,{className:"bwf-template-search",value:d,onChange:function(e){return g(e)},autoComplete:"off",placeholder:Object(c.__)("Search by name","wp-marketing-automations-crm")})),Object(n.createElement)("div",{className:"bwf-template-content-wrap"},Object(n.createElement)("div",{className:"bwf-filter-section"},Object(n.createElement)("div",{className:"bwf-header-option "+(""===j?"active":""),onClick:function(){return w("")}},Object(n.createElement)(m.a,{icon:"templates",size:18}),Object(n.createElement)("span",{className:"bwf-filter-label"},Object(c.__)("All","wp-marketing-automations-crm")),""===j&&Object(n.createElement)("span",{className:"bwf-filter-arrow"},Object(n.createElement)(m.a,{icon:"tailless-arrow-forward"}))),Object(n.createElement)("div",{className:"bwf-category-label"},Object(c.__)("Filter By Type","wp-marketing-automations-crm")),x&&Object.entries(x).map((function(e){var t=b(e,2),r=t[0],a=t[1];if(S.includes(r))return Object(n.createElement)("div",{className:"bwf-header-option "+(a.value===j?"active":""),onClick:function(){return w(a.value)}},Object(n.createElement)(m.a,{icon:"templates",size:18}),Object(n.createElement)("span",{className:"bwf-filter-label"},a.label),a.value===j&&Object(n.createElement)("span",{className:"bwf-filter-arrow"},Object(n.createElement)(m.a,{icon:"tailless-arrow-forward"})))}))),Object(n.createElement)("div",{className:"bwf-template-table-wrapper"},Object(n.createElement)("table",{className:"bwf-template-list-table"},Object(n.createElement)("tbody",null,T().length>0?T().map((function(e){return Object(n.createElement)("tr",null,Object(n.createElement)("td",null,Object(n.createElement)("span",{className:"bwf-template-title"},e.title?e.title:"-"),Object(n.createElement)("span",{className:"bwf-template-create-detail bwf-subject"},e.subject?Object(n.createElement)(n.Fragment,null,Object(c.__)("Subject ","wp-marketing-automations-crm")+" : "+e.subject):"-"),Object(n.createElement)("span",{className:"bwf-template-create-detail"},e.created_at?Object(n.createElement)(n.Fragment,null,Object(c.__)("Created on ","wp-marketing-automations-crm"),Object(l.A)(e.created_at)):"")),Object(n.createElement)("td",null,Object(n.createElement)("div",{className:"bwf-template-action-wrap"},Object(n.createElement)("span",{className:"bwf-template-item-apply",onClick:function(){C||(t(function(e){var t={body:4!==parseInt(e.mode)&&e.hasOwnProperty("template")?e.template:"",type:e.hasOwnProperty("mode")?I[e.mode]:1,subject:e.hasOwnProperty("subject")?e.subject:"",preheader:e.hasOwnProperty("data")&&e.data&&e.data.hasOwnProperty("preheader")?e.data.preheader:"",utmEnabled:!!(e.hasOwnProperty("data")&&e.data&&e.data.hasOwnProperty("utmEnabled"))&&e.data.utmEnabled};return t.utmEnabled&&(t.utm=e.hasOwnProperty("data")&&e.data&&e.data.hasOwnProperty("utm")?e.data.utm:{}),4===parseInt(e.mode)&&(t.editor={body:e.hasOwnProperty("template")?e.template:"",design:e.hasOwnProperty("data")&&e.data&&e.data.hasOwnProperty("design")?e.data.design:""}),t}(e)),p(!1))}},Object(c.__)("Apply","wp-marketing-automations-crm")))))})):Object(n.createElement)("span",{className:"bwf-display-flex bwf-p-15"},Object(c.__)("No Templates Found","wp-marketing-automations-crm")))))))))}},520:function(e,t,r){"use strict";var n=r(0),a=r(9),c=r(178),o=r(124),i=r(7),l=r(4),s=r(323);function u(){return(u=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(e[n]=r[n])}return e}).apply(this,arguments)}var m=function(e){var t=e.size,r=void 0===t?10:t;return Object(n.createElement)("table",null,Object(n.createElement)("tbody",null,Array.from(Array(r).keys()).map((function(){return Object(n.createElement)("tr",null,Object(n.createElement)("td",null,Object(n.createElement)("div",{className:"bwf-c-product-modal-row"},Object(n.createElement)(s.a,{width:50,height:50}),Object(n.createElement)("div",{className:"bwf-c-product-modal-row-info"},Object(n.createElement)("span",{className:"bwf-tag-label"},Object(n.createElement)(s.a,{width:70})),Object(n.createElement)("span",{className:"bwf-tag-slug"},Object(n.createElement)(s.a,{width:120,height:10}))))),Object(n.createElement)("td",null,Object(n.createElement)(s.a,{width:80,height:32})))}))))},b=function(e){var t=e.products,r=e.onProductSelected,a=Object(o.a)(Object(i.G)()).formatAmount;return Object(n.createElement)("table",null,Object(n.createElement)("tbody",null,t.map((function(e){var t=[Object(l.__)("Price: ","wp-marketing-automations")+a(e.price),!!e.stock_quantity&&Object(l.__)("Stock: ","wp-marketing-automations")+e.stock_quantity,e.stock_status].filter(Boolean).join(" | ");return Object(n.createElement)("tr",null,Object(n.createElement)("td",null,Object(n.createElement)("div",{className:"bwf-c-product-modal-row"},Array.isArray(e.images)&&e.images.length>0&&Object(n.createElement)("img",u({className:"bwf-c-product-modal-image"},e.images[0])),Object(n.createElement)("div",{className:"bwf-c-product-modal-row-info"},Object(n.createElement)("span",{className:"bwf-tag-label"},e.name," ",Object(n.createElement)("span",{className:"bwf-c-product-id"},"#",e.id)),Object(n.createElement)("span",{className:"bwf-tag-slug"},t)))),Object(n.createElement)("td",null,Object(n.createElement)("span",{className:"bwf-tag-item-select",onClick:function(){return!!r&&r(e)}},Object(l.__)("Select","wp-marketing-automations-crm"))))}))))},p=r(5),f=r(16),d=r.n(f),g=r(32),O=r(798);function j(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(r.push(o.value),!t||r.length!==t);n=!0);}catch(e){a=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(a)throw c}}return r}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return w(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return w(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function w(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}function y(e,t,r,n,a,c,o){try{var i=e[c](o),l=i.value}catch(e){return void r(e)}i.done?t(l):Promise.resolve(l).then(n,a)}var h=function(){var e,t=(e=regeneratorRuntime.mark((function e(t){var r,n,a,c,o,i,l,s;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return r=t.queryKey,(n=j(r,2))[0],a=n[1],c=a.search,o=a.limit,i=a.offset,l={search:c,per_page:o,page:parseInt(i/o)+1,orderby:"popularity"},e.next=5,d()({path:Object(g.addQueryArgs)("/wc-analytics/products",l),parse:!1});case 5:return s=e.sent,e.next=8,s.json();case 8:return e.t0=e.sent,e.t1=parseInt(s.headers.get("x-wp-total"),10),e.abrupt("return",{products:e.t0,totalCount:e.t1});case 11:case"end":return e.stop()}}),e)})),function(){var t=this,r=arguments;return new Promise((function(n,a){var c=e.apply(t,r);function o(e){y(c,n,a,o,i,"next",e)}function i(e){y(c,n,a,o,i,"throw",e)}o(void 0)}))});return function(e){return t.apply(this,arguments)}}(),v=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:10,r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:0;return Object(O.a)(["wc-products",{search:e,limit:t,offset:r}],h,{staleTime:6e4})};r(554);function E(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var r=[],n=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(n=(o=i.next()).done)&&(r.push(o.value),!t||r.length!==t);n=!0);}catch(e){a=!0,c=e}finally{try{n||null==i.return||i.return()}finally{if(a)throw c}}return r}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return P(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return P(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function P(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}t.a=function(e){var t=e.onProductSelected,r=e.onRequestClose,o=E(Object(n.useState)(""),2),i=o[0],s=o[1],u=E(Object(n.useState)(10),2),f=u[0],d=u[1],g=E(Object(n.useState)(0),2),O=g[0],j=g[1],w=Object(n.useCallback)(Object(p.debounce)((function(e){return s(e)}),700),[]),y=v(i,f,O),h=y.isLoading,P=y.data,k=(P=void 0===P?{}:P).products,_=void 0===k?[]:k,C=P.totalCount,N=void 0===C?0:C,S=y.error,A=y.isError,x=y.isSuccess;return Object(n.createElement)(a.Modal,{title:Object(l.__)("Select a Product","wp-marketing-automations-crm"),onRequestClose:function(){return!!r&&r()},className:"bwf-crm-products-model bwf-admin-modal bwf-admin-modal-xl bwf-admin-modal-no-header bwf-show-close"},Object(n.createElement)("div",{className:"bwf-products-header"},Object(n.createElement)("div",{className:"bwf-products-label bwf-h3"},Object(l.__)("Products","wp-marketing-automations-crm")),Object(n.createElement)(a.TextControl,{className:"bwf-products-search",onChange:function(e){return w(e)},placeholder:Object(l.__)("Search product","wp-marketing-automations-crm")})),Object(n.createElement)("div",{className:"bwf-merge-tab"},Object(n.createElement)("div",{className:"bwf-products-list"},h&&Object(n.createElement)(m,null),A&&Object(n.createElement)(a.Notice,{status:"error"},S),x&&Array.isArray(_)&&0===_.length&&Object(n.createElement)("div",{className:"bwf-p-15 bwf-t-center"},Object(l.__)("No Products Available","wp-marketing-automations-crm")),x&&Array.isArray(_)&&_.length>0&&Object(n.createElement)(n.Fragment,null,Object(n.createElement)(b,{products:_,onProductSelected:t}),Object(n.createElement)(c.a,{page:parseInt(O/f)+1,onPageChange:function(e){return j((e-1)*f)},perPage:f,onPerPageChange:function(e){return d(e)},total:N,showPagePicker:!0,showPerPagePicker:!0,showPageArrowsLabel:!0})))))}},553:function(e,t,r){},554:function(e,t,r){},556:function(e,t,r){}}]);