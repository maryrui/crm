<template>
    <el-dialog v-loading="loading"
               :title="title"
               width="400px"
               :append-to-body="true"
               @close="hiddenView"
               :visible.sync="showDialog">
        <!--<p v-if="itemData.order_id== 2 && status  == 1" style="float:right">处理时长：{{itemData.update_time - itemData.create_time |filterTimestampToFormatTime('MM-DD dddd')}}</p>-->
        <div v-if="itemData.order_id == 1">
            <div style="margin-bottom:15px;"><treeselect v-model="departmentVal" :disable-branch-nodes="true" :multiple="true" :options="options" placeholder="选择部门责任负责人(必填)"/></div>
            <div style="margin-bottom:15px;"><treeselect v-model="visitorVal" :disable-branch-nodes="true" :multiple="true" :options="options" placeholder="选择客户回访负责人(必填)"/></div>
            <div style="margin-bottom:15px;">
                <el-select v-model="type">
                    <el-option v-for="(item, index) in typeList" :label="item.type" :value="item.type" :key="index"></el-option>
            </el-select>
            </div>
        </div>
        <div style="margin-bottom:15px;" v-if="itemData.order_id == 3">
            <el-rate
                    v-model="rate"
                    :texts="['非常不满意','不满意','一般','满意','非常满意']"
                    show-text>
            </el-rate>
        </div>
        <div v-if="status == 1 && detail.config == 1"
             class="title"
            style="margin-top:15px"
        >意见</div>
        <el-input v-model="content"
                  type="textarea"
                  resize="none"
                  :rows="5"
                  :placeholder="placeholder"></el-input>
        <div slot="footer"
             class="dialog-footer">
            <el-button @click="handleClick('cancel')">取 消</el-button>
            <el-button type="primary"
                       @click="handleClick('confirm')">确 定</el-button>
        </div>
    </el-dialog>
