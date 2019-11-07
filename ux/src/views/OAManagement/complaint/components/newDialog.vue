<template>
    <create-view :body-style="{height: '100%'}">
        <div class="new-journal"
             v-loading="newLoading">
            <div slot="header"
                 class="header">
                <span class="text">{{ dialogTitle }}</span>
                <img class="el-icon-close rt"
                     src="@/assets/img/task_close.png"
                     @click="close"
                     alt="">
            </div>
            <div class="content">
                <el-form :model="formData" ref="formData" :rules="rules">
                    <el-form-item label="客户公司" prop="company">
                        <el-input v-model="formData.company" placeholder="请输入客户公司"></el-input>
                    </el-form-item>
                    <el-form-item label="联系人" prop="name">
                        <el-input v-model="formData.name" placeholder="请输入联系人"></el-input>
                    </el-form-item>
                    <el-form-item label="联系电话" prop="phone">
                        <el-input v-model="formData.phone" placeholder="请输入联系电话"></el-input>
                    </el-form-item>
                    <!--<el-form-item label="投诉类型" prop="type">
                        <el-select style="width:100%;" v-model = "formData.type">
                            <el-option :label="item.type" :value="item.id" v-for="(item, index) in formList" :key="index"></el-option>
                        </el-select>
                    </el-form-item>-->
                    <el-form-item label="投诉内容" prop="content">
                        <el-input v-model="formData.content" placeholder="请输入投诉内容" type="textarea"></el-input>
                    </el-form-item>
                </el-form>
                <!-- 图片附件 -->
                <div class="img-accessory">
                    <div class="img-box">
                        <el-upload ref="imageUpload"
                                   :action="crmFileSaveUrl"
                                   :headers="httpHeader"
                                   name="img[]"
                                   multiple
                                   accept="image/*"
                                   list-type="picture-card"
                                   :on-preview="handleFilePreview"
                                   :before-remove="beforeRemove"
                                   :on-success="imgFileUploadSuccess"
                                   :file-list="imageFileList">
                            <p class="add-img">
                                <span class="el-icon-picture"></span>
                                <span>添加图片</span>
                            </p>
                            <i class="el-icon-plus"></i>
                        </el-upload>
                    </div>
                    <p class="add-accessory">
                        <el-upload ref="fileUpload"
                                   :action="crmFileSaveUrl"
                                   :headers="httpHeader"
                                   :before-upload="beforeAvatarUpload"
                                   name="file[]"
                                   multiple
                                   accept="*.*"
                                   :on-preview="handleFilePreview"
                                   :before-remove="handleFileRemove"
                                   :on-success="fileUploadSuccess"
                                   :file-list="fileList">
                    <p>
                        <img src="@/assets/img/relevance_file.png"
                             alt="">
                        添加附件
                    </p>
                    </el-upload>
                    </p>
                </div>
            </div>
            <div class="btn-group">
                <el-button @click="submitBtn"
                    type="primary">提交</el-button>
                <el-button @click="close">取消</el-button>
            </div>
        </div>
    </create-view>
</template>

