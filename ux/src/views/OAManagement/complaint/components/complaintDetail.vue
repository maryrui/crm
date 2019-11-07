<template>
    <div>
        <slide-view
            class="d-view"
            v-loading="loading"
            :body-style="{padding: '10px 30px', height: '100%'}"
            :listenerIDs="['crm-main-container','complaint-main-container']"
            :noListenerClass="noListenerClass"
            @side-close="hideView"
        >
            <flexbox orient="vertical"
                     style="height: 100%;">
                <flexbox class="detail-header">
                    <div class="header-name"><!--{{category_name}}-->客户投诉</div>
                    <el-button type="primary" size="mini" @click="handleClick('editor')" class="head-handle-button">编辑</el-button>
                    <img @click="hideView"
                         class="header-close"
                         src="@/assets/img/task_close.png" />
                </flexbox>
                <div class="detail-body">
                    <!--基本信息-->
                    <flexbox :gutter="0"
                             align="stretch"
                             wrap="wrap">
                     <!--   <flexbox-item :span="0.5"
                                      class="b-cell">
                            <flexbox
                                    align="stretch"
                                    class="b-cell-b">
                                <div class="b-cell-name">客诉类型</div>
                                <div class="b-cell-value">
                                    <flexbox :gutter="0"
                                             wrap="wrap"
                                             style="padding: 0px 10px 10px 0px;">
                                        <div>
                                            {{dataDetail.type}}&nbsp;
                                        </div>
                                    </flexbox>
                                </div>
                            </flexbox>
                        </flexbox-item>-->
                        <flexbox-item :span="0.5"
                                      class="b-cell">
                            <flexbox
                                    align="stretch"
                                    class="b-cell-b">
                                <div class="b-cell-name">客户名称</div>
                                <div class="b-cell-value">
                                    <flexbox :gutter="0"
                                             wrap="wrap"
                                             style="padding: 0px 10px 10px 0px;">
                                        <div>
                                            {{dataDetail.company}}&nbsp;
                                        </div>
                                    </flexbox>
                                </div>
                            </flexbox>
                        </flexbox-item>
                        <flexbox-item :span="0.5"
                                      class="b-cell">
                            <flexbox
                                    align="stretch"
                                    class="b-cell-b">
                                <div class="b-cell-name">客户联系人</div>
                                <div class="b-cell-value">
                                    <flexbox :gutter="0"
                                             wrap="wrap"
                                             style="padding: 0px 10px 10px 0px;">
                                        <div>
                                            {{dataDetail.name}}&nbsp;
                                        </div>
                                    </flexbox>
                                </div>
                            </flexbox>
                        </flexbox-item>
                        <flexbox-item :span="0.5"
                                      class="b-cell">
                            <flexbox
                                    align="stretch"
                                    class="b-cell-b">
                                <div class="b-cell-name">联系人电话</div>
                                <div class="b-cell-value">
                                    <flexbox :gutter="0"
                                             wrap="wrap"
                                             style="padding: 0px 10px 10px 0px;">
                                        <div>
                                            {{dataDetail.phone}}
                                        </div>
                                    </flexbox>
                                </div>
                            </flexbox>
                        </flexbox-item>
                        <flexbox-item :span="0.5"
                                      class="b-cell">
                            <flexbox
                                    align="stretch"
                                    class="b-cell-b">
                                <div class="b-cell-name">投诉日期</div>
                                <div class="b-cell-value">
                                    <flexbox :gutter="0"
                                             wrap="wrap"
                                             style="padding: 0px 10px 10px 0px;">
                                        <div>
                                            {{dataDetail.create_time | filterTimestampToFormatTime}}
                                        </div>
                                    </flexbox>
                                </div>
                            </flexbox>
                        </flexbox-item>
                        <flexbox-item :span="0.5"
                                      class="b-cell">
                            <flexbox
                                    align="stretch"
                                    class="b-cell-b">
                                <div class="b-cell-name">投诉内容</div>
                                <div class="b-cell-value">
                                    <flexbox :gutter="0"
                                             wrap="wrap"
                                             style="padding: 0px 10px 10px 0px;">
                                        <div>
                                            {{dataDetail.content}}
                                        </div>
                                    </flexbox>
                                </div>
                            </flexbox>
                        </flexbox-item>
                    </flexbox>

                    <!-- 审核信息 -->
                    <create-sections title="审核信息"
                                     class="create-sections">
                        <examine-info :id="dataDetail.id"
                                      class="create-sections-content list-box"
                                      examineType="crm_complaint"
                                      :itemData="dataDetail"
                                      :flow_id="dataDetail.flow_id">
                        </examine-info>
                    </create-sections>

                    <!--附件信息-->
                    <create-sections title="附件信息"
                                     class="create-sections">
                        <relativeFiles
                                :id="dataDetail.id"
                                crmType="complaint"
                                :isSeas="true"
                                :detail="dataDetail"
                        >

                        </relativeFiles>
                    </create-sections>
                </div>
            </flexbox>
        </slide-view>
        <new-dialog v-if="showNewDialog1"
                    :formData="formData"
                    :dialogTitle="dialogTitle"
                    :newLoading="newLoading"
                    @close="newClose"
                    @submitBtn="submitBtn">
        </new-dialog>
    </div>
