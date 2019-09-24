<template>
    <div class="complaint_box complaint-main-container">
        <el-button type="primary" class="newBtn" @click="createBtn">新建客诉</el-button>
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="客户投诉" name="pending">
                <div class="box-list">
                    <el-table
                            :data="tableData"
                            @row-click="tableClick"
                            class="list-box"
                            style="width: 100%">
                        <el-table-column
                                prop="company"
                                label="公司名称">
                        </el-table-column>
                        <el-table-column
                                prop="name"
                                label="姓名"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="name"
                                label="电话"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="content"
                                label="投诉内容"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="create_time"
                                label="日期"
                                width="180">
                        </el-table-column>
                        <!-- <el-table-column
                                 prop="name"
                                 label="操作"
                                 width="180">
                             <template slot-scope="scope">
                                 <el-button @click="changClick(scope.$index,scope.row)">确认处理</el-button>
                             </template>
                         </el-table-column>-->
                    </el-table>
                </div>
            </el-tab-pane>
        </el-tabs>
        <div class="pagination_box">
            <el-pagination
                    background
                    @current-change="paginaClick"
                    layout="prev, pager, next"
                    :total="count">
            </el-pagination>
        </div>
        <new-dialog v-if="showNewDialog"
                    :formData="formData"
                    :dialogTitle="dialogTitle"
                    :newLoading="newLoading"
                    @close="newClose"
                    @submitBtn="submitBtn">
        </new-dialog>
        <complaint-detail v-if="showDview" @hide-view="showDview=false" :detail="detail" :id="detail.id"></complaint-detail>
    </div>
</template>

<script>
    import complaintDetail from './components/complaintDetail'
    import { getComplaintList, updateComplaintOne, saveComplaint } from '@/api/oamanagement/complaint'
    import newDialog from './components/newDialog'
    export default {
        name: "index",
        data() {
            return {
                activeName: "pending",
                tableData: [],
                pageSize: 10,
                pageIndex: 1,
                dialogTitle: '',
                newLoading: false,
                count: 0,
                // 新建数据
                formData: {
                    company: "",
                    phone: "",
                    name: "",
                    content: ""
                },
                showNewDialog: false,
                showDview: false,
                rowID: "",
                detail: ""
            }
        },
        components: {
            complaintDetail,
            newDialog
        },
        created() {
            this.fetchData()
        },
        methods: {
            fetchData() {
                getComplaintList({
                    status: this.activeName == 'pending' ? 0 : 1,
                    limit: this.pageSize,
                    page: this.pageIndex
                }).then(res => {
                    this.tableData = res.data.list
                    this.count = res.data.dataCount
                })
            },
            handleClick() {
                // this.fetchData()
            },
            changClick(index, row) {
                this.$confirm('确认处理过该投诉, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    updateComplaintOne({
                        id: row.id,
                        status: 1
                    }).then(res => {
                        if (res.code == 200) {
                            this.tableData.splice(index, 1)
                            this.$message({
                                message: '处理成功!',
                                type: 'success'
                            })
                        }
                    })
                }).catch(() => {

                })
            },
            createBtn() {
                this.showNewDialog = true
            },
            // 关闭新建页面
            newClose() {
                if (this.$route.query.routerKey == 1) {
                    this.showNewDialog = false
                    this.$router.go(-1)
                } else {
                    this.showNewDialog = false
                }
            },
            submitBtn(data) {
                saveComplaint(data).then(res => {
                    if (res.code == 200) {
                        this.newLoading = false
                        this.$message.success('新建成功')
                        this.formData = {}
                        this.fetchData()
                        this.newClose()
                    }
                }).catch(err => {
                    this.newLoading = false
                    this.$message.error('新建失败')
                })
            },
            paginaClick(val) {
                this.pageIndex = val
                this.fetchData();
            },
            tableClick(item) {
               this.detail = item
               this.showDview = true
            }
        }
    }
</script>

<style scoped lang="scss">
    .complaint_box{
        background:#fff;
        width:1130px;
        padding:25px;
        position:relative;
        .newBtn{
            position:absolute;
            top:20px;
            right:10px;
            z-index:999;
        }
    }

    .pagination_box{
        text-align:center;
        margin-top:55px;
    }
</style>