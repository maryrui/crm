(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-0b74"],{"08yZ":function(t,e,s){"use strict";s.r(e);var a=s("oj9+"),i=s("MT78"),n=s.n(i),o=s("oU2a"),l={name:"customer-record-statistics",data:function(){return{loading:!1,axisOption:null,axisChart:null,list:[],postParams:{},axisList:[],fieldList:[{field:"realname",name:"员工姓名"},{field:"record_num",name:"跟进次数"},{field:"customer_num",name:"跟进客户数"}]}},mixins:[a.a],computed:{},mounted:function(){this.initAxis()},methods:{searchClick:function(t){this.postParams=t,this.getDataList(),this.getRecordList()},getDataList:function(){var t=this;this.loading=!0,Object(o.i)(this.postParams).then(function(e){t.loading=!1,t.axisList=e.data||[];for(var s=[],a=[],i=[],n=0;n<e.data.length;n++){var o=e.data[n];s.push(o.customerCount),a.push(o.dataCount),i.push(o.type)}t.axisOption.xAxis[0].data=i,t.axisOption.series[0].data=s,t.axisOption.series[1].data=a,t.axisChart.setOption(t.axisOption,!0)}).catch(function(){t.loading=!1})},getRecordList:function(t){var e=this;this.list=[];var s={};if(void 0!==t){var a=this.axisList[t];s.user_id=this.postParams.user_id,s.structure_id=this.postParams.structure_id,s.start_time=a.start_time,s.end_time=a.end_time}else s=this.postParams;this.loading=!0,Object(o.g)(s).then(function(t){e.loading=!1,e.list=t.data}).catch(function(){e.loading=!1})},initAxis:function(){var t=this,e=n.a.init(document.getElementById("axismain")),s={color:["#6ca2ff","#ff7474"],tooltip:{trigger:"axis",axisPointer:{type:"shadow"}},legend:{data:["跟进客户数","跟进次数"],bottom:"0px",itemWidth:14},grid:{top:"40px",left:"30px",right:"30px",bottom:"40px",containLabel:!0,borderColor:"#fff"},xAxis:[{type:"category",data:[],axisTick:{alignWithLabel:!0,lineStyle:{width:0}},axisLabel:{color:"#BDBDBD"},axisLine:{lineStyle:{color:"#BDBDBD"}},splitLine:{show:!1}}],yAxis:[{type:"value",name:"跟进客户数",axisTick:{alignWithLabel:!0,lineStyle:{width:0}},axisLabel:{color:"#BDBDBD",formatter:"{value}个"},axisLine:{lineStyle:{color:"#BDBDBD"}},splitLine:{show:!1}},{type:"value",name:"跟进次数",axisTick:{alignWithLabel:!0,lineStyle:{width:0}},axisLabel:{color:"#BDBDBD",formatter:"{value}次"},axisLine:{lineStyle:{color:"#BDBDBD"}},splitLine:{show:!1}}],series:[{name:"跟进客户数",type:"bar",yAxisIndex:0,barWidth:15,data:[]},{name:"跟进次数",type:"bar",yAxisIndex:1,barWidth:15,data:[]}]};e.setOption(s,!0),e.on("click",function(e){t.getRecordList(e.dataIndex)}),this.axisOption=s,this.axisChart=e}}},u=(s("E3sT"),s("KHd+")),r=Object(u.a)(l,function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticClass:"main-container"},[s("filtrate-handle-view",{staticClass:"filtrate-bar",attrs:{moduleType:"customer"},on:{load:function(e){t.loading=!0},change:t.searchClick}}),t._v(" "),s("div",{staticClass:"content"},[t._m(0),t._v(" "),s("div",{staticClass:"table-content"},[s("el-table",{attrs:{data:t.list,height:"400",stripe:"",border:"","highlight-current-row":""}},t._l(t.fieldList,function(t,e){return s("el-table-column",{key:e,attrs:{align:"center","header-align":"center","show-overflow-tooltip":"",prop:t.field,label:t.name}})}))],1)])],1)},[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"axis-content"},[e("div",{attrs:{id:"axismain"}})])}],!1,null,"5abe5c81",null);r.options.__file="CustomerRecordStatistics.vue";e.default=r.exports},C2qb:function(t,e,s){"use strict";var a=s("zgx2");s.n(a).a},E3sT:function(t,e,s){"use strict";var a=s("ie2/");s.n(a).a},O1t1:function(t,e,s){"use strict";var a=s("nkdU");s.n(a).a},"gXW+":function(t,e,s){"use strict";var a=s("KTTK"),i=s("UcQx"),n=s("conU"),o=s("uKQN"),l=s("wd/R"),u=s.n(l),r={name:"filtrate-handle-view",components:{timeTypeSelect:o.a},watch:{},data:function(){return{pickerOptions:{disabledDate:function(t){return t.getTime()>Date.now()}},yearValue:"",timeTypeValue:{},structuresProps:{children:"children",label:"label",value:"id"},deptList:[],structuresSelectValue:"",userOptions:[],userSelectValue:"",businessOptions:[],businessStatusValue:"",productValue:[],productOptions:[],customValue:""}},props:{moduleType:{required:!0,type:String},showYearSelect:{default:!1,type:Boolean},showBusinessSelect:{default:!1,type:Boolean},showUserSelect:{default:!0,type:Boolean},showCustomSelect:{default:!1,type:Boolean},customDefault:"",customOptions:{default:function(){return[]},type:Array},showProductSelect:{default:!1,type:Boolean}},mounted:function(){var t=this;this.showCustomSelect&&(this.customValue=this.customDefault),this.showYearSelect&&(this.yearValue=u()(new Date).year().toString()),this.$emit("load"),this.getDeptList(function(){t.showBusinessSelect?t.getBusinessStatusList(function(){t.postFiltrateValue()}):t.postFiltrateValue()}),this.showProductSelect&&this.getProductCategoryIndex()},methods:{customSelectChange:function(){this.$emit("typeChange",this.customValue)},timeTypeChange:function(t){this.timeTypeValue=t},getDeptList:function(t){var e=this;Object(a.b)({m:"bi",c:this.moduleType,a:"read"}).then(function(s){e.deptList=s.data,s.data.length>0?(e.structuresSelectValue=s.data[0].id,e.showUserSelect&&e.getUserList()):e.structuresSelectValue="",t(!0)}).catch(function(){e.$emit("error")})},structuresValueChange:function(t){this.showUserSelect&&(this.userSelectValue="",this.userOptions=[],this.getUserList())},getUserList:function(){var t=this,e={};e.structure_id=this.structuresSelectValue,Object(a.j)(e).then(function(e){t.userOptions=e.data}).catch(function(){t.$emit("error")})},getBusinessStatusList:function(t){var e=this;Object(i.g)().then(function(s){e.businessOptions=s.data,s.data.length>0&&(e.businessStatusValue=s.data[0].type_id),t(!0)}).catch(function(){e.$emit("error")})},getProductCategoryIndex:function(){var t=this;Object(n.o)({type:"tree"}).then(function(e){t.productOptions=e.data}).catch(function(){})},postFiltrateValue:function(){var t={structure_id:this.structuresSelectValue};this.showUserSelect&&(t.user_id=this.userSelectValue),this.showBusinessSelect&&(t.type_id=this.businessStatusValue),this.showProductSelect&&(t.category_id=this.productValue.length>0?this.productValue[this.productValue.length-1]:""),this.showYearSelect?t.year=this.yearValue:"custom"==this.timeTypeValue.type?(t.start_time=this.timeTypeValue.startTime,t.end_time=this.timeTypeValue.endTime):t.type=this.timeTypeValue.value,this.$emit("change",t)}},beforeDestroy:function(){}},c=(s("C2qb"),s("KHd+")),d=Object(c.a)(r,function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"filtrate-content"},[t.showYearSelect?t._e():s("time-type-select",{on:{change:t.timeTypeChange}}),t._v(" "),t.showYearSelect?s("el-date-picker",{attrs:{type:"year",clearable:!1,"value-format":"yyyy","picker-options":t.pickerOptions,placeholder:"选择年"},model:{value:t.yearValue,callback:function(e){t.yearValue=e},expression:"yearValue"}}):t._e(),t._v(" "),s("el-select",{attrs:{placeholder:"选择部门"},on:{change:t.structuresValueChange},model:{value:t.structuresSelectValue,callback:function(e){t.structuresSelectValue=e},expression:"structuresSelectValue"}},t._l(t.deptList,function(t){return s("el-option",{key:t.id,attrs:{label:t.name,value:t.id}})})),t._v(" "),t.showUserSelect?s("el-select",{attrs:{clearable:!0,placeholder:"选择员工"},model:{value:t.userSelectValue,callback:function(e){t.userSelectValue=e},expression:"userSelectValue"}},t._l(t.userOptions,function(t){return s("el-option",{key:t.id,attrs:{label:t.realname,value:t.id}})})):t._e(),t._v(" "),t.showBusinessSelect?s("el-select",{attrs:{placeholder:"商机组"},model:{value:t.businessStatusValue,callback:function(e){t.businessStatusValue=e},expression:"businessStatusValue"}},t._l(t.businessOptions,function(t){return s("el-option",{key:t.type_id,attrs:{label:t.name,value:t.type_id}})})):t._e(),t._v(" "),t.showProductSelect?s("el-cascader",{staticStyle:{width:"100%"},attrs:{options:t.productOptions,"change-on-select":"","show-all-levels":!1,props:{children:"children",label:"label",value:"category_id"}},model:{value:t.productValue,callback:function(e){t.productValue=e},expression:"productValue"}}):t._e(),t._v(" "),t.showCustomSelect?s("el-select",{attrs:{placeholder:"图标类型"},on:{change:t.customSelectChange},model:{value:t.customValue,callback:function(e){t.customValue=e},expression:"customValue"}},t._l(t.customOptions,function(t){return s("el-option",{key:t.value,attrs:{label:t.name,value:t.value}})})):t._e(),t._v(" "),s("el-button",{attrs:{type:"primary"},nativeOn:{click:function(e){t.postFiltrateValue()}}},[t._v("搜索")])],1)},[],!1,null,"6c3275f8",null);d.options.__file="filtrateHandleView.vue";e.a=d.exports},"ie2/":function(t,e,s){},nkdU:function(t,e,s){},oU2a:function(t,e,s){"use strict";s.d(e,"j",function(){return i}),s.d(e,"k",function(){return n}),s.d(e,"i",function(){return o}),s.d(e,"g",function(){return l}),s.d(e,"h",function(){return u}),s.d(e,"c",function(){return r}),s.d(e,"b",function(){return c}),s.d(e,"d",function(){return d}),s.d(e,"e",function(){return p}),s.d(e,"l",function(){return h}),s.d(e,"a",function(){return m}),s.d(e,"f",function(){return f});var a=s("t3Un");function i(t){return Object(a.a)({url:"bi/customer/total",method:"post",data:t})}function n(t){return Object(a.a)({url:"bi/customer/statistics",method:"post",data:t})}function o(t){return Object(a.a)({url:"bi/customer/recordTimes",method:"post",data:t})}function l(t){return Object(a.a)({url:"bi/customer/recordList",method:"post",data:t})}function u(t){return Object(a.a)({url:"bi/customer/recordMode",method:"post",data:t})}function r(t){return Object(a.a)({url:"bi/customer/conversionInfo",method:"post",data:t})}function c(t){return Object(a.a)({url:"bi/customer/conversion",method:"post",data:t})}function d(t){return Object(a.a)({url:"bi/customer/pool",method:"post",data:t})}function p(t){return Object(a.a)({url:"bi/customer/poolList",method:"post",data:t})}function h(t){return Object(a.a)({url:"bi/customer/userCycle",method:"post",data:t})}function m(t){return Object(a.a)({url:"bi/customer/addressCycle",method:"post",data:t})}function f(t){return Object(a.a)({url:"bi/customer/productCycle",method:"post",data:t})}},"oj9+":function(t,e,s){"use strict";var a=s("gXW+");e.a={data:function(){return{chartColors:["#6CA2FF","#6AC9D7","#72DCA2","#48E78D","#FECD51","#DBB375","#FF7474","#F59561","#A3AEBC","#4C84FF","#0DBEB4","#00DEDE","#FFAA00","#C7C116","#F7A57C","#F661AC","#8652EE"]}},components:{filtrateHandleView:a.a},props:{},computed:{},watch:{},mounted:function(){},methods:{},deactivated:function(){}}},uKQN:function(t,e,s){"use strict";var a={name:"time-type-select",computed:{iconClass:function(){return this.showTypePopover?"arrow-up":"arrow-down"},typeShowValue:function(){return this.sureCustomContent?this.startTime||this.endTime?(this.startTime||"")+"-"+(this.endTime||""):"":this.selectType.label}},data:function(){return{selectType:{label:"本年",value:"year"},showTypePopover:!1,showCustomContent:!1,sureCustomContent:!1,startTime:"",endTime:"",typeOptions:[{label:"今天",value:"today"},{label:"昨天",value:"yesterday"},{label:"本周",value:"week"},{label:"上周",value:"lastWeek"},{label:"本月",value:"month"},{label:"上月",value:"lastMonth"},{label:"本季度",value:"quarter"},{label:"上季度",value:"lastQuarter"},{label:"本年",value:"year"},{label:"去年",value:"lastYear"}]}},props:{defaultType:Object},mounted:function(){this.defaultType?this.selectType=this.defaultType:this.$emit("change",{type:"default",value:this.selectType.value})},methods:{typeSelectClick:function(t){this.showTypePopover=!1,this.sureCustomContent=!1,this.showCustomContent=!1,this.selectType=t,this.$emit("change",{type:"default",value:this.selectType.value})},customSureClick:function(){this.startTime&&this.endTime&&(this.sureCustomContent=!0,this.showTypePopover=!1,this.$emit("change",{type:"custom",startTime:this.startTime,endTime:this.endTime}))}}},i=(s("O1t1"),s("KHd+")),n=Object(i.a)(a,function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("el-popover",{attrs:{placement:"bottom",width:"200","popper-class":"no-padding-popover",trigger:"click"},model:{value:t.showTypePopover,callback:function(e){t.showTypePopover=e},expression:"showTypePopover"}},[s("div",{staticClass:"type-popper"},[s("div",{staticClass:"type-content"},[t._l(t.typeOptions,function(e,a){return s("div",{key:a,staticClass:"type-content-item",class:{selected:t.selectType.value==e.value&&!t.showCustomContent},on:{click:function(s){t.typeSelectClick(e)}}},[s("div",{staticClass:"mark"}),t._v(t._s(e.label)+"\n      ")])}),t._v(" "),s("div",{staticClass:"type-content-item",class:{selected:t.showCustomContent},on:{click:function(e){t.showCustomContent=!0}}},[s("div",{staticClass:"mark"}),t._v("自定义\n      ")])],2),t._v(" "),t.showCustomContent?s("div",{staticClass:"type-content-custom"},[s("el-date-picker",{attrs:{type:"date","value-format":"yyyy-MM-dd",placeholder:"选择日期"},model:{value:t.startTime,callback:function(e){t.startTime=e},expression:"startTime"}}),t._v(" "),s("el-date-picker",{attrs:{type:"date","value-format":"yyyy-MM-dd",placeholder:"选择日期"},model:{value:t.endTime,callback:function(e){t.endTime=e},expression:"endTime"}}),t._v(" "),s("el-button",{on:{click:t.customSureClick}},[t._v("确定")])],1):t._e()]),t._v(" "),s("el-input",{staticClass:"type-select",attrs:{slot:"reference",placeholder:"请选择选择",readonly:!0},slot:"reference",model:{value:t.typeShowValue,callback:function(e){t.typeShowValue=e},expression:"typeShowValue"}},[s("i",{class:["el-input__icon","el-icon-"+t.iconClass],attrs:{slot:"suffix"},slot:"suffix"})])],1)},[],!1,null,"043e8b5b",null);n.options.__file="index.vue";e.a=n.exports},zgx2:function(t,e,s){}}]);