</template>
<script type="text/javascript">
    import Treeselect from '@riophae/vue-treeselect'
    // import the styles
    import '@riophae/vue-treeselect/dist/vue-treeselect.css'
    import {
        crmContractCheck,
        crmContractRevokeCheck
    } from '@/api/customermanagement/contract'
    import {
        crmReceivablesCheck,
        crmReceivablesRevokeCheck
    } from '@/api/customermanagement/money'
    import {
        oaExamineCheck,
        oaExamineRevokeCheck
    } from '@/api/oamanagement/examine'

    import { getComplaintUserTree, checkComplaintOne, getComplaintType } from '@/api/oamanagement/complaint'
    import { XhUserCell } from '@/components/CreateCom'

    export default {
        name: 'examine-handle', // 订单审核操作
        components: {
            XhUserCell,
            Treeselect
        },
        computed: {
            title() {
                if (this.status == 1) {
                    return '审批通过'
                } else if (this.status == 0) {
                    return '审批拒绝'
                } else if (this.status == 2) {
                    return '撤回审批'
                }
                return ''
            },
            placeholder() {
                // 1通过0拒绝2撤回
                if (this.status == 1) {
                    return '请输入审批意见（选填）'
                } else if (this.status == 0) {
                    return '请输入审批意见（必填）'
                } else if (this.status == 2) {
                    return '请输入撤回理由（必填）'
                }
                return ''
            }
        },
        watch: {
            show: {
                handler(val) {
                    this.showDialog = val
                },
                deep: true,
                immediate: true
            }
        },
        created() {
            getComplaintUserTree().then( res => {
                this.options = this.handleData(res.data)
            })
            this.fetchData()
        },
        data() {
            return {
                loading: false,
                departmentVal: [],
                type: "",
                rate: '',
                visitorVal: [],
                typeList: [],
                options: [],
                showDialog: false,
                handleType: 1,
                selectUsers: [],
                content: '' // 输入内容
            }
        },
        props: {
            show: {
                type: Boolean,
                default: false
            },
            /** 操作类型  1通过0拒绝2撤回*/
            status: {
                type: [String, Number],
                default: 1
            },
            // 详情信息id
            id: [String, Number],
            // 审核信息 config 1 固定 0 自选
            detail: {
                type: Object,
                default: () => {
                    return {}
                }
            },
            itemData: {
                type: Object,
                default: () => {
                    return {}
                }
            },
            // crm_contract crm_receivables oa_examine
            examineType: {
                type: String,
                default: ''
            }
        },
        mounted() {
            console.log(this.itemData)

        },
        methods: {
            fetchData() {
                getComplaintType().then(res => {
                    this.typeList = res.data
                })
            },
            submitInfo() {
                if ((this.status == 0 || this.status == 2) && !this.content) {
                    this.$message.error(this.placeholder)
                } else {
                    if (this.status == 0) {
                        // 1通过0拒绝2撤回
                        this.handlePassAndReject()
                    } else if (this.status == 1) {
                        this.handlePassAndReject()
                    } else if (this.status == 2) {
                        this.handleRevoke()
                    }
                }
            },
            // 撤回操作
            handleRevoke() {
                this.loading = true
                this.getExamineRevokeRequest()({
                    id: this.id,
                    content: this.content
                })
                    .then(res => {
                        this.loading = false
                        this.$message.success(res.data)
                        this.$emit('save')
                        this.hiddenView()
                    })
                    .catch(() => {
                        this.loading = false
                    })
            },
            getExamineRevokeRequest() {
                if (this.examineType == 'crm_contract') {
                    return crmContractRevokeCheck
                } else if (this.examineType == 'crm_receivables') {
                    return crmReceivablesRevokeCheck
                } else if (this.examineType == 'oa_examine') {
                    return oaExamineRevokeCheck
                }
            },
            // 通过 拒绝操作
            handlePassAndReject() {
                this.loading = true
                var params = {
                    id: this.id,
                    status: this.status,
                    content: this.content,
                    flow_id: this.itemData.flow_id,
                    order_id: this.itemData.order_id
                }
                if (this.status == 1 && this.detail.config == 1 && this.itemData.order_id == 1) {
                    if (!this.departmentVal || !this.visitorVal || !this.type) {
                        this.$message({
                            type: 'error',
                            message: "客户负责人、部门负责人、客诉类型不能为空！",
                            showClose: true
                        })
                        return false
                    }
                    params['departmentVal'] = this.departmentVal
                    params['visitorVal'] = this.visitorVal
                    params['type'] = this.type
                    if (this.handleType == 1) {
                        params['is_end'] = 1
                    } else {
                        params['check_user_id'] = this.selectUsers[0].id
                    }
                }
                if (this.status == 1 && this.detail.config == 1 && this.itemData.order_id == 3) {
                    params['rate'] = this.rate
                }
                this.getExamineRequest()(params)
                    .then(res => {
                        this.loading = false
                        this.$message.success(res.data)
                        this.$emit('save', { type: this.status })
                        this.hiddenView()
                    })
                    .catch(() => {
                        this.loading = false
                    })
            },
            getExamineRequest() {
                if (this.examineType == 'crm_contract') {
                    return crmContractCheck
                } else if (this.examineType == 'crm_receivables') {
                    return crmReceivablesCheck
                } else if (this.examineType == 'oa_examine') {
                    return oaExamineCheck
                } else if (this.examineType == 'crm_complaint') {
                    return checkComplaintOne
                }
            },
            handleClick(type) {
                if (type == 'cancel') {
                    this.hiddenView()
                } else if (type == 'confirm') {
                    this.submitInfo()
                }
            },
            /** 选择了下一审批人 */
            selectUserFocus() {
                this.handleType = 2
            },
            selectExamineUser(data) {
                this.selectUsers = data.value
            },
            hiddenView() {
                this.$emit('close')
            },
            handleData(arr){
                if(arr.length < 1){
                    return arr
                }
                arr.splice(0, 1)
                var params = []
                for (var i = 0; i < arr.length; i++) {
                    params.push({
                        id: arr[i].id + 'crm',
                        label: arr[i].name,
                        children: []
                    })
                    if (arr[i].users.length > 0) {
                        for (var j = 0; j < arr[i].users.length; j++) {
                            params[i].children.push({
                                id: arr[i].users[j].id,
                                label: arr[i].users[j].realname
                            })
                        }
                    }
                }
                return params
            }
        }
    }
</script>
<style lang="scss" scoped>
    .handle-type {
        padding-bottom: 8px;
        .handle-item {
            padding: 8px 0;
            cursor: pointer;
        }
    }

    .el-dialog__wrapper /deep/ .el-dialog__body {
        padding: 10px 25px 20px;
    }

    .el-radio-group /deep/ .el-radio + .el-radio {
        margin-left: 0px;
    }

    .select-user {
        flex: 1;
    }

    .title {
        font-size: 12px;
        padding-bottom: 8px;
        color: #666;
    }
</style>
