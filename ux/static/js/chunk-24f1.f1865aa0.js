(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-24f1","chunk-06ca"],{ApRr:function(t,e,a){},JTIi:function(t,e,a){},NoVw:function(t,e,a){"use strict";a.r(e);var i=a("ViDN"),n=(a("KTTK"),a("utuE")),s=a("d8QR"),o={components:{CreateView:i.a,membersDep:n.a},data:function(){return{formList:[{label:"公告标题",field:"title"},{label:"通知部门",field:"dep",type:"plus"},{label:"开始时间",field:"start_time",type:"date"},{label:"结束时间",field:"end_time",type:"date"},{label:"公告正文",field:"content",type:"textarea"}],formData:{dep:{staff:[],dep:[]}},rules:{title:[{required:!0,message:"公告标题不能为空",trigger:"blur"},{max:50,message:"公告标题长度最多为50个字符",trigger:"blur"}],content:[{required:!0,message:"公告正文不能为空",trigger:"blur"}]},loading:!1}},methods:{onSubmit:function(){var t=this;this.$refs.form.validate(function(e){if(!e)return!1;t.loading=!0,Object(s.a)({title:t.formData.title,content:t.formData.content,start_time:t.formData.start_time?new Date(t.formData.start_time).getTime()/1e3:"",end_time:t.formData.end_time?new Date(t.formData.end_time).getTime()/1e3:"",owner_structure_ids:t.formData.dep.dep.map(function(t){return t.id}),owner_user_ids:t.formData.dep.staff.map(function(t){return t.id})}).then(function(e){t.$message.success("新建公告成功"),1==t.$route.query.routerKey?t.$router.push("notice"):(e.data.create_time=(new Date).getTime(),t.$emit("onSubmit",e.data)),t.loading=!1}).catch(function(e){t.$message.error("新建公告失败"),t.loading=!1})})},close:function(){1==this.$route.query.routerKey?this.$router.go(-1):this.$emit("close")},popoverSubmit:function(t,e){this.$set(this.formData,"dep",{staff:t,dep:e})},deleteuser:function(t){this.formData.dep.staff.splice(t,1)},deleteDepuser:function(t){this.formData.dep.dep.splice(t,1)}}},l=(a("lrnB"),a("ZrdR")),r=Object(l.a)(o,function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("create-view",{attrs:{"body-style":{height:"100%"}}},[i("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"details-box"},[i("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[i("span",{staticClass:"text"},[t._v("新建公告")]),t._v(" "),i("img",{staticClass:"el-icon-close rt",attrs:{src:a("cjwK"),alt:""},on:{click:t.close}})]),t._v(" "),i("div",{staticClass:"content"},[i("el-form",{ref:"form",attrs:{model:t.formData,rules:t.rules}},t._l(t.formList,function(e,a){return i("el-form-item",{key:a,class:"el-form-item"+e.field,attrs:{label:e.label,prop:e.field}},["date"==e.type?[i("el-date-picker",{attrs:{type:"date",placeholder:"选择日期"},model:{value:t.formData[e.field],callback:function(a){t.$set(t.formData,e.field,a)},expression:"formData[item.field]"}})]:"textarea"==e.type?[i("el-input",{attrs:{type:"textarea",autosize:{minRows:6},placeholder:"请输入内容"},model:{value:t.formData[e.field],callback:function(a){t.$set(t.formData,e.field,a)},expression:"formData[item.field]"}})]:"plus"==e.type?[i("members-dep",{attrs:{popoverDisplay:"block",title:"通知部门",userCheckedData:t.formData[e.field].staff,depCheckedData:t.formData[e.field].dep},on:{popoverSubmit:t.popoverSubmit}},[i("flexbox",{staticClass:"user-container",attrs:{slot:"membersDep",wrap:"wrap"},slot:"membersDep"},[t._l(t.formData[e.field].staff,function(e,a){return i("div",{key:"user"+a,staticClass:"user-item",on:{click:function(e){e.stopPropagation(),t.deleteuser(a)}}},[t._v(t._s(e.realname)+"\n                  "),i("i",{staticClass:"delete-icon el-icon-close"})])}),t._v(" "),t._l(t.formData[e.field].dep,function(e,a){return i("div",{key:"dep"+a,staticClass:"user-item",on:{click:function(e){e.stopPropagation(),t.deleteDepuser(a)}}},[t._v(t._s(e.name)+"\n                  "),i("i",{staticClass:"delete-icon el-icon-close"})])}),t._v(" "),i("div",{staticClass:"add-item"},[t._v("+添加")])],2)],1)]:i("el-input",{model:{value:t.formData[e.field],callback:function(a){t.$set(t.formData,e.field,a)},expression:"formData[item.field]"}})],2)}))],1),t._v(" "),i("div",{staticClass:"btn-box"},[i("el-button",{attrs:{type:"primary"},on:{click:t.onSubmit}},[t._v("提交")]),t._v(" "),i("el-button",{on:{click:t.close}},[t._v("取消")])],1)])])},[],!1,null,"f4e6c0fa",null);r.options.__file="newDialog.vue";e.default=r.exports},PDKF:function(t,e,a){"use strict";var i=a("jVab");a.n(i).a},"R/I/":function(t,e,a){},bW7A:function(t,e,a){"use strict";a.r(e);var i=a("rerW"),n=a.n(i),s=a("iz3W"),o=a("NoVw"),l=a("d8QR"),r={components:{VDetails:s.a,newDialog:o.default},data:function(){return{activeName:"first",options:[{value:"1",label:"公示中"},{value:"2",label:"已结束"}],optionsValue:"1",listData:[],dialog:!1,titleList:{},showNewDialog:!1,loading:!0,pageNum:1,loadText:"加载更多",loadMoreLoading:!0,isPost:!0,newStatus:!1}},watch:{$route:function(t,e){this.$router.go(0)}},mounted:function(){this.noticeDataFun(1,this.pageNum);var t=this;document.getElementsByClassName("content")[0].onscroll=function(){var e=document.getElementsByClassName("content")[0];e.scrollTop+e.clientHeight==e.scrollHeight&&(t.loadMoreLoading=!0,t.isPost?(t.pageNum++,t.noticeDataFun(t.optionsValue,t.pageNum)):t.loadMoreLoading=!1)}},methods:{noticeDataFun:function(t,e){var a=this;Object(l.d)({type:t,page:e,limit:15}).then(function(t){1==t.data.is_create?a.newStatus=!0:a.newStatus=!1;var e=!0,i=!1,s=void 0;try{for(var o,l=n()(t.data.list);!(e=(o=l.next()).done);e=!0){var r=o.value;r.contentSub=r.content.substring(0,150)}}catch(t){i=!0,s=t}finally{try{!e&&l.return&&l.return()}finally{if(i)throw s}}a.listData=a.listData.concat(t.data.list),0==t.data.list.length||15!=t.data.list.length?(a.loadText="没有更多了",a.isPost=!1):(a.loadText="加载更多",a.isPost=!0),a.loading=!1,a.loadMoreLoading=!1}).catch(function(t){a.loading=!1,a.loadMoreLoading=!1})},rowFun:function(t){this.titleList=t,this.dialog=!0},close:function(){this.dialog=!1},deleteFun:function(){for(var t in this.listData)this.listData[t].announcement_id==this.titleList.announcement_id&&this.listData.splice(t,1);this.close()},newBtn:function(){this.showNewDialog=!0},newClose:function(){this.showNewDialog=!1},onSubmit:function(t){this.selectChange(this.optionsValue),this.newClose()},editSubmit:function(t){this.selectChange(this.optionsValue),this.close()},selectChange:function(t){this.loading=!0,this.listData=[],this.pageNum=1,this.noticeDataFun(t,this.pageNum)},loadMoreBtn:function(t){this.$set(t,"preShow",!0),this.$set(t,"loadMore",!0)}}},c=(a("naYT"),a("ZrdR")),d=Object(c.a)(r,function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"notice oa-bgcolor"},[t.newStatus?a("el-button",{staticClass:"new-btn",attrs:{type:"primary"},on:{click:t.newBtn}},[t._v("新建公告")]):t._e(),t._v(" "),a("el-tabs",{model:{value:t.activeName,callback:function(e){t.activeName=e},expression:"activeName"}},[a("el-tab-pane",{attrs:{label:"公告",name:"first"}},[a("div",{staticClass:"text-top"},[a("label",{staticClass:"text"},[t._v("公告状态")]),t._v(" "),a("el-select",{attrs:{placeholder:"请选择",size:"small"},on:{change:t.selectChange},model:{value:t.optionsValue,callback:function(e){t.optionsValue=e},expression:"optionsValue"}},t._l(t.options,function(t){return a("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})}))],1),t._v(" "),a("div",{staticClass:"content"},[a("div",{staticClass:"list-box"},[t._l(t.listData,function(e,i){return a("div",{key:i,staticClass:"list"},[a("div",{staticClass:"header"},[a("div",{directives:[{name:"photo",rawName:"v-photo",value:e,expression:"item"},{name:"lazy",rawName:"v-lazy:background-image",value:t.$options.filters.filterUserLazyImg(e.thumb_img),expression:"$options.filters.filterUserLazyImg(item.thumb_img)",arg:"background-image"}],key:e.thumb_img,staticClass:"div-photo"}),t._v(" "),a("div",{staticClass:"name-time"},[a("p",{staticClass:"name"},[t._v(t._s(e.realname))]),t._v(" "),a("p",{staticClass:"time"},[t._v(t._s(t._f("moment")(e.create_time,"YYYY-MM-DD HH:mm")))])])]),t._v(" "),a("div",{staticClass:"title",on:{click:function(a){t.rowFun(e)}}},[t._v(t._s(e.title))]),t._v(" "),e.preShow?a("div",{staticClass:"item-content"},[t._v(t._s(e.content))]):a("div",{staticClass:"item-content"},[t._v(t._s(e.contentSub))]),t._v(" "),e.contentSub.length<e.content.length?a("div",{staticClass:"load-more"},[e.loadMore?a("span",{on:{click:function(t){e.loadMore=!1,e.preShow=!1}}},[t._v("收起全文")]):a("span",{on:{click:function(a){t.loadMoreBtn(e)}}},[t._v("展开全文")])]):t._e()])}),t._v(" "),a("p",{staticClass:"load"},[a("el-button",{attrs:{type:"text",loading:t.loadMoreLoading}},[t._v(t._s(t.loadText))])],1)],2)])])],1),t._v(" "),t.dialog?a("v-details",{attrs:{titleList:t.titleList},on:{editSubmit:t.editSubmit,deleteFun:t.deleteFun,close:t.close}}):t._e(),t._v(" "),t.showNewDialog?a("new-dialog",{on:{onSubmit:t.onSubmit,close:t.newClose}}):t._e()],1)},[],!1,null,"6c7ce724",null);d.options.__file="index.vue";e.default=d.exports},d8QR:function(t,e,a){"use strict";a.d(e,"d",function(){return n}),a.d(e,"a",function(){return s}),a.d(e,"c",function(){return o}),a.d(e,"b",function(){return l});var i=a("t3Un");function n(t){return Object(i.a)({url:"oa/announcement/index",method:"post",data:t})}function s(t){return Object(i.a)({url:"oa/announcement/save",method:"post",data:t})}function o(t){return Object(i.a)({url:"oa/announcement/update",method:"post",data:t})}function l(t){return Object(i.a)({url:"oa/announcement/delete",method:"post",data:t})}},i3rx:function(t,e,a){"use strict";var i=a("JTIi");a.n(i).a},iz3W:function(t,e,a){"use strict";var i=a("6ZY3"),n=a.n(i),s=a("ViDN"),o={components:{CreateView:s.a},data:function(){return{formList:[{label:"公告标题",field:"title"},{label:"开始时间",field:"start_time",type:"date"},{label:"结束时间",field:"end_time",type:"date"},{label:"公告正文",field:"content",type:"textarea"}],rules:{title:[{required:!0,message:"公告标题不能为空",trigger:"blur"},{max:50,message:"公告标题长度最多为50个字符",trigger:"blur"}],content:[{required:!0,message:"公告正文不能为空",trigger:"blur"}]}}},props:{formData:Object,loading:Boolean},methods:{onSubmit:function(){this.$emit("editSubmit")},close:function(){this.$emit("editClose")},inputChange:function(){this.popoverVisible=!0}}},l=(a("i3rx"),a("ZrdR")),r=Object(l.a)(o,function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("create-view",{attrs:{"body-style":{height:"100%"}}},[i("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"details-box"},[i("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[i("span",{staticClass:"text"},[t._v("编辑公告")]),t._v(" "),i("img",{staticClass:"el-icon-close rt",attrs:{src:a("cjwK"),alt:""},on:{click:t.close}})]),t._v(" "),i("div",{staticClass:"content"},[i("el-form",{ref:"form",attrs:{model:t.formData,rules:t.rules}},t._l(t.formList,function(e,a){return i("el-form-item",{key:a,class:"el-form-item"+e.field,attrs:{label:e.label,prop:e.field}},["date"==e.type?[i("el-date-picker",{attrs:{type:"date",placeholder:"选择日期"},model:{value:t.formData[e.field],callback:function(a){t.$set(t.formData,e.field,a)},expression:"formData[item.field]"}})]:"textarea"==e.type?[i("el-input",{attrs:{type:"textarea",autosize:"",placeholder:"请输入内容"},model:{value:t.formData[e.field],callback:function(a){t.$set(t.formData,e.field,a)},expression:"formData[item.field]"}})]:i("el-input",{model:{value:t.formData[e.field],callback:function(a){t.$set(t.formData,e.field,a)},expression:"formData[item.field]"}})],2)}))],1),t._v(" "),i("div",{staticClass:"btn-box"},[i("el-button",{attrs:{type:"primary"},on:{click:t.onSubmit}},[t._v("提交")]),t._v(" "),i("el-button",{on:{click:t.close}},[t._v("取消")])],1)])])},[],!1,null,"fc5d422a",null);r.options.__file="edit.vue";var c=r.exports,d=a("d8QR"),u={components:{CreateView:s.a,VEdit:c},data:function(){return{showEdit:!1,formData:{},loading:!1}},props:{titleList:Object,btnShow:{type:Boolean,default:!0}},methods:{onEdit:function(){this.showEdit=!0,this.formData=n()({},this.titleList);this.formData.start_time=1e3*this.titleList.start_time,this.formData.end_time=1e3*this.titleList.end_time},close:function(){this.$emit("close")},deleteFun:function(){var t=this;this.$confirm("确定删除?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){Object(d.b)({announcement_id:t.titleList.announcement_id}).then(function(e){t.$emit("deleteFun"),t.$message({type:"success",message:"删除成功!"})})}).catch(function(){t.$message({type:"info",message:"已取消删除"})})},editClose:function(){this.showEdit=!1},editSubmit:function(){var t=this;this.loading=!0,Object(d.c)({announcement_id:this.formData.announcement_id,title:this.formData.title,content:this.formData.content,start_time:new Date(this.formData.start_time).getTime()/1e3,end_time:new Date(this.formData.end_time).getTime()/1e3}).then(function(e){t.$emit("editSubmit",t.formData),t.editClose(),t.$message.success("公告编辑成功"),t.loading=!1}).catch(function(e){t.loading=!1,t.$message.error("公告编辑失败")})}}},m=(a("PDKF"),Object(l.a)(u,function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("create-view",{attrs:{"body-style":{height:"100%"}}},[a("div",{staticClass:"details-box"},[a("div",{staticClass:"header",attrs:{slot:"header"},slot:"header"},[a("span",{staticClass:"text"},[t._v("公告详情")]),t._v(" "),a("span",{staticClass:"el-icon-close rt",on:{click:t.close}})]),t._v(" "),a("div",{staticClass:"content"},[a("div",{staticClass:"title"},[t._v(t._s(t.titleList.title))]),t._v(" "),a("div",{staticClass:"time"},[t._v(t._s(t._f("moment")(t.titleList.create_time,"YYYY-MM-DD HH:mm")))]),t._v(" "),a("div",{staticClass:"text"},[t._v(t._s(t.titleList.content))])]),t._v(" "),t.btnShow?a("div",{staticClass:"btn-box"},[1==t.titleList.permission.is_update?a("el-button",{attrs:{type:"primary"},on:{click:t.onEdit}},[t._v("编辑")]):t._e(),t._v(" "),1==t.titleList.permission.is_delete?a("el-button",{attrs:{type:"danger"},on:{click:t.deleteFun}},[t._v("删除")]):t._e()],1):t._e()]),t._v(" "),t.showEdit?a("v-edit",{attrs:{formData:t.formData,loading:t.loading},on:{editSubmit:t.editSubmit,editClose:t.editClose}}):t._e()],1)},[],!1,null,"9141e250",null));m.options.__file="details.vue";e.a=m.exports},jVab:function(t,e,a){},lrnB:function(t,e,a){"use strict";var i=a("R/I/");a.n(i).a},naYT:function(t,e,a){"use strict";var i=a("ApRr");a.n(i).a}}]);