(window.webpackJsonp=window.webpackJsonp||[]).push([[72],{123:function(e,t,n){"use strict";var r=n(0),a=n(4),c=n(5),o=n(8),i=n.n(o),l=n(15),s=n.n(l),u=(n(134),n(160));function m(e){return function(e){if(Array.isArray(e))return b(e)}(e)||function(e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}(e)||function(e,t){if(!e)return;if("string"==typeof e)return b(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return b(e,t)}(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function b(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function p(e,t,n,r,a,c,o){try{var i=e[c](o),l=i.value}catch(e){return void n(e)}i.done?t(l):Promise.resolve(l).then(r,a)}function f(e){return(f="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function g(e,t){return(g=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function O(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=v(e);if(t){var a=v(this).constructor;n=Reflect.construct(r,arguments,a)}else n=r.apply(this,arguments);return j(this,n)}}function j(e,t){return!t||"object"!==f(t)&&"function"!=typeof t?h(e):t}function h(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function v(e){return(v=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var y=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&g(e,t)}(i,e);var t,n,c,o=O(i);function i(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,i),(t=o.call(this,e)).state={options:[]},t.appendFreeTextSearch=t.appendFreeTextSearch.bind(h(t)),t.fetchOptions=t.fetchOptions.bind(h(t)),t.updateSelected=t.updateSelected.bind(h(t)),t}return t=i,(n=[{key:"getAutocompleter",value:function(){return this.props.autocompleter&&"object"===f(this.props.autocompleter)?this.props.autocompleter:{}}},{key:"getFormattedOptions",value:function(e,t){var n=this.getAutocompleter(),r=[];return e.forEach((function(e){var a={key:n.getOptionIdentifier(e),label:n.getOptionLabel(e,t),keywords:n.getOptionKeywords(e).filter(Boolean),value:e};r.push(a)})),r}},{key:"fetchOptions",value:function(e,t){var n=this;return this.props.bwfEnableEmptySearch||t?this.getAutocompleter().options(t).then(function(){var e,r=(e=regeneratorRuntime.mark((function e(r){var a;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return a=n.getFormattedOptions(r,t),n.setState({options:a}),e.abrupt("return",a);case 3:case"end":return e.stop()}}),e)})),function(){var t=this,n=arguments;return new Promise((function(r,a){var c=e.apply(t,n);function o(e){p(c,r,a,o,i,"next",e)}function i(e){p(c,r,a,o,i,"throw",e)}o(void 0)}))});return function(e){return r.apply(this,arguments)}}()):[]}},{key:"updateSelected",value:function(e){var t=this.props.onChange,n=this.getAutocompleter();t(e.map((function(e){return!e.notFound&&(e.value?n.getOptionCompletion(e.value):e)})).filter(Boolean))}},{key:"appendFreeTextSearch",value:function(e,t){var n=this.props.allowFreeTextSearch;if(!t||!t.length)return[];if(!n)return e.length>0?e:[{label:Object(a.__)("Not Found","wp-marketing-automations-crm"),key:"",notFound:!0}];var r=this.getAutocompleter();return[].concat(m(r.getFreeTextOptions(t,e)),m(e))}},{key:"render",value:function(){var e=this.getAutocompleter(),t=this.props,n=t.className,a=t.inlineTags,c=t.placeholder,o=t.selected,i=t.showClearButton,l=t.staticResults,m=t.disabled,b=(t.multiple,this.state.options),p=e.inputType?e.inputType:"text";return Object(r.createElement)("div",null,Object(r.createElement)(u.a,{className:s()("bwf-search",n,{"is-static-results":l}),disabled:m,hideBeforeSearch:!1,inlineTags:a,isSearchable:!0,label:c,getSearchExpression:e.getSearchExpression,multiple:!0,placeholder:c,onChange:this.updateSelected,onFilter:this.appendFreeTextSearch,onSearch:this.fetchOptions,options:b,remove:this.props.onRemoveTag,searchDebounceTime:500,searchInputType:p,selected:o,showClearButton:i,bwfMaintainSingleTerm:this.props.bwfMaintainSingleTerm}))}}])&&d(t.prototype,n),c&&d(t,c),i}(r.Component);y.propTypes={allowFreeTextSearch:i.a.bool,className:i.a.string,onChange:i.a.func,autocompleter:i.a.object,placeholder:i.a.string,selected:i.a.arrayOf(i.a.shape({key:i.a.oneOfType([i.a.number,i.a.string]).isRequired,label:i.a.string})),inlineTags:i.a.bool,showClearButton:i.a.bool,staticResults:i.a.bool,disabled:i.a.bool,bwfMaintainSingleTerm:i.a.bool,bwfEnableEmptySearch:i.a.bool},y.defaultProps={allowFreeTextSearch:!1,onChange:c.noop,selected:[],inlineTags:!1,showClearButton:!1,staticResults:!1,disabled:!1,bwfMaintainSingleTerm:!1,bwfEnableEmptySearch:!1},t.a=y},134:function(e,t,n){},146:function(e,t){e.exports=function(e){return e.webpackPolyfill||(e.deprecate=function(){},e.paths=[],e.children||(e.children=[]),Object.defineProperty(e,"loaded",{enumerable:!0,get:function(){return e.l}}),Object.defineProperty(e,"id",{enumerable:!0,get:function(){return e.i}}),e.webpackPolyfill=1),e}},361:function(e,t,n){"use strict";var r=n(0),a=n(123),c=n(4),o=n(139);t.a=function(e){var t=e.selected,n=e.onTagsChange;return Object(r.createElement)("div",{className:"bwf-c-field-mapper-terms"},Object(r.createElement)("div",{className:"bwf-input-label"},Object(c.__)("Add Tags","wp-marketing-automations-crm")),Object(r.createElement)(a.a,{autocompleter:o.b,multiple:!1,allowFreeTextSearch:!0,inlineTags:!1,selected:t,onChange:function(e){n(e)},onRemoveTag:function(e,r){var a=t.filter((function(t){return!(t.key==e&&t.label==r)}));n(a)},placeholder:Object(c.__)("Search by tag name","wp-marketing-automations-crm"),showClearButton:!0,disabled:!1}))}},455:function(e,t,n){"use strict";var r=n(0),a=n(123),c=n(4),o=n(139);t.a=function(e){var t=e.selected,n=e.onListsChange;return Object(r.createElement)("div",{className:"bwf-c-field-mapper-terms"},Object(r.createElement)("div",{className:"bwf-input-label"},Object(c.__)("Add to Lists","wp-marketing-automations-crm")),Object(r.createElement)(a.a,{autocompleter:o.a,multiple:!1,allowFreeTextSearch:!0,inlineTags:!1,selected:t,onChange:function(e){n(e)},onRemoveTag:function(e){var r=t.filter((function(t){return t.key!=e}));n(r)},placeholder:Object(c.__)("Search by list name","wp-marketing-automations-crm"),showClearButton:!0,disabled:!1}))}},561:function(e,t,n){"use strict";n.d(t,"a",(function(){return O}));var r=n(0),a=n(9),c=n(4),o=n(5),i=n(361),l=n(455),s=n(31),u=n(117),m=n(13);function b(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function p(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?b(Object(n),!0).forEach((function(t){f(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):b(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function f(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function d(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){if("undefined"==typeof Symbol||!(Symbol.iterator in Object(e)))return;var n=[],r=!0,a=!1,c=void 0;try{for(var o,i=e[Symbol.iterator]();!(r=(o=i.next()).done)&&(n.push(o.value),!t||n.length!==t);r=!0);}catch(e){a=!0,c=e}finally{try{r||null==i.return||i.return()}finally{if(a)throw c}}return n}(e,t)||function(e,t){if(!e)return;if("string"==typeof e)return g(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return g(e,t)}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function g(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}var O=function(e){var t=e.headersData,n=e.onRequestImport,b=e.submitButtonText,g=e.secondaryButtonText,O=e.onSecondaryButtonClick,j=e.savedData,h=e.buttonAlignClass,v=e.backButtonText,y=e.backButtonClick,w=e.extra,_=e.isSubmitButtonBusy,E=e.sourceHeaderText,k=void 0===E?Object(c.__)("Columns To Import","wp-marketing-automations-crm"):E,S=e.contactHeaderText,T=void 0===S?Object(c.__)("Map Into Field","wp-marketing-automations-crm"):S,x=t.headers,C=t.fields,N=d(Object(r.useState)(j&&j.mapped_fields?function(e,t){if(!e)return{};var n={},r=function(r){t.find((function(e){return e.index.toString()===r}))&&(n[r]=e[r])};for(var a in e)r(a);return n}(j.mapped_fields,x):{}),2),B=N[0],F=N[1],A=d(Object(r.useState)(1),2),P=A[0],I=A[1],R=j&&j.tags?j.tags.map((function(e){return{key:e.id,label:e.value}})):[],M=j&&j.lists?j.lists.map((function(e){return{key:e.id,label:e.value}})):[],D=d(Object(r.useState)(R),2),U=D[0],H=D[1],L=d(Object(r.useState)(M),2),q=L[0],J=L[1],$=d(Object(r.useState)(!j||!("update_existing"in j)||j.update_existing),2),z=$[0],K=$[1],G=d(Object(r.useState)(!j||!("marketing_status"in j)||j.marketing_status),2),Q=G[0],V=(G[1],d(Object(r.useState)(!(!j||!("trigger_events"in j))&&j.trigger_events),2)),W=V[0],X=V[1],Y=d(Object(r.useState)(null),2),Z=Y[0],ee=Y[1],te=(w||{}).disableMarketingStatusCheck,ne=void 0!==te&&te,re=function(){return ee(null),0===Object(o.size)(B)?(setTimeout((function(){return ee(Object(c.__)("Select the contact fields.","wp-marketing-automations-crm"))}),500),!1):!!Object.values(B).includes("email")||(setTimeout((function(){return ee(Object(c.__)("Map contact email with a form field.","wp-marketing-automations-crm"))}),500),!1)},ae=function(){if(re()){var e={};x.map((function(t){t.index in B&&(e[t.index]=B[t.index])}));var t={map:e,tags:U.map((function(e){return{id:e.key,value:e.label}})),lists:q.map((function(e){return{id:e.key,value:e.label}})),update_existing:z,trigger_events:W,imported_contact_status:P};!ne&&(t.marketing_status=Q),n(t)}},ce=function(){if(re()){var e={};x.map((function(t){t.index in B&&(e[t.index]=B[t.index])}));var t={map:e,tags:U.map((function(e){return{id:e.key,value:e.label}})),lists:q.map((function(e){return{id:e.key,value:e.label}})),update_existing:z,trigger_events:W};!ne&&(t.marketing_status=Q),O(t)}};if(!Object(o.isArray)(C)||!C.length>0)return Object(r.createElement)(a.Notice,{status:"error"},Object(c.__)("No contact fields found"));if(!Object(o.isArray)(x)||!x.length>0)return Object(r.createElement)(a.Notice,{status:"error"},Object(c.__)("No mapping fields found"));var oe={0:Object(c.__)("Unverified","wp-marketing-automation-crm"),1:Object(c.__)("Subscribed","wp-marketing-automation-crm"),2:Object(c.__)("Bounced","wp-marketing-automation-crm"),3:Object(c.__)("Unsubscribed","wp-marketing-automation-crm")};return Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-c-import-mapper-fields bwf-card-wrap bwf-form-fields bwf-crm-importer-csv-wrap"},Object(r.createElement)("div",{className:"bwf-card-header"},Object(r.createElement)("span",{className:"bwf-form-title"},Object(c.__)("Mapping","wp-marketing-automations-crm"))),Z&&Object(r.createElement)(r.Fragment,null,Object(r.createElement)(u.a,null),Object(r.createElement)(a.Notice,{status:"error",onRemove:function(){return ee(null)}},Z)),Object(r.createElement)("div",{className:"bwf-crm-import-section"},Object(r.createElement)("div",{className:"bwf_clear_20"}),Object(r.createElement)("div",{className:"bwf-c-import-field"},Object(r.createElement)("strong",null,k),Object(r.createElement)("span",null),Object(r.createElement)("strong",null,T)),x.map((function(e){return Object(r.createElement)("div",{key:e.index,className:"bwf-c-import-field"},Object(r.createElement)(a.TextControl,{disabled:!0,value:e.header}),Object(r.createElement)("span",{className:"bwf-display-flex"},Object(r.createElement)(m.a,{icon:"mapper"})),Object(r.createElement)("select",{value:e.index in B?B[e.index]:"",onChange:function(t){return function(e,t){var n=t.target.value,r="index"in e?e.index:"";""===n&&r in B?F(Object(o.omit)(B,r)):""!==n&&""!==r&&F(p(p({},B),{},f({},r,n)))}(e,t)}},Object(r.createElement)("option",{value:""},Object(c.__)("Do not import this field","wp-marketing-automations-crm")),C.map((function(e){return"fields"in e&&e.fields.length>0&&Object(r.createElement)("optgroup",{key:e.id,label:e.name},e.fields.map((function(e){return Object(r.createElement)("option",{key:e.id,value:e.id},e.name)})))}))))}))),Object(r.createElement)("div",{className:"bwf-crm-import-section"},Object(r.createElement)("div",{className:"bwf-h4"},Object(c.__)("Contact Profile","wp-marketing-automations-crm")),Object(r.createElement)("div",{className:"bwf-c-import-field-contact"},Object(r.createElement)(i.a,{onTagsChange:H,selected:U}),Object(r.createElement)("div",{className:"bwf_clear_20"}),Object(r.createElement)(l.a,{onListsChange:J,selected:q})),Object(r.createElement)("div",{className:"bwf_clear_20"}),!ne&&Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"bwf-p"},Object(c.__)("Status","wp-marketing-automations-crm")),Object(r.createElement)("select",{className:"bwf-import-csv-status-select",onChange:function(e){I(e.target.value)}},Object.keys(oe).map((function(e){return Object(r.createElement)("option",{key:e,value:e,selected:P==e},oe[e])})))),Object(r.createElement)("div",{className:"bwf_clear_20"}),Object(r.createElement)("div",null,Object(r.createElement)(a.ToggleControl,{label:Object(c.__)("Update existing contacts","wp-marketing-automations-crm"),checked:z,className:"bwf-tooglecontrol-advance",onChange:K})),Object(r.createElement)("div",{className:"bwf_clear_10"}),Object(r.createElement)("div",null,Object(r.createElement)(a.ToggleControl,{label:Object(c.__)("Trigger automations (for tag & list related events)","wp-marketing-automations-crm"),checked:W,className:"bwf-tooglecontrol-advance",onChange:X}))),Object(r.createElement)("div",{className:"bwf_clear_20"}),Object(r.createElement)("div",{className:"bwf-p-gap bwf-pt-0"},h?Object(r.createElement)(r.Fragment,null,Object(r.createElement)(s.a,null,!!v&&Object(r.createElement)(s.b,null,Object(r.createElement)(a.Button,{isSecondary:!0,onClick:y},Object(r.createElement)("span",null,v))),Object(r.createElement)(s.b,{className:h},Object(r.createElement)(a.Button,{onClick:ae,isPrimary:!0},Object(r.createElement)("span",null,b||Object(c.__)("Import","wp-marketing-automations-crm"))),g&&Object(r.createElement)(a.Button,{onClick:ce,className:"bwf-ml-10",isSecondary:!0},g)))):Object(r.createElement)(r.Fragment,null,Object(r.createElement)(a.Button,{onClick:ae,isPrimary:!0,isBusy:!!_,disabled:!!_,className:"bwf-display-flex"},Object(r.createElement)("span",null,b||Object(c.__)("Import","wp-marketing-automations-crm"))),g&&Object(r.createElement)(a.Button,{onClick:ce,className:"bwf-ml-10",isSecondary:!0},g)))))}},773:function(e,t,n){"use strict";n.r(t);var r=n(0),a=n(9),c=n(4),o=n(457),i=n(561),l=n(458),s=n(117),u=n(109),m=n(7);t.default=function(e){var t=e.feedId,n=Object(o.a)(),b=n.setStep,p=n.fetchMappingData,f=n.updateStepTwo,d=n.resetUpdateStepTwoStatus,g=Object(l.a)(),O=g.getFormFields,j=g.getFormHeaders,h=g.getFeed,v=g.getLoading,y=g.getError,w=g.getEditMapMode,_=g.getUpdateStepTwoStatus,E={fields:O(),headers:j()},k=v(),S=y(),T=h(),x=T&&T.status?parseInt(T.status):1,C=T&&T.data?T.data:{},N=w(),B=_();Object(r.useEffect)((function(){p(parseInt(t))}),[]);var F=Object(r.useContext)(m.b);Object(r.useEffect)((function(){3===B&&F("Unable to save mapping data"),2===B&&(F("Mapping data saved successfully"),b("double_optin")),B&&1!==B&&(d(),setTimeout((function(){return F("")}),3e3))}),[B]);var A=Object(c.__)("Next","wp-marketing-automations-crm");return Object(r.createElement)(r.Fragment,null,Object(r.createElement)(s.a,null),Object(r.createElement)("div",{className:"bwf-crm-stepper-wrap"},Object(r.createElement)("div",{className:"bwf-crm-importer-wrap"},S&&Object(r.createElement)(a.Notice,{status:"error"},S.message?S.message:Object(c.__)("Unknown error occurred","wp-marketing-automations-crm")),k?Object(r.createElement)(u.a,null):Object(r.createElement)(i.a,{headersData:E,onRequestImport:function(e){f(t,e,x)},submitButtonText:A,submitButtonIcon:"arrow-right-alt",savedData:C,buttonAlignClass:"bwf_text_right",backButtonText:!N&&Object(c.__)("Back","wp-marketing-automation-crm"),backButtonClick:!N&&function(){return b("selection")},sourceHeaderText:Object(c.__)("Forms Fields","wp-marketing-automation-crm"),contactHeaderText:Object(c.__)("Contact Fields","wp-marketing-automation-crm"),extra:{disableMarketingStatusCheck:!0},isSubmitButtonBusy:1===B}))))}}}]);