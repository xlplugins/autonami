(window.webpackJsonp=window.webpackJsonp||[]).push([[20],{703:function(e,t,a){},771:function(e,t,a){"use strict";a.r(t);var n=a(0),c=a(325),s=a(4);t.default=function(e){var t=function(){return Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Total Revenue","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},"-")),Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Details","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},"-",Object(n.createElement)("div",{className:"bwf_clear_20"}))))};return Object(n.createElement)("div",{className:"bwf-c-s-data"},Object(n.createElement)(c.a,{items:[{key:0,label:Object(s.__)("Funnels","wp-marketing-automations-crm"),display:t()},{key:1,label:Object(s.__)("Optin","wp-marketing-automations-crm"),display:t()},{key:2,label:Object(s.__)("Checkouts","wp-marketing-automations-crm"),display:t()},{key:3,label:Object(s.__)("Bumps","wp-marketing-automations-crm"),display:t()},{key:4,label:Object(s.__)("Upsells","wp-marketing-automations-crm"),display:t()}]}))}},788:function(e,t,a){"use strict";a.r(t);var n=a(0),c=a(130),s=a(4),l=a(246),r=a(124),m=a(7),i=a(5),u=Object(r.a)(Object(m.F)()).formatAmount,b=function(){var e=l.a.getFunnels(),t=e&&"checkout"in e?e.checkout:[],a=[{key:"name",label:Object(s.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"funnel",label:Object(s.__)("Funnel","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"order",label:Object(s.__)("Order #","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"amount",label:Object(s.__)("Revenue","wp-marketing-automations-crm"),isLeftAligned:!0}],r=t.map((function(e){return[{display:Object(i.isEmpty)(e.name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=wfacp&wfacp_id="+e.wfacp_id},e.name),value:e.name},{display:Object(i.isEmpty)(e.funnel_name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=bwf_funnels&section=funnel&path=/funnel&edit="+e.funnel},e.funnel_name),value:e.funnel_name},{display:Object(i.isEmpty)(e.order)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"post.php?post="+e.order+"&action=edit"},"#"+e.order),value:e.order},{display:u(e.amount),value:e.amount}]})),m=t.reduce((function(e,t){return parseFloat(e)+parseFloat(t.amount)}),0);return Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Total Revenue","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},u(m))),Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Details","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},Object(i.isEmpty)(r)?"-":Object(n.createElement)(c.a,{className:"contact-funnel",title:"",rows:r,headers:a,rowsPerPage:1,totalRows:1,isLoading:!1,rowHeader:!0,showMenu:!1,emptyMessage:Object(s.__)("No checkouts found","wp-marketing-automations-crm")}),Object(n.createElement)("div",{className:"bwf_clear_20"}))))},o=a(367),f=Object(r.a)(Object(m.F)()).formatAmount,p=function(){var e=l.a.getFunnels(),t=e&&"upsells"in e?e.upsells:[],a=[{key:"name",label:Object(s.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"funnel",label:Object(s.__)("Funnel","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"order",label:Object(s.__)("Order #","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"status",label:Object(s.__)("Status","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"amount",label:Object(s.__)("Revenue","wp-marketing-automations-crm"),isLeftAligned:!0}],r=Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s"}),m=Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s bwfcrm-cs-status-success"}),u=Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s bwfcrm-cs-status-failed"}),b=function(e){switch(parseInt(e)){case 4:return m;case 10:return r;default:return u}},o=t.map((function(e){return[{display:Object(i.isEmpty)(e.object_name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=upstroke&section=rules&edit="+e.object_id+"&wffn_funnel_ref="+e.funnel_id},e.object_name),value:e.object_name},{display:Object(i.isEmpty)(e.funnel_name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=bwf_funnels&section=funnel&path=/funnel&edit="+e.funnel_id},e.funnel_name),value:e.funnel_name},{display:Object(i.isEmpty)(e.order_id)?"-":Object(n.createElement)("a",{target:"_blank",className:"bwf-a-no-underline",href:"post.php?post="+e.order_id+"&action=edit"},"#"+e.order_id),value:e.order_id},{display:b(e.action_type_id),value:e.action_type_id},{display:e.value?f(e.value):f(0),value:e.value}]})),p=t.reduce((function(e,t){return isNaN(parseFloat(t.value))?parseFloat(e):parseFloat(e)+parseFloat(t.value)}),0);return Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Total Revenue","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},f(p))),Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Details","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},Object(i.isEmpty)(o)?"-":Object(n.createElement)(c.a,{className:"contact-funnel",title:"",rows:o,headers:a,rowsPerPage:1,totalRows:1,isLoading:!1,rowHeader:!0,showMenu:!1,emptyMessage:Object(s.__)("No upsells found","wp-marketing-automations-crm")}),Object(n.createElement)("div",{className:"bwf_clear_20"}))))},d=Object(r.a)(Object(m.F)()).formatAmount,w=function(){var e=l.a.getFunnels(),t=e&&"order_bump"in e?e.order_bump:[],a=[{key:"name",label:Object(s.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"funnel",label:Object(s.__)("Funnel","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"order",label:Object(s.__)("Order #","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"status",label:Object(s.__)("Status","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"amount",label:Object(s.__)("Revenue","wp-marketing-automations-crm"),isLeftAligned:!0}],r=t.map((function(e){return[{display:Object(i.isEmpty)(e.name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=wfob&section=rules&wfob_id="+e.bump_id},e.name),value:e.name},{display:Object(i.isEmpty)(e.funnel_name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=bwf_funnels&section=funnel&path=/funnel&edit="+e.funnel},e.funnel_name),value:e.funnel_name},{display:Object(i.isEmpty)(e.order)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"post.php?post="+e.order+"&action=edit"},"#"+e.order),value:e.order},{display:1==parseInt(e.converted)?Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s bwfcrm-cs-status-success"}):Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s bwfcrm-cs-status-failed"}),value:e.converted},{display:d(e.amount),value:e.amount}]})),m=t.reduce((function(e,t){return parseFloat(e)+parseFloat(t.amount)}),0);return Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Total Revenue","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},d(m))),Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Details","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},Object(i.isEmpty)(r)?"-":Object(n.createElement)(c.a,{className:"contact-funnel",title:"",rows:r,headers:a,rowsPerPage:1,totalRows:1,isLoading:!1,rowHeader:!0,showMenu:!1,emptyMessage:Object(s.__)("No bumps found","wp-marketing-automations-crm")}),Object(n.createElement)("div",{className:"bwf_clear_20"}))))},_=Object(r.a)(Object(m.F)()).formatAmount,O=function(){var e=l.a.getFunnels(),t=e&&"records"in e?e.records:[],a=[{key:"name",label:Object(s.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"optin",label:Object(s.__)("Optin","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"checkout",label:Object(s.__)("Checkout","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"bump",label:Object(s.__)("Bump","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"upsell",label:Object(s.__)("Upsell","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"total_revenue",label:Object(s.__)("Revenue","wp-marketing-automations-crm"),isLeftAligned:!0}],r=t.map((function(e){return[{display:Object(i.isEmpty)(e.funnel_name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=bwf_funnels&section=funnel&path=/funnel&edit="+e.funnel_id},e.funnel_name),value:e.funnel_name},{display:null!=e.in_optin?Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s bwfcrm-cs-status-success"}):Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s"}),value:e.in_optin},{display:!0===e.in_checkout||!1===e.in_checkout?Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s "+(!0===e.in_checkout?"bwfcrm-cs-status-success":"bwfcrm-cs-status-failed")}):Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s"}),value:e.in_checkout},{display:!0===e.in_bump||!1===e.in_bump?Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s "+(!0===e.in_bump?"bwfcrm-cs-status-success":"bwfcrm-cs-status-failed")}):Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s"}),value:e.in_bump},{display:!0===e.in_upsell||!1===e.in_upsell?Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s "+(!0===e.in_upsell?"bwfcrm-cs-status-success":"bwfcrm-cs-status-failed")}):Object(n.createElement)("div",{className:"bwfcrm-cs-status bwfcrm-cs-status-s"}),value:e.in_upsell},{display:_(e.total_revenue),value:e.total_revenue}]})),m=t.reduce((function(e,t){return parseFloat(e)+parseFloat(t.total_revenue)}),0);return Object(n.createElement)(n.Fragment,null,Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Total Revenue","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},_(m))),Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Details","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},Object(i.isEmpty)(r)?"-":Object(n.createElement)(c.a,{className:"contact-funnel",title:"",rows:r,headers:a,rowsPerPage:1,totalRows:1,isLoading:!1,rowHeader:!0,showMenu:!1,emptyMessage:Object(s.__)("No funnels found","wp-marketing-automations-crm")}),Object(n.createElement)("div",{className:"bwf_clear_20"}))))},j=function(){var e=l.a.getFunnels(),t=e&&"optin"in e?e.optin:[],a=[{key:"name",label:Object(s.__)("Name","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"funnel",label:Object(s.__)("Funnel","wp-marketing-automations-crm"),isLeftAligned:!0},{key:"entry",label:Object(s.__)("Entry","wp-marketing-automations-crm"),isLeftAligned:!0}],r=t.map((function(e){return[{display:Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=wf-op&section=design&edit="+e.id},e.name),value:e.name},{display:Object(i.isEmpty)(e.funnel_name)?"-":Object(n.createElement)("a",{className:"bwf-a-no-underline",target:"_blank",href:"admin.php?page=bwf_funnels&section=funnel&path=/funnel&edit="+e.funnel},e.funnel_name),value:e.funnel_name},{display:m(e.entry),value:e.entry}]}));function m(e){var t=[];return Object(i.each)(JSON.parse(e),(function(e,a){var c=a.split("_");if(c.splice(0,1),!Object(i.isEmpty)(e)){for(var s=0;s<c.length;s++)c[s]=c[s].charAt(0).toUpperCase()+c[s].substring(1);t.push(Object(n.createElement)("li",{key:e.toString()},"".concat(c.join(" "),": ").concat(e)))}})),Object(n.createElement)("ul",{className:"bwf-terms-inline"},t)}return Object(n.createElement)("div",{className:"bwf-c-s-list"},Object(n.createElement)("div",{className:"bwf-c-s-label"},Object(s.__)("Details","wp-marketing-automations-crm")),Object(n.createElement)("div",{className:"bwf-c-s-po-value"},Object(i.isEmpty)(r)?"-":Object(n.createElement)(c.a,{className:"contact-funnel",title:"",rows:r,headers:a,rowsPerPage:1,totalRows:1,isLoading:!1,rowHeader:!0,showMenu:!1,emptyMessage:Object(s.__)("No optins data found","wp-marketing-automations-crm")}),Object(n.createElement)("div",{className:"bwf_clear_20"})))},g=(a(703),a(520)),v=a(325),E=a(9);t.default=function(){var e=Object(o.a)(),t=l.a.getContact(),a=l.a.getFunnelContactId();Object(n.useEffect)((function(){t&&"id"in t&&parseInt(t.id)!==parseInt(a)&&e.fetchFunnel(parseInt(t.id))}),[t]);var c=l.a.getFunnelError();if(l.a.getFunnelLoading())return Object(n.createElement)(g.c,null);if(c)return Object(n.createElement)(E.Notice,null,"message"in c?c.message:c);var r=function(){return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(O,null))},m=function(){return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(b,null))},i=function(){return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(w,null))},u=function(){return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(j,null))},f=function(){return Object(n.createElement)(n.Fragment,null,Object(n.createElement)(p,null))};return Object(n.createElement)("div",{className:"bwf-c-s-data"},Object(n.createElement)(v.a,{items:[{key:0,label:Object(s.__)("Funnels","wp-marketing-automations-crm"),display:r()},{key:1,label:Object(s.__)("Optin","wp-marketing-automations-crm"),display:u()},{key:2,label:Object(s.__)("Checkouts","wp-marketing-automations-crm"),display:m()},{key:3,label:Object(s.__)("Bumps","wp-marketing-automations-crm"),display:i()},{key:4,label:Object(s.__)("Upsells","wp-marketing-automations-crm"),display:f()}]}))}}}]);