<script>
    import axios from 'axios'
    import { getComplaintType } from '@/api/oamanagement/complaint'
    import { crmFileSave, crmFileDelete, crmFileSaveUrl } from '@/api/common'
    import { baseUrl } from "@/utils/env"
    import CreateView from '@/components/CreateView'
    // 部门员工优化版
    import membersDep from '@/components/selectEmployee/membersDep'
    // 关联业务 - 选中列表
    import relatedBusiness from '@/components/relatedBusiness'
    import { regexIsCRMMobile } from '@/utils'
    export default {
        components: {
            CreateView,
            membersDep,
            relatedBusiness
        },
        computed: {
            crmFileSaveUrl() {
                return baseUrl + crmFileSaveUrl
            },
            httpHeader() {
                return {
                    authKey: axios.defaults.headers.authKey,
                    sessionId: axios.defaults.headers.sessionId
                }
            }
        },
        data() {
            var validatePhone = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入手机号码'))
                } else if ( !regexIsCRMMobile(value)) {
                    callback(new Error('请输入正确的手机格式'))
                } else {
                    callback()
                }
            }
            return {
                // 表格数据
                formList: [],
                imageFileList: [],
                fileList: [],
                rules: {
                    name: [
                        { required: true, message: '请输入联系人姓名', trigger: 'blur' }
                    ],
                    company: [
                        { required: true, message: '请输入客户公司名称', trigger: 'blur' }
                    ],
                    type: [
                        { required: true, message: '请选择类型', trigger: 'change' }
                    ],
                    phone: [
                        { required: true, validator: validatePhone, trigger: 'blur' }
                    ],
                    content: [
                        { required: true, message: '请输入投诉内容', trigger: 'blur' }
                    ]
                }

            }
        },
        props: {
            formData: {
                type: Object,
                default: () => {
                    return {}
                }
            },
            dialogTitle: {
                type: String,
                default: '新建客诉'
            },
            // 附件
            accessoryFileList: {
                type: Array,
                default: () => {
                    return []
                }
            },
            newLoading: Boolean
        },
        mounted() {
            document.body.appendChild(this.$el)
        },
        methods: {
           /* fetchData() {
                getComplaintType().then(res => {
                    this.formList = res.data
                })
            },*/
            close() {
                if (this.$route.query.routerKey == 1) {
                    this.$router.go(-1)
                } else {
                    this.$emit('close')
                }
            },
            // 提交按钮
            submitBtn() {
               this.$refs['formData'].validate(valid => {
                   var img = []
                   var file = []
                   if (valid) {
                       if (this.fileList) {
                           img = this.fileList.map(function(file, index, array) {
                               if (file.response) {
                                   return file.response.data[0].file_id
                               } else if (file.file_id) {
                                   return file.file_id
                               }
                               return ''
                           })
                       }
                       if (this.imageFileList) {
                           file = this.imageFileList.map(function(file, index, array) {
                               if (file.response) {
                                   return file.response.data[0].file_id
                               } else if (file.file_id) {
                                   return file.file_id
                               }
                               return ''
                           })
                       }
                       if (img || file) {
                           this.formData['file'] = img.concat(file)
                       }
                       this.$emit('submitBtn', this.formData)
                   }
               })
            },
            popoverSubmit(members, dep) {
                this.$set(this.formData, 'sentWhoList', members)
                this.$set(this.formData, 'depData', dep)
            },
            // 取消
            handleClose() {
                this.dialogVisible = false
            },
            checkInfos(val) {
                this.relevanceAll = val
            },
            beforeRemove() {
                return this.$confirm('此操作将永久删除该图片, 是否继续？')
            },
            // 图片和附件
            // 上传图片
            imgFileUploadSuccess(response, file, fileList) {
                this.imageFileList = fileList
            },
            beforeAvatarUpload(file, fileList) {
                let accept = ['jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'pdf', 'plain', 'msword', 'vnd.ms-excel', 'vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                let ft = file.type.split('/')[1]
                if (accept.indexOf(ft) < 0) {
                    this.$message.error('暂不支持该格式')
                    return false
                }
            },
            // 查看图片
            handleFilePreview(file) {
                if (file.response || file.file_id) {
                    let perviewFile
                    if (file.response) {
                        perviewFile = {
                            url: file.response.data[0].path,
                            name: file.response.data[0].name
                        }
                    } else {
                        perviewFile = {
                            url: file.file_path,
                            name: file.name
                        }
                    }
                    this.$bus.emit('preview-image-bus', {
                        index: 0,
                        data: [perviewFile]
                    })
                }
            },
            beforeRemove(file, fileList) {
                if (file.response || file.file_id) {
                    let save_name
                    if (file.response) {
                        save_name = file.response.data[0].save_name
                    } else {
                        save_name = file.save_name
                    }
                    this.$confirm('您确定要删除该文件吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    })
                        .then(() => {
                            crmFileDelete({
                                save_name: save_name
                            })
                                .then(res => {
                                    this.$message.success(res.data)
                                    var removeIndex = this.getFileIndex(
                                        this.$refs.imageUpload.uploadFiles,
                                        save_name
                                    )
                                    if (removeIndex != -1) {
                                        this.$refs.imageUpload.uploadFiles.splice(removeIndex, 1)
                                    }
                                    removeIndex = this.getFileIndex(this.imgFileList, save_name)
                                    if (removeIndex != -1) {
                                        this.imgFileList.splice(removeIndex, 1)
                                    }
                                })
                                .catch(() => {})
                        })
                        .catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消操作'
                            })
                        })
                    return false
                } else {
                    return true
                }
            },
            // 附件索引
            getFileIndex(files, save_name) {
                var removeIndex = -1
                for (let index = 0; index < files.length; index++) {
                    const item = files[index]
                    let item_save_name
                    if (item.response) {
                        item_save_name = item.response.data[0].save_name
                    } else {
                        item_save_name = item.save_name
                    }
                    if (item_save_name == save_name) {
                        removeIndex = index
                        break
                    }
                }
                return removeIndex
            },
            fileUploadSuccess(response, file, fileList) {
                this.fileList = fileList
            },
            handleFileRemove(file, fileList) {
                console.log(file)
                if (file.response || file.file_id) {
                    let save_name
                    if (file.response) {
                        save_name = file.response.data[0].save_name
                    } else {
                        save_name = file.save_name
                    }
                    this.$confirm('您确定要删除该文件吗?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    })
                        .then(() => {
                            crmFileDelete({
                                save_name: save_name
                            })
                                .then(res => {
                                    this.$message.success(res.data)
                                    var removeIndex = this.getFileIndex(
                                        this.$refs.fileUpload.uploadFiles,
                                        save_name
                                    )
                                    if (removeIndex != -1) {
                                        this.$refs.fileUpload.uploadFiles.splice(removeIndex, 1)
                                    }
                                    removeIndex = this.getFileIndex(this.fileList, save_name)
                                    if (removeIndex != -1) {
                                        this.fileList.splice(removeIndex, 1)
                                    }
                                })
                                .catch(() => {})
                        })
                        .catch(() => {
                            this.$message({
                                type: 'info',
                                message: '已取消操作'
                            })
                        })
                    return false
                } else {
                    return true
                }
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

<style scoped lang="scss">
    .new-journal {
        display: flex;
        flex-direction: column;
        height: 100%;
        .header {
            height: 40px;
            line-height: 40px;
            padding: 0 0 0 10px;
            .el-icon-close {
                margin-right: 0;
                width: 40px;
                line-height: 40px;
                padding: 10px;
                cursor: pointer;
            }
            .text {
                font-size: 17px;
            }
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: auto;
            padding: 20px;
        }
        .btn-group {
            text-align: right;
            padding-right: 20px;
        }
        .content /deep/ .el-tabs {
            .el-tabs__header {
                .el-tabs__item {
                    height: 50px;
                    line-height: 50px;
                }
                .el-tabs__nav {
                    margin-left: 20px;
                    font-size: 13px;
                }
                .el-tabs__nav-wrap::after {
                    height: 1px;
                }
            }
            .el-tabs__content {
                padding: 0 20px;
            }
        }
    }

    .img-accessory {
        font-size: 12px;
        img {
            vertical-align: middle;
        }
        .img-box /deep/ .el-upload {
            width: 80px;
            height: 80px;
            line-height: 90px;
        }
        .img-box /deep/ .el-upload-list {
            .el-upload-list__item {
                width: 80px;
                height: 80px;
            }
        }
        .img-box {
            position: relative;
            margin-top: 40px;
            .add-img {
                position: absolute;
                left: 0;
                top: -30px;
                height: 20px;
                line-height: 20px;
                margin-bottom: 10px;
                color: #3e84e9;
            }
        }
        .add-accessory {
            margin-top: 25px;
            margin-bottom: 20px;
            color: #3e84e9;
        }
    }
    .form {
        flex: 1;
        margin-top: 10px;
        padding: 0 20px;
        overflow-y: scroll;
        .row-list {
            margin-bottom: 20px;
            padding-bottom: 10px;
            .item-label {
                margin-bottom: 9px;
                display: block;
                font-size: 12px;
                // padding-bottom: 10px;
            }
            .el-textarea {
                .el-textarea__inner {
                    resize: none;
                }
            }
        }

        .sent-who {
            margin-bottom: 15px;
            .label,
            img,
            .k-img {
                vertical-align: middle;
            }
            .label {
                margin-right: 15px;
                font-size: 12px;
            }
            img {
                cursor: pointer;
            }
            .item-name {
                margin-right: 7px;
            }
            .k-img {
                width: 25px;
                height: 25px;
                border-radius: 17.5px;
                margin-right: 7px;
            }
            .must {
                color: #f56c6c;
                font-size: 12px;
                margin-top: 5px;
            }
            .sent-img {
                width: 24px;
                height: 24px;
            }
        }
    }
</style>