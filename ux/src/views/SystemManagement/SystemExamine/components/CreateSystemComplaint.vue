<template>
    <create-view :loading="loading"
                 :body-style="{ height: '100%'}">
        <flexbox direction="column"
                 align="stretch"
                 class="crm-create-container">
            <flexbox class="crm-create-header">
                <div style="flex:1;font-size:17px;color:#333;">客诉类型</div>
                <img @click="hidenView"
                     class="close"
                     src="@/assets/img/task_close.png" />
            </flexbox>
            <flexbox class="crm-create-flex"
                     direction="column"
                     align="stretch">
                <div
                     class="crm-create-body">
                    <div class="create-name">基本信息</div>
                        <el-form :model="dynamicValidateForm" ref="crmComplaintForm"
                             label-position="top"
                             class="crm-create-box">
                            <template v-for="(domain, index) in dynamicValidateForm.domains">
                                <el-form-item
                                        label="类型名称"
                                        :key="domain.key"
                                        :prop="'domains.' + index + '.type'"
                                        class="crm-create-item"
                                        :rules="{
                                        required: true, message: '类型不能为空', trigger: 'blur'
                                }">
                                  <div style="position:relative;">
                                      <el-input v-model="domain.type"></el-input> <el-button @click.prevent="removeDomain(index)" icon="el-icon-delete" type="danger" circle style="position:absolute;top:3px;right:-60px"></el-button>
                                  </div>

                                </el-form-item>

                              <!--  <el-form-item
                                        label="关联对象"
                                        :key="index"
                                        :prop="'domains.' + index + '.depart'"
                                        style="margin-left:15px;width:45%"
                                        :rules="{
                                        required: true, message: '类型不能为空', trigger: 'blur'

                                }">
                                    <el-select v-model="domain.depart" multiple placeholder="请选择" style="width:85%">
                                        <el-option v-for="(item,index) in team" :label="item.name" :value="item.id+''" :key="item.id"></el-option>
                                    </el-select>

                                </el-form-item>-->
                            </template>

                    </el-form>
                    <div class="examine-items-add"><span @click="complaintItemsAdd">+ 添加客诉类型</span></div>
                </div>

            </flexbox>
            <div
                 class="handle-bar">
                <el-button class="handle-button"
                           @click.native="hidenView">取消</el-button>
                <el-button class="handle-button"
                           type="primary"
                           @click.native="saveField">保存</el-button>
            </div>
        </flexbox>
    </create-view>
</template>
<script type="text/javascript">
    import CreateView from '@/components/CreateView'
    import { getComplaintList, saveComplaintType } from '@/api/systemManagement/Systemcomplaint'
    import { depList } from '@/api/common'
    export default {
        name: 'create-system-complaint', // 所有新建效果的view
        components: {
            CreateView
        },
        data() {
            return {
                // 标题展示名称
                loading: false,
                team: [],
                dataList: [],
                dynamicValidateForm: {
                    domains: [{
                        type: ""
                        // depart:[]
                    }]
                }
            }
        },
        created() {
            // this.getTemList()
            this.fetchData()
        },
        methods: {
            fetchData() {
                getComplaintList().then(res => {
                    let arr = []
                    for (var i = 0; i < res.data.length; i++) {
                        arr.push({
                            type: res.data[i].type
                            // depart: res.data[i].depart.replace(/\,/g, ' ').trim().split(' ')
                        })
                    }
                    this.dynamicValidateForm.domains = arr
                })
            },
            //获取关联对象
            getTemList() {
                depList().then(res => {
                    if (res.code == '200') {
                        this.team = res.data
                    }
                })
            },
            // 保存数据
            saveField() {
                this.$refs.crmComplaintForm.validate(valid => {
                    if (valid) {
                        this.submiteParams(this.dynamicValidateForm.domains)
                    } else {
                        this.$message.error('请完善必填信息')
                        return false
                    }
                })
            },
            /** 上传 */
            submiteParams(array) {
                this.loading = true
                saveComplaintType(array)
                    .then(res => {
                        this.loading = false
                        this.hidenView()
                        this.$message.success(res.data)
                        // 回到保存成功
                        this.$emit('save')
                    })
                    .catch(() => {
                        this.loading = false
                    })
            },
            hidenView() {
                this.$emit('hiden-view')
            },
            removeDomain(index) {
                if (this.dynamicValidateForm.domains.length > 1) {
                    this.dynamicValidateForm.domains.splice(index, 1)
                }
            },
            complaintItemsAdd() {
                this.dynamicValidateForm.domains.push({
                    type: ""
                    // depart: ""
                })
            }
        },
        destroyed() {
            // remove DOM node after destroy
            if (this.$el && this.$el.parentNode) {
                this.$el.parentNode.removeChild(this.$el)
            }
        }
    }