</template>

<script>
    import SlideView from "@/components/SlideView"
    import relativeFiles from './RelativeFiles'
    import ExamineInfo from '@/components/Examine/ExamineInfo1'
    import CreateSections from '@/components/CreateSections'
    import { saveComplaint, updateComplaint} from '@/api/oamanagement/complaint'
    import newDialog from './newDialog'
    export default {
        name:  "complaintDetail",
        data() {
            return {
                id: "",
                loading: false,
                showNewDialog1: false,
                type: 'created',
                dialogTitle: '',
                newLoading: false,
                formData: {
                    company: "",
                    phone: "",
                    name: "",
                    content: "",
                    file_id: [],
                    id: ''
                }
            }
        },
        components: { SlideView, ExamineInfo, CreateSections, relativeFiles, newDialog },
        methods: {
            hideView() {
                this.$emit('hide-view')
            },
            handleClick() {
                for (var item in this.formData) {
                     this.formData[item] = this.dataDetail[item]
                }
                this.type = 'editor'
                this.showNewDialog1 = true
            },
            submitBtn(data) {
                updateComplaint(data).then(res => {
                    if (res.code == 200) {
                        this.newLoading = false
                        this.$message.success('编辑成功')
                        this.formData = {}
                        var timer =  setTimeout(function() {
                            window.location.reload()
                        },1000)
                    }
                }).catch(err => {
                    this.newLoading = false
                })
            },
            // 关闭新建页面
            newClose() {
                console.log(this.$route.query.routerKey)
                if (this.$route.query.routerKey == 1) {
                    this.showNewDialog1 = false
                    this.showNewDialog = false
                    this.$router.go(-1)
                } else {
                    this.showNewDialog1 = false
                    this.showNewDialog = false
                }
            }
        },
        props: {
            dataDetail: {
                type: Object,
                default: {}
            },
            noListenerClass: {
                type: Array,
                default: () => {
                    return ['list-box', 'head-handle-button']
                }
            }
        }
    }
</script>

<style scoped lang="scss">
    .detail-header {
        position: relative;
        min-height: 60px;
    .header-name {
        font-size: 14px;
        color: #333333;
        flex: 1;
    }
    .header-close {
        display: block;
        width: 40px;
        height: 40px;
        margin-left: 20px;
        padding: 10px;
        cursor: pointer;
    }
    }
    .b-cell {
        .b-cell-b {
            width: auto;
            padding: 8px;
            line-height: 22px;
            .b-cell-name {
                width: 100px;
                margin-right: 10px;
                font-size: 13px;
                flex-shrink: 0;
                color: #777;
            }
            .b-cell-value {
                font-size: 13px;
                color: #333;
            }
            .b-cell-foot {
                flex-shrink: 0;
                display: block;
                width: 15px;
                height: 15px;
                margin-left: 8px;
            }
        }
    }
    .detail-body {
        flex: 1;
        overflow-y: auto;
        width: 100%;
    }
    .f-item {
        padding: 3px 0;
        height: 25px;
        .f-img {
            position: block;
            width: 15px;
            height: 15px;
            padding: 0 1px;
            margin-right: 8px;
        }
        .f-name {
            color: #666;
            font-size: 12px;
            margin-right: 10px;
        }
    }
    .d-view {
        position: fixed;
        width: 926px;
        top: 60px;
        bottom: 0px;
        right: 0px;
    }
</style>