</script>
<style lang="scss" scoped>
    .crm-create-container {
        position: relative;
        height: 100%;
    }

    .crm-create-flex {
        position: relative;
        overflow-x: hidden;
        overflow-y: auto;
        flex: 1;
    }

    .crm-create-header {
        height: 40px;
        margin-bottom: 20px;
        padding: 0 10px;
        flex-shrink: 0;
        .close {
            display: block;
            width: 40px;
            height: 40px;
            margin-right: -10px;
            padding: 10px;
        }
    }
    .create-name {
        font-size: 12px;
        padding: 0 10px;
        margin-left: 15px;
        margin-bottom: 10px;
        color: #333333;
        border-left: 2px solid #46cdcf;
    }

    .crm-create-body {
        flex: 1;
        overflow-x: hidden;
        overflow-y: auto;
    }

    /** 将其改变为flex布局 */
    .crm-create-box {
        /*display: flex;*/
        flex-wrap: wrap;
        padding: 0 20px;
    }

    .crm-create-item {
        flex: 0 0 50%;
        width:60%;
        flex-shrink: 0;
        padding-bottom: 10px;
    }

    // 占用一整行
    .crm-create-block-item {
        flex: 0 0 100%;
        flex-shrink: 0;
        padding-bottom: 10px;
    }

    .el-form-item /deep/ .el-form-item__label {
        line-height: normal;
        font-size: 13px;
        color: #333333;
        position: relative;
        padding-left: 8px;
        padding-bottom: 0;
    }

    .el-form /deep/ .el-form-item {
        margin-bottom: 0px;
    }

    .el-form /deep/ .el-form-item.is-required .el-form-item__label:before {
        content: '*';
        color: #f56c6c;
        margin-right: 4px;
        position: absolute;
        left: 0;
        top: 5px;
    }

    .examine-items {
        padding: 10px 0;
    }

    .examine-item {
        padding-bottom: 8px;
        .examine-item-name {
            width: 60px;
            padding-left: 20px;
            font-size: 13px;
            margin-right: 20px;
        }
        .examine-item-select {
            margin-right: 20px;
        }
        .examine-item-user {
            flex: 1;
            margin-right: 42px;
        }
        .examine-item-delete {
            color: #ff6767;
            font-size: 22px;
            margin: 0 10px;
            display: none;
        }
    }

    .examine-item:hover {
        .examine-item-delete {
            display: block;
        }
        .examine-item-user {
            margin-right: 0px;
        }
    }

    .examine-items-add {
        margin-top:30px;
        padding: 5px 0 20px 0;
        font-size: 13px;
        color: $xr-color-primary;
    }

    .examine-add-des {
        font-size: 12px;
        background-color: #fffcf0;
        padding: 10px;
        line-height: 23px;
        color: #999;
        margin-bottom: 10px;
        .examine-add-required {
            color: #ff6767;
        }
    }
    .handle-bar {
        position: relative;
        .handle-button {
            float: right;
            margin-top: 5px;
            margin-right: 20px;
        }
    }
</